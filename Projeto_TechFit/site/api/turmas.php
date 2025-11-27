<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT t.*, m.nome as modalidade, 
          (SELECT COUNT(*) FROM agendamentos a WHERE a.turma_id = t.id AND a.status = 'confirmado') as inscritos,
          (SELECT COUNT(*) FROM agendamentos a WHERE a.turma_id = t.id AND a.status = 'espera') as espera
          FROM turmas t 
          JOIN modalidades m ON t.modalidade_id = m.id 
          WHERE t.data >= CURDATE() 
          ORDER BY t.data, t.inicio";

$stmt = $db->prepare($query);
$stmt->execute();

$turmas = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $turmas[] = $row;
}

echo json_encode($turmas);
?>