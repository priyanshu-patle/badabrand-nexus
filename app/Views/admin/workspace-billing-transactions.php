<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Transaction Ledger</h4>
            <p class="mb-0 text-muted">Chronological billing activity across gateways and invoice states.</p>
        </div>
        <span class="badge-soft"><?= e((string) count($payments)) ?> transactions</span>
    </div>

    <?php foreach ($payments as $payment): ?>
        <div class="dash-form-block">
            <div class="list-row">
                <div>
                    <strong><?= e($payment['invoice_number'] ?? 'Manual payment') ?></strong>
                    <span><?= e(ucwords(str_replace('_', ' ', $payment['gateway'] ?? 'manual_bank'))) ?><?= ! empty($payment['transaction_id']) ? ' | ' . e($payment['transaction_id']) : '' ?></span>
                </div>
                <span class="badge-soft"><?= e(money_format_inr($payment['amount'])) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
