<div class="dash-card">
    <div class="card-title-row"><h4>Invoices</h4><span>GST-ready billing</span></div>
    <?php foreach ($invoices as $invoice): ?>
        <div class="list-row">
            <div>
                <strong><?= e($invoice['invoice_number']) ?></strong>
                <span class="small-text"><?= e($invoice['order_label'] ?: ($invoice['order_number'] ?: 'Billing record')) ?></span>
            </div>
            <div>
                <span><?= e(money_format_inr($invoice['total'])) ?></span>
                <span class="badge-soft mb-0 mt-2"><?= e($invoice['status']) ?></span>
            </div>
            <div class="toolbar-actions">
                <a class="badge-soft" href="<?= route_url('/client/invoice?id=' . $invoice['id']) ?>">pdf / print</a>
                <a class="badge-soft" href="<?= route_url('/client/invoice/text?id=' . $invoice['id']) ?>">text</a>
                <?php if ((string) $invoice['status'] === 'unpaid'): ?>
                    <a class="badge-soft" href="<?= route_url('/client/payments?invoice_id=' . $invoice['id']) ?>">pay now</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
