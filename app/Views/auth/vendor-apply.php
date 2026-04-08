<section class="auth-wrap">
    <div class="auth-card auth-card-wide">
        <div>
            <span class="eyebrow">Vendor Onboarding</span>
            <h1>Apply to become a marketplace vendor</h1>
            <p>Submit your business, payout, and verification details. Admin approval is required before publishing products.</p>
        </div>
        <form class="stack-form" method="post" action="<?= route_url('/vendor/apply') ?>" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="store_name" value="<?= e((string) old('store_name')) ?>" placeholder="Store name" required></div>
                <div class="col-md-6"><input class="form-control" name="display_name" value="<?= e((string) old('display_name')) ?>" placeholder="Display name" required></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="first_name" value="<?= e((string) old('first_name')) ?>" placeholder="First name" required></div>
                <div class="col-md-6"><input class="form-control" name="last_name" value="<?= e((string) old('last_name')) ?>" placeholder="Last name" required></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" type="email" name="email" value="<?= e((string) old('email')) ?>" placeholder="Business email" required></div>
                <div class="col-md-6"><input class="form-control" name="phone" value="<?= e((string) old('phone')) ?>" placeholder="Phone number" required></div>
            </div>
            <input class="form-control" type="password" name="password" placeholder="Password" required>
            <textarea class="form-control" name="short_bio" rows="3" placeholder="Store description / bio"><?= e((string) old('short_bio')) ?></textarea>
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="tax_gst" value="<?= e((string) old('tax_gst')) ?>" placeholder="GST / Tax number"></div>
                <div class="col-md-6">
                    <select class="form-select" name="payout_method">
                        <?php foreach (['bank_transfer' => 'Bank Transfer', 'upi' => 'UPI', 'paypal' => 'PayPal'] as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="account_name" value="<?= e((string) old('account_name')) ?>" placeholder="Account holder name"></div>
                <div class="col-md-6"><input class="form-control" name="account_number" value="<?= e((string) old('account_number')) ?>" placeholder="Account number"></div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="ifsc_swift" value="<?= e((string) old('ifsc_swift')) ?>" placeholder="IFSC / Swift code"></div>
                <div class="col-md-6"><input class="form-control" name="upi_id" value="<?= e((string) old('upi_id')) ?>" placeholder="UPI ID"></div>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><label class="small-text d-block mb-2">Store logo</label><input class="form-control" type="file" name="logo_file"></div>
                <div class="col-md-4"><label class="small-text d-block mb-2">Store banner</label><input class="form-control" type="file" name="banner_file"></div>
                <div class="col-md-4"><label class="small-text d-block mb-2">Identity / KYC document</label><input class="form-control" type="file" name="identity_file"></div>
            </div>
            <div>
                <label class="small-text d-block mb-2">GST document</label>
                <input class="form-control" type="file" name="gst_file">
            </div>
            <button class="btn btn-primary rounded-pill" type="submit">Submit Vendor Application</button>
            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/login') ?>">Back to Login</a>
        </form>
    </div>
</section>
