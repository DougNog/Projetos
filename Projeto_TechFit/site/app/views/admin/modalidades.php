<h1><i class="fas fa-dumbbell"></i> Gerenciar Modalidades</h1>

<section class="admin-actions">
  <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
  <div class="action-buttons">
    <a href="/admin/turmas" class="btn-primary"><i class="fas fa-calendar-week"></i> Gerenciar Turmas</a>
    <a href="/agendamentos/relatorio" class="btn-secondary"><i class="fas fa-chart-pie"></i> Relatório de Ocupação</a>
  </div>
</section>

<form method="POST" action="/admin/modalidades/salvar" class="form-inline">
  <input type="hidden" name="id" id="id">
  <label for="nome">Nome da modalidade</label>
  <input type="text" name="nome" id="nome" required>
  <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar</button>
</form>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nome</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($modalidades as $m): ?>
    <tr>
      <td><?= $m['id'] ?></td>
      <td><?= htmlspecialchars($m['nome']) ?></td>
      <td>
        <button onclick="editarModalidade(<?= htmlspecialchars(json_encode($m)) ?>)" class="btn-secondary"><i class="fas fa-edit"></i> Editar</button>
        <form method="POST" action="/admin/modalidades/excluir" class="inline-form" onsubmit="return confirm('Tem certeza?')">
          <input type="hidden" name="id" value="<?= $m['id'] ?>">
          <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Excluir</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<script>
function editarModalidade(modalidade) {
  document.getElementById('id').value = modalidade.id;
  document.getElementById('nome').value = modalidade.nome;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
