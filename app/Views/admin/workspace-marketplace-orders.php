<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Marketplace Orders</h4>
            <p class="mb-0 text-muted">Product-originated orders flowing through the same billing and delivery engine.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/billing/payments') ?>">Open Billing</a>
    </div>

    <?php if ($orders): ?>
        <?php foreach ($orders as $order): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($order['order_number']) ?></strong>
                        <span><?= e($order['display_name'] ?? $order['product_name'] ?? 'Product') ?><?= ! empty($order['vendor_display_name']) ? ' | Vendor: ' . e($order['vendor_display_name']) : '' ?></span>
                    </div>
                    <span class="badge-soft"><?= e($order['status']) ?></span>
                </div>
                <div class="toolbar-actions mt-3">
                    <span class="badge-soft"><?= e(money_format_inr((float) ($order['total'] ?? 0))) ?></span>
                    <?php if (! empty($order['vendor_net_amount'])): ?><span class="badge-soft">Vendor Net <?= e(money_format_inr((float) $order['vendor_net_amount'])) ?></span><?php endif; ?>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/projects') ?>">Manage Delivery</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="mb-0 text-muted">No marketplace orders yet.</p>
    <?php endif; ?>
</div>
