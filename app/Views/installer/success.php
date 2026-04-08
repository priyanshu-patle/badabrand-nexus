<?php $payload = $successPayload ?? []; ?>
<section class="installer-wrap">
    <div class="installer-success-card">
        <div class="installer-brand installer-brand-center">
            <img src="<?= asset('images/badabrand-logo.svg') ?>" alt="Badabrand Logo">
            <div>
                <strong>Badabrand Technologies</strong>
                <small>Installation Complete</small>
            </div>
        </div>
        <div class="installer-section-head text-center">
            <span class="eyebrow">Success</span>
            <h1>Your platform is ready</h1>
            <p>The installer has completed successfully. The platform is now locked against accidental reinstallation and ready for admin login.</p>
        </div>
        <div class="installer-summary-grid">
            <div class="mini-panel">
                <strong>Public site</strong>
                <span><?= e((string) ($payload['site_url'] ?? route_url('/'))) ?></span>
            </div>
            <div class="mini-panel">
                <strong>Admin login URL</strong>
                <span><?= e((string) ($payload['login_url'] ?? route_url('/login'))) ?></span>
            </div>
            <div class="mini-panel">
                <strong>Admin email</strong>
                <span><?= e((string) ($payload['admin_email'] ?? '')) ?></span>
            </div>
            <div class="mini-panel">
                <strong>Installed at</strong>
                <span><?= e((string) ($payload['installed_at'] ?? date('c'))) ?></span>
            </div>
        </div>
        <div class="mini-panel mt-4">
            <strong>Next steps</strong>
            <span>Login to the admin panel, review branding, SMTP, appearance settings, and remove any demo content if you installed it for preview purposes.</span>
        </div>
        <div class="installer-actions justify-content-center mt-4">
            <a class="btn btn-primary rounded-pill" href="<?= e((string) ($payload['login_url'] ?? route_url('/login'))) ?>">Go to Admin Login</a>
            <a class="btn btn-outline-light rounded-pill" href="<?= e((string) ($payload['site_url'] ?? route_url('/'))) ?>">Open Website</a>
        </div>
        <p class="small-text text-center mt-3">For security, the installer is now locked. To reinstall, you must manually remove the install lock and `.env` configuration.</p>
    </div>
</section>
