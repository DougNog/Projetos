<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Modalidade.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/Acesso.php';
require_once __DIR__ . '/../models/Agendamento.php';

class AdminController extends Controller {
    public function index() {
        $this->requireLogin('admin');

        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->all();

        $acessoModel = new Acesso();
        $acessos = $acessoModel->todos();

        $this->view('admin/dashboard', [
            'usuarios' => $usuarios,
            'acessos'  => $acessos,
            'titulo'   => 'Painel Administrativo'
        ]);
    }

    public function modalidades() {
        $this->requireLogin('admin');
        $model = new Modalidade();
        $modalidades = $model->all();

        $this->view('admin/modalidades', [
            'modalidades' => $modalidades,
            'titulo'      => 'Modalidades'
        ]);
    }

    public function salvarModalidade() {
        $this->requireLogin('admin');
        $model = new Modalidade();
        if (!empty($_POST['id'])) {
            $model->update($_POST['id'], ['nome' => $_POST['nome']]);
        } else {
            $model->create(['nome' => $_POST['nome']]);
        }
        $this->redirect('/admin/modalidades');
    }

    public function excluirModalidade() {
        $this->requireLogin('admin');
        $id = $_POST['id'] ?? null;
        if ($id) {
            $model = new Modalidade();
            $model->delete($id);
        }
        $this->redirect('/admin/modalidades');
    }

    public function turmas() {
        $this->requireLogin('admin');
        $turmaModel = new Turma();
        $modalidadeModel = new Modalidade();

        $todasTurmas = $turmaModel->all();
        $modalidades = $modalidadeModel->all();
        
        // Adicionar nome da modalidade para cada turma
        $turmas = [];
        foreach ($todasTurmas as $turma) {
            $modalidade = $modalidadeModel->find($turma['modalidade_id']);
            $turma['modalidade_nome'] = $modalidade['nome'] ?? 'N/A';
            $turmas[] = $turma;
        }

        $this->view('admin/turmas', [
            'turmas'      => $turmas,
            'modalidades' => $modalidades,
            'titulo'      => 'Turmas'
        ]);
    }

    public function salvarTurma() {
        $this->requireLogin('admin');
        $turmaModel = new Turma();

        $dados = [
            'modalidade_id' => $_POST['modalidade_id'],
            'instrutor'     => $_POST['instrutor'],
            'data'          => $_POST['data'],
            'inicio'        => $_POST['inicio'],
            'fim'           => $_POST['fim'],
            'vagas'         => $_POST['vagas']
        ];

        $turmaAntiga = null;
        if (!empty($_POST['id'])) {
            $turmaAntiga = $turmaModel->find($_POST['id']);
            $turmaModel->update($_POST['id'], $dados);
            
            // Verificar se houve alteração de horário
            if ($turmaAntiga && ($turmaAntiga['data'] != $dados['data'] || 
                $turmaAntiga['inicio'] != $dados['inicio'] || 
                $turmaAntiga['fim'] != $dados['fim'])) {
                $mensagem = "A turma de {$turmaAntiga['data']} das {$turmaAntiga['inicio']} foi alterada para {$dados['data']} das {$dados['inicio']} às {$dados['fim']}.";
                $turmaModel->notificarAlteracao($_POST['id'], 'alteracao_horario', $mensagem);
            }
        } else {
            $turmaModel->create($dados);
        }
        $this->redirect('/admin/turmas');
    }

    public function relatorios() {
        $this->requireLogin('admin');
        
        require_once __DIR__ . '/../models/Avaliacao.php';
        require_once __DIR__ . '/../models/Agendamento.php';
        
        $usuarioModel = new Usuario();
        $acessoModel = new Acesso();
        $agendamentoModel = new Agendamento();
        $avaliacaoModel = new Avaliacao();
        
        // Estatísticas gerais
        $totalUsuarios = count($usuarioModel->all());
        $totalAcessos = count($acessoModel->todos());
        $totalAgendamentos = count($agendamentoModel->all());
        $totalAvaliacoes = count($avaliacaoModel->all());
        
        require_once __DIR__ . '/../../config/database.php';
        $pdo = Database::getInstance();
        
        // Relatório de frequência
        $sql = "SELECT 
                    u.id,
                    u.nome,
                    u.checkins_mes,
                    COUNT(a.id) AS total_acessos
                FROM usuarios u
                LEFT JOIN acessos a ON u.id = a.usuario_id
                WHERE u.perfil = 'aluno'
                GROUP BY u.id, u.nome, u.checkins_mes
                ORDER BY u.checkins_mes DESC";
        $frequencia = $pdo->query($sql)->fetchAll();
        
        // Relatório de modalidades mais populares
        $sql = "SELECT 
                    m.nome AS modalidade,
                    COUNT(DISTINCT a.usuario_id) AS alunos,
                    COUNT(a.id) AS agendamentos
                FROM modalidades m
                LEFT JOIN turmas t ON m.id = t.modalidade_id
                LEFT JOIN agendamentos a ON t.id = a.turma_id
                GROUP BY m.id, m.nome
                ORDER BY agendamentos DESC";
        $modalidades = $pdo->query($sql)->fetchAll();
        
        $this->view('admin/relatorios', [
            'totalUsuarios' => $totalUsuarios,
            'totalAcessos' => $totalAcessos,
            'totalAgendamentos' => $totalAgendamentos,
            'totalAvaliacoes' => $totalAvaliacoes,
            'frequencia' => $frequencia,
            'modalidades' => $modalidades,
            'titulo' => 'Relatórios Gerenciais'
        ]);
    }

    public function usuarios() {
        $this->requireLogin('admin');
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->all();

        $this->view('admin/usuarios', [
            'usuarios' => $usuarios,
            'titulo' => 'Gerenciar Usuários'
        ]);
    }

    public function salvarUsuario() {
        $this->requireLogin('admin');
        $usuarioModel = new Usuario();
        
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'perfil' => $_POST['perfil'],
            'modalidade' => $_POST['modalidade'] ?? null
        ];
        
        if (!empty($_POST['senha'])) {
            $dados['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        }
        
        if (!empty($_POST['id'])) {
            if (empty($dados['senha'])) {
                unset($dados['senha']);
            }
            $usuarioModel->update($_POST['id'], $dados);
        } else {
            if (empty($dados['senha'])) {
                $dados['senha'] = password_hash('senha123', PASSWORD_DEFAULT);
            }
            $usuarioModel->create($dados);
        }
        
        $this->redirect('/admin/usuarios');
    }

    public function excluirUsuario() {
        $this->requireLogin('admin');
        $id = $_POST['id'] ?? null;
        if ($id) {
            $usuarioModel = new Usuario();
            $usuarioModel->delete($id);
        }
        $this->redirect('/admin/usuarios');
    }
}
