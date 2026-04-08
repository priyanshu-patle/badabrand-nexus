<div class="row g-4">
    <div class="col-md-4"><div class="metric-card"><span>Project Queue</span><strong><?= e((string) count($orders)) ?></strong></div></div>
    <div class="col-md-4"><div class="metric-card"><span>Support Queue</span><strong><?= e((string) count($tickets)) ?></strong></div></div>
    <div class="col-md-4"><div class="metric-card"><span>Delivery Mode</span><strong>Active</strong></div></div>
</div>
<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Assigned Project Queue</h4><span>Live project tracker</span></div>
            <?php foreach (array_slice($orders, 0, 6) as $order): ?>
                <div class="progress-row">
                    <div class="d-flex justify-content-between"><strong><?= e($order['order_number']) ?> - <?= e($order['service_name'] ?? 'Service') ?></strong><span><?= e($order['progress_percent']) ?>%</span></div>
                    <div class="progress my-2"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
                    <small><?= e($order['status']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Ticket Queue</h4><span>Support handoff</span></div>
            <?php foreach (array_slice($tickets, 0, 5) as $ticket): ?>
                <div class="activity-item"><?= e($ticket['subject']) ?> - <?= e($ticket['status']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
