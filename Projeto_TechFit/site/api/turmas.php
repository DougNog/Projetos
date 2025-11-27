<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = conectarBanco();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar turmas
    $stmt = $pdo->prepare("
        SELECT t.*, f.nome as instrutor_nome
        FROM turmas t
        LEFT JOIN funcionarios f ON t.instrutor_id = f.id
        ORDER BY t.data, t.inicio
    ");
    $stmt->execute();
    $turmas = $stmt->fetchAll();

    // Para cada turma, buscar os alunos inscritos
    foreach ($turmas as &$turma) {
        $stmt = $pdo->prepare("
            SELECT a.id, a.nome, ag.status
            FROM agendamentos ag
            JOIN alunos a ON ag.usuario_id = a.id
            WHERE ag.turma_id = ? AND ag.status = 'confirmado'
            ORDER BY a.nome
        ");
        $stmt->execute([$turma['id']]);
        $turma['alunos_inscritos'] = $stmt->fetchAll();
    }

    echo json_encode($turmas);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Criar nova turma (admin)
    $dados = json_decode(file_get_contents('php://input'), true);
    $dados = validarEntrada($dados, ['modalidade', 'instrutor', 'data', 'inicio', 'fim', 'vagas']);
    $dados = array_map('sanitizar', $dados);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO turmas (modalidade, instrutor, data, inicio, fim, vagas, inscritos, espera)
            VALUES (?, ?, ?, ?, ?, ?, 0, 0)
        ");
        $stmt->execute([
            $dados['modalidade'],
            $dados['instrutor'],
            $dados['data'],
            $dados['inicio'],
            $dados['fim'],
            (int)$dados['vagas']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Turma criada com sucesso',
            'id' => $pdo->lastInsertId()
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao criar turma: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
