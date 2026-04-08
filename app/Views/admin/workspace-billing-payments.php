<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Payment Review Desk</h4>
            <p class="mb-0 text-muted">Approve or reject manual bank and QR submissions.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/billing/transactions') ?>">View Ledger</a>
    </div>

    <?php foreach ($payments as $payment): ?>
        <form class="dash-form-block" method="post" action="<?= route_url('/admin/payments/status') ?>">
            <input type="hidden" name="_redirect" value="/admin/billing/payments">
            <input type="hidden" name="payment_id" value="<?= e((string) $payment['id']) ?>">
            <div class="list-row">
                <div>
                    <strong><?= e(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''))) ?></strong>
                    <span><?= e($payment['invoice_number'] ?? 'No invoice') ?> | <?= e(money_format_inr($payment['amount'])) ?></span>
                </div>
                <span class="badge-soft"><?= e($payment['status']) ?></span>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <?php foreach (['pending', 'approved', 'rejected', 'cancelled', 'refund', 'invalid'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($payment['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8"><input class="form-control" name="notes" value="<?= e($payment['notes'] ?? '') ?>" placeholder="Admin note"></div>
            </div>
            <div class="toolbar-actions mt-3">
                <button class="btn btn-primary" type="submit">Update Payment</button>
            </div>
        </form>
    <?php endforeach; ?>
</div>
