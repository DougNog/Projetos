<h1><i class="fas fa-calendar-alt"></i> Agendamento de Aulas</h1>

<?php if (!($isAdmin ?? false)): ?>
<section>
  <h2><i class="fas fa-calendar-plus"></i> Novo Agendamento Personalizado</h2>
  <form method="POST" action="/agendamentos/criar" class="form">
    <div class="form-group">
      <label>Data:</label>
      <input type="date" name="data" required min="<?= date('Y-m-d') ?>">
    </div>
    <div class="form-group">
      <label>Horário de Início:</label>
      <input type="time" name="horario_inicio" required>
    </div>
    <div class="form-group">
      <label>Horário de Término:</label>
      <input type="time" name="horario_fim" required>
    </div>
    <div class="form-group">
      <label>Modalidade:</label>
      <select name="modalidade" required>
        <option value="">Selecione uma modalidade...</option>
        <?php foreach ($modalidades ?? [] as $mod): ?>
          <option value="<?= htmlspecialchars($mod['nome']) ?>"><?= htmlspecialchars($mod['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Observações (opcional):</label>
      <textarea name="observacoes" rows="3" placeholder="Informações adicionais sobre o agendamento..."></textarea>
    </div>
    <button type="submit" class="btn-primary"><i class="fas fa-calendar-check"></i> Agendar</button>
  </form>
</section>
<?php endif; ?>

<section>
  <h2><i class="fas fa-clock"></i> Próximas Turmas Disponíveis</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Data</th>
        <th>Horário</th>
        <th>Modalidade</th>
        <th>Instrutor</th>
        <th>Vagas/Ocupação</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($turmas as $turma): ?>
      <tr>
        <td><?= date('d/m/Y', strtotime($turma['data'])) ?></td>
        <td><?= substr($turma['inicio'],0,5) ?> - <?= substr($turma['fim'],0,5) ?></td>
        <td><?= htmlspecialchars($turma['modalidade']) ?></td>
        <td><?= htmlspecialchars($turma['instrutor']) ?></td>
        <td>
          <?= ($turma['ocupadas'] ?? 0) ?>/<?= $turma['vagas'] ?> (<?= number_format($turma['ocupacao_percentual'] ?? 0, 1) ?>%)
        </td>
        <td>
          <?php if (!($isAdmin ?? false)): ?>
            <form method="POST" action="/agendamentos/criar">
              <input type="hidden" name="turma_id" value="<?= $turma['id'] ?>">
              <button type="submit" class="btn-primary" <?= ($turma['ocupadas'] ?? 0) >= $turma['vagas'] ? 'disabled title="Turma lotada"' : '' ?>><i class="fas fa-calendar-plus"></i> Agendar</button>
            </form>
          <?php else: ?>
            <span class="text-muted">Apenas alunos podem agendar</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>

<section>
  <h2><i class="fas fa-list"></i> <?= ($isAdmin ?? false) ? 'Todos os Agendamentos' : 'Meus Agendamentos' ?></h2>
  <table class="table">
    <thead>
      <tr>
        <?php if ($isAdmin ?? false): ?>
          <th>Aluno</th>
        <?php endif; ?>
        <th>Data</th>
        <th>Horário</th>
        <th>Modalidade</th>
        <?php if ($isAdmin ?? false): ?>
          <th>Observações</th>
        <?php endif; ?>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($meusAgendamentos as $ag): ?>
      <tr>
        <?php if ($isAdmin ?? false): ?>
          <td><?= htmlspecialchars($ag['usuario_nome'] ?? 'N/A') ?></td>
        <?php endif; ?>
        <td><?= $ag['data'] ? date('d/m/Y', strtotime($ag['data'])) : '-' ?></td>
        <td>
          <?php if ($ag['inicio'] && $ag['fim']): ?>
            <?= substr($ag['inicio'],0,5) ?> - <?= substr($ag['fim'],0,5) ?>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($ag['modalidade'] ?? '-') ?></td>
        <?php if ($isAdmin ?? false): ?>
          <td><?= htmlspecialchars($ag['observacoes'] ?? '-') ?></td>
        <?php endif; ?>
        <td>
          <?php if ($ag['status'] === 'confirmado'): ?>
            <span class="badge-read"><i class="fas fa-check-circle"></i> Confirmado</span>
          <?php elseif ($ag['status'] === 'pendente'): ?>
            <span class="badge-unread"><i class="fas fa-hourglass-half"></i> Pendente</span>
          <?php else: ?>
            <span class="badge-unread"><i class="fas fa-clock"></i> Lista de Espera</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($isAdmin ?? false): ?>
            <?php if ($ag['status'] === 'pendente'): ?>
              <form method="POST" action="/agendamentos/confirmar" class="inline-form">
                <input type="hidden" name="id" value="<?= $ag['id'] ?>">
                <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Confirmar</button>
              </form>
            <?php endif; ?>
            <form method="POST" action="/agendamentos/cancelar" class="inline-form" onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?')">
              <input type="hidden" name="id" value="<?= $ag['id'] ?>">
              <button type="submit" class="btn-danger"><i class="fas fa-times"></i> Cancelar</button>
            </form>
          <?php elseif ($ag['usuario_id'] == $_SESSION['usuario']['id']): ?>
            <form method="POST" action="/agendamentos/cancelar" class="inline-form" onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?')">
              <input type="hidden" name="id" value="<?= $ag['id'] ?>">
              <button type="submit" class="btn-secondary"><i class="fas fa-times"></i> Cancelar</button>
            </form>
          <?php else: ?>
            <span class="text-muted">-</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
