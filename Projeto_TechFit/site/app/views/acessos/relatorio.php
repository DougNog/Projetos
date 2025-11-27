<h1><i class="fas fa-chart-line"></i> Relatório de Utilização</h1>

<section class="admin-actions">
  <div class="action-buttons">
    <a href="/acessos" class="btn-secondary"><i class="fas fa-list"></i> Ver Todos os Acessos</a>
    <a href="/admin/acessos" class="btn-secondary"><i class="fas fa-door-open"></i> Acessos Detalhados</a>
  </div>
</section>

<form method="GET" action="/acessos/relatorio" class="filter-form">
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
                <th>Usuários Únicos</th>
                <th>Total de Acessos</th>
                <th>Tempo Médio (minutos)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($relatorio as $item): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($item['data'])) ?></td>
                <td><?= $item['usuarios_unicos'] ?></td>
                <td><?= $item['total_acessos'] ?></td>
                <td><?= number_format($item['tempo_medio_minutos'] ?? 0, 1) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>


