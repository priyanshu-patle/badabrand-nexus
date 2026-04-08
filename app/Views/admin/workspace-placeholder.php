<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4><?= e($pageTitle ?? 'Workspace') ?></h4>
            <p class="mb-0 text-muted"><?= e($summary ?? '') ?></p>
        </div>
        <span class="badge-soft"><?= e($adminSection ?? 'admin') ?></span>
    </div>

    <?php if (! empty($bullets)): ?>
        <div class="stack-list compact mt-4">
            <?php foreach ($bullets as $bullet): ?>
                <li><?= e($bullet) ?></li>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (! empty($actions)): ?>
        <div class="toolbar-actions mt-4">
            <?php foreach ($actions as $action): ?>
                <a class="btn btn-outline-light" href="<?= route_url($action['href']) ?>"><?= e($action['label']) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
