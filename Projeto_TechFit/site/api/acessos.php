<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = conectarBanco();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar acessos
    $stmt = $pdo->prepare("
        SELECT a.*, al.nome
        FROM acessos a
        JOIN alunos al ON a.usuario_id = al.id
        ORDER BY a.data_acesso DESC
        LIMIT 100
    ");
    $stmt->execute();
    $acessos = $stmt->fetchAll();
    echo json_encode($acessos);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Registrar acesso
    $dados = json_decode(file_get_contents('php://input'), true);
    $dados = validarEntrada($dados, ['usuario_id', 'acao']);
    $dados = array_map('sanitizar', $dados);

    $usuario_id = (int)$dados['usuario_id'];
    $acao = in_array($dados['acao'], ['entrada', 'saida']) ? $dados['acao'] : 'entrada';

    try {
        // Verificar se o usuário existe
        $stmt = $pdo->prepare("SELECT nome FROM alunos WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            exit;
        }

        // Registrar acesso
        $stmt = $pdo->prepare("INSERT INTO acessos (usuario_id, acao, data_acesso) VALUES (?, ?, NOW())");
        $stmt->execute([$usuario_id, $acao]);

        // Atualizar contador de check-ins se for entrada
        if ($acao === 'entrada') {
            $stmt = $pdo->prepare("UPDATE alunos SET checkins_mes = checkins_mes + 1 WHERE id = ?");
            $stmt->execute([$usuario_id]);
        }

        echo json_encode([
            'success' => true,
            'message' => "Acesso de $acao registrado para {$usuario['nome']}",
            'id' => $pdo->lastInsertId()
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao registrar acesso: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
