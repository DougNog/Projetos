<?php
require_once __DIR__ . '/../../core/BaseModel.php';

class Usuario extends BaseModel {
    protected $table = 'usuarios';

    public function buscarPorEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
}
