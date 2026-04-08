<div class="document-actions">
    <button class="btn btn-warning" onclick="window.print()">Download / Print PDF</button>
    <a class="btn btn-outline-light" href="<?= route_url(($user['role'] ?? 'customer') === 'admin' ? '/admin/projects' : '/client/contracts') ?>">Back</a>
</div>

<section class="document-sheet invoice-sheet-yellow">
    <div class="document-accent"></div>
    <div class="document-header">
        <div>
            <span class="document-kicker">Service Agreement</span>
            <h1><?= e($contract['title']) ?></h1>
            <p class="document-meta mb-0">Client: <?= e(trim($contract['first_name'] . ' ' . $contract['last_name'])) ?></p>
        </div>
        <div class="document-title-wrap">
            <div class="document-brand"><?= e($settings['footer_company_name'] ?? $settings['site_title'] ?? 'Badabrand Technologies') ?></div>
            <p class="document-meta mb-1"><?= e($contract['email']) ?></p>
            <p class="document-meta mb-0">Status: <?= e($contract['status']) ?></p>
        </div>
    </div>

    <div class="document-grid">
        <div class="document-panel">
            <h5>Agreement Summary</h5>
            <p class="mb-1">Provider: <?= e($settings['footer_company_name'] ?? 'Badabrand Technologies') ?></p>
            <p class="mb-1">Client: <?= e(trim($contract['first_name'] . ' ' . $contract['last_name'])) ?></p>
            <p class="mb-0">Execution Model: milestone based delivery and support</p>
        </div>
        <div class="document-panel">
            <h5>Commercial Terms</h5>
            <p class="mb-1">Pricing: as approved proposal or invoice</p>
            <p class="mb-1">Discounts: documented separately if applicable</p>
            <p class="mb-0">Acceptance: digital signature in client portal</p>
        </div>
    </div>

    <div class="document-panel mt-4">
        <h5>Contract Terms</h5>
        <div class="document-copy"><?= nl2br(e($contract['contract_body'] ?? '')) ?></div>
    </div>

    <div class="document-summary mt-4">
        <div class="document-note">
            <h5>Signature</h5>
            <?php if (!empty($contract['signature_name'])): ?>
                <p class="mb-1">Signed by <?= e($contract['signature_name']) ?></p>
                <p class="mb-0">Signed on <?= e((string) $contract['signed_at']) ?></p>
            <?php else: ?>
                <p class="mb-0">Awaiting client signature in the portal.</p>
            <?php endif; ?>
        </div>
        <div class="summary-card">
            <div><span>Current Status</span><strong><?= e(ucfirst((string) $contract['status'])) ?></strong></div>
            <div><span>Contract Type</span><strong>Digital Service Agreement</strong></div>
            <div class="summary-total"><span>PDF Ready</span><strong>Yes</strong></div>
        </div>
    </div>
</section>
