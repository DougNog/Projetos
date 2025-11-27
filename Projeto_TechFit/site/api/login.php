<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->senha)) {
    $query = "SELECT id, nome, email, senha, perfil, modalidade, checkins_mes FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();
    
    if($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar senha (em produção, use password_verify)
        if($data->senha === '123456') { // Senha padrão para demonstração
            if(!empty($data->is_admin) && $data->is_admin && $row['perfil'] !== 'admin') {
                http_response_code(401);
                echo json_encode(array("message" => "Esta conta não é de administrador."));
            } else {
                $user_data = array(
                    "id" => $row['id'],
                    "nome" => $row['nome'],
                    "email" => $row['email'],
                    "perfil" => $row['perfil'],
                    "modalidade" => $row['modalidade'],
                    "checkinsMes" => $row['checkins_mes']
                );
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login realizado com sucesso.",
                    "user" => $user_data
                ));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Credenciais inválidas."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Credenciais inválidas."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
}
?>