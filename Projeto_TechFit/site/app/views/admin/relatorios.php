<h1><i class="fas fa-chart-bar"></i> Relatórios Gerenciais</h1>

<section class="admin-actions">
  <h2><i class="fas fa-bolt"></i> Acesso Rápido a Relatórios</h2>
  <div class="action-buttons">
    <a href="/agendamentos/relatorio" class="btn-primary"><i class="fas fa-chart-pie"></i> Relatório de Ocupação</a>
    <a href="/acessos/relatorio" class="btn-primary"><i class="fas fa-chart-line"></i> Relatório de Utilização</a>
    <a href="/avaliacoes/alertas" class="btn-secondary"><i class="fas fa-bell"></i> Alertas de Avaliação</a>
  </div>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <h3>Total de Usuários</h3>
        <p class="stat-number"><?= $totalUsuarios ?></p>
    </div>
    <div class="stat-card">
        <h3>Total de Acessos</h3>
        <p class="stat-number"><?= $totalAcessos ?></p>
    </div>
    <div class="stat-card">
        <h3>Total de Agendamentos</h3>
        <p class="stat-number"><?= $totalAgendamentos ?></p>
    </div>
    <div class="stat-card">
        <h3>Total de Avaliações</h3>
        <p class="stat-number"><?= $totalAvaliacoes ?></p>
    </div>
</section>

<section>
    <h2><i class="fas fa-user-check"></i> Frequência dos Alunos</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Aluno</th>
                <th>Check-ins no Mês</th>
                <th>Total de Acessos</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($frequencia as $freq): ?>
            <tr>
                <td><?= htmlspecialchars($freq['nome']) ?></td>
                <td><?= $freq['checkins_mes'] ?></td>
                <td><?= $freq['total_acessos'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section>
    <h2><i class="fas fa-fire"></i> Modalidades Mais Populares</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Modalidade</th>
                <th>Alunos</th>
                <th>Agendamentos</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($modalidades as $mod): ?>
            <tr>
                <td><?= htmlspecialchars($mod['modalidade']) ?></td>
                <td><?= $mod['alunos'] ?></td>
                <td><?= $mod['agendamentos'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>


