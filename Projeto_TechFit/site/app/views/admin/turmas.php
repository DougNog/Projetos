<h1><i class="fas fa-calendar-week"></i> Gerenciar Turmas</h1>

<section class="admin-actions">
  <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
  <div class="action-buttons">
    <a href="/agendamentos/relatorio" class="btn-primary"><i class="fas fa-chart-pie"></i> Relatório de Ocupação</a>
    <a href="/admin/modalidades" class="btn-secondary"><i class="fas fa-dumbbell"></i> Gerenciar Modalidades</a>
  </div>
</section>

<form method="POST" action="/admin/turmas/salvar" class="form">
  <input type="hidden" name="id" id="id">
  <label for="modalidade_id">Modalidade</label>
  <select name="modalidade_id" id="modalidade_id" required>
    <option value="">Selecione...</option>
    <?php foreach ($modalidades as $m): ?>
      <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
    <?php endforeach; ?>
  </select>

  <label for="instrutor">Instrutor</label>
  <input type="text" name="instrutor" id="instrutor" required>

  <label for="data">Data</label>
  <input type="date" name="data" id="data" required>

  <label for="inicio">Início</label>
  <input type="time" name="inicio" id="inicio" required>

  <label for="fim">Fim</label>
  <input type="time" name="fim" id="fim" required>

  <label for="vagas">Vagas</label>
  <input type="number" name="vagas" id="vagas" min="1" required>

  <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar</button>
</form>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Modalidade</th>
      <th>Instrutor</th>
      <th>Data</th>
      <th>Horário</th>
      <th>Vagas</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($turmas as $t): ?>
    <tr>
      <td><?= $t['id'] ?></td>
      <td><?= htmlspecialchars($t['modalidade_nome'] ?? 'N/A') ?></td>
      <td><?= htmlspecialchars($t['instrutor']) ?></td>
      <td><?= date('d/m/Y', strtotime($t['data'])) ?></td>
      <td><?= substr($t['inicio'],0,5) ?> - <?= substr($t['fim'],0,5) ?></td>
      <td><?= $t['vagas'] ?></td>
      <td>
        <button onclick="editarTurma(<?= htmlspecialchars(json_encode($t)) ?>)" class="btn-secondary"><i class="fas fa-edit"></i> Editar</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<script>
function editarTurma(turma) {
  document.getElementById('id').value = turma.id;
  document.getElementById('modalidade_id').value = turma.modalidade_id;
  document.getElementById('instrutor').value = turma.instrutor;
  document.getElementById('data').value = turma.data;
  document.getElementById('inicio').value = turma.inicio;
  document.getElementById('fim').value = turma.fim;
  document.getElementById('vagas').value = turma.vagas;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
