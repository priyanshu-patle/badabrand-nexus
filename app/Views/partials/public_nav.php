<nav class="navbar navbar-expand-lg topbar simple-nav">
    <div class="container py-2">
        <a class="navbar-brand brand-mark" href="<?= route_url('/') ?>">
            <img src="<?= brand_asset_url($settings['company_logo'] ?? null, 'images/badabrand-logo.svg') ?>" alt="Badabrand Logo" class="brand-logo">
            <span><strong><?= e($settings['site_title'] ?? 'Badabrand Technologies') ?></strong></span>
        </a>
        <button class="navbar-toggler text-white border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/about') ?>">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/services') ?>">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/marketplace') ?>">Marketplace</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/pricing') ?>">Pricing</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/portfolio') ?>">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/blog') ?>">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= route_url('/contact') ?>">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 nav-cta-group">
                <button class="icon-btn" type="button" data-theme-toggle aria-label="Switch site theme" title="Switch site theme">
                    <i class="bi bi-circle-half"></i>
                </button>
                <?php if (!empty($user)): ?>
                    <?php $dash = $user['role'] === 'admin' ? '/admin' : ($user['role'] === 'developer' ? '/developer' : '/client'); ?>
                    <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= route_url($dash) ?>">Dashboard</a>
                    <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= route_url('/logout') ?>">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm rounded-pill px-3" href="<?= route_url('/login') ?>">Login</a>
                    <a class="btn btn-primary btn-sm rounded-pill px-3" href="<?= route_url('/register') ?>">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
