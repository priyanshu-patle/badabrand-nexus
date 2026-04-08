<div class="dash-card">
    <div class="card-title-row"><h4>Vendor Program Settings</h4><span>Approval and payout rules</span></div>
    <div class="row g-4 mt-1">
        <div class="col-md-6">
            <div class="mini-panel">
                <strong>Status</strong>
                <span><?= e(ucfirst((string) ($vendor['status'] ?? 'pending'))) ?></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mini-panel">
                <strong>Commission Rate</strong>
                <span><?= e((string) ($vendor['commission_percent'] ?? app_setting('vendor_default_commission', '15'))) ?>%</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mini-panel">
                <strong>Minimum Payout</strong>
                <span><?= e(money_format_inr((float) app_setting('vendor_minimum_payout', '1000'))) ?></span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mini-panel">
                <strong>Product Review Policy</strong>
                <span><?= app_setting('vendor_product_requires_review', '1') === '1' ? 'Vendor products require admin review' : 'Vendor products auto-publish' ?></span>
            </div>
        </div>
    </div>
</div>
