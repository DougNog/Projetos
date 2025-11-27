<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController extends Controller {
    public function showLoginForm() {
        $this->view('auth/login', ['titulo' => 'Login - TechFit']);
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = [
                'id'     => $usuario['id'],
                'nome'   => $usuario['nome'],
                'email'  => $usuario['email'],
                'perfil' => $usuario['perfil']
            ];
            // Redirecionar conforme perfil
            if ($usuario['perfil'] === 'admin') {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/agendamentos');
            }
        } else {
            $this->view('auth/login', [
                'titulo' => 'Login - TechFit',
                'erro'   => 'E-mail ou senha incorretos.'
            ]);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
