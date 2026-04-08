<div class="dash-card">
    <div class="card-title-row"><h4>Orders, Services, and Product Purchases</h4><span>Approval, billing, receipt, and delivery progress</span></div>
    <?php foreach ($orders as $order): ?>
        <div class="dash-form-block">
            <div class="d-flex justify-content-between gap-3 flex-wrap">
                <strong><?= e($order['display_name'] ?? $order['service_name'] ?? 'Order') ?></strong>
                <span class="badge-soft"><?= e($order['status']) ?></span>
            </div>
            <div class="small-text mt-2">
                <?= e(ucfirst((string) ($order['order_type'] ?? 'service'))) ?> |
                <?= e($order['order_number']) ?> |
                Receipt <?= e($order['receipt_number'] ?? 'pending') ?>
            </div>
            <div class="progress my-3"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
            <div class="row g-3">
                <div class="col-md-3"><div class="mini-panel"><strong>Total</strong><span><?= e(money_format_inr($order['total'])) ?></span></div></div>
                <div class="col-md-3"><div class="mini-panel"><strong>Invoice</strong><span><?= e($order['invoice_status'] ?: 'not created') ?></span></div></div>
                <div class="col-md-3"><div class="mini-panel"><strong>Expected Delivery</strong><span><?= e((string) ($order['expected_delivery'] ?: 'TBD')) ?></span></div></div>
                <div class="col-md-3"><div class="mini-panel"><strong>Payment</strong><?php if (!empty($order['invoice_id']) && (string) $order['invoice_status'] === 'unpaid'): ?><a class="btn btn-primary btn-sm mt-2" href="<?= route_url('/client/payments?invoice_id=' . $order['invoice_id']) ?>">Pay now</a><?php else: ?><span><?= e($order['invoice_status'] ? ucfirst((string) $order['invoice_status']) : 'Awaiting invoice') ?></span><?php endif; ?></div></div>
                <div class="col-md-12"><div class="mini-panel"><strong>Order Note</strong><span><?= e($order['notes'] ?: 'No special note added') ?></span></div></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
