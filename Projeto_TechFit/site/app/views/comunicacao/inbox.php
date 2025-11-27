<h1><i class="fas fa-inbox"></i> Mensagens Recebidas</h1>

<?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
  <p>
    <a href="/mensagens/nova" class="btn-primary"><i class="fas fa-plus"></i> Nova Mensagem</a>
    <a href="/mensagens/segmentada" class="btn-primary"><i class="fas fa-bullhorn"></i> Mensagem Segmentada</a>
  </p>
<?php endif; ?>

<table class="table">
  <thead>
    <tr>
      <th>Remetente</th>
      <th>Assunto</th>
      <th>Mensagem</th>
      <th>Data</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($mensagens as $m): ?>
    <tr>
      <td><?= htmlspecialchars($m['remetente_nome']) ?></td>
      <td><?= htmlspecialchars($m['assunto']) ?></td>
      <td><?= htmlspecialchars(substr($m['corpo'], 0, 50)) ?><?= strlen($m['corpo']) > 50 ? '...' : '' ?></td>
      <td><?= date('d/m/Y H:i', strtotime($m['data_envio'])) ?></td>
      <td>
        <?php if ($m['lido']): ?>
          <span class="badge-read">Lida</span>
        <?php else: ?>
          <span class="badge-unread">Não lida</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
