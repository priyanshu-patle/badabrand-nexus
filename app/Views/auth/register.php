<section class="auth-wrap">
    <div class="auth-card">
        <div>
            <span class="eyebrow">Client Onboarding</span>
            <h1>Create your client account</h1>
            <p>Includes client ID generation, role setup, and first-time terms acceptance flow.</p>
        </div>
        <form class="stack-form" method="post" action="<?= route_url('/register') ?>">
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="first_name" value="<?= e((string) old('first_name')) ?>" placeholder="First Name"></div>
                <div class="col-md-6"><input class="form-control" name="last_name" value="<?= e((string) old('last_name')) ?>" placeholder="Last Name"></div>
            </div>
            <input class="form-control" name="email" type="email" value="<?= e((string) old('email')) ?>" placeholder="Email Address">
            <input class="form-control" name="phone" value="<?= e((string) old('phone')) ?>" placeholder="Phone Number">
            <input class="form-control" name="password" type="password" placeholder="Password">
            <input class="form-control" name="referral_code" value="<?= e((string) old('referral_code', $referralCode ?? '')) ?>" placeholder="Referral Code (Optional)">
            <label class="small-text"><input type="checkbox" checked> I accept the Terms & Conditions</label>
            <button class="btn btn-primary rounded-pill" type="submit">Register</button>
            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/vendor/apply') ?>">Apply as Vendor</a>
        </form>
    </div>
</section>
