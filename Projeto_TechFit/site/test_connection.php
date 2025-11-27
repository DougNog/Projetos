<?php
/**
 * Script de teste de conexão com o banco de dados
 * Execute este arquivo para verificar se a conexão está funcionando
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = Database::getInstance();
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!\n\n";
    
    // Verificar se as tabelas existem
    $tables = ['usuarios', 'modalidades', 'turmas', 'agendamentos', 'acessos', 'mensagens', 'avaliacoes'];
    echo "Verificando tabelas...\n";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '{$table}' existe\n";
        } else {
            echo "❌ Tabela '{$table}' NÃO existe\n";
        }
    }
    
    echo "\n✅ Sistema pronto para uso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "\nVerifique:\n";
    echo "1. Se o MySQL está rodando\n";
    echo "2. Se o banco de dados 'techfit_academia' foi criado\n";
    echo "3. Se as credenciais em config/database.php estão corretas\n";
}
?>