<div class="dash-card dashboard-panel-card">
    <div class="dashboard-panel-head">
        <div>
            <span class="section-kicker">Modules</span>
            <h3>Extensions manager</h3>
            <p class="mb-0 text-muted">Activate, deactivate, and review extension packages without touching core files.</p>
        </div>
        <div class="toolbar-actions">
            <span class="badge-soft">Total <?= e((string) ($moduleStats['total'] ?? 0)) ?></span>
            <a class="btn btn-primary" href="<?= route_url('/admin/modules/upload') ?>">Upload Module</a>
        </div>
    </div>
</div>

<div class="module-manager-grid mt-4">
    <?php if ($modules === []): ?>
        <div class="dash-card dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-box-seam"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No modules installed</h5>
                <p>Upload a compatible module package to extend the workspace with new tools, automation, or product features.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="<?= route_url('/admin/modules/upload') ?>">Upload Module</a>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/modules/hooks') ?>">Hooks Explorer</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($modules as $module): ?>
            <article class="dash-card module-manager-card">
                <div class="module-manager-head">
                    <div class="module-manager-icon">
                        <i class="bi bi-plugin"></i>
                    </div>
                    <div>
                        <h4><?= e($module['name']) ?></h4>
                        <p><?= e((string) ($module['description'] ?? 'No description provided.')) ?></p>
                    </div>
                </div>
                <div class="module-manager-meta">
                    <span class="badge-soft"><?= e($module['status']) ?></span>
                    <span class="badge-soft"><?= e($module['slug']) ?></span>
                    <span class="badge-soft">v<?= e((string) $module['version']) ?></span>
                </div>
                <div class="module-manager-actions">
                    <?php if (($module['status'] ?? '') === 'active'): ?>
                        <form method="post" action="<?= route_url('/admin/modules/deactivate') ?>">
                            <input type="hidden" name="slug" value="<?= e($module['slug']) ?>">
                            <button class="btn btn-outline-light" type="submit">Deactivate</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="<?= route_url('/admin/modules/activate') ?>">
                            <input type="hidden" name="slug" value="<?= e($module['slug']) ?>">
                            <button class="btn btn-primary" type="submit" <?= ! empty($module['can_activate']) ? '' : 'disabled' ?>>Activate</button>
                        </form>
                    <?php endif; ?>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/modules/hooks') ?>">Open Hooks</a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
