<h1><i class="fas fa-question-circle"></i> <?= $titulo ?></h1>

<?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
  <section class="admin-actions">
    <div class="action-buttons">
      <a href="/mensagens/nova" class="btn-primary">Nova Mensagem</a>
      <a href="/mensagens/segmentada" class="btn-primary">Mensagem Segmentada</a>
    </div>
  </section>
<?php endif; ?>

<?php if ($_SESSION['usuario']['perfil'] === 'aluno'): ?>
    <section>
        <h2>Enviar Dúvida ou Sugestão</h2>
        <form method="POST" action="/duvidas/criar" class="form">
            <div class="form-group">
                <label>Tipo:</label>
                <select name="tipo" required>
                    <option value="duvida">Dúvida</option>
                    <option value="sugestao">Sugestão</option>
                    <option value="reclamacao">Reclamação</option>
                </select>
            </div>
            <div class="form-group">
                <label>Assunto:</label>
                <input type="text" name="assunto" required>
            </div>
            <div class="form-group">
                <label>Mensagem:</label>
                <textarea name="mensagem" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar</button>
        </form>
    </section>
<?php endif; ?>

<section>
    <h2><?= $_SESSION['usuario']['perfil'] === 'admin' ? 'Todas as Dúvidas e Sugestões' : 'Minhas Mensagens' ?></h2>
    <table class="table">
        <thead>
            <tr>
                <?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
                    <th>Aluno</th>
                <?php endif; ?>
                <th>Tipo</th>
                <th>Assunto</th>
                <th>Mensagem</th>
                <th>Data</th>
                <th>Status</th>
                <?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
                    <th>Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($duvidas as $duvida): ?>
            <tr>
                <?php if ($_SESSION['usuario']['perfil'] === 'admin'): ?>
                    <td><?= htmlspecialchars($duvida['usuario_nome']) ?></td>
                <?php endif; ?>
                <td>
                  <?php 
                    $badgeClass = $duvida['tipo'] === 'sugestao' ? 'badge-read' : ($duvida['tipo'] === 'reclamacao' ? 'badge-unread' : 'badge-read');
                    $icon = $duvida['tipo'] === 'sugestao' ? 'fa-lightbulb' : ($duvida['tipo'] === 'reclamacao' ? 'fa-exclamation-triangle' : 'fa-question-circle');
                  ?>
                  <span class="<?= $badgeClass ?>">
                    <i class="fas <?= $icon ?>"></i> <?= ucfirst(htmlspecialchars($duvida['tipo'])) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($duvida['assunto']) ?></td>
                <td><?= htmlspecialchars($duvida['mensagem']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($duvida['data_criacao'])) ?></td>
                <td>
                  <?php if ($duvida['resposta']): ?>
                    <span class="badge-read"><i class="fas fa-check"></i> Respondida</span>
                  <?php else: ?>
                    <span class="badge-unread"><i class="fas fa-clock"></i> Pendente</span>
                  <?php endif; ?>
                </td>
                <?php if ($_SESSION['usuario']['perfil'] === 'admin' && !$duvida['resposta']): ?>
                    <td>
                        <form method="POST" action="/duvidas/responder" class="inline-form">
                            <input type="hidden" name="id" value="<?= $duvida['id'] ?>">
                            <textarea name="resposta" rows="2" placeholder="Digite a resposta..." required></textarea>
                            <button type="submit" class="btn-primary"><i class="fas fa-reply"></i> Responder</button>
                        </form>
                    </td>
                <?php elseif ($_SESSION['usuario']['perfil'] === 'admin' && $duvida['resposta']): ?>
                    <td>
                        <strong>Resposta:</strong><br>
                        <?= htmlspecialchars($duvida['resposta']) ?><br>
                        <small>Por: <?= htmlspecialchars($duvida['respondente_nome']) ?> em <?= date('d/m/Y H:i', strtotime($duvida['data_resposta'])) ?></small>
                    </td>
                <?php elseif ($duvida['resposta']): ?>
                    <td>
                        <strong>Resposta:</strong><br>
                        <?= htmlspecialchars($duvida['resposta']) ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>


