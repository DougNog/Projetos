<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$pdo = conectarBanco();

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'alunos':
        handleAlunos($pdo);
        break;

    case 'modalidades':
        handleModalidades($pdo);
        break;

    case 'turmas':
        handleTurmas($pdo);
        break;

    case 'relatorios':
        // Buscar dados para relatórios
        $relatorios = [];

        // Ocupação por turma
        $stmt = $pdo->query("
            SELECT t.modalidade, t.data, t.vagas, t.inscritos,
                   ROUND((t.inscritos / t.vagas) * 100, 1) as ocupacao_pct
            FROM turmas t
            WHERE t.data >= CURDATE()
            ORDER BY t.data, t.modalidade
            LIMIT 20
        ");
        $relatorios['ocupacao'] = $stmt->fetchAll();

        // Frequência por aluno
        $stmt = $pdo->query("
            SELECT nome, checkins_mes
            FROM alunos
            WHERE checkins_mes > 0
            ORDER BY checkins_mes DESC
            LIMIT 20
        ");
        $relatorios['frequencia'] = $stmt->fetchAll();

        echo json_encode($relatorios);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação não especificada']);
        break;
}

function handleAlunos($pdo) {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Buscar alunos
            $stmt = $pdo->query("SELECT id, nome, email, modalidade, checkins_mes FROM alunos ORDER BY nome");
            $alunos = $stmt->fetchAll();
            echo json_encode($alunos);
            break;

        case 'POST':
            // Criar novo aluno
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = validarEntrada($dados, ['nome', 'email', 'senha']);
            $dados = array_map('sanitizar', $dados);

            // Verificar se email já existe
            $stmt = $pdo->prepare("SELECT id FROM alunos WHERE email = ?");
            $stmt->execute([$dados['email']]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Email já cadastrado']);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO alunos (nome, email, senha, modalidade, checkins_mes) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$dados['nome'], $dados['email'], password_hash($dados['senha'], PASSWORD_DEFAULT), $dados['modalidade'] ?? null]);

            echo json_encode([
                'success' => true,
                'message' => 'Aluno criado com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'PUT':
            // Atualizar aluno
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = validarEntrada($dados, ['id', 'nome', 'email']);
            $dados = array_map('sanitizar', $dados);

            $stmt = $pdo->prepare("UPDATE alunos SET nome = ?, email = ?, modalidade = ? WHERE id = ?");
            $stmt->execute([$dados['nome'], $dados['email'], $dados['modalidade'] ?? null, $dados['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Aluno atualizado com sucesso'
            ]);
            break;

        case 'DELETE':
            // Excluir aluno
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID do aluno não especificado']);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Aluno excluído com sucesso'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
}

function handleModalidades($pdo) {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Buscar modalidades
            $stmt = $pdo->query("SELECT * FROM modalidades WHERE ativa = 1 ORDER BY nome");
            $modalidades = $stmt->fetchAll();
            echo json_encode($modalidades);
            break;

        case 'POST':
            // Criar nova modalidade
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = validarEntrada($dados, ['nome']);
            $dados = array_map('sanitizar', $dados);

            // Verificar se modalidade já existe
            $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE nome = ? AND ativa = 1");
            $stmt->execute([$dados['nome']]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Modalidade já existe']);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO modalidades (nome, ativa) VALUES (?, 1)");
            $stmt->execute([$dados['nome']]);

            echo json_encode([
                'success' => true,
                'message' => 'Modalidade criada com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'DELETE':
            // Desativar modalidade (soft delete)
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID da modalidade não especificado']);
                return;
            }

            $stmt = $pdo->prepare("UPDATE modalidades SET ativa = 0 WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Modalidade removida com sucesso'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
}

function handleTurmas($pdo) {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Buscar turmas
            $stmt = $pdo->query("
                SELECT t.*, m.nome as modalidade_nome
                FROM turmas t
                LEFT JOIN modalidades m ON t.modalidade_id = m.id
                ORDER BY t.data, t.inicio
            ");
            $turmas = $stmt->fetchAll();
            echo json_encode($turmas);
            break;

        case 'POST':
            // Criar nova turma
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = validarEntrada($dados, ['modalidade', 'instrutor', 'data', 'inicio', 'fim', 'vagas']);
            $dados = array_map('sanitizar', $dados);

            // Buscar ID da modalidade
            $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE nome = ? AND ativa = 1");
            $stmt->execute([$dados['modalidade']]);
            $modalidade = $stmt->fetch();

            if (!$modalidade) {
                http_response_code(400);
                echo json_encode(['error' => 'Modalidade não encontrada']);
                return;
            }

            $stmt = $pdo->prepare("
                INSERT INTO turmas (modalidade_id, modalidade, instrutor, data, inicio, fim, vagas, inscritos, espera)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0)
            ");
            $stmt->execute([
                $modalidade['id'],
                $dados['modalidade'],
                $dados['instrutor'],
                $dados['data'],
                $dados['inicio'],
                $dados['fim'],
                $dados['vagas']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Turma criada com sucesso',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'PUT':
            // Atualizar turma
            $dados = json_decode(file_get_contents('php://input'), true);
            $dados = validarEntrada($dados, ['id', 'modalidade', 'instrutor', 'data', 'inicio', 'fim', 'vagas']);
            $dados = array_map('sanitizar', $dados);

            // Buscar ID da modalidade
            $stmt = $pdo->prepare("SELECT id FROM modalidades WHERE nome = ? AND ativa = 1");
            $stmt->execute([$dados['modalidade']]);
            $modalidade = $stmt->fetch();

            if (!$modalidade) {
                http_response_code(400);
                echo json_encode(['error' => 'Modalidade não encontrada']);
                return;
            }

            $stmt = $pdo->prepare("
                UPDATE turmas SET
                    modalidade_id = ?, modalidade = ?, instrutor = ?,
                    data = ?, inicio = ?, fim = ?, vagas = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $modalidade['id'],
                $dados['modalidade'],
                $dados['instrutor'],
                $dados['data'],
                $dados['inicio'],
                $dados['fim'],
                $dados['vagas'],
                $dados['id']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Turma atualizada com sucesso'
            ]);
            break;

        case 'DELETE':
            // Excluir turma
            $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID da turma não especificado']);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM turmas WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Turma excluída com sucesso'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
}
?>
