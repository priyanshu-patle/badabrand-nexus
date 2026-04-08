<div class="document-actions">
    <button class="btn btn-warning" onclick="window.print()">Download / Print PDF</button>
    <a class="btn btn-outline-light" href="<?= route_url('/client/invoice/text?id=' . $invoice['id']) ?>">Download Text</a>
    <?php if ((string) $invoice['status'] === 'unpaid'): ?>
        <a class="btn btn-primary" href="<?= route_url('/client/payments?invoice_id=' . $invoice['id']) ?>">Pay This Invoice</a>
    <?php endif; ?>
    <a class="btn btn-outline-light" href="<?= route_url(($user['role'] ?? 'customer') === 'admin' ? '/admin/payments' : '/client/invoices') ?>">Back</a>
</div>

<section class="document-sheet invoice-sheet-yellow">
    <div class="document-accent"></div>
    <div class="document-header">
        <div>
            <div class="document-brand"><?= e($settings['footer_company_name'] ?? $settings['site_title'] ?? 'Badabrand Technologies') ?></div>
            <p class="document-meta mb-1"><?= e($settings['contact_address'] ?? 'India') ?></p>
            <p class="document-meta mb-0"><?= e($settings['contact_email'] ?? 'support@badabrand.in') ?> | <?= e($settings['contact_phone'] ?? '+91 00000 00000') ?></p>
        </div>
        <div class="document-title-wrap">
            <span class="document-kicker">Tax Invoice</span>
            <h1>Invoice</h1>
            <p class="document-meta mb-0"><?= e($invoice['invoice_number']) ?></p>
        </div>
    </div>

    <div class="document-grid">
        <div class="document-panel">
            <h5>Bill To</h5>
            <p class="mb-1"><?= e($invoice['billing_name'] ?: trim($invoice['first_name'] . ' ' . $invoice['last_name'])) ?></p>
            <p class="mb-1"><?= e($invoice['email']) ?></p>
            <p class="mb-0">GST: <?= e($invoice['gst_number'] ?: 'N/A') ?></p>
        </div>
        <div class="document-panel">
            <h5>Invoice Details</h5>
            <p class="mb-1">Order: <?= e($invoice['order_number'] ?: 'Direct billing') ?></p>
            <p class="mb-1">Service: <?= e($invoice['service_name'] ?: ($invoice['product_name'] ?? 'Custom IT Service')) ?></p>
            <p class="mb-1">Due Date: <?= e((string) ($invoice['due_date'] ?: 'On receipt')) ?></p>
            <p class="mb-0">Status: <span class="status-pill status-<?= e(strtolower((string) $invoice['status'])) ?>"><?= e($invoice['status']) ?></span></p>
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="document-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item / Service</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= e((string) ($index + 1)) ?></td>
                            <td><?= e($item['item_name']) ?></td>
                            <td><?= e($item['description']) ?></td>
                            <td><?= e((string) $item['quantity']) ?></td>
                            <td><?= e(money_format_inr($item['unit_price'])) ?></td>
                            <td><?= e(money_format_inr($item['line_total'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>1</td>
                        <td><?= e($invoice['service_name'] ?: 'Project Billing') ?></td>
                        <td>Professional IT delivery and support services.</td>
                        <td>1</td>
                        <td><?= e(money_format_inr($invoice['subtotal'])) ?></td>
                        <td><?= e(money_format_inr($invoice['subtotal'])) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="document-summary">
        <div class="document-note">
            <h5>Payment Notes</h5>
            <p class="mb-0">Please complete payment before the due date. If this invoice is unpaid or under review, use the Pay This Invoice button to upload bank transfer or QR payment proof.</p>
        </div>
        <div class="summary-card">
            <div><span>Subtotal</span><strong><?= e(money_format_inr($invoice['subtotal'])) ?></strong></div>
            <div><span>GST (18%)</span><strong><?= e(money_format_inr($invoice['gst_amount'])) ?></strong></div>
            <div class="summary-total"><span>Grand Total</span><strong><?= e(money_format_inr($invoice['total'])) ?></strong></div>
        </div>
    </div>
</section>
