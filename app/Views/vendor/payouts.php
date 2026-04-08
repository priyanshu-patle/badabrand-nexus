<div class="row g-4">
    <div class="col-xl-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Available</span><strong><?= e(money_format_inr($summary['available_balance'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Requested</span><strong><?= e(money_format_inr($summary['requested_balance'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xl-12"><div class="metric-card"><span>Paid</span><strong><?= e(money_format_inr($summary['paid_earnings'] ?? 0)) ?></strong></div></div>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Request Payout</h4><span>Manual payout workflow</span></div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/vendor/payouts/request') ?>">
                <input class="form-control" name="request_amount" placeholder="Request amount">
                <textarea class="form-control" name="vendor_note" rows="3" placeholder="Payout note or preferred batch details"></textarea>
                <button class="btn btn-primary" type="submit">Submit Payout Request</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Payout History</h4><span>Vendor settlement records</span></div>
            <?php if ($payouts === []): ?>
                <p class="text-muted mb-0">No payout records yet.</p>
            <?php else: ?>
                <?php foreach ($payouts as $payout): ?>
                    <div class="dash-form-block">
                        <div class="list-row">
                            <strong><?= e(money_format_inr($payout['request_amount'] ?? 0)) ?></strong>
                            <span><?= e($payout['status'] ?? 'requested') ?></span>
                            <span class="badge-soft"><?= e($payout['reference_number'] ?? 'Awaiting admin reference') ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Commission Ledger</h4><span>Gross, fee, and net values</span></div>
            <?php if ($commissions === []): ?>
                <p class="text-muted mb-0">No commission records available yet.</p>
            <?php else: ?>
                <?php foreach ($commissions as $commission): ?>
                    <div class="dash-form-block">
                        <div class="list-row">
                            <strong><?= e($commission['invoice_number'] ?? $commission['order_number'] ?? 'Order') ?></strong>
                            <span><?= e($commission['product_name'] ?? 'Product') ?></span>
                            <span class="badge-soft"><?= e($commission['payout_status'] ?? 'pending') ?></span>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4"><input class="form-control" value="<?= e(money_format_inr($commission['gross_amount'] ?? 0)) ?>" readonly></div>
                            <div class="col-md-4"><input class="form-control" value="<?= e(money_format_inr($commission['platform_fee_amount'] ?? 0)) ?>" readonly></div>
                            <div class="col-md-4"><input class="form-control" value="<?= e(money_format_inr($commission['vendor_net_amount'] ?? 0)) ?>" readonly></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
