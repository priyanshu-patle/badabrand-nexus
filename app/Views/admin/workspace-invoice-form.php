<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Create Invoice</h4>
            <p class="mb-0 text-muted">Global billing quick-create destination.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/billing/invoices') ?>">Back to Invoices</a>
    </div>

    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/invoices') ?>">
        <input type="hidden" name="_redirect" value="/admin/billing/invoices">
        <select class="form-select" name="user_id" required>
            <option value="">Select client</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?= e((string) $client['id']) ?>"><?= e(trim($client['first_name'] . ' ' . $client['last_name'])) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="form-control" name="order_id" placeholder="Optional order ID">
        <input class="form-control" name="billing_name" placeholder="Billing name" required>
        <input class="form-control" name="gst_number" placeholder="GST number">
        <input class="form-control" name="subtotal" placeholder="Subtotal amount" required>
        <input class="form-control" type="date" name="due_date">
        <button class="btn btn-primary" type="submit">Generate Invoice</button>
    </form>
</div>
