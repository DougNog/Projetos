<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class Turma extends BaseModel {
    protected $table = 'turmas';

    public function proximasTurmas() {
        $sql = "SELECT t.*, m.nome AS modalidade
                FROM turmas t
                JOIN modalidades m ON t.modalidade_id = m.id
                WHERE t.data >= CURDATE()
                ORDER BY t.data, t.inicio";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function contagemPorStatus($turmaId) {
        $sql = "SELECT
                    SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) AS confirmados,
                    SUM(CASE WHEN status = 'espera' THEN 1 ELSE 0 END) AS espera
                FROM agendamentos
                WHERE turma_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $turmaId]);
        return $stmt->fetch();
    }

    public function relatorioOcupacao($dataInicio = null, $dataFim = null) {
        $sql = "SELECT 
                    t.id,
                    t.data,
                    t.inicio,
                    t.fim,
                    m.nome AS modalidade,
                    t.instrutor,
                    t.vagas,
                    COUNT(CASE WHEN a.status = 'confirmado' THEN 1 END) AS ocupadas,
                    COUNT(CASE WHEN a.status = 'espera' THEN 1 END) AS lista_espera,
                    ROUND((COUNT(CASE WHEN a.status = 'confirmado' THEN 1 END) / t.vagas) * 100, 2) AS ocupacao_percentual
                FROM turmas t
                JOIN modalidades m ON t.modalidade_id = m.id
                LEFT JOIN agendamentos a ON t.id = a.turma_id
                WHERE 1=1";
        
        $params = [];
        if ($dataInicio) {
            $sql .= " AND t.data >= :data_inicio";
            $params['data_inicio'] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND t.data <= :data_fim";
            $params['data_fim'] = $dataFim;
        }
        
        $sql .= " GROUP BY t.id, t.data, t.inicio, t.fim, m.nome, t.instrutor, t.vagas
                  ORDER BY t.data, t.inicio";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function notificarAlteracao($turmaId, $tipo, $mensagem) {
        $sql = "INSERT INTO notificacoes_turma (turma_id, tipo, mensagem) VALUES (:turma_id, :tipo, :mensagem)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'turma_id' => $turmaId,
            'tipo' => $tipo,
            'mensagem' => $mensagem
        ]);
    }

    public function getNotificacoes($usuarioId) {
        $sql = "SELECT nt.*, t.data, t.inicio, t.fim, m.nome AS modalidade
                FROM notificacoes_turma nt
                JOIN turmas t ON nt.turma_id = t.id
                JOIN modalidades m ON t.modalidade_id = m.id
                JOIN agendamentos a ON t.id = a.turma_id
                WHERE a.usuario_id = :usuario_id
                ORDER BY nt.data_envio DESC
                LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }
}
