<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Invoices</h4>
            <p class="mb-0 text-muted">Dedicated invoice index with print and edit actions.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/admin/billing/invoices/create') ?>">Create Invoice</a>
    </div>

    <?php foreach ($invoices as $invoice): ?>
        <div class="dash-form-block">
            <div class="list-row">
                <div>
                    <strong><?= e($invoice['invoice_number']) ?></strong>
                    <span><?= e(trim($invoice['first_name'] . ' ' . $invoice['last_name'])) ?></span>
                </div>
                <span class="badge-soft"><?= e($invoice['status']) ?></span>
            </div>
            <div class="toolbar-actions mt-3">
                <span class="badge-soft"><?= e(money_format_inr((float) $invoice['total'])) ?></span>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/invoice/edit?id=' . $invoice['id']) ?>">Edit</a>
                <a class="btn btn-outline-light" href="<?= route_url('/client/invoice?id=' . $invoice['id']) ?>">Document</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
