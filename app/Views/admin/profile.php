<div class="dash-card">
    <div class="card-title-row"><h4>Admin Profile</h4><span>Edit your account</span></div>
    <form class="stack-form" method="post" action="<?= route_url('/admin/profile') ?>">
        <div id="details" class="stack-form-section">
            <label class="form-label small text-uppercase text-secondary mb-2">Profile details</label>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="first_name" value="<?= e($profile['first_name']) ?>" placeholder="First name"></div>
            <div class="col-md-6"><input class="form-control" name="last_name" value="<?= e($profile['last_name']) ?>" placeholder="Last name"></div>
        </div>
        <input class="form-control" name="email" value="<?= e($profile['email']) ?>" placeholder="Email">
        <input class="form-control" name="phone" value="<?= e($profile['phone'] ?? '') ?>" placeholder="Phone">
        <input class="form-control" value="<?= e(ucfirst($profile['role'] ?? 'admin')) ?>" readonly placeholder="Role">
        <div id="security" class="stack-form-section">
            <label class="form-label small text-uppercase text-secondary mb-2">Security</label>
        </div>
        <input class="form-control" name="password" type="password" placeholder="New password (optional)">
        <button class="btn btn-primary rounded-pill" type="submit">Update Profile</button>
    </form>
</div>
