<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Coupon</h4><span>Discount management</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/marketing/coupons') ?>">
                <input type="hidden" name="_redirect" value="/admin/marketing/coupons">
                <input class="form-control" name="code" placeholder="Coupon code" required>
                <div class="row g-3">
                    <div class="col-md-6">
                        <select class="form-select" name="discount_type">
                            <option value="percent">Percent</option>
                            <option value="flat">Flat</option>
                        </select>
                    </div>
                    <div class="col-md-6"><input class="form-control" name="discount_value" placeholder="Discount value"></div>
                </div>
                <label class="small-text"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn btn-primary" type="submit">Create Coupon</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Coupon Records</h4><span>Active and archived discounts</span></div>
            <?php foreach ($coupons as $coupon): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($coupon['code']) ?></strong>
                        <span><?= e($coupon['discount_type']) ?> <?= e((string) $coupon['discount_value']) ?></span>
                    </div>
                    <span class="badge-soft"><?= (int) $coupon['is_active'] === 1 ? 'active' : 'inactive' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
