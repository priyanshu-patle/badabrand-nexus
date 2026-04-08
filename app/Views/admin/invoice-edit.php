<div class="dash-card">
    <div class="card-title-row"><h4>Edit Invoice <?= e($invoice['invoice_number']) ?></h4><span>Advanced editable invoice table</span></div>
    <form class="stack-form" method="post" action="<?= route_url('/admin/invoice/update') ?>">
        <input type="hidden" name="invoice_id" value="<?= e((string) $invoice['id']) ?>">
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="billing_name" value="<?= e($invoice['billing_name'] ?? '') ?>" placeholder="Billing name"></div>
            <div class="col-md-4"><input class="form-control" name="gst_number" value="<?= e($invoice['gst_number'] ?? '') ?>" placeholder="GST number"></div>
            <div class="col-md-4"><input class="form-control" type="date" name="due_date" value="<?= e((string) $invoice['due_date']) ?>"></div>
        </div>
        <select class="form-select" name="status">
            <?php foreach (['unpaid','paid','approved','cancelled','refunded'] as $status): ?>
                <option value="<?= e($status) ?>" <?= $invoice['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
            <?php endforeach; ?>
        </select>
        <?php for ($i = 0; $i < max(3, count($items)); $i++): $item = $items[$i] ?? ['item_name'=>'','description'=>'','quantity'=>1,'unit_price'=>0]; ?>
            <div class="invoice-item-grid">
                <input class="form-control" name="item_name[]" value="<?= e($item['item_name']) ?>" placeholder="Item name">
                <input class="form-control" name="item_description[]" value="<?= e($item['description']) ?>" placeholder="Description">
                <input class="form-control" name="item_quantity[]" value="<?= e((string) $item['quantity']) ?>" placeholder="Qty">
                <input class="form-control" name="item_price[]" value="<?= e((string) $item['unit_price']) ?>" placeholder="Unit price">
            </div>
        <?php endfor; ?>
        <button class="btn btn-primary rounded-pill" type="submit">Save Invoice</button>
        <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/client/invoice?id=' . $invoice['id']) ?>">Open Document</a>
    </form>
</div>
