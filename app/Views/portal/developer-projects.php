<div class="dash-card">
    <div class="card-title-row"><h4>Assigned Projects</h4><span>Developer work queue</span></div>
    <?php foreach ($orders as $order): ?>
        <div class="progress-row">
            <div class="d-flex justify-content-between"><strong><?= e($order['order_number']) ?> - <?= e($order['service_name'] ?? 'Service') ?></strong><span><?= e($order['progress_percent']) ?>%</span></div>
            <div class="progress my-2"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
            <small><?= e($order['status']) ?></small>
        </div>
    <?php endforeach; ?>
</div>
