<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = conectarBanco();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar mensagens
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;

    if ($usuario_id) {
        // Mensagens para um usuário específico
        $stmt = $pdo->prepare("
            SELECT m.*, f.nome as autor_nome
            FROM mensagens m
            LEFT JOIN funcionarios f ON m.autor_id = f.id
            WHERE
                (m.segmento_modalidade IS NULL OR m.segmento_modalidade = (
                    SELECT modalidade FROM alunos WHERE id = ?
                ))
                AND (m.segmento_frequencia = 0 OR m.segmento_frequencia <= (
                    SELECT checkins_mes FROM alunos WHERE id = ?
                ))
            ORDER BY m.data_envio DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
    } else {
        // Todas as mensagens (admin)
        $stmt = $pdo->prepare("
            SELECT m.*, f.nome as autor_nome
            FROM mensagens m
            LEFT JOIN funcionarios f ON m.autor_id = f.id
            ORDER BY m.data_envio DESC
        ");
        $stmt->execute();
    }

    $mensagens = $stmt->fetchAll();
    echo json_encode($mensagens);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enviar nova mensagem
    $dados = json_decode(file_get_contents('php://input'), true);
    $dados = validarEntrada($dados, ['titulo', 'corpo', 'autor_id']);
    $dados = array_map('sanitizar', $dados);

    $segmento_modalidade = isset($dados['segmento_modalidade']) ? $dados['segmento_modalidade'] : null;
    $segmento_frequencia = isset($dados['segmento_frequencia']) ? (int)$dados['segmento_frequencia'] : 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO mensagens (titulo, corpo, segmento_modalidade, segmento_frequencia, autor_id, data_envio)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $dados['titulo'],
            $dados['corpo'],
            $segmento_modalidade,
            $segmento_frequencia,
            (int)$dados['autor_id']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Mensagem enviada com sucesso',
            'id' => $pdo->lastInsertId()
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao enviar mensagem: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
