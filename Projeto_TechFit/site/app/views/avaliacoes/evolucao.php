<h1><i class="fas fa-chart-line"></i> <?= $titulo ?></h1>

<?php if ($isAdmin ?? false && !empty($alunos)): ?>
  <section>
    <form method="GET" action="/avaliacoes/evolucao" class="filter-form">
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
    <?php if (empty($evolucao)): ?>
        <p>Nenhuma avaliação registrada ainda.</p>
    <?php else: ?>
        <div class="chart-container">
            <canvas id="evolucaoChart"></canvas>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Peso (kg)</th>
                    <th>Altura (m)</th>
                    <th>IMC</th>
                    <th>Gordura Corporal (%)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($evolucao as $av): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?></td>
                    <td><?= number_format($av['peso'], 2) ?></td>
                    <td><?= number_format($av['altura'], 2) ?></td>
                    <td><?= number_format($av['imc'], 2) ?></td>
                    <td><?= number_format($av['gordura_corporal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php if (!empty($evolucao)): ?>
const ctx = document.getElementById('evolucaoChart').getContext('2d');
const evolucaoChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php foreach ($evolucao as $av): ?>'<?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?>',<?php endforeach; ?>],
        datasets: [{
            label: 'Peso (kg)',
            data: [<?php foreach ($evolucao as $av): ?><?= $av['peso'] ?>,<?php endforeach; ?>],
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }, {
            label: 'IMC',
            data: [<?php foreach ($evolucao as $av): ?><?= $av['imc'] ?>,<?php endforeach; ?>],
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }, {
            label: 'Gordura Corporal (%)',
            data: [<?php foreach ($evolucao as $av): ?><?= $av['gordura_corporal'] ?>,<?php endforeach; ?>],
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false
            }
        }
    }
});
<?php endif; ?>
</script>


