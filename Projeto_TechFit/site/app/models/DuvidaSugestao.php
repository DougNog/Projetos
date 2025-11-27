<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class DuvidaSugestao extends BaseModel {
    protected $table = 'duvidas_sugestoes';

    public function doUsuario($usuarioId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE usuario_id = :uid ORDER BY data_criacao DESC");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function todas() {
        $sql = "SELECT d.*, u.nome AS usuario_nome, r.nome AS respondente_nome
                FROM {$this->table} d
                JOIN usuarios u ON d.usuario_id = u.id
                LEFT JOIN usuarios r ON d.respondido_por = r.id
                ORDER BY d.data_criacao DESC";
        return $this->pdo->query($sql)->fetchAll();
    }
}


