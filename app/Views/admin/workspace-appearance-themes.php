<div class="row g-4">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row">
                <h4>Theme Library</h4>
                <span>Choose default admin and public appearance modes</span>
            </div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
                <input type="hidden" name="_redirect" value="/admin/appearance/themes">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Admin Theme</label>
                        <select class="form-select" name="theme_admin">
                            <?php foreach ($themePresets as $key => $theme): ?>
                                <option value="<?= e($key) ?>" <?= ($systemSettings['theme_admin'] ?? $systemSettings['theme_default'] ?? 'dark') === $key ? 'selected' : '' ?>><?= e($theme['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Public Theme</label>
                        <select class="form-select" name="theme_public">
                            <?php foreach ($themePresets as $key => $theme): ?>
                                <option value="<?= e($key) ?>" <?= ($systemSettings['theme_public'] ?? $systemSettings['theme_default'] ?? 'dark') === $key ? 'selected' : '' ?>><?= e($theme['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="theme-grid mt-4">
                    <?php foreach ($themePresets as $key => $theme): ?>
                        <div class="theme-card-preview <?= (($systemSettings['theme_admin'] ?? $systemSettings['theme_default'] ?? 'dark') === $key || ($systemSettings['theme_public'] ?? $systemSettings['theme_default'] ?? 'dark') === $key) ? 'is-selected' : '' ?>">
                            <span class="theme-card-swatch" style="background: <?= e($theme['swatch']) ?>;"></span>
                            <div class="theme-card-copy">
                                <strong><?= e($theme['name']) ?></strong>
                                <span><?= e($theme['description']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save Theme Settings</button>
            </form>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row">
                <h4>Theme Notes</h4>
                <span>Business-safe appearance defaults</span>
            </div>
            <div class="mini-panel">
                <strong>Dark Pro</strong>
                <span>Best for operations teams, billing desks, and long daily sessions.</span>
            </div>
            <div class="mini-panel mt-3">
                <strong>Light Classic</strong>
                <span>Ideal for bright-office environments and client demos.</span>
            </div>
            <div class="mini-panel mt-3">
                <strong>Midnight Glass</strong>
                <span>Premium darker variant for product demos, screenshots, and launch materials.</span>
            </div>
        </div>
    </div>
</div>
