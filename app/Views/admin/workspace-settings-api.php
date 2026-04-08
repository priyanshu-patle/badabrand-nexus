<div class="dash-card">
    <div class="card-title-row"><h4>API Settings</h4><span>Versioning and API readiness</span></div>
    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
        <input type="hidden" name="_redirect" value="/admin/settings/api">
        <input class="form-control" name="api_enabled" value="<?= e($systemSettings['api_enabled'] ?? '0') ?>" placeholder="API enabled flag (0/1)">
        <input class="form-control" name="api_default_version" value="<?= e($systemSettings['api_default_version'] ?? 'v1') ?>" placeholder="Default API version">
        <input class="form-control" name="api_token_ttl" value="<?= e($systemSettings['api_token_ttl'] ?? '3600') ?>" placeholder="Token TTL in seconds">
        <button class="btn btn-primary" type="submit">Save API Settings</button>
    </form>
    <div class="mini-panel mt-4">
        <strong>Next phase</strong>
        <span>Expose `/api/v1` and `/api/v2` using token-based auth without changing current admin routes.</span>
    </div>
</div>
