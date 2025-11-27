<?php
class Database {
    private $host = "localhost";
    private $db_name = "techfit_academia";
    private $username = "root";
    private $password = "senaisp";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";port=3306", 
                $this->username, 
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            $error_message = "Erro de conexão MySQL: " . $exception->getMessage();
            
            if ($exception->getCode() == 1049) {
                $error_message .= "<br>⚠️ O banco de dados '{$this->db_name}' não existe.";
                $error_message .= "<br>Execute o arquivo: config/create_database.sql";
            } elseif ($exception->getCode() == 1045) {
                $error_message .= "<br>⚠️ Acesso negado. Verifique usuário e senha.";
            } elseif ($exception->getCode() == 2002) {
                $error_message .= "<br>⚠️ Não foi possível conectar ao servidor MySQL.";
            }
            
            echo $error_message;
        }
        return $this->conn;
    }
}
?>