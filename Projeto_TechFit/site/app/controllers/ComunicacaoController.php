<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Mensagem.php';
require_once __DIR__ . '/../models/Usuario.php';

class ComunicacaoController extends Controller {
    public function index() {
        $this->requireLogin();

        $msgModel = new Mensagem();
        $mensagens = $msgModel->inbox($_SESSION['usuario']['id']);

        $this->view('comunicacao/inbox', [
            'mensagens' => $mensagens,
            'titulo'    => 'Mensagens'
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

        $this->view('comunicacao/nova', [
            'alunos' => $alunos,
            'titulo' => 'Nova Mensagem'
        ]);
    }

    public function enviar() {
        $this->requireLogin();

        $destinatarioId = $_POST['destinatario_id'] ?? null;
        $assunto        = $_POST['assunto'] ?? '';
        $corpo          = $_POST['corpo'] ?? '';

        if ($destinatarioId && $corpo) {
            $msgModel = new Mensagem();
            $msgModel->create([
                'remetente_id'    => $_SESSION['usuario']['id'],
                'destinatario_id' => $destinatarioId,
                'assunto'         => $assunto,
                'corpo'           => $corpo
            ]);
        }
        $this->redirect('/mensagens');
    }

    public function enviarSegmentada() {
        $this->requireLogin('admin');
        
        $segmento = $_POST['segmento'] ?? 'todos';
        $valorSegmento = $_POST['valor_segmento'] ?? null;
        $assunto = $_POST['assunto'] ?? '';
        $corpo = $_POST['corpo'] ?? '';
        
        if ($assunto && $corpo) {
            $msgModel = new Mensagem();
            $enviadas = $msgModel->enviarSegmentada(
                $_SESSION['usuario']['id'],
                $segmento,
                $valorSegmento,
                $assunto,
                $corpo
            );
            
            $_SESSION['mensagem_sucesso'] = "Mensagem enviada para {$enviadas} destinatários.";
        }
        
        $this->redirect('/mensagens/segmentada');
    }

    public function segmentada() {
        $this->requireLogin('admin');
        require_once __DIR__ . '/../models/Modalidade.php';
        $modalidadeModel = new Modalidade();
        $modalidades = $modalidadeModel->all();
        
        $this->view('comunicacao/segmentada', [
            'modalidades' => $modalidades,
            'titulo' => 'Enviar Mensagem Segmentada'
        ]);
    }

    public function duvidas() {
        $this->requireLogin();
        require_once __DIR__ . '/../models/DuvidaSugestao.php';
        $duvidaModel = new DuvidaSugestao();
        
        if ($_SESSION['usuario']['perfil'] === 'admin') {
            $duvidas = $duvidaModel->todas();
        } else {
            $duvidas = $duvidaModel->doUsuario($_SESSION['usuario']['id']);
        }
        
        $this->view('comunicacao/duvidas', [
            'duvidas' => $duvidas,
            'titulo' => $_SESSION['usuario']['perfil'] === 'admin' ? 'Dúvidas e Sugestões' : 'Minhas Dúvidas'
        ]);
    }

    public function criarDuvida() {
        $this->requireLogin('aluno');
        
        $tipo = $_POST['tipo'] ?? 'duvida';
        $assunto = $_POST['assunto'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';
        
        if ($assunto && $mensagem) {
            require_once __DIR__ . '/../models/DuvidaSugestao.php';
            $duvidaModel = new DuvidaSugestao();
            $duvidaModel->create([
                'usuario_id' => $_SESSION['usuario']['id'],
                'tipo' => $tipo,
                'assunto' => $assunto,
                'mensagem' => $mensagem
            ]);
            
            $_SESSION['mensagem_sucesso'] = 'Sua mensagem foi enviada com sucesso!';
        }
        
        $this->redirect('/duvidas');
    }

    public function responderDuvida() {
        $this->requireLogin('admin');
        
        $id = $_POST['id'] ?? null;
        $resposta = $_POST['resposta'] ?? '';
        
        if ($id && $resposta) {
            require_once __DIR__ . '/../models/DuvidaSugestao.php';
            $duvidaModel = new DuvidaSugestao();
            $duvidaModel->update($id, [
                'resposta' => $resposta,
                'respondido_por' => $_SESSION['usuario']['id'],
                'data_resposta' => date('Y-m-d H:i:s')
            ]);
            
            $_SESSION['mensagem_sucesso'] = 'Resposta enviada com sucesso!';
        }
        
        $this->redirect('/duvidas');
    }
}
