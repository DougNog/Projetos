<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$dados = json_decode(file_get_contents('php://input'), true);
if (!$dados) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Validar campos obrigatórios
$dados = validarEntrada($dados, ['email', 'senha']);
$dados = array_map('sanitizar', $dados);

$email = strtolower($dados['email']);
$senha = $dados['senha'];
$is_admin = isset($dados['is_admin']) ? (bool)$dados['is_admin'] : false;

try {
    $pdo = conectarBanco();

    // Determinar tabela baseada no tipo de usuário
    $tabela = $is_admin ? 'funcionarios' : 'alunos';
    $perfil = $is_admin ? 'admin' : 'aluno';

    // Buscar usuário
    $stmt = $pdo->prepare("SELECT id, nome, email, modalidade, checkins_mes FROM $tabela WHERE email = ? AND senha = ?");
    $stmt->execute([$email, $senha]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Login bem-sucedido
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'perfil' => $perfil,
                'modalidade' => $usuario['modalidade'] ?? null,
                'checkinsMes' => (int)($usuario['checkins_mes'] ?? 0)
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Credenciais inválidas']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
