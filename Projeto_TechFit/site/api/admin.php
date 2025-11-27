<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch($action) {
    case 'alunos':
        if($method == 'GET') {
            $query = "SELECT * FROM usuarios WHERE perfil = 'aluno'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $alunos = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alunos[] = $row;
            }
            
            echo json_encode($alunos);
        }
        break;
        
    case 'modalidades':
        if($method == 'GET') {
            $query = "SELECT * FROM modalidades";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $modalidades = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $modalidades[] = $row;
            }
            
            echo json_encode($modalidades);
        }
        break;
        
    case 'relatorios':
        if($method == 'GET') {
            // Relatório de ocupação
            $query_ocupacao = "SELECT t.*, m.nome as modalidade,
                             (SELECT COUNT(*) FROM agendamentos a WHERE a.turma_id = t.id AND a.status = 'confirmado') as inscritos,
                             (SELECT COUNT(*) FROM agendamentos a WHERE a.turma_id = t.id AND a.status = 'espera') as espera
                             FROM turmas t 
                             JOIN modalidades m ON t.modalidade_id = m.id 
                             WHERE t.data >= CURDATE() 
                             ORDER BY t.data";
            $stmt = $db->prepare($query_ocupacao);
            $stmt->execute();
            
            $ocupacao = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ocupacao[] = $row;
            }
            
            // Relatório de frequência
            $query_frequencia = "SELECT nome, checkins_mes FROM usuarios WHERE perfil = 'aluno'";
            $stmt = $db->prepare($query_frequencia);
            $stmt->execute();
            
            $frequencia = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $frequencia[] = $row;
            }
            
            echo json_encode(array(
                "ocupacao" => $ocupacao,
                "frequencia" => $frequencia
            ));
        }
        break;
}
?>