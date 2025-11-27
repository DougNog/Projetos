<h1><i class="fas fa-bell"></i> Alertas de Avaliação Física</h1>

<section class="admin-actions">
  <div class="action-buttons">
    <a href="/avaliacoes/nova" class="btn-primary"><i class="fas fa-plus"></i> Nova Avaliação</a>
    <a href="/avaliacoes" class="btn-secondary"><i class="fas fa-list"></i> Ver Todas as Avaliações</a>
  </div>
</section>

<section>
    <?php if (empty($alertas)): ?>
        <p>Todos os alunos estão com as avaliações em dia.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Email</th>
                    <th>Última Avaliação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($alertas as $alerta): ?>
                <tr>
                    <td><?= htmlspecialchars($alerta['usuario']['nome']) ?></td>
                    <td><?= htmlspecialchars($alerta['usuario']['email']) ?></td>
                    <td>
                        <?php if ($alerta['ultima_avaliacao']): ?>
                            <?= date('d/m/Y', strtotime($alerta['ultima_avaliacao']['data_avaliacao'])) ?>
                        <?php else: ?>
                            Nunca avaliado
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="/avaliacoes/alertas/enviar" class="inline-form">
                            <input type="hidden" name="usuario_id" value="<?= $alerta['usuario']['id'] ?>">
                            <button type="submit" class="btn-primary"><i class="fas fa-bell"></i> Enviar Alerta</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>


