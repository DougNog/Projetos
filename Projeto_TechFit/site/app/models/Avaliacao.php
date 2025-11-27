<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class Avaliacao extends BaseModel {
    protected $table = 'avaliacoes';

    public function doUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_avaliacao DESC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function todasComAlunos() {
        $sql = "SELECT a.*, u.nome 
                FROM avaliacoes a
                JOIN usuarios u ON a.usuario_id = u.id
                ORDER BY a.data_avaliacao DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function evolucaoUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_avaliacao ASC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function ultimaAvaliacao($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_avaliacao DESC LIMIT 1");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetch();
    }

    public function verificarNecessidadeAlerta($usuarioId) {
        $ultima = $this->ultimaAvaliacao($usuarioId);
        if (!$ultima) {
            return true; // Nunca teve avaliação
        }
        
        $dataUltima = new DateTime($ultima['data_avaliacao']);
        $dataAtual = new DateTime();
        $diferenca = $dataAtual->diff($dataUltima);
        
        // Alertar se passou mais de 90 dias
        return $diferenca->days > 90;
    }

    public function criarAlerta($usuarioId) {
        $dataPrevista = date('Y-m-d', strtotime('+7 days'));
        $sql = "INSERT INTO alertas_avaliacao (usuario_id, data_prevista) 
                VALUES (:usuario_id, :data_prevista)
                ON DUPLICATE KEY UPDATE data_prevista = :data_prevista, enviado = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'usuario_id' => $usuarioId,
            'data_prevista' => $dataPrevista
        ]);
    }
}
