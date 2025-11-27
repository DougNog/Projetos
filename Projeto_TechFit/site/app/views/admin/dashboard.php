<h1><i class="fas fa-tachometer-alt"></i> Painel Administrativo</h1>

<section class="admin-actions">
  <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
  <div class="action-buttons">
    <a href="/avaliacoes/nova" class="btn-primary"><i class="fas fa-clipboard-check"></i> Nova Avaliação</a>
    <a href="/mensagens/nova" class="btn-primary"><i class="fas fa-envelope"></i> Nova Mensagem</a>
    <a href="/mensagens/segmentada" class="btn-primary"><i class="fas fa-bullhorn"></i> Mensagem Segmentada</a>
    <a href="/admin/usuarios" class="btn-primary"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a>
    <a href="/admin/turmas" class="btn-primary"><i class="fas fa-calendar-week"></i> Gerenciar Turmas</a>
    <a href="/admin/modalidades" class="btn-primary"><i class="fas fa-dumbbell"></i> Gerenciar Modalidades</a>
    <a href="/admin/relatorios" class="btn-primary"><i class="fas fa-chart-bar"></i> Relatórios</a>
    <a href="/agendamentos/relatorio" class="btn-secondary"><i class="fas fa-chart-pie"></i> Rel. Ocupação</a>
    <a href="/acessos/relatorio" class="btn-secondary"><i class="fas fa-chart-line"></i> Rel. Utilização</a>
    <a href="/avaliacoes/alertas" class="btn-secondary"><i class="fas fa-bell"></i> Alertas</a>
  </div>
</section>

<section>
  <h2><i class="fas fa-users"></i> Usuários</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Perfil</th>
        <th>Modalidade</th>
        <th>Check-ins no mês</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['nome']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <?php if ($u['perfil'] === 'admin'): ?>
            <span class="badge-unread"><i class="fas fa-user-shield"></i> Admin</span>
          <?php else: ?>
            <span class="badge-read"><i class="fas fa-user"></i> Aluno</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($u['modalidade'] ?? '-') ?></td>
        <td>
          <span class="badge-read"><?= $u['checkins_mes'] ?> check-ins</span>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>

<section>
  <h2><i class="fas fa-door-open"></i> Últimos Acessos</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Aluno</th>
        <th>Entrada</th>
        <th>Saída</th>
        <th>Tipo</th>
        <th>Código</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($acessos as $ac): ?>
      <tr>
        <td><?= htmlspecialchars($ac['nome']) ?></td>
        <td><?= $ac['data_hora_entrada'] ?></td>
        <td><?= $ac['data_hora_saida'] ?: '-' ?></td>
        <td>
          <span class="badge-read"><?= htmlspecialchars($ac['tipo_identificacao']) ?></span>
        </td>
        <td><code style="background: rgba(108, 240, 255, 0.1); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;"><?= htmlspecialchars($ac['codigo'] ?? '-') ?></code></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
