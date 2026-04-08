<section class="auth-wrap">
    <div class="auth-card">
        <div>
            <span class="eyebrow">Secure Access</span>
            <h1>Login to your workspace</h1>
            <p>Role-based access for Admin, Customer, and Developer users.</p>
        </div>
        <form class="stack-form" method="post" action="<?= route_url('/login') ?>">
            <input class="form-control" name="email" type="email" value="<?= e((string) old('email')) ?>" placeholder="Email Address">
            <input class="form-control" name="password" type="password" placeholder="Password">
            <div class="d-flex justify-content-between small-text">
                <label><input type="checkbox"> Remember me</label>
                <a href="<?= route_url('/forgot-password') ?>">Forgot password?</a>
            </div>
            <button class="btn btn-primary rounded-pill" type="submit">Login</button>
            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/register') ?>">Create Account</a>
        </form>
    </div>
</section>
