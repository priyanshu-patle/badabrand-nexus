<div class="row g-4">
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Email & Push Broadcast</h4><span>Send notifications to all users</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/marketing/broadcast') ?>">
                <input class="form-control" name="title" placeholder="Broadcast title">
                <select class="form-select" name="type"><option value="broadcast">broadcast</option><option value="push">push</option><option value="email">email</option></select>
                <textarea class="form-control" name="body" rows="4" placeholder="Broadcast body"></textarea>
                <input class="form-control" name="action_url" placeholder="Optional action URL">
                <button class="btn btn-primary rounded-pill" type="submit">Send Broadcast</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Coupon / Discount System</h4><span>Create live discounts</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/marketing/coupons') ?>">
                <input class="form-control" name="code" placeholder="Coupon code">
                <div class="row g-3">
                    <div class="col-md-6"><select class="form-select" name="discount_type"><option value="percent">percent</option><option value="flat">flat</option></select></div>
                    <div class="col-md-6"><input class="form-control" name="discount_value" placeholder="Discount value"></div>
                </div>
                <label class="small-text"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn btn-outline-light rounded-pill" type="submit">Create Coupon</button>
            </form>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Active Coupons</h4><span>Discount list</span></div>
            <?php foreach ($coupons as $coupon): ?>
                <div class="list-row"><strong><?= e($coupon['code']) ?></strong><span><?= e($coupon['discount_type']) ?> <?= e((string) $coupon['discount_value']) ?></span><span class="badge-soft"><?= (int) $coupon['is_active'] === 1 ? 'active' : 'inactive' ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Referral System</h4><span>Balances and codes</span></div>
            <?php foreach ($referrals as $referral): ?>
                <div class="list-row"><strong><?= e(trim($referral['first_name'] . ' ' . $referral['last_name'])) ?></strong><span><?= e($referral['referral_code']) ?></span><span class="badge-soft"><?= e(money_format_inr($referral['reward_balance'])) ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Email Delivery Log</h4><span>Automatic order and billing emails</span></div>
            <?php foreach ($emailLogs as $log): ?>
                <div class="list-row">
                    <strong><?= e($log['subject']) ?></strong>
                    <span><?= e($log['recipient_email']) ?></span>
                    <span class="badge-soft"><?= e($log['delivery_status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
