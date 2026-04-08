<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Project Queue</h4>
            <p class="mb-0 text-muted">Approved, active, and completed delivery work across customers.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/admin/projects/create') ?>">Create Project</a>
    </div>

    <?php foreach ($orders as $order): ?>
        <div class="dash-form-block">
            <div class="list-row">
                <div>
                    <strong><?= e($order['order_number']) ?></strong>
                    <span><?= e($order['display_name'] ?? $order['service_name'] ?? 'Project') ?></span>
                </div>
                <span class="badge-soft"><?= e((string) $order['progress_percent']) ?>%</span>
            </div>
            <div class="progress my-3"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
            <div class="toolbar-actions">
                <span class="badge-soft"><?= e($order['status']) ?></span>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/projects/tasks') ?>">Tasks</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
