<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = conectarBanco();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar agendamentos
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;

    if ($usuario_id) {
        // Agendamentos de um usuário específico
        $stmt = $pdo->prepare("
            SELECT a.*, t.modalidade, t.instrutor, t.data, t.inicio, t.fim, t.vagas, t.inscritos
            FROM agendamentos a
            JOIN turmas t ON a.turma_id = t.id
            WHERE a.usuario_id = ?
            ORDER BY t.data, t.inicio
        ");
        $stmt->execute([$usuario_id]);
    } else {
        // Todos os agendamentos
        $stmt = $pdo->prepare("
            SELECT a.*, t.modalidade, t.instrutor, t.data, t.inicio, t.fim, u.nome as usuario_nome
            FROM agendamentos a
            JOIN turmas t ON a.turma_id = t.id
            JOIN alunos u ON a.usuario_id = u.id
            ORDER BY a.data_agendamento DESC
        ");
        $stmt->execute();
    }

    $agendamentos = $stmt->fetchAll();
    echo json_encode($agendamentos);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Criar novo agendamento
    $dados = json_decode(file_get_contents('php://input'), true);
    $dados = validarEntrada($dados, ['usuario_id', 'turma_id']);
    $dados = array_map('sanitizar', $dados);

    $usuario_id = (int)$dados['usuario_id'];
    $turma_id = (int)$dados['turma_id'];

    try {
        // Verificar se a turma existe e tem vagas
        $stmt = $pdo->prepare("SELECT vagas, inscritos FROM turmas WHERE id = ?");
        $stmt->execute([$turma_id]);
        $turma = $stmt->fetch();

        if (!$turma) {
            http_response_code(404);
            echo json_encode(['error' => 'Turma não encontrada']);
            exit;
        }

        // Verificar se já está agendado
        $stmt = $pdo->prepare("SELECT id, status FROM agendamentos WHERE usuario_id = ? AND turma_id = ?");
        $stmt->execute([$usuario_id, $turma_id]);
        $existente = $stmt->fetch();

        if ($existente) {
            if ($existente['status'] === 'confirmado') {
                http_response_code(400);
                echo json_encode(['error' => 'Já está inscrito nesta turma']);
                exit;
            } else {
                // Está na lista de espera, promover para confirmado se houver vaga
                if ($turma['inscritos'] < $turma['vagas']) {
                    $pdo->beginTransaction();

                    // Atualizar status do agendamento
                    $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'confirmado' WHERE id = ?");
                    $stmt->execute([$existente['id']]);

                    // Incrementar contador de inscritos
                    $stmt = $pdo->prepare("UPDATE turmas SET inscritos = inscritos + 1 WHERE id = ?");
                    $stmt->execute([$turma_id]);

                    $pdo->commit();

                    echo json_encode([
                        'success' => true,
                        'message' => 'Inscrição confirmada com sucesso!'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Turma lotada, permanece na lista de espera']);
                }
                exit;
            }
        }

        // Novo agendamento
        $pdo->beginTransaction();

        $status = $turma['inscritos'] < $turma['vagas'] ? 'confirmado' : 'espera';

        $stmt = $pdo->prepare("INSERT INTO agendamentos (usuario_id, turma_id, status, data_agendamento) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$usuario_id, $turma_id, $status]);

        if ($status === 'confirmado') {
            // Incrementar contador de inscritos
            $stmt = $pdo->prepare("UPDATE turmas SET inscritos = inscritos + 1 WHERE id = ?");
            $stmt->execute([$turma_id]);
        } else {
            // Incrementar contador de espera
            $stmt = $pdo->prepare("UPDATE turmas SET espera = espera + 1 WHERE id = ?");
            $stmt->execute([$turma_id]);
        }

        $pdo->commit();

        $message = $status === 'confirmado' ? 'Inscrição realizada com sucesso!' : 'Adicionado à lista de espera';
        echo json_encode([
            'success' => true,
            'message' => $message,
            'status' => $status
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao processar agendamento: ' . $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Cancelar agendamento
    $dados = json_decode(file_get_contents('php://input'), true);
    $dados = validarEntrada($dados, ['usuario_id', 'turma_id']);
    $dados = array_map('sanitizar', $dados);

    $usuario_id = (int)$dados['usuario_id'];
    $turma_id = (int)$dados['turma_id'];

    try {
        $pdo->beginTransaction();

        // Buscar agendamento
        $stmt = $pdo->prepare("SELECT status FROM agendamentos WHERE usuario_id = ? AND turma_id = ?");
        $stmt->execute([$usuario_id, $turma_id]);
        $agendamento = $stmt->fetch();

        if (!$agendamento) {
            http_response_code(404);
            echo json_encode(['error' => 'Agendamento não encontrado']);
            exit;
        }

        // Remover agendamento
        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE usuario_id = ? AND turma_id = ?");
        $stmt->execute([$usuario_id, $turma_id]);

        // Decrementar contadores
        if ($agendamento['status'] === 'confirmado') {
            $stmt = $pdo->prepare("UPDATE turmas SET inscritos = GREATEST(inscritos - 1, 0) WHERE id = ?");
            $stmt->execute([$turma_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE turmas SET espera = GREATEST(espera - 1, 0) WHERE id = ?");
            $stmt->execute([$turma_id]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Agendamento cancelado com sucesso'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao cancelar agendamento: ' . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
