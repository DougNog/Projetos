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
        // Buscar mensagens para o usuário
        if(isset($_GET['usuario_id'])) {
            $usuario_id = $_GET['usuario_id'];
            
            // Buscar dados do usuário
            $query_user = "SELECT modalidade, checkins_mes FROM usuarios WHERE id = :usuario_id";
            $stmt_user = $db->prepare($query_user);
            $stmt_user->bindParam(":usuario_id", $usuario_id);
            $stmt_user->execute();
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            // Buscar mensagens
            $query = "SELECT * FROM mensagens 
                     WHERE (segmento_modalidade IS NULL OR segmento_modalidade = :modalidade)
                     AND (segmento_frequencia = 0 OR segmento_frequencia <= :checkins_mes)
                     ORDER BY data_envio DESC 
                     LIMIT 10";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":modalidade", $user['modalidade']);
            $stmt->bindParam(":checkins_mes", $user['checkins_mes']);
            $stmt->execute();
            
            $mensagens = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mensagens[] = $row;
            }
            
            echo json_encode($mensagens);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        // Enviar mensagem (apenas admin)
        $query = "INSERT INTO mensagens (titulo, corpo, segmento_modalidade, segmento_frequencia, autor_id) 
                 VALUES (:titulo, :corpo, :segmento_modalidade, :segmento_frequencia, :autor_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":titulo", $data->titulo);
        $stmt->bindParam(":corpo", $data->corpo);
        $stmt->bindParam(":segmento_modalidade", $data->segmento_modalidade);
        $stmt->bindParam(":segmento_frequencia", $data->segmento_frequencia);
        $stmt->bindParam(":autor_id", $data->autor_id);
        
        if($stmt->execute()) {
            echo json_encode(array("message" => "Mensagem enviada com sucesso."));
        } else {
            echo json_encode(array("message" => "Erro ao enviar mensagem."));
        }
        break;
}
?>