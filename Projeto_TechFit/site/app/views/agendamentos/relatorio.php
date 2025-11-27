<h1><i class="fas fa-chart-pie"></i> Relatório de Ocupação das Turmas</h1>

<section class="admin-actions">
  <div class="action-buttons">
    <a href="/admin/turmas" class="btn-secondary"><i class="fas fa-calendar-week"></i> Gerenciar Turmas</a>
    <a href="/agendamentos" class="btn-secondary"><i class="fas fa-calendar-alt"></i> Ver Agendamentos</a>
  </div>
</section>

<form method="GET" action="/agendamentos/relatorio" class="filter-form">
    <div class="form-group">
        <label>Data Início:</label>
        <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Data Fim:</label>
        <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim ?? '') ?>">
    </div>
    <button type="submit" class="btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
</form>

<section>
    <table class="table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Horário</th>
                <th>Modalidade</th>
                <th>Instrutor</th>
                <th>Vagas</th>
                <th>Ocupadas</th>
                <th>Lista de Espera</th>
                <th>Ocupação</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($relatorio as $item): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($item['data'])) ?></td>
                <td><?= substr($item['inicio'],0,5) ?> - <?= substr($item['fim'],0,5) ?></td>
                <td><?= htmlspecialchars($item['modalidade']) ?></td>
                <td><?= htmlspecialchars($item['instrutor']) ?></td>
                <td><?= $item['vagas'] ?></td>
                <td><?= $item['ocupadas'] ?></td>
                <td><?= $item['lista_espera'] ?></td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $item['ocupacao_percentual'] ?>%"></div>
                        <span><?= number_format($item['ocupacao_percentual'], 1) ?>%</span>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>


