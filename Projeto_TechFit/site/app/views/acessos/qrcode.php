<h1><i class="fas fa-qrcode"></i> <?= $titulo ?></h1>

<?php if ($isAdmin ?? false && !empty($alunos)): ?>
  <section>
    <form method="GET" action="/acessos/qrcode" class="filter-form">
      <div class="form-group">
        <label>Selecionar Aluno:</label>
        <select name="usuario_id" onchange="this.form.submit()">
          <option value="">Selecione um aluno...</option>
          <?php foreach ($alunos as $aluno): ?>
            <option value="<?= $aluno['id'] ?>" <?= $usuarioId == $aluno['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($aluno['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </section>
<?php endif; ?>

<section class="qrcode-section">
    <div class="qrcode-container">
        <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
        <div class="qrcode-display" id="qrcode"></div>
        <p><strong>Código:</strong> <?= htmlspecialchars($codigoQR) ?></p>
        <p class="info-text">Apresente este QR Code na catraca para acessar a academia.</p>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qrcode"), {
        text: "<?= htmlspecialchars($codigoQR) ?>",
        width: 256,
        height: 256
    });
});
</script>


