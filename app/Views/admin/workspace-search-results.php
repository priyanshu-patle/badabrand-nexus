<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Global Search</h4>
            <p class="mb-0 text-muted">Cross-module search for users, services, orders, tickets, and projects.</p>
        </div>
        <span class="badge-soft"><?= e($globalSearch ?: 'No query') ?></span>
    </div>
</div>

<?php foreach ($searchResults as $group => $items): ?>
    <div class="dash-card mt-4">
        <div class="card-title-row">
            <h4><?= e($group) ?></h4>
            <span><?= e((string) count($items)) ?> results</span>
        </div>
        <?php if ($items): ?>
            <?php foreach ($items as $item): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($item['label']) ?></strong>
                        <span><?= e($item['meta'] ?? '') ?></span>
                    </div>
                    <a class="badge-soft" href="<?= route_url($item['href']) ?>">Open</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="mb-0 text-muted">No <?= e(strtolower($group)) ?> matched this query.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
