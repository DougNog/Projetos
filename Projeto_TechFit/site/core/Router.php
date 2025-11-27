<?php
class Router {
    private $routes = [
        'GET'  => [],
        'POST' => []
    ];

    public function get($path, $action) {
        $this->routes['GET'][$this->normalize($path)] = $action;
    }

    public function post($path, $action) {
        $this->routes['POST'][$this->normalize($path)] = $action;
    }

    private function normalize($uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    public function dispatch($uri, $method) {
        $path = $this->normalize($uri);
        $method = strtoupper($method);

        // Ignorar requisições para arquivos estáticos
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo "404 - Página não encontrada: {$path}";
            return;
        }

        list($controllerName, $methodName) = explode('@', $this->routes[$method][$path]);

        $controllerFile = __DIR__ . "/../app/controllers/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller não encontrado.";
            return;
        }

        require_once $controllerFile;
        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Classe do controller {$controllerName} não encontrada.";
            return;
        }

        $controller = new $controllerName();
        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            echo "Método {$methodName} do controller {$controllerName} não encontrado.";
            return;
        }

        return $controller->$methodName();
    }
}
