<div class="row g-4">
    <div class="col-md-3"><div class="metric-card"><span>Client ID</span><strong><?= e($user['client_id']) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Orders</span><strong><?= e((string) count($orders)) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Invoices</span><strong><?= e((string) count($invoices)) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Payments</span><strong><?= e((string) count($payments)) ?></strong></div></div>
</div>
<?php if (empty($user['terms_accepted_at'])): ?>
    <div class="dash-card mt-4">
        <div class="card-title-row"><h4>First Login Terms Acceptance</h4><span>Required before proceeding</span></div>
        <p class="text-muted">Please accept the terms and conditions for your account before using the full portal.</p>
        <form method="post" action="<?= route_url('/client/accept-terms') ?>">
            <button class="btn btn-primary rounded-pill" type="submit">Accept Terms & Conditions</button>
        </form>
    </div>
<?php endif; ?>
<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Welcome back, <?= e($user['first_name']) ?></h4><span>Orders, billing, products, and approvals</span></div>
            <div class="row g-3 mt-1">
                <?php foreach (array_slice($orders, 0, 4) as $order): ?>
                    <div class="col-md-6">
                        <div class="mini-panel">
                            <strong><?= e($order['display_name'] ?? $order['service_name'] ?? 'Order') ?></strong>
                            <span><?= e($order['order_number']) ?> | <?= e($order['status']) ?></span>
                            <small><?= e($order['receipt_number'] ?? 'Receipt pending') ?> | <?= e(money_format_inr($order['total'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Quick Links</h4><span>Client tools</span></div>
            <div class="stack-actions">
                <a class="btn btn-primary w-100" href="<?= route_url('/client/marketplace') ?>">Buy Products & Services</a>
                <a class="btn btn-outline-light w-100" href="<?= route_url('/client/invoices') ?>">Download Invoices</a>
                <a class="btn btn-outline-light w-100" href="<?= route_url('/client/payments') ?>">Upload Payment Proof</a>
                <a class="btn btn-outline-light w-100" href="<?= route_url('/client/tickets') ?>">Open Support Ticket</a>
                <a class="btn btn-outline-light w-100" href="<?= route_url('/client/projects') ?>">Review Active Project</a>
            </div>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Notifications</h4><span>Latest updates</span></div>
            <?php foreach (array_slice($notifications, 0, 4) as $notice): ?>
                <div class="activity-item"><strong><?= e($notice['title']) ?></strong><div class="small-text"><?= e($notice['body'] ?? '') ?></div></div>
            <?php endforeach; ?>
        </div>
        <?php if (! empty($referral)): ?>
            <div class="dash-card mt-4">
                <div class="card-title-row"><h4>Referral Program</h4><span>Invite and earn</span></div>
                <div class="mini-panel">
                    <strong><?= e($referral['referral_code']) ?></strong>
                    <span><?= e(route_url('/register?ref=' . urlencode((string) $referral['referral_code']))) ?></span>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-6"><div class="mini-panel"><strong><?= e((string) ($referral['total_signups'] ?? 0)) ?></strong><span>Signups</span></div></div>
                    <div class="col-6"><div class="mini-panel"><strong><?= e(money_format_inr($referral['reward_balance'] ?? 0)) ?></strong><span>Pending rewards</span></div></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Recent Payment Status</h4><span>Manual, QR, and approval workflow</span></div>
            <?php foreach (array_slice($payments, 0, 4) as $payment): ?>
                <div class="list-row">
                    <strong><?= e($payment['invoice_number'] ?? 'Manual payment') ?></strong>
                    <span><?= e(ucwords(str_replace('_', ' ', $payment['gateway'] ?? 'manual_bank'))) ?></span>
                    <span class="badge-soft"><?= e($payment['status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Marketplace Picks</h4><span>Themes, plugins, software</span></div>
            <?php foreach ($products as $product): ?>
                <div class="list-row">
                    <strong><?= e($product['name']) ?></strong>
                    <span><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                    <a class="badge-soft" href="<?= route_url('/client/marketplace') ?>">buy</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
