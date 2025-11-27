<?php
require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $table;
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data) {
        $cols = implode(',', array_keys($data));
        $params = ':' . implode(',:', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$cols}) VALUES ({$params})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data) {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "{$col} = :{$col}";
        }
        $set = implode(',', $set);
        $sql = "UPDATE {$this->table} SET {$set} WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
