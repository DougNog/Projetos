<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class Mensagem extends BaseModel {
    protected $table = 'mensagens';

    public function inbox($usuarioId) {
        $sql = "SELECT m.*, u.nome AS remetente_nome
                FROM mensagens m
                JOIN usuarios u ON m.remetente_id = u.id
                WHERE m.destinatario_id = :uid
                ORDER BY m.data_envio DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function marcarComoLida($mensagemId) {
        $sql = "UPDATE {$this->table} SET lido = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $mensagemId]);
    }

    public function enviarSegmentada($remetenteId, $segmento, $valorSegmento, $assunto, $corpo) {
        // Salvar mensagem segmentada
        $sql = "INSERT INTO mensagens_segmentadas (remetente_id, segmento, valor_segmento, assunto, corpo) 
                VALUES (:remetente_id, :segmento, :valor_segmento, :assunto, :corpo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'remetente_id' => $remetenteId,
            'segmento' => $segmento,
            'valor_segmento' => $valorSegmento,
            'assunto' => $assunto,
            'corpo' => $corpo
        ]);
        
        $mensagemId = $this->pdo->lastInsertId();
        
        // Buscar destinatários baseado no segmento
        $destinatarios = $this->buscarDestinatariosSegmentados($segmento, $valorSegmento);
        
        // Enviar mensagem para cada destinatário
        foreach ($destinatarios as $destinatario) {
            $this->create([
                'remetente_id' => $remetenteId,
                'destinatario_id' => $destinatario['id'],
                'assunto' => $assunto,
                'corpo' => $corpo
            ]);
        }
        
        return count($destinatarios);
    }

    private function buscarDestinatariosSegmentados($segmento, $valorSegmento) {
        $sql = "SELECT id FROM usuarios WHERE perfil = 'aluno'";
        
        if ($segmento === 'modalidade' && $valorSegmento) {
            $sql .= " AND modalidade = :valor";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['valor' => $valorSegmento]);
        } elseif ($segmento === 'frequencia' && $valorSegmento) {
            if ($valorSegmento === 'alta') {
                $sql .= " AND checkins_mes >= 15";
            } elseif ($valorSegmento === 'media') {
                $sql .= " AND checkins_mes >= 8 AND checkins_mes < 15";
            } else {
                $sql .= " AND checkins_mes < 8";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
}
