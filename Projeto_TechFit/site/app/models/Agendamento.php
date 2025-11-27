<?php
require_once __DIR__ . '/../../core/BaseModel.php';
require_once __DIR__ . '/Turma.php';

class Agendamento extends BaseModel {
    protected $table = 'agendamentos';

    public function doUsuario($usuarioId) {
        // Verificar se as novas colunas existem
        $columns = $this->pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'data'")->fetch();
        $hasNewColumns = !empty($columns);
        
        if ($hasNewColumns) {
            $sql = "SELECT a.*, 
                           COALESCE(t.data, a.data) AS data,
                           COALESCE(t.inicio, a.horario_inicio) AS inicio,
                           COALESCE(t.fim, a.horario_fim) AS fim,
                           COALESCE(m.nome, a.modalidade) AS modalidade,
                           a.observacoes
                    FROM agendamentos a
                    LEFT JOIN turmas t ON a.turma_id = t.id
                    LEFT JOIN modalidades m ON t.modalidade_id = m.id
                    WHERE a.usuario_id = :uid
                    ORDER BY COALESCE(t.data, a.data) DESC, COALESCE(t.inicio, a.horario_inicio) DESC";
        } else {
            // Fallback para estrutura antiga
            $sql = "SELECT a.*, t.data, t.inicio, t.fim, m.nome AS modalidade, NULL AS observacoes
                    FROM agendamentos a
                    JOIN turmas t ON a.turma_id = t.id
                    JOIN modalidades m ON t.modalidade_id = m.id
                    WHERE a.usuario_id = :uid
                    ORDER BY t.data DESC, t.inicio DESC";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function criarComEspera($usuarioId, $turmaId) {
        $turmaModel = new Turma();
        $turma = $turmaModel->find($turmaId);
        if (!$turma) return false;

        $contagem = $turmaModel->contagemPorStatus($turmaId);
        $confirmados = (int)($contagem['confirmados'] ?? 0);

        $status = $confirmados < (int)$turma['vagas'] ? 'confirmado' : 'espera';

        return $this->create([
            'usuario_id' => $usuarioId,
            'turma_id'   => $turmaId,
            'status'     => $status
        ]);
    }

    public function all() {
        // Verificar se as novas colunas existem
        $columns = $this->pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'data'")->fetch();
        $hasNewColumns = !empty($columns);
        
        if ($hasNewColumns) {
            $sql = "SELECT a.*, 
                           COALESCE(t.data, a.data) AS data,
                           COALESCE(t.inicio, a.horario_inicio) AS inicio,
                           COALESCE(t.fim, a.horario_fim) AS fim,
                           COALESCE(m.nome, a.modalidade) AS modalidade,
                           a.observacoes,
                           u.nome AS usuario_nome, 
                           u.id AS usuario_id
                    FROM agendamentos a
                    LEFT JOIN turmas t ON a.turma_id = t.id
                    LEFT JOIN modalidades m ON t.modalidade_id = m.id
                    JOIN usuarios u ON a.usuario_id = u.id
                    ORDER BY COALESCE(t.data, a.data) DESC, COALESCE(t.inicio, a.horario_inicio) DESC";
        } else {
            // Fallback para estrutura antiga
            $sql = "SELECT a.*, t.data, t.inicio, t.fim, m.nome AS modalidade, NULL AS observacoes,
                           u.nome AS usuario_nome, u.id AS usuario_id
                    FROM agendamentos a
                    JOIN turmas t ON a.turma_id = t.id
                    JOIN modalidades m ON t.modalidade_id = m.id
                    JOIN usuarios u ON a.usuario_id = u.id
                    ORDER BY t.data DESC, t.inicio DESC";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function criarAgendamentoLivre($usuarioId, $data, $horarioInicio, $horarioFim, $modalidade, $observacoes = null) {
        return $this->create([
            'usuario_id' => $usuarioId,
            'turma_id' => null,
            'data' => $data,
            'horario_inicio' => $horarioInicio,
            'horario_fim' => $horarioFim,
            'modalidade' => $modalidade,
            'observacoes' => $observacoes,
            'status' => 'pendente'
        ]);
    }
}
