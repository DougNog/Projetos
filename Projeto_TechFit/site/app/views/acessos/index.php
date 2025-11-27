<h1><i class="fas fa-door-open"></i> <?= $titulo ?></h1>

<?php if ($isAdmin ?? false): ?>
  <p>
    <a href="/acessos/relatorio" class="btn-primary"><i class="fas fa-chart-line"></i> Relatório de Utilização</a>
    <a href="/admin/acessos" class="btn-secondary"><i class="fas fa-list"></i> Ver Todos os Acessos</a>
  </p>
<?php endif; ?>

<table class="table">
  <thead>
    <tr>
      <?php if ($isAdmin ?? false): ?>
        <th>Aluno</th>
      <?php endif; ?>
      <th>Entrada</th>
      <th>Saída</th>
      <th>Tipo</th>
      <th>Código</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($acessos as $ac): ?>
    <tr>
      <?php if ($isAdmin ?? false): ?>
        <td><?= htmlspecialchars($ac['nome'] ?? 'N/A') ?></td>
      <?php endif; ?>
      <td><?= date('d/m/Y H:i', strtotime($ac['data_hora_entrada'])) ?></td>
      <td><?= $ac['data_hora_saida'] ? date('d/m/Y H:i', strtotime($ac['data_hora_saida'])) : '-' ?></td>
      <td><?= htmlspecialchars($ac['tipo_identificacao']) ?></td>
      <td><?= htmlspecialchars($ac['codigo'] ?? '-') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
