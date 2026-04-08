<section class="auth-wrap">
    <div class="auth-card">
        <div>
            <span class="eyebrow">Password Recovery</span>
            <h1>Reset your account access</h1>
            <p>This demo app includes the page flow. Password reset emails can be added next if you want full recovery support.</p>
        </div>
        <form class="stack-form">
            <input class="form-control" type="email" placeholder="Registered Email Address">
            <button class="btn btn-primary rounded-pill" type="button">Send Reset Link</button>
            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/login') ?>">Back to Login</a>
        </form>
    </div>
</section>
