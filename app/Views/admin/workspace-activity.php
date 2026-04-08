<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>System Activity</h4>
            <p class="mb-0 text-muted">Hook-backed logs for key account, order, payment, ticket, and module events.</p>
        </div>
        <span class="badge-soft"><?= e((string) count($activityLogs)) ?> entries</span>
    </div>

    <?php if ($activityLogs): ?>
        <?php foreach ($activityLogs as $entry): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($entry['summary']) ?></strong>
                        <span><?= e($entry['event_type']) ?><?= ! empty($entry['actor_label']) ? ' | ' . e($entry['actor_label']) : '' ?></span>
                    </div>
                    <span class="badge-soft"><?= e((string) $entry['created_at']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="mb-0 text-muted">No activity has been logged yet.</p>
    <?php endif; ?>
</div>
