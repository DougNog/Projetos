<h1><i class="fas fa-clipboard-check"></i> <?= $titulo ?></h1>

<?php if ($isAdmin ?? false): ?>
  <p><a href="/avaliacoes/nova" class="btn-primary"><i class="fas fa-plus"></i> Nova Avaliação Física</a></p>
<?php endif; ?>

<table class="table">
  <thead>
    <tr>
      <?php if ($isAdmin ?? false): ?>
        <th>Aluno</th>
      <?php endif; ?>
      <th>Data</th>
      <th>Peso (kg)</th>
      <th>Altura (m)</th>
      <th>IMC</th>
      <th>% Gordura</th>
      <th>Observações</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($avaliacoes as $av): ?>
    <tr>
      <?php if ($isAdmin ?? false): ?>
        <td><?= htmlspecialchars($av['nome'] ?? 'N/A') ?></td>
      <?php endif; ?>
      <td><?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?></td>
      <td><?= number_format($av['peso'], 2) ?></td>
      <td><?= number_format($av['altura'], 2) ?></td>
      <td><?= number_format($av['imc'], 2) ?></td>
      <td><?= number_format($av['gordura_corporal'], 2) ?></td>
      <td><?= nl2br(htmlspecialchars($av['observacoes'] ?? '')) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
