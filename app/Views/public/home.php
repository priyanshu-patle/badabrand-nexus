<section class="simple-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow">Premium IT, Web & Marketing Solutions</span>
                <h1><?= e($settings['hero_title'] ?? 'Build your digital business with Badabrand Technologies') ?></h1>
                <p class="lead"><?= e($settings['hero_subtitle'] ?? 'We design websites, apps, hosting, branding, and business systems with a clean admin CMS and secure customer dashboards.') ?></p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-primary btn-lg rounded-pill px-4" href="<?= route_url('/services') ?>"><?= e($settings['hero_cta_primary'] ?? 'Explore Services') ?></a>
                    <a class="btn btn-outline-light btn-lg rounded-pill px-4" href="<?= route_url('/contact') ?>"><?= e($settings['hero_cta_secondary'] ?? 'Contact Us') ?></a>
                </div>
                <div class="hero-stats">
                    <?php foreach (array_slice($stats ?? [], 0, 4) as $item): ?>
                        <div>
                            <strong><?= e($item['value'] . ($item['suffix'] ?? '')) ?></strong>
                            <span><?= e($item['label']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="simple-hero-card">
                    <div class="simple-screen">
                        <div class="screen-badge">Admin CMS</div>
                        <div class="screen-badge alt">Client Portal</div>
                        <div class="screen-badge dark">Developer Panel</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($slider)): ?>
<section class="section-space pt-0">
    <div class="container">
        <div class="row g-4">
            <?php foreach (array_slice($slider, 0, 3) as $item): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="mini-panel h-100">
                        <span class="eyebrow mb-2"><?= e($item['badge']) ?></span>
                        <strong><?= e($item['title']) ?></strong>
                        <span class="mt-2"><?= e($item['subtitle'] ?? '') ?></span>
                        <?php if (!empty($item['cta_text']) && !empty($item['cta_link'])): ?>
                            <a class="btn btn-outline-light rounded-pill mt-3" href="<?= e($item['cta_link']) ?>"><?= e($item['cta_text']) ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section-space">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Core Services</span>
            <h2>Simple service pages outside, powerful management inside</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-xl-3">
                    <article class="feature-card h-100">
                        <div class="icon-wrap"><i class="bi <?= e($service['icon'] ?: 'bi-briefcase') ?>"></i></div>
                        <h4><?= e($service['name']) ?></h4>
                        <p><?= e($service['short_description']) ?></p>
                        <div class="card-foot"><span><?= e($service['price_label']) ?></span><a href="<?= route_url('/services') ?>">View</a></div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space section-alt">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="glass-panel h-100">
                    <span class="eyebrow">About Badabrand</span>
                    <h2>One-page style inspiration, now upgraded into a real multipage web app</h2>
                    <p class="lead mb-0"><?= e($settings['about_summary'] ?? 'This build keeps the bold corporate tone of your provided homepage design, but now runs with login, registration, CMS editing, role-based dashboards, and MySQL-backed data.') ?></p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-panel h-100">
                    <span class="eyebrow">Why it works</span>
                    <div class="mini-panel-grid">
                        <div class="mini-panel"><strong>Admin control</strong><span>Edit homepage text, theme default, and services.</span></div>
                        <div class="mini-panel"><strong>Client area</strong><span>View project progress, invoices, and support.</span></div>
                        <div class="mini-panel"><strong>Developer area</strong><span>Track assignments, tickets, and sprint tasks.</span></div>
                        <div class="mini-panel"><strong>Marketplace flow</strong><span>Orders, invoices, payment proof, and customer dashboard updates work together.</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Pricing</span>
            <h2>Packages managed by the admin panel</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-4">
                    <div class="pricing-card <?= (int) $plan['is_featured'] === 1 ? 'featured' : '' ?>">
                        <h3><?= e($plan['name']) ?></h3>
                        <div class="price"><?= e($plan['price']) ?></div>
                        <p><?= e($plan['description']) ?></p>
                        <a class="btn btn-primary rounded-pill w-100 mt-3" href="<?= route_url('/pricing') ?>">See Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Digital Marketplace</span>
            <h2>Buy themes, plugins, and software directly from the frontend</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <article class="feature-card h-100">
                        <span class="badge-soft"><?= e(ucfirst((string) $product['product_type'])) ?></span>
                        <h4><?= e($product['name']) ?></h4>
                        <p><?= e($product['short_description']) ?></p>
                        <div class="card-foot">
                            <span><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                            <a href="<?= route_url('/marketplace') ?>">View</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space section-alt">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Testimonials</span>
            <h2>Trusted by growing businesses</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $item): ?>
                <div class="col-md-4">
                    <div class="blog-card h-100">
                        <p>"<?= e($item['quote']) ?>"</p>
                        <strong><?= e($item['name']) ?></strong>
                        <span><?= e($item['role']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
