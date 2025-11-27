<div class="login-page">
  <div class="login-card">
    <h1 class="login-title">TechFit</h1>
    <p class="login-subtitle">Acesse sua conta</p>

    <?php if (!empty($erro)): ?>
      <div class="alert-error">
        <?= htmlspecialchars($erro) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/login" id="formLogin">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>

      <label for="senha">Senha</label>
      <div class="password-group">
        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
        <button type="button" class="toggle-password" aria-label="Mostrar ou ocultar senha">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <button type="submit" class="btn-login-submit">Entrar</button>
    </form>

    <a href="/" class="back-link">← Voltar para a página inicial</a>
  </div>
</div>
