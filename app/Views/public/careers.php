<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Careers</span>
        <h1>Open roles for product builders, designers, and growth specialists.</h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6">
                    <div class="feature-card h-100">
                        <h4><?= e($job['title']) ?></h4>
                        <p><?= e($job['location']) ?> | <?= e($job['employment_type']) ?></p>
                        <p><?= e($job['summary']) ?></p>
                        <div class="card-foot">
                            <span class="badge-soft"><?= e($job['status']) ?></span>
                            <a href="<?= route_url('/contact') ?>">Apply</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php return; ?>
<?php $demo = $demo ?? require base_path('app/Config/demo.php'); ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">Careers</span><h1>Open roles for product builders, designers, and growth specialists.</h1></div></section>
<section class="section-space"><div class="container"><div class="row g-4"><?php foreach ($demo['jobs'] as $job): ?><div class="col-md-6"><div class="feature-card h-100"><h4><?= e($job['title']) ?></h4><p><?= e($job['location']) ?> | <?= e($job['type']) ?></p><a href="#" class="btn btn-outline-light rounded-pill">Apply Now</a></div></div><?php endforeach; ?></div></div></section>
