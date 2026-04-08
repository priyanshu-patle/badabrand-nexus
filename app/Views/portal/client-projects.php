<div class="row g-4">
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Project Tracker</h4><span>Live progress</span></div>
            <?php foreach ($orders as $order): ?>
                <div class="progress-row">
                    <div class="d-flex justify-content-between"><strong><?= e($order['order_number']) ?></strong><span><?= e($order['progress_percent']) ?>%</span></div>
                    <div class="progress my-2"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
                    <small><?= e($order['service_name'] ?? 'Service') ?> | <?= e($order['status']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Project Updates</h4><span>Team collaboration feed</span></div>
            <?php foreach ($updates as $update): ?>
                <div class="activity-item"><strong><?= e($update['title']) ?></strong><br><?= e($update['details']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
