<div class="dash-card">
    <div class="card-title-row"><h4>Vendor Orders</h4><span>Orders and related commission records</span></div>
    <?php if ($orders === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-receipt"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No vendor orders yet</h5>
                <p>Product orders will appear here automatically once your listings start converting into marketplace sales.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($order['order_number']) ?></strong>
                        <span><?= e($order['display_name'] ?? $order['product_name'] ?? 'Product') ?> | <?= e(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''))) ?></span>
                    </div>
                    <span class="badge-soft"><?= e($order['status']) ?></span>
                </div>
                <div class="toolbar-actions mt-3">
                    <span class="badge-soft"><?= e($order['invoice_number'] ?? 'Invoice pending') ?></span>
                    <span class="badge-soft"><?= e(money_format_inr($order['vendor_net_amount'] ?? $order['total'] ?? 0)) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
