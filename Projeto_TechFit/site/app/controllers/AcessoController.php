<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Acesso.php';

class AcessoController extends Controller {
    public function index() {
        $this->requireLogin();
        $acessoModel = new Acesso();
        
        // Se for admin, mostrar todos os acessos. Se for aluno, apenas os dele.
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $acessos = $acessoModel->todos();
        } else {
            $acessos = $acessoModel->doUsuario($_SESSION['usuario']['id']);
        }

        $this->view('acessos/index', [
            'acessos' => $acessos,
            'titulo'  => $_SESSION['usuario']['perfil'] === 'admin' ? 'Todos os Acessos' : 'Meu Histórico de Acessos',
            'isAdmin' => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function adminIndex() {
        $this->requireLogin('admin');
        $acessoModel = new Acesso();
        $acessos = $acessoModel->todos();

        $this->view('acessos/admin', [
            'acessos' => $acessos,
            'titulo'  => 'Acessos - Administração'
        ]);
    }

    public function gerarQRCode() {
        $this->requireLogin();
        
        require_once __DIR__ . '/../models/Usuario.php';
        require_once __DIR__ . '/../../config/database.php';
        
        $usuarioModel = new Usuario();
        $pdo = Database::getInstance();
        
        // Se for admin, permitir gerar QR Code para qualquer aluno
        $usuarioId = $_GET['usuario_id'] ?? $_SESSION['usuario']['id'];
        
        // Verificar se é admin tentando gerar para outro usuário
        if ($_SESSION['usuario']['perfil'] === 'admin' && isset($_GET['usuario_id'])) {
            $usuario = $usuarioModel->find($usuarioId);
            $titulo = 'QR Code - ' . ($usuario['nome'] ?? 'Aluno');
        } else {
            // Aluno só pode ver o próprio QR Code
            if ($usuarioId != $_SESSION['usuario']['id']) {
                $this->redirect('/acessos/qrcode');
            }
            $usuario = $usuarioModel->find($usuarioId);
            $titulo = 'Meu QR Code';
        }
        
        // Verificar se já existe QR Code
        $sql = "SELECT codigo_qr FROM usuarios_qrcode WHERE usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        $existente = $stmt->fetch();
        
        if ($existente) {
            $codigoQR = $existente['codigo_qr'];
        } else {
            // Gerar código QR único
            $codigoQR = bin2hex(random_bytes(16));
            
            // Salvar no banco
            $sql = "INSERT INTO usuarios_qrcode (usuario_id, codigo_qr) 
                    VALUES (:usuario_id, :codigo_qr)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuarioId,
                'codigo_qr' => $codigoQR
            ]);
        }
        
        // Se for admin, buscar lista de alunos para seleção
        $alunos = [];
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $todosUsuarios = $usuarioModel->all();
            $alunos = array_filter($todosUsuarios, function($u) {
                return $u['perfil'] === 'aluno';
            });
        }
        
        $this->view('acessos/qrcode', [
            'codigoQR' => $codigoQR,
            'usuario' => $usuario,
            'titulo' => $titulo,
            'alunos' => $alunos,
            'usuarioId' => $usuarioId,
            'isAdmin' => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function registrarEntrada() {
        require_once __DIR__ . '/../../config/database.php';
        
        $codigo = $_POST['codigo'] ?? null;
        $tipo = $_POST['tipo'] ?? 'qrcode';
        
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Código não fornecido']);
            return;
        }
        
        $pdo = Database::getInstance();
        $acessoModel = new Acesso();
        
        // Buscar usuário pelo QR Code
        $sql = "SELECT u.id FROM usuarios u
                JOIN usuarios_qrcode uq ON u.id = uq.usuario_id
                WHERE uq.codigo_qr = :codigo AND uq.ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['erro' => 'QR Code inválido']);
            return;
        }
        
        $acessoId = $acessoModel->registrarAcesso($usuario['id'], $tipo, $codigo);
        
        // Atualizar check-ins do mês
        $sql = "UPDATE usuarios SET checkins_mes = checkins_mes + 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $usuario['id']]);
        
        echo json_encode(['sucesso' => true, 'acesso_id' => $acessoId]);
    }

    public function relatorioUtilizacao() {
        $this->requireLogin('admin');
        $acessoModel = new Acesso();
        
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        
        $relatorio = $acessoModel->relatorioUtilizacao($dataInicio, $dataFim);
        
        $this->view('acessos/relatorio', [
            'relatorio' => $relatorio,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'titulo' => 'Relatório de Utilização'
        ]);
    }
}
