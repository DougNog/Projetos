<?php
class Controller {
    protected function view($view, $data = [], $layout = 'main') {
        extract($data);
        $viewFile   = __DIR__ . "/../app/views/{$view}.php";
        $layoutFile = __DIR__ . "/../app/views/layouts/{$layout}.php";

        if (!file_exists($viewFile)) {
            die("View {$view} não encontrada.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function redirect($path) {
        header("Location: {$path}");
        exit;
    }

    protected function isLogged() {
        return isset($_SESSION['usuario']);
    }

    protected function requireLogin($role = null) {
        if (!$this->isLogged()) {
            $this->redirect('/login');
        }
        if ($role && $_SESSION['usuario']['perfil'] !== $role) {
            $this->redirect('/');
        }
    }
}
