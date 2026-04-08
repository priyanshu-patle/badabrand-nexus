<?php
$allPayouts = [];
foreach ($vendors as $vendorItem) {
    foreach (\App\Core\Content::vendorPayouts((int) $vendorItem['id']) as $payout) {
        $payout['vendor_name'] = $vendorItem['display_name'] ?: $vendorItem['store_name'];
        $allPayouts[] = $payout;
    }
}
usort($allPayouts, static fn(array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
?>
<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Vendor Payout Desk</h4>
            <p class="mb-0 text-muted">Track payout requests, references, and vendor settlement workflow.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/marketplace/vendors') ?>">Back to Vendors</a>
    </div>
    <?php if ($allPayouts === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-wallet2"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No payout requests yet</h5>
                <p>Approved vendors will be able to request payouts once they have available commission balance.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($allPayouts as $payout): ?>
            <form class="dash-form-block" method="post" action="<?= route_url('/admin/marketplace/payouts/status') ?>">
                <input type="hidden" name="payout_id" value="<?= e((string) $payout['id']) ?>">
                <input type="hidden" name="_redirect" value="/admin/marketplace/payouts">
                <div class="list-row">
                    <div>
                        <strong><?= e($payout['vendor_name']) ?></strong>
                        <span><?= e(money_format_inr($payout['request_amount'] ?? 0)) ?> | <?= e($payout['created_at'] ?? '') ?></span>
                    </div>
                    <span class="badge-soft"><?= e($payout['status'] ?? 'requested') ?></span>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <?php foreach (['requested', 'processing', 'paid', 'rejected', 'cancelled'] as $status): ?>
                                <option value="<?= e($status) ?>" <?= ($payout['status'] ?? 'requested') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4"><input class="form-control" name="reference_number" value="<?= e((string) ($payout['reference_number'] ?? '')) ?>" placeholder="Reference number"></div>
                    <div class="col-md-4"><input class="form-control" value="<?= e((string) ($payout['processed_at'] ?? 'Awaiting processing')) ?>" readonly></div>
                </div>
                <textarea class="form-control mt-3" name="admin_note" rows="2" placeholder="Admin payout note"><?= e((string) ($payout['admin_note'] ?? '')) ?></textarea>
                <button class="btn btn-primary mt-3" type="submit">Update Payout</button>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
