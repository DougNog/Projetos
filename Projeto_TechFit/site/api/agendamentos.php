<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Buscar agendamentos do usuário
        if(isset($_GET['usuario_id'])) {
            $query = "SELECT a.*, t.modalidade_id, m.nome as modalidade, t.instrutor, t.data, t.inicio, t.fim, t.vagas 
                     FROM agendamentos a 
                     JOIN turmas t ON a.turma_id = t.id 
                     JOIN modalidades m ON t.modalidade_id = m.id 
                     WHERE a.usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario_id", $_GET['usuario_id']);
            $stmt->execute();
            
            $agendamentos = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $agendamentos[] = $row;
            }
            
            echo json_encode($agendamentos);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Verificar vagas disponíveis
        $query = "SELECT COUNT(*) as ocupadas, vagas FROM agendamentos a 
                 JOIN turmas t ON a.turma_id = t.id 
                 WHERE a.turma_id = :turma_id AND a.status = 'confirmado'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":turma_id", $data->turma_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $status = ($result['ocupadas'] < $result['vagas']) ? 'confirmado' : 'espera';
        
        // Inserir agendamento
        $query = "INSERT INTO agendamentos (usuario_id, turma_id, status) VALUES (:usuario_id, :turma_id, :status)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $data->usuario_id);
        $stmt->bindParam(":turma_id", $data->turma_id);
        $stmt->bindParam(":status", $status);
        
        if($stmt->execute()) {
            echo json_encode(array("message" => "Agendamento realizado.", "status" => $status));
        } else {
            echo json_encode(array("message" => "Erro ao realizar agendamento."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "DELETE FROM agendamentos WHERE usuario_id = :usuario_id AND turma_id = :turma_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $data->usuario_id);
        $stmt->bindParam(":turma_id", $data->turma_id);
        
        if($stmt->execute()) {
            echo json_encode(array("message" => "Agendamento cancelado."));
        } else {
            echo json_encode(array("message" => "Erro ao cancelar agendamento."));
        }
        break;
}
?>