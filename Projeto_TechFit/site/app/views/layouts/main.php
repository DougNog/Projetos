<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= isset($titulo) ? $titulo : 'TechFit' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header class="site-header">
    <nav class="nav">
        <a href="/" class="nav-logo">TechFit</a>
        <ul class="nav-links">
            <li><a href="/"><i class="fas fa-home"></i> Home</a></li>
            <?php if (!empty($_SESSION['usuario'])): ?>
                <li><a href="/agendamentos"><i class="fas fa-calendar-alt"></i> Agendamentos</a></li>
                <li><a href="/acessos"><i class="fas fa-door-open"></i> Acessos</a></li>
                <li><a href="/avaliacoes"><i class="fas fa-chart-line"></i> Avaliações</a></li>
                <li><a href="/mensagens"><i class="fas fa-envelope"></i> Mensagens</a></li>
                <?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
                    <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="/admin/relatorios"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <?php endif; ?>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            <?php else: ?>
                <li><a href="/login"><i class="fas fa-sign-in-alt"></i> Entrar</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="site-main">
    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert-success">
            <?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?>
        </div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert-error">
            <?= htmlspecialchars($_SESSION['mensagem_erro']) ?>
        </div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <p>© <?= date('Y') ?> TechFit — Sistema de agendamento, acesso, comunicação e avaliação física.</p>
</footer>

<script src="/assets/js/app.js"></script>
<script src="/assets/js/login.js"></script>
</body>
</html>
