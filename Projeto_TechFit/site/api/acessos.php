<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Buscar histórico de acessos
        $query = "SELECT a.*, u.nome FROM acessos a 
                 JOIN usuarios u ON a.usuario_id = u.id 
                 ORDER BY a.data_acesso DESC 
                 LIMIT 30";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $acessos = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $acessos[] = $row;
        }
        
        echo json_encode($acessos);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Registrar acesso
        $query = "INSERT INTO acessos (usuario_id, acao) VALUES (:usuario_id, :acao)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $data->usuario_id);
        $stmt->bindParam(":acao", $data->acao);
        
        if($stmt->execute()) {
            // Atualizar contador de check-ins se for entrada
            if($data->acao === 'entrada') {
                $query = "UPDATE usuarios SET checkins_mes = checkins_mes + 1 WHERE id = :usuario_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":usuario_id", $data->usuario_id);
                $stmt->execute();
            }
            
            echo json_encode(array("message" => "Acesso registrado com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao registrar acesso."));
        }
        break;
}
?>