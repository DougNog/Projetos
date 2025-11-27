<h1><i class="fas fa-bell"></i> Notificações de Turmas</h1>

<section>
    <?php if (empty($notificacoes)): ?>
        <p>Nenhuma notificação no momento.</p>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notificacoes as $notif): ?>
                <div class="notification-card">
                    <h3><?= htmlspecialchars($notif['modalidade']) ?></h3>
                    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($notif['data'])) ?> 
                       <strong>Horário:</strong> <?= substr($notif['inicio'],0,5) ?> - <?= substr($notif['fim'],0,5) ?></p>
                    <p><?= htmlspecialchars($notif['mensagem']) ?></p>
                    <small><?= date('d/m/Y H:i', strtotime($notif['data_envio'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>


