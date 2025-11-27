<h1><i class="fas fa-envelope"></i> Nova Mensagem</h1>

<form method="POST" action="/mensagens/enviar" class="form">
  <label for="destinatario_id">Destinatário</label>
  <select name="destinatario_id" id="destinatario_id" required>
    <option value="">Selecione...</option>
    <?php foreach ($alunos as $aluno): ?>
      <option value="<?= $aluno['id'] ?>"><?= htmlspecialchars($aluno['nome']) ?> (<?= htmlspecialchars($aluno['email']) ?>)</option>
    <?php endforeach; ?>
  </select>

  <label for="assunto">Assunto</label>
  <input type="text" name="assunto" id="assunto" required>

  <label for="corpo">Mensagem</label>
  <textarea name="corpo" id="corpo" rows="5" required></textarea>

  <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar</button>
</form>
