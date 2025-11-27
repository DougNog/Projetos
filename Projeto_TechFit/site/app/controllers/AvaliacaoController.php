<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Avaliacao.php';
require_once __DIR__ . '/../models/Usuario.php';

class AvaliacaoController extends Controller {
    public function index() {
        $this->requireLogin();
        $model = new Avaliacao();
        
        // Se for admin, mostrar todas as avaliações. Se for aluno, apenas as dele.
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $avaliacoes = $model->todasComAlunos();
        } else {
            $avaliacoes = $model->doUsuario($_SESSION['usuario']['id']);
        }

        $this->view('avaliacoes/index', [
            'avaliacoes' => $avaliacoes,
            'titulo'     => $_SESSION['usuario']['perfil'] === 'admin' ? 'Todas as Avaliações Físicas' : 'Minhas Avaliações Físicas',
            'isAdmin'    => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function nova() {
        $this->requireLogin('admin');
        $usuarioModel = new Usuario();
        $todosUsuarios = $usuarioModel->all();
        // Filtrar apenas alunos
        $alunos = array_filter($todosUsuarios, function($u) {
            return $u['perfil'] === 'aluno';
        });

        $this->view('avaliacoes/form', [
            'alunos' => $alunos,
            'titulo' => 'Nova Avaliação Física'
        ]);
    }

    public function store() {
        $this->requireLogin('admin');

        $usuarioId = $_POST['usuario_id'];
        $peso = (float)$_POST['peso'];
        $altura = (float)$_POST['altura'];
        $gordura = (float)($_POST['gordura_corporal'] ?? 0);
        $imc = $altura > 0 ? $peso / ($altura * $altura) : 0;

        $model = new Avaliacao();
        $avaliacaoId = $model->create([
            'usuario_id'      => $usuarioId,
            'data_avaliacao'  => $_POST['data_avaliacao'],
            'peso'            => $peso,
            'altura'          => $altura,
            'imc'             => $imc,
            'gordura_corporal'=> $gordura,
            'observacoes'     => $_POST['observacoes'] ?? null
        ]);

        // Gerar sugestão de treino automaticamente
        require_once __DIR__ . '/../models/TreinoPersonalizado.php';
        $treinoModel = new TreinoPersonalizado();
        $treinoModel->gerarSugestao($usuarioId, $avaliacaoId);

        $this->redirect('/avaliacoes');
    }

    public function evolucao() {
        $this->requireLogin();
        $model = new Avaliacao();
        $usuarioModel = new Usuario();
        
        $usuarioId = $_GET['usuario_id'] ?? $_SESSION['usuario']['id'];
        
        // Se for admin, permitir ver evolução de qualquer aluno
        if ($_SESSION['usuario']['perfil'] === 'admin' && isset($_GET['usuario_id'])) {
            $usuario = $usuarioModel->find($usuarioId);
            $evolucao = $model->evolucaoUsuario($usuarioId);
            $titulo = 'Evolução Física - ' . ($usuario['nome'] ?? 'Aluno');
        } else {
            $evolucao = $model->evolucaoUsuario($_SESSION['usuario']['id']);
            $titulo = 'Minha Evolução Física';
        }
        
        // Se for admin, buscar lista de alunos para seleção
        $alunos = [];
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $todosUsuarios = $usuarioModel->all();
            $alunos = array_filter($todosUsuarios, function($u) {
                return $u['perfil'] === 'aluno';
            });
        }

        $this->view('avaliacoes/evolucao', [
            'evolucao' => $evolucao,
            'titulo' => $titulo,
            'alunos' => $alunos,
            'usuarioId' => $usuarioId,
            'isAdmin' => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function treinos() {
        $this->requireLogin();
        require_once __DIR__ . '/../models/TreinoPersonalizado.php';
        $treinoModel = new TreinoPersonalizado();
        $usuarioModel = new Usuario();
        
        $usuarioId = $_GET['usuario_id'] ?? $_SESSION['usuario']['id'];
        
        // Se for admin, permitir ver treinos de qualquer aluno
        if ($_SESSION['usuario']['perfil'] === 'admin' && isset($_GET['usuario_id'])) {
            $usuario = $usuarioModel->find($usuarioId);
            $treinos = $treinoModel->doUsuario($usuarioId);
            $titulo = 'Treinos Personalizados - ' . ($usuario['nome'] ?? 'Aluno');
        } else {
            $treinos = $treinoModel->doUsuario($_SESSION['usuario']['id']);
            $titulo = 'Meus Treinos Personalizados';
        }
        
        // Se for admin, buscar lista de alunos para seleção
        $alunos = [];
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $todosUsuarios = $usuarioModel->all();
            $alunos = array_filter($todosUsuarios, function($u) {
                return $u['perfil'] === 'aluno';
            });
        }

        $this->view('avaliacoes/treinos', [
            'treinos' => $treinos,
            'titulo' => $titulo,
            'alunos' => $alunos,
            'usuarioId' => $usuarioId,
            'isAdmin' => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function alertas() {
        $this->requireLogin('admin');
        $model = new Avaliacao();
        
        // Buscar usuários que precisam de alerta
        require_once __DIR__ . '/../models/Usuario.php';
        $usuarioModel = new Usuario();
        $alunos = $usuarioModel->all();
        
        $alertas = [];
        foreach ($alunos as $aluno) {
            if ($aluno['perfil'] === 'aluno' && $model->verificarNecessidadeAlerta($aluno['id'])) {
                $alertas[] = [
                    'usuario' => $aluno,
                    'ultima_avaliacao' => $model->ultimaAvaliacao($aluno['id'])
                ];
            }
        }

        $this->view('avaliacoes/alertas', [
            'alertas' => $alertas,
            'titulo' => 'Alertas de Avaliação'
        ]);
    }

    public function enviarAlerta() {
        $this->requireLogin('admin');
        $usuarioId = $_POST['usuario_id'] ?? null;
        
        if ($usuarioId) {
            $model = new Avaliacao();
            $model->criarAlerta($usuarioId);
            
            // Aqui você pode integrar com sistema de email/notificação
            $_SESSION['mensagem_sucesso'] = 'Alerta criado com sucesso!';
        }
        
        $this->redirect('/avaliacoes/alertas');
    }
}
