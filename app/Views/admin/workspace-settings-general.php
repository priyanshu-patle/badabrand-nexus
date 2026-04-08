<div class="dash-card">
    <div class="card-title-row"><h4>General Settings</h4><span>Business identity and support channels</span></div>
    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
        <input type="hidden" name="_redirect" value="/admin/settings/general">
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="support_email" value="<?= e($systemSettings['support_email'] ?? '') ?>" placeholder="Support email"></div>
            <div class="col-md-6"><input class="form-control" name="support_phone" value="<?= e($systemSettings['support_phone'] ?? '') ?>" placeholder="Support phone"></div>
            <div class="col-md-6"><input class="form-control" name="company_whatsapp" value="<?= e($systemSettings['company_whatsapp'] ?? '') ?>" placeholder="WhatsApp number"></div>
            <div class="col-md-6"><input class="form-control" name="product_version" value="<?= e($systemSettings['product_version'] ?? '') ?>" placeholder="Product version"></div>
            <div class="col-md-6"><input class="form-control" name="license_type" value="<?= e($systemSettings['license_type'] ?? '') ?>" placeholder="License type"></div>
            <div class="col-md-6"><input class="form-control" name="buyer_support_window" value="<?= e($systemSettings['buyer_support_window'] ?? '') ?>" placeholder="Support window"></div>
            <div class="col-md-6"><input class="form-control" name="release_channel" value="<?= e($systemSettings['release_channel'] ?? '') ?>" placeholder="Release channel"></div>
            <div class="col-md-6">
                <select class="form-select" name="theme_admin">
                    <?php foreach (theme_presets() as $key => $theme): ?>
                        <option value="<?= e($key) ?>" <?= ($systemSettings['theme_admin'] ?? $systemSettings['theme_default'] ?? 'dark') === $key ? 'selected' : '' ?>>Admin Theme: <?= e($theme['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <select class="form-select" name="theme_public">
                    <?php foreach (theme_presets() as $key => $theme): ?>
                        <option value="<?= e($key) ?>" <?= ($systemSettings['theme_public'] ?? $systemSettings['theme_default'] ?? 'dark') === $key ? 'selected' : '' ?>>Public Theme: <?= e($theme['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save General Settings</button>
    </form>
</div>
