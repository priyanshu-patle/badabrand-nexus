<div class="dash-card">
    <div class="card-title-row"><h4>Vendor Profile</h4><span>Store, support, and payout information</span></div>
    <form class="stack-form mt-4" method="post" action="<?= route_url('/vendor/profile') ?>" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="store_name" value="<?= e((string) ($vendor['store_name'] ?? '')) ?>" placeholder="Store name"></div>
            <div class="col-md-6"><input class="form-control" name="display_name" value="<?= e((string) ($vendor['display_name'] ?? '')) ?>" placeholder="Display name"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="phone" value="<?= e((string) ($vendor['phone'] ?? '')) ?>" placeholder="Phone"></div>
            <div class="col-md-6"><input class="form-control" name="tax_gst" value="<?= e((string) ($vendor['tax_gst'] ?? '')) ?>" placeholder="GST / Tax"></div>
        </div>
        <textarea class="form-control" name="short_bio" rows="3" placeholder="Store description"><?= e((string) ($vendor['short_bio'] ?? '')) ?></textarea>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="business_name" value="<?= e((string) ($vendor['business_name'] ?? '')) ?>" placeholder="Business name"></div>
            <div class="col-md-6"><input class="form-control" name="legal_name" value="<?= e((string) ($vendor['legal_name'] ?? '')) ?>" placeholder="Legal name"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="website" value="<?= e((string) ($vendor['website'] ?? '')) ?>" placeholder="Website"></div>
            <div class="col-md-4"><input class="form-control" name="support_email" value="<?= e((string) ($vendor['support_email'] ?? '')) ?>" placeholder="Support email"></div>
            <div class="col-md-4"><input class="form-control" name="support_phone" value="<?= e((string) ($vendor['support_phone'] ?? '')) ?>" placeholder="Support phone"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="account_name" value="<?= e((string) ($vendor['account_name'] ?? '')) ?>" placeholder="Account name"></div>
            <div class="col-md-4"><input class="form-control" name="account_number" value="<?= e((string) ($vendor['account_number'] ?? '')) ?>" placeholder="Account number"></div>
            <div class="col-md-4"><input class="form-control" name="ifsc_swift" value="<?= e((string) ($vendor['ifsc_swift'] ?? '')) ?>" placeholder="IFSC / Swift"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="upi_id" value="<?= e((string) ($vendor['upi_id'] ?? '')) ?>" placeholder="UPI ID"></div>
            <div class="col-md-4"><input class="form-control" name="paypal_email" value="<?= e((string) ($vendor['paypal_email'] ?? '')) ?>" placeholder="PayPal email"></div>
            <div class="col-md-4">
                <select class="form-select" name="payout_method">
                    <?php foreach (['bank_transfer' => 'Bank Transfer', 'upi' => 'UPI', 'paypal' => 'PayPal'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($vendor['payout_method'] ?? 'bank_transfer') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <textarea class="form-control" name="payout_notes" rows="2" placeholder="Payout note"><?= e((string) ($vendor['payout_notes'] ?? '')) ?></textarea>
        <div class="row g-3">
            <div class="col-md-3"><input class="form-control" name="address_line1" value="<?= e((string) ($vendor['address_line1'] ?? '')) ?>" placeholder="Address line 1"></div>
            <div class="col-md-3"><input class="form-control" name="address_line2" value="<?= e((string) ($vendor['address_line2'] ?? '')) ?>" placeholder="Address line 2"></div>
            <div class="col-md-2"><input class="form-control" name="city" value="<?= e((string) ($vendor['city'] ?? '')) ?>" placeholder="City"></div>
            <div class="col-md-2"><input class="form-control" name="state" value="<?= e((string) ($vendor['state'] ?? '')) ?>" placeholder="State"></div>
            <div class="col-md-2"><input class="form-control" name="postal_code" value="<?= e((string) ($vendor['postal_code'] ?? '')) ?>" placeholder="Postal code"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-3"><input class="form-control" name="country" value="<?= e((string) ($vendor['country'] ?? '')) ?>" placeholder="Country"></div>
            <div class="col-md-3"><input class="form-control" type="file" name="logo_file"></div>
            <div class="col-md-3"><input class="form-control" type="file" name="banner_file"></div>
            <div class="col-md-3"><input class="form-control" type="file" name="identity_file"></div>
        </div>
        <div>
            <input class="form-control" type="file" name="gst_file">
        </div>
        <button class="btn btn-primary" type="submit">Save Vendor Profile</button>
    </form>
</div>
