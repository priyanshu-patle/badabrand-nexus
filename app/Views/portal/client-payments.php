<div class="row g-4">
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Upload Payment Proof</h4><span>Manual bank / QR approval</span></div>
            <div class="payment-option-grid mb-4">
                <div class="mini-panel">
                    <strong>Bank Transfer</strong>
                    <span>Account Name: Badabrand Technologies</span>
                    <span>Account No: 1234567890</span>
                    <span>IFSC: BADA0001234</span>
                </div>
                <div class="mini-panel text-center">
                    <strong>QR Payment</strong>
                    <img class="qr-preview" src="<?= asset('images/qr-payment.svg') ?>" alt="QR payment">
                    <span>Scan and upload proof after payment.</span>
                </div>
            </div>
            <form class="stack-form" method="post" action="<?= route_url('/client/payments') ?>" enctype="multipart/form-data">
                <select class="form-select" name="invoice_id">
                    <option value="">Select invoice</option>
                    <?php foreach ($invoices as $invoice): ?><option value="<?= e((string) $invoice['id']) ?>" <?= (int) ($selectedInvoiceId ?? 0) === (int) $invoice['id'] ? 'selected' : '' ?>><?= e($invoice['invoice_number']) ?> - <?= e(money_format_inr($invoice['total'])) ?> - <?= e($invoice['status']) ?></option><?php endforeach; ?>
                </select>
                <select class="form-select" name="gateway"><option value="manual_bank">Bank Transfer</option><option value="manual_qr">QR Payment</option></select>
                <input class="form-control" name="transaction_id" placeholder="Transaction ID / UTR">
                <input class="form-control" name="amount" placeholder="Amount">
                <textarea class="form-control" name="notes" rows="3" placeholder="Payment note"></textarea>
                <input class="form-control" type="file" name="proof_file">
                <button class="btn btn-primary rounded-pill" type="submit">Submit Payment Proof</button>
            </form>
            <div class="mini-panel mt-4">
                <strong>Accepted payment decisions</strong>
                <span>pending, approved, rejected, cancelled, refund, and invalid are now supported from admin billing review.</span>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Payment History</h4><span>Status tracking</span></div>
            <?php foreach ($payments as $payment): ?>
                <div class="dash-form-block">
                    <div class="list-row"><strong><?= e($payment['invoice_number'] ?? 'Manual Payment') ?></strong><span><?= e(money_format_inr($payment['amount'])) ?></span><span class="badge-soft"><?= e($payment['status']) ?></span></div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-8">
                            <div class="small-text">Gateway: <?= e(ucwords(str_replace('_', ' ', $payment['gateway'] ?? 'manual_bank'))) ?></div>
                            <div class="small-text">Transaction: <?= e($payment['transaction_id'] ?: 'Not provided') ?></div>
                            <div class="small-text">Note: <?= e($payment['notes'] ?: 'No note added') ?></div>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($payment['proof_path'])): ?>
                                <a class="proof-thumb" href="<?= storage_url($payment['proof_path']) ?>" target="_blank" rel="noopener">
                                    <img src="<?= storage_url($payment['proof_path']) ?>" alt="Payment proof">
                                    <span>Open screenshot</span>
                                </a>
                            <?php else: ?>
                                <div class="proof-thumb empty"><span>No screenshot uploaded</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
