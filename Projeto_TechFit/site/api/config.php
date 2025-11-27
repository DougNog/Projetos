<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'senaisp');
define('DB_NAME', 'techfit_academia');

// Conectar ao banco de dados
function conectarBanco() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro de conexão com o banco de dados: ' . $e->getMessage()]);
        exit;
    }
}

// Função para validar entrada
function validarEntrada($dados, $camposObrigatorios = []) {
    foreach ($camposObrigatorios as $campo) {
        if (!isset($dados[$campo]) || empty($dados[$campo])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo obrigatório ausente: $campo"]);
            exit;
        }
    }
    return $dados;
}

// Função para sanitizar entrada
function sanitizar($dados) {
    if (is_array($dados)) {
        return array_map('sanitizar', $dados);
    }
    return htmlspecialchars(strip_tags(trim($dados)), ENT_QUOTES, 'UTF-8');
}
?>
