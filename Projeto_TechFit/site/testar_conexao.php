<?php
header('Content-Type: text/html; charset=utf-8');

// Configurações do banco de dados
$host = 'localhost';
$user = 'root';
$password = 'senaisp';
$database = 'techfit_academia';

echo "<div class='success'>";
echo "<h3>🧪 Teste de Conexão MySQL - TechFit</h3>";
echo "<p>Iniciando testes de conexão...</p>";
echo "</div><br>";

try {
    // Teste 1: Conexão básica
    echo "<div class='info'>";
    echo "<h4>🔌 Teste 1: Conexão Básica</h4>";
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Conexão básica estabelecida com sucesso!</p>";
    echo "</div><br>";

    // Teste 2: Verificar se o banco existe
    echo "<div class='info'>";
    echo "<h4>📊 Teste 2: Verificação do Banco de Dados</h4>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p>✅ Banco de dados '$database' encontrado!</p>";

        // Conectar ao banco específico
        $pdo->exec("USE `$database`");

        // Teste 3: Verificar tabelas
        echo "<h4>📋 Teste 3: Verificação das Tabelas</h4>";
        $expected_tables = ['alunos', 'funcionarios', 'modalidades', 'turmas', 'agendamentos', 'mensagens', 'acessos', 'avaliacoes'];
        $stmt = $pdo->query("SHOW TABLES");
        $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "<p>Tabelas encontradas: " . count($existing_tables) . "</p>";
        echo "<ul>";
        foreach ($expected_tables as $table) {
            if (in_array($table, $existing_tables)) {
                echo "<li>✅ $table - OK</li>";
            } else {
                echo "<li>❌ $table - FALTANDO</li>";
            }
        }
        echo "</ul>";

        // Teste 4: Verificar dados de exemplo
        echo "<h4>👥 Teste 4: Dados de Exemplo</h4>";

        // Alunos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM alunos");
        $alunos = $stmt->fetch()['total'];
        echo "<p>Alunos cadastrados: $alunos</p>";

        // Funcionários
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM funcionarios");
        $funcionarios = $stmt->fetch()['total'];
        echo "<p>Funcionários cadastrados: $funcionarios</p>";

        // Modalidades
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM modalidades");
        $modalidades = $stmt->fetch()['total'];
        echo "<p>Modalidades cadastradas: $modalidades</p>";

        // Turmas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM turmas");
        $turmas = $stmt->fetch()['total'];
        echo "<p>Turmas cadastradas: $turmas</p>";

        if ($alunos > 0) {
            echo "<h5>📝 Alguns alunos de exemplo:</h5>";
            $stmt = $pdo->query("SELECT nome, email, modalidade FROM alunos LIMIT 3");
            $alunos_exemplo = $stmt->fetchAll();
            echo "<ul>";
            foreach ($alunos_exemplo as $aluno) {
                echo "<li>{$aluno['nome']} ({$aluno['email']}) - {$aluno['modalidade']}</li>";
            }
            echo "</ul>";
        }

        if ($funcionarios > 0) {
            echo "<h5>👨‍💼 Funcionários de exemplo:</h5>";
            $stmt = $pdo->query("SELECT nome, email FROM funcionarios LIMIT 2");
            $funcs_exemplo = $stmt->fetchAll();
            echo "<ul>";
            foreach ($funcs_exemplo as $func) {
                echo "<li>{$func['nome']} ({$func['email']})</li>";
            }
            echo "</ul>";
        }

    } else {
        echo "<p>❌ Banco de dados '$database' NÃO encontrado!</p>";
        echo "<div class='warning'>";
        echo "<h4>⚠️ Solução: Criar Banco de Dados</h4>";
        echo "<p>Execute o script SQL localizado em <code>config/create_database.sql</code></p>";
        echo "<p>Ou execute manualmente no phpMyAdmin/MySQL Workbench:</p>";
        echo "<pre>CREATE DATABASE techfit_academia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>";
        echo "</div>";
    }

    echo "</div><br>";

} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Erro de Conexão MySQL</h3>";
    echo "<p><strong>Detalhes:</strong> " . $e->getMessage() . "</p>";

    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<h4>🔧 Soluções possíveis:</h4>";
        echo "<ul>";
        echo "<li>Verifique se o MySQL Server está rodando</li>";
        echo "<li>No XAMPP, inicie o serviço MySQL</li>";
        echo "<li>No WAMP, certifique-se de que o MySQL está ativo</li>";
        echo "<li>Verifique se a porta 3306 não está bloqueada</li>";
        echo "</ul>";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<h4>🔐 Problema de Autenticação:</h4>";
        echo "<ul>";
        echo "<li>Verifique se o usuário 'root' existe</li>";
        echo "<li>Confirme se a senha está correta (configurada como 'senaisp')</li>";
        echo "<li>Tente resetar a senha do MySQL root</li>";
        echo "</ul>";
    }

    echo "</div><br>";
}

// Informações do sistema
echo "<div class='info'>";
echo "<h4>ℹ️ Informações do Sistema</h4>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li><strong>Current Directory:</strong> " . __DIR__ . "</li>";
echo "</ul>";
echo "</div><br>";

echo "<div class='success'>";
echo "<h3>✅ Teste Concluído</h3>";
echo "<p>Se encontrou erros acima, resolva-os antes de testar o sistema de login.</p>";
echo "<p><a href='loginsystem.html' style='color: #6cf0ff;'>🔐 Testar Sistema de Login</a></p>";
echo "</div>";
?>
