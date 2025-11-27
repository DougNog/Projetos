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
        // Buscar avaliações do usuário
        if(isset($_GET['usuario_id'])) {
            $query = "SELECT * FROM avaliacoes WHERE usuario_id = :usuario_id ORDER BY data_avaliacao DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario_id", $_GET['usuario_id']);
            $stmt->execute();
            
            $avaliacoes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $avaliacoes[] = $row;
            }
            
            echo json_encode($avaliacoes);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Registrar avaliação
        $query = "INSERT INTO avaliacoes (usuario_id, peso, altura_cm, gordura, peito, cintura, quadril) 
                 VALUES (:usuario_id, :peso, :altura_cm, :gordura, :peito, :cintura, :quadril)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":usuario_id", $data->usuario_id);
        $stmt->bindParam(":peso", $data->peso);
        $stmt->bindParam(":altura_cm", $data->altura_cm);
        $stmt->bindParam(":gordura", $data->gordura);
        $stmt->bindParam(":peito", $data->peito);
        $stmt->bindParam(":cintura", $data->cintura);
        $stmt->bindParam(":quadril", $data->quadril);
        
        if($stmt->execute()) {
            echo json_encode(array("message" => "Avaliação registrada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao registrar avaliação."));
        }
        break;
}
?>