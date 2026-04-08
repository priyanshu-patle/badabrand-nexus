<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">About Us</span>
        <h1>Badabrand Technologies builds modern digital systems for growing businesses.</h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass-panel h-100">
                    <h3>Our story</h3>
                    <p><?= e($settings['about_summary'] ?? 'We help offline and growing businesses move online with websites, applications, branding, hosting, and managed support.') ?></p>
                    <p>From websites and marketplaces to contracts, billing, customer portals, and internal workflows, the platform is designed to let a service company operate from one connected system.</p>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="glass-panel h-100">
                    <h3>Delivery snapshot</h3>
                    <div class="mini-panel-grid">
                        <?php foreach (array_slice($stats ?? [], 0, 4) as $item): ?>
                            <div class="mini-panel">
                                <strong><?= e($item['value'] . ($item['suffix'] ?? '')) ?></strong>
                                <span><?= e($item['label']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if (!empty($teamMembers)): ?>
<section class="section-space section-alt">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Team</span>
            <h2>The people behind the platform</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($teamMembers as $member): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="feature-card h-100">
                        <div class="icon-wrap"><i class="bi bi-person-badge"></i></div>
                        <h4><?= e($member['name']) ?></h4>
                        <p><?= e($member['role']) ?></p>
                        <div class="card-foot">
                            <span><?= e($member['email']) ?></span>
                            <span class="badge-soft">Team</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php return; ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">About Us</span><h1>Badabrand Technologies builds modern digital systems for growing businesses.</h1></div></section>
<section class="section-space"><div class="container"><div class="row g-4"><div class="col-lg-7"><div class="glass-panel"><h3>Our story</h3><p><?= e($settings['about_summary'] ?? 'We help offline and growing businesses move online with websites, applications, branding, hosting, and managed support.') ?></p><p>This new version turns a one-page marketing design into a real multipage application with login, registration, role-based dashboards, and editable homepage content from the admin panel.</p></div></div><div class="col-lg-5"><div class="glass-panel"><h3>What’s included</h3><ul class="stack-list"><li>Responsive frontend pages</li><li>Admin CMS for homepage content</li><li>MySQL user accounts and roles</li><li>Separate client and developer dashboards</li></ul></div></div></div></div></section>
