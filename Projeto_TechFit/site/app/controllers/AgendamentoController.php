<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/Agendamento.php';

class AgendamentoController extends Controller {
    public function index() {
        // Permitir acesso para alunos e admins
        $this->requireLogin();
        
        // Se for aluno, verificar perfil. Admin pode ver tudo.
        if ($_SESSION['usuario']['perfil'] === 'aluno') {
            $this->requireLogin('aluno');
        }

        $turmaModel = new Turma();
        $agendamentoModel = new Agendamento();
        require_once __DIR__ . '/../models/Modalidade.php';
        $modalidadeModel = new Modalidade();

        $turmas = $turmaModel->proximasTurmas();
        $modalidades = $modalidadeModel->all();
        
        // Se for admin, mostrar todos os agendamentos. Se for aluno, apenas os dele.
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $meusAgendamentos = $agendamentoModel->all();
        } else {
            $meusAgendamentos = $agendamentoModel->doUsuario($_SESSION['usuario']['id']);
        }
        
        // Adicionar informações de ocupação para cada turma
        foreach ($turmas as &$turma) {
            $contagem = $turmaModel->contagemPorStatus($turma['id']);
            $ocupadas = (int)($contagem['confirmados'] ?? 0);
            $ocupacao = $turma['vagas'] > 0 ? round(($ocupadas / $turma['vagas']) * 100, 1) : 0;
            $turma['ocupadas'] = $ocupadas;
            $turma['ocupacao_percentual'] = $ocupacao;
        }

        $this->view('agendamentos/index', [
            'turmas'           => $turmas,
            'meusAgendamentos' => $meusAgendamentos,
            'modalidades'      => $modalidades,
            'titulo'           => $_SESSION['usuario']['perfil'] === 'admin' ? 'Agendamentos - Todos' : 'Meus Agendamentos',
            'isAdmin'          => $_SESSION['usuario']['perfil'] === 'admin'
        ]);
    }

    public function store() {
        $this->requireLogin();
        // Apenas alunos podem agendar. Admin pode ver mas não agendar para si mesmo.
        if ($_SESSION['usuario']['perfil'] !== 'aluno') {
            $_SESSION['mensagem_sucesso'] = 'Apenas alunos podem fazer agendamentos.';
            $this->redirect('/agendamentos');
        }
        
        $agendamentoModel = new Agendamento();
        
        // Verificar se é agendamento de turma ou agendamento livre
        $turmaId = $_POST['turma_id'] ?? null;
        
        if ($turmaId) {
            // Agendamento de turma pré-definida
            $agendamentoModel->criarComEspera($_SESSION['usuario']['id'], $turmaId);
            $_SESSION['mensagem_sucesso'] = 'Agendamento realizado com sucesso!';
        } else {
            // Agendamento livre (data e horário personalizados)
            $data = $_POST['data'] ?? null;
            $horarioInicio = $_POST['horario_inicio'] ?? null;
            $horarioFim = $_POST['horario_fim'] ?? null;
            $modalidade = $_POST['modalidade'] ?? null;
            $observacoes = $_POST['observacoes'] ?? null;
            
            if (!$data || !$horarioInicio || !$horarioFim || !$modalidade) {
                $_SESSION['mensagem_erro'] = 'Por favor, preencha todos os campos obrigatórios.';
                $this->redirect('/agendamentos');
            }
            
            // Validar que a data não seja no passado
            if (strtotime($data) < strtotime('today')) {
                $_SESSION['mensagem_erro'] = 'Não é possível agendar para datas passadas.';
                $this->redirect('/agendamentos');
            }
            
            $agendamentoModel->criarAgendamentoLivre(
                $_SESSION['usuario']['id'],
                $data,
                $horarioInicio,
                $horarioFim,
                $modalidade,
                $observacoes
            );
            
            $_SESSION['mensagem_sucesso'] = 'Agendamento personalizado realizado com sucesso! Aguarde confirmação do administrador.';
        }
        
        $this->redirect('/agendamentos');
    }

    public function cancelar() {
        $this->requireLogin();
        $id = $_POST['id'] ?? null;
        if ($id) {
            $agendamentoModel = new Agendamento();
            // Verificar se o agendamento pertence ao usuário ou se é admin
            $agendamento = $agendamentoModel->find($id);
            if ($agendamento && ($agendamento['usuario_id'] == $_SESSION['usuario']['id'] || $_SESSION['usuario']['perfil'] === 'admin')) {
                $agendamentoModel->delete($id);
                $_SESSION['mensagem_sucesso'] = 'Agendamento cancelado com sucesso!';
            }
        }
        $this->redirect('/agendamentos');
    }

    public function relatorioOcupacao() {
        $this->requireLogin('admin');
        $turmaModel = new Turma();
        
        $dataInicio = $_GET['data_inicio'] ?? null;
        $dataFim = $_GET['data_fim'] ?? null;
        
        $relatorio = $turmaModel->relatorioOcupacao($dataInicio, $dataFim);
        
        $this->view('agendamentos/relatorio', [
            'relatorio' => $relatorio,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'titulo' => 'Relatório de Ocupação'
        ]);
    }

    public function notificacoes() {
        $this->requireLogin();
        $turmaModel = new Turma();
        $notificacoes = $turmaModel->getNotificacoes($_SESSION['usuario']['id']);
        
        $this->view('agendamentos/notificacoes', [
            'notificacoes' => $notificacoes,
            'titulo' => 'Notificações'
        ]);
    }
}
