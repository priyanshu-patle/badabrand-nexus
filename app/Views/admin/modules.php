<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="search-toolbar">
                <div>
                    <h4 class="mb-1">Module Runtime</h4>
                    <p class="mb-0 text-muted">Manage plug-and-play extensions, hook registrations, dependency safety, and ZIP-based installs without changing core files.</p>
                </div>
                <div class="toolbar-actions">
                    <span class="badge-soft">Total <?= e((string) ($moduleStats['total'] ?? 0)) ?></span>
                    <span class="badge-soft">Active <?= e((string) ($moduleStats['active'] ?? 0)) ?></span>
                    <span class="badge-soft">Inactive <?= e((string) ($moduleStats['inactive'] ?? 0)) ?></span>
                    <span class="badge-soft">With deps <?= e((string) ($moduleStats['with_dependencies'] ?? 0)) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row">
                <h4>Install Module ZIP</h4>
                <span>Upload, extract, run `install.sql`, and register metadata</span>
            </div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/modules/upload') ?>" enctype="multipart/form-data">
                <input class="form-control" type="file" name="module_zip" accept=".zip" required>
                <div class="small text-muted">
                    Required structure: <code>module.json</code>, <code>install.sql</code>, <code>routes.php</code>, <code>controllers/</code>, <code>models/</code>, <code>views/</code>.
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Upload & Install</button>
            </form>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row">
                <h4>Core Hook Events</h4>
                <span>Use <code>add_action()</code> and <code>add_filter()</code> inside modules</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($coreEvents as $event): ?>
                    <span class="badge-soft"><?= e($event) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row">
                <h4>Recent Activity</h4>
                <span>Install, upgrade, activation, and deletion history</span>
            </div>
            <?php if ($activity): ?>
                <?php foreach ($activity as $entry): ?>
                    <div class="dash-form-block">
                        <div class="card-title-row mb-2">
                            <div>
                                <h5 class="mb-1"><?= e($entry['module_name']) ?></h5>
                                <span><?= e(ucfirst($entry['action'])) ?> at <?= e((string) $entry['created_at']) ?></span>
                            </div>
                            <span class="badge-soft"><?= e($entry['module_slug']) ?></span>
                        </div>
                        <p class="mb-2 text-muted"><?= e((string) ($entry['notes'] ?? '')) ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (! empty($entry['from_version'])): ?><span class="badge-soft">From <?= e($entry['from_version']) ?></span><?php endif; ?>
                            <?php if (! empty($entry['to_version'])): ?><span class="badge-soft">To <?= e($entry['to_version']) ?></span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No module activity yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row">
                <h4>Installed Modules</h4>
                <span>Dependency-aware activation and isolated route loading</span>
            </div>
            <?php if ($modules): ?>
                <?php foreach ($modules as $module): ?>
                    <div class="dash-form-block">
                        <div class="card-title-row mb-3">
                            <div>
                                <h5 class="mb-1"><?= e($module['name']) ?></h5>
                                <span><?= e((string) ($module['description'] ?? 'No description provided.')) ?></span>
                            </div>
                            <span class="badge-soft"><?= e(ucfirst((string) $module['status'])) ?></span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><span class="badge-soft w-100 text-start">Slug: <?= e($module['slug']) ?></span></div>
                            <div class="col-md-4"><span class="badge-soft w-100 text-start">Version: <?= e((string) $module['version']) ?></span></div>
                            <div class="col-md-4"><span class="badge-soft w-100 text-start">Directory: <?= e((string) $module['directory_name']) ?></span></div>
                            <div class="col-md-6"><span class="badge-soft w-100 text-start">Routes: <?= ! empty($module['routes_file_exists']) ? 'ready' : 'missing' ?></span></div>
                            <div class="col-md-6"><span class="badge-soft w-100 text-start">Install SQL: <?= ! empty($module['install_file_exists']) ? 'ready' : 'optional / missing' ?></span></div>
                        </div>

                        <?php if (! empty($module['dependencies'])): ?>
                            <div class="mb-3">
                                <strong class="d-block mb-2">Dependencies</strong>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($module['dependencies'] as $dependency): ?>
                                        <span class="badge-soft"><?= e($dependency['slug']) ?> <?= e($dependency['constraint']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (! empty($module['dependency_errors'])): ?>
                            <div class="alert alert-warning mb-3">
                                <?php foreach ($module['dependency_errors'] as $error): ?>
                                    <div><?= e($error) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="admin-tools">
                            <div class="d-flex flex-wrap gap-2">
                                <?php if (! empty($module['manifest']['namespace'])): ?><span class="badge-soft"><?= e($module['manifest']['namespace']) ?></span><?php endif; ?>
                                <?php if (! empty($module['installed_at'])): ?><span class="badge-soft">Installed <?= e((string) $module['installed_at']) ?></span><?php endif; ?>
                                <?php if (! empty($module['activated_at'])): ?><span class="badge-soft">Activated <?= e((string) $module['activated_at']) ?></span><?php endif; ?>
                            </div>
                            <div class="toolbar-actions">
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
                                <form method="post" action="<?= route_url('/admin/modules/delete') ?>" onsubmit="return confirm('Delete this module directory and registration?')">
                                    <input type="hidden" name="slug" value="<?= e($module['slug']) ?>">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No modules discovered yet. Upload a ZIP or place a module folder inside <code>/modules</code>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
