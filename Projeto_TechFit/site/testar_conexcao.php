<?php
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html>
<head>
    <title>Teste de Conexão MySQL - TechFit</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 20px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        ul { background: white; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🧪 Teste de Conexão MySQL - TechFit</h1>";

include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db) {
    echo "<div class='success'>
        <h3>✅ Conexão estabelecida com sucesso!</h3>
        <p><strong>Banco:</strong> techfit_academia</p>
        <p><strong>Host:</strong> localhost:3306</p>
        <p><strong>Usuário:</strong> root</p>
        <p><strong>Status:</strong> Conectado</p>
    </div>";
    
    // Testar se as tabelas existem
    try {
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>
            <h3>📋 Tabelas do Banco de Dados:</h3>";
        
        if(count($tables) > 0) {
            echo "<p>Foram encontradas " . count($tables) . " tabelas:</p>
            <ul>";
            foreach($tables as $table) {
                echo "<li>📊 {$table}</li>";
            }
            echo "</ul>";
            
            // Mostrar dados dos usuários se a tabela existir
            if(in_array('usuarios', $tables)) {
                echo "<h3>👥 Usuários Cadastrados:</h3>";
                $stmt = $db->query("SELECT id, nome, email, perfil, modalidade FROM usuarios");
                $usuarios = $stmt->fetchAll();
                
                if(count($usuarios) > 0) {
                    echo "<ul>";
                    foreach($usuarios as $usuario) {
                        echo "<li><strong>{$usuario['nome']}</strong> ({$usuario['email']}) - {$usuario['perfil']} - {$usuario['modalidade']}</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Nenhum usuário cadastrado.</p>";
                }
            }
            
        } else {
            echo "<div class='warning'>
                <h3>⚠️ Nenhuma tabela encontrada!</h3>
                <p>Execute o script SQL para criar as tabelas:</p>
                <p><code>config/create_database.sql</code></p>
            </div>";
        }
        
    } catch(PDOException $e) {
        echo "<div class='error'>
            <h3>❌ Erro ao acessar o banco:</h3>
            <p>{$e->getMessage()}</p>
        </div>";
    }
    
} else {
    echo "<div class='error'>
        <h3>❌ Falha na conexão com o MySQL</h3>
        <p><strong>Possíveis causas:</strong></p>
        <ul>
            <li>Servidor MySQL não está rodando</li>
            <li>Usuário ou senha incorretos</li>
            <li>Banco de dados não existe</li>
            <li>Porta 3306 bloqueada</li>
        </ul>
        <p><strong>Configuração testada:</strong></p>
        <ul>
            <li>Host: localhost:3306</li>
            <li>Usuário: root</li>
            <li>Senha: senaisp</li>
            <li>Banco: techfit_academia</li>
        </ul>
    </div>";
}

echo "</body></html>";
?>