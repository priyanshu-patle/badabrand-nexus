<footer class="footer-shell">
    <div class="container">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="brand-mark mb-3">
                    <img src="<?= brand_asset_url($settings['company_logo'] ?? null, 'images/badabrand-logo.svg') ?>" alt="Badabrand Logo" class="brand-logo">
                    <span><strong><?= e($settings['footer_company_name'] ?? ($settings['site_title'] ?? 'Badabrand Technologies')) ?></strong></span>
                </div>
                <p class="text-muted mb-3"><?= e($settings['footer_text'] ?? 'Production-ready IT services website with admin CMS, billing, support, and client portal workflows.') ?></p>
                <div class="social-row">
                    <a href="<?= e($settings['social_facebook'] ?? '#') ?>"><i class="bi bi-facebook"></i></a>
                    <a href="<?= e($settings['social_twitter'] ?? '#') ?>"><i class="bi bi-twitter-x"></i></a>
                    <a href="<?= e($settings['social_linkedin'] ?? '#') ?>"><i class="bi bi-linkedin"></i></a>
                    <a href="<?= e($settings['social_instagram'] ?? '#') ?>"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Company</h6>
                <a href="<?= route_url('/about') ?>">About</a>
                <a href="<?= route_url('/services') ?>">Services</a>
                <a href="<?= route_url('/portfolio') ?>">Portfolio</a>
                <a href="<?= route_url('/pricing') ?>">Pricing</a>
                <a href="<?= route_url('/contact') ?>">Contact</a>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Resources</h6>
                <a href="<?= route_url('/blog') ?>">Blog</a>
                <a href="<?= route_url('/faq') ?>">FAQ</a>
                <a href="<?= route_url('/careers') ?>">Careers</a>
                <a href="<?= route_url('/privacy') ?>">Privacy</a>
                <a href="<?= route_url('/terms') ?>">Terms</a>
            </div>
            <div class="col-lg-4">
                <h6>Newsletter</h6>
                <p class="text-muted">Get product updates, case studies, and launch news.</p>
                <form class="newsletter-form">
                    <input type="email" class="form-control" placeholder="Enter your email">
                    <button class="btn btn-primary rounded-pill" type="button">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> <?= e($settings['footer_company_name'] ?? ($settings['site_title'] ?? 'Badabrand Technologies')) ?>. All rights reserved.</span>
            <span><?= e($settings['contact_email'] ?? config('app.company_email')) ?> | <?= e($settings['contact_phone'] ?? config('app.company_phone')) ?></span>
        </div>
    </div>
</footer>
