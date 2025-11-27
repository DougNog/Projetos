<h1><i class="fas fa-clipboard-check"></i> Nova Avaliação Física</h1>

<form method="POST" action="/avaliacoes/salvar" class="form">
  <label for="usuario_id">Aluno</label>
  <select name="usuario_id" id="usuario_id" required>
    <option value="">Selecione...</option>
    <?php foreach ($alunos as $aluno): ?>
      <option value="<?= $aluno['id'] ?>"><?= htmlspecialchars($aluno['nome']) ?> (<?= htmlspecialchars($aluno['email']) ?>)</option>
    <?php endforeach; ?>
  </select>

  <label for="data_avaliacao">Data da avaliação</label>
  <input type="date" name="data_avaliacao" id="data_avaliacao" required>

  <label for="peso">Peso (kg)</label>
  <input type="number" step="0.01" name="peso" id="peso" required>

  <label for="altura">Altura (m)</label>
  <input type="number" step="0.01" name="altura" id="altura" required>

  <label for="gordura_corporal">% Gordura corporal</label>
  <input type="number" step="0.01" name="gordura_corporal" id="gordura_corporal">

  <label for="observacoes">Observações</label>
  <textarea name="observacoes" id="observacoes" rows="4"></textarea>

  <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar</button>
</form>
