<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="search-toolbar">
                <div>
                    <h4 class="mb-1">Payments, Billing, and Invoice Desk</h4>
                    <p class="mb-0 text-muted">Review payment screenshots, search transactions, edit invoices, and export records.</p>
                </div>
                <form class="toolbar-actions" method="get" action="<?= route_url('/admin/payments') ?>">
                    <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search invoice, client, UTR, gateway, note">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/export?type=payments&q=' . urlencode($search ?? '')) ?>">Export CSV</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Manual Payment Approval</h4><span>Review screenshot and bank/QR submissions</span></div>
            <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/payments/status') ?>">
                        <input type="hidden" name="payment_id" value="<?= e((string) $payment['id']) ?>">
                        <div class="list-row">
                            <strong><?= e(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''))) ?></strong>
                            <span><?= e($payment['invoice_number'] ?? 'No invoice') ?> | <?= e(money_format_inr($payment['amount'])) ?></span>
                            <span class="badge-soft"><?= e($payment['status']) ?></span>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-4"><input class="form-control" value="<?= e($payment['transaction_id'] ?? '') ?>" placeholder="UTR / transaction" disabled></div>
                            <div class="col-md-4"><input class="form-control" value="<?= e(ucwords(str_replace('_', ' ', $payment['gateway'] ?? 'manual_bank'))) ?>" placeholder="Gateway" disabled></div>
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <?php foreach (['pending','approved','rejected','cancelled','refund','invalid'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= ($payment['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8"><input class="form-control" name="notes" value="<?= e($payment['notes'] ?? '') ?>" placeholder="Admin note"></div>
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
                        <button class="btn btn-primary rounded-pill mt-3" type="submit">Update Payment</button>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No payments found for this search.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create GST Invoice</h4><span>Billing and invoice generation</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/invoices') ?>">
                <input class="form-control" name="user_id" placeholder="Client user ID">
                <input class="form-control" name="order_id" placeholder="Order ID">
                <input class="form-control" name="billing_name" placeholder="Billing name">
                <input class="form-control" name="gst_number" placeholder="GST number">
                <input class="form-control" name="subtotal" placeholder="Subtotal amount">
                <input class="form-control" type="date" name="due_date">
                <button class="btn btn-outline-light rounded-pill" type="submit">Generate Invoice</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Recent Invoices</h4><span>Printable invoice pages</span></div>
            <?php if (!empty($invoices)): ?>
                <?php foreach ($invoices as $invoice): ?>
                    <div class="list-row">
                        <strong><?= e($invoice['invoice_number']) ?></strong>
                        <span><?= e(trim($invoice['first_name'] . ' ' . $invoice['last_name'])) ?></span>
                        <div class="toolbar-actions">
                            <a class="badge-soft" href="<?= route_url('/admin/invoice/edit?id=' . $invoice['id']) ?>">edit</a>
                            <a class="badge-soft" href="<?= route_url('/client/invoice?id=' . $invoice['id']) ?>">document</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No invoices found for this search.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
