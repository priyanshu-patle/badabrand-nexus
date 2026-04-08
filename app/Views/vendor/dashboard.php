<div class="row g-4">
    <div class="col-md-3"><div class="metric-card"><span>Products</span><strong><?= e((string) ($summary['products'] ?? 0)) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Orders</span><strong><?= e((string) ($summary['orders'] ?? 0)) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Available</span><strong><?= e(money_format_inr($summary['available_balance'] ?? 0)) ?></strong></div></div>
    <div class="col-md-3"><div class="metric-card"><span>Paid Earnings</span><strong><?= e(money_format_inr($summary['paid_earnings'] ?? 0)) ?></strong></div></div>
</div>
<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Store Status</h4><span><?= e($vendor['status'] ?? 'pending') ?></span></div>
            <p class="text-muted">Manage products, monitor orders, and track earnings from your vendor workspace.</p>
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="<?= route_url('/vendor/products/create') ?>">Add Product</a>
                <a class="btn btn-outline-light" href="<?= route_url('/vendor/orders') ?>">View Orders</a>
                <a class="btn btn-outline-light" href="<?= route_url('/vendor/payouts') ?>">Open Payouts</a>
            </div>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Recent Orders</h4><span>Latest buyer activity</span></div>
            <?php if ($orders === []): ?>
                <p class="text-muted mb-0">No orders yet for this vendor account.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="list-row">
                        <strong><?= e($order['order_number']) ?></strong>
                        <span><?= e($order['display_name'] ?? $order['product_name'] ?? 'Product') ?></span>
                        <span class="badge-soft"><?= e(money_format_inr($order['vendor_net_amount'] ?? $order['total'] ?? 0)) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Recent Reviews</h4><span>Trust signals</span></div>
            <?php if ($reviews === []): ?>
                <p class="text-muted mb-0">No approved reviews yet.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="mini-panel">
                        <strong><?= e((string) ($review['rating'] ?? 0)) ?>/5</strong>
                        <span><?= e($review['product_name'] ?? 'Product') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Payout History</h4><span>Latest requests</span></div>
            <?php if ($payouts === []): ?>
                <p class="text-muted mb-0">No payout requests submitted yet.</p>
            <?php else: ?>
                <?php foreach ($payouts as $payout): ?>
                    <div class="list-row">
                        <strong><?= e(money_format_inr($payout['request_amount'] ?? 0)) ?></strong>
                        <span><?= e($payout['status'] ?? 'requested') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
