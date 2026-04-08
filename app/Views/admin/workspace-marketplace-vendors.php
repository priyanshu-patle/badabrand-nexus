<div class="row g-4">
    <div class="col-xl-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Total Vendors</span><strong><?= e((string) ($vendorSummary['vendors'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Approved Vendors</span><strong><?= e((string) ($vendorSummary['approved_vendors'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Pending Vendors</span><strong><?= e((string) ($vendorSummary['pending_vendors'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Pending Payouts</span><strong><?= e(money_format_inr($vendorSummary['pending_payouts'] ?? 0)) ?></strong></div></div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Marketplace Vendors</h4>
                    <p class="mb-0 text-muted">Approve, suspend, verify, and inspect vendor stores from one workspace.</p>
                </div>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/marketplace/payouts') ?>">Open Payout Desk</a>
            </div>
            <?php if ($vendors === []): ?>
                <div class="dash-empty-state">
                    <span class="dash-empty-state-icon"><i class="bi bi-shop"></i></span>
                    <div class="dash-empty-state-copy">
                        <h5>No vendor applications yet</h5>
                        <p>Vendor onboarding submissions will appear here after applicants use the public vendor registration form.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($vendors as $vendor): ?>
                    <div class="dash-form-block">
                        <div class="list-row">
                            <div>
                                <strong><?= e($vendor['display_name'] ?: $vendor['store_name']) ?></strong>
                                <span><?= e($vendor['user_email'] ?? $vendor['email'] ?? '') ?> | <?= e($vendor['slug']) ?></span>
                            </div>
                            <span class="badge-soft"><?= e($vendor['status']) ?></span>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4"><input class="form-control" value="<?= e((string) ($vendor['product_count'] ?? 0)) ?>" readonly></div>
                            <div class="col-md-4"><input class="form-control" value="<?= e((string) ($vendor['order_count'] ?? 0)) ?>" readonly></div>
                            <div class="col-md-4"><input class="form-control" value="<?= e(money_format_inr($vendor['pending_balance'] ?? 0)) ?>" readonly></div>
                        </div>
                        <div class="toolbar-actions mt-3">
                            <a class="btn btn-primary" href="<?= route_url('/admin/marketplace/vendors/' . $vendor['id']) ?>">View Vendor</a>
                            <a class="btn btn-outline-light" href="<?= route_url('/marketplace/vendor/' . $vendor['slug']) ?>" target="_blank">Public Store</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
