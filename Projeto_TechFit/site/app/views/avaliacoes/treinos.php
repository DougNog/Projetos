<h1><i class="fas fa-dumbbell"></i> <?= $titulo ?></h1>

<?php if ($isAdmin ?? false && !empty($alunos)): ?>
  <section>
    <form method="GET" action="/avaliacoes/treinos" class="filter-form">
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

<section>
    <?php if (empty($treinos)): ?>
        <p>Nenhum treino personalizado disponível ainda. Faça uma avaliação física para receber sugestões de treino.</p>
    <?php else: ?>
        <?php foreach ($treinos as $treino): ?>
            <div class="treino-card">
                <h3><?= htmlspecialchars($treino['descricao']) ?></h3>
                <p><strong>Exercícios:</strong> <?= htmlspecialchars($treino['exercicios']) ?></p>
                <?php if ($treino['observacoes']): ?>
                    <p><strong>Observações:</strong> <?= htmlspecialchars($treino['observacoes']) ?></p>
                <?php endif; ?>
                <small>Criado em: <?= date('d/m/Y H:i', strtotime($treino['data_criacao'])) ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>


