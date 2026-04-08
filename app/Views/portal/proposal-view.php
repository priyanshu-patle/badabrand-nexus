<div class="document-actions">
    <button class="btn btn-warning" onclick="window.print()">Download / Print PDF</button>
    <a class="btn btn-outline-light" href="<?= route_url(($user['role'] ?? 'customer') === 'admin' ? '/admin/projects' : '/client/proposals') ?>">Back</a>
</div>

<section class="document-sheet invoice-sheet-yellow">
    <div class="document-accent"></div>
    <div class="document-header">
        <div>
            <span class="document-kicker">Proposal Generator</span>
            <h1><?= e($proposal['title']) ?></h1>
            <p class="document-meta mb-0">Prepared for <?= e(trim($proposal['first_name'] . ' ' . $proposal['last_name'])) ?></p>
        </div>
        <div class="document-title-wrap">
            <div class="document-brand"><?= e($settings['footer_company_name'] ?? $settings['site_title'] ?? 'Badabrand Technologies') ?></div>
            <p class="document-meta mb-1">Valid Until: <?= e((string) $proposal['valid_until']) ?></p>
            <p class="document-meta mb-0">Status: <?= e($proposal['status']) ?></p>
        </div>
    </div>

    <div class="document-grid">
        <div class="document-panel">
            <h5>Client</h5>
            <p class="mb-1"><?= e(trim($proposal['first_name'] . ' ' . $proposal['last_name'])) ?></p>
            <p class="mb-0"><?= e($proposal['email']) ?></p>
        </div>
        <div class="document-panel">
            <h5>Commercials</h5>
            <p class="mb-1">Quoted Amount: <?= e(money_format_inr($proposal['amount'])) ?></p>
            <p class="mb-1">Discounts: Included as per final scope</p>
            <p class="mb-0">Terms: 50% advance, balance on milestone delivery</p>
        </div>
    </div>

    <div class="document-panel mt-4">
        <h5>Scope and Deliverables</h5>
        <div class="document-copy"><?= safe_rich_text($proposal['description'] ?? 'Custom website, dashboard, integrations, testing, and deployment support.') ?></div>
    </div>

    <div class="document-summary mt-4">
        <div class="document-note">
            <h5>Includes</h5>
            <ul class="document-list mb-0">
                <li>UI design aligned with Badabrand branding</li>
                <li>Admin-managed content and pricing sections</li>
                <li>Client portal access and support workflow</li>
                <li>Deployment-ready files and revision support</li>
            </ul>
        </div>
        <div class="summary-card">
            <div><span>Quotation Value</span><strong><?= e(money_format_inr($proposal['amount'])) ?></strong></div>
            <div><span>Discount</span><strong>As approved</strong></div>
            <div class="summary-total"><span>Approval Window</span><strong><?= e((string) $proposal['valid_until']) ?></strong></div>
        </div>
    </div>
</section>
