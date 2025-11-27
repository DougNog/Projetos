<h1><i class="fas fa-door-open"></i> Acessos - Administração</h1>

<section class="admin-actions">
  <div class="action-buttons">
    <a href="/acessos/relatorio" class="btn-primary"><i class="fas fa-chart-line"></i> Relatório de Utilização</a>
    <a href="/acessos" class="btn-secondary"><i class="fas fa-list"></i> Ver Todos os Acessos</a>
  </div>
</section>

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
      <td><?= date('d/m/Y H:i', strtotime($ac['data_hora_entrada'])) ?></td>
      <td><?= $ac['data_hora_saida'] ? date('d/m/Y H:i', strtotime($ac['data_hora_saida'])) : '-' ?></td>
      <td><?= $ac['tipo_identificacao'] ?></td>
      <td><?= htmlspecialchars($ac['codigo']) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
