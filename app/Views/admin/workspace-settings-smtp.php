<div class="dash-card">
    <div class="card-title-row"><h4>SMTP Settings</h4><span>Mail sender and transport</span></div>
    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
        <input type="hidden" name="_redirect" value="/admin/settings/smtp">
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="smtp_host" value="<?= e($systemSettings['smtp_host'] ?? '') ?>" placeholder="SMTP host"></div>
            <div class="col-md-2"><input class="form-control" name="smtp_port" value="<?= e($systemSettings['smtp_port'] ?? '') ?>" placeholder="Port"></div>
            <div class="col-md-6"><input class="form-control" name="smtp_username" value="<?= e($systemSettings['smtp_username'] ?? '') ?>" placeholder="Username"></div>
            <div class="col-md-6"><input class="form-control" name="smtp_password" value="<?= e($systemSettings['smtp_password'] ?? '') ?>" placeholder="Password"></div>
            <div class="col-md-3"><input class="form-control" name="smtp_from_name" value="<?= e($systemSettings['smtp_from_name'] ?? '') ?>" placeholder="From name"></div>
            <div class="col-md-3"><input class="form-control" name="smtp_from_email" value="<?= e($systemSettings['smtp_from_email'] ?? '') ?>" placeholder="From email"></div>
        </div>
        <button class="btn btn-primary" type="submit">Save SMTP Settings</button>
    </form>
</div>
