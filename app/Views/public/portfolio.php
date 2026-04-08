<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Portfolio</span>
        <h1>Filterable project showcase for web, mobile, cloud, and marketing work.</h1>
    </div>
</section>
<section class="section-space section-alt">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="portfolio-card h-100">
                        <div class="portfolio-thumb"></div>
                        <span class="badge-soft"><?= e($project['category']) ?></span>
                        <h4><?= e($project['title']) ?></h4>
                        <p><?= e($project['summary']) ?></p>
                        <div class="card-foot">
                            <span><?= e($project['client_name'] ?: 'Badabrand Client') ?></span>
                            <?php if ((int) ($project['is_featured'] ?? 0) === 1): ?>
                                <span class="badge-soft">Featured</span>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php return; ?>
<?php $demo = $demo ?? require base_path('app/Config/demo.php'); ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">Portfolio</span><h1>Filterable project showcase for web, mobile, cloud, and marketing work.</h1></div></section>
<section class="section-space section-alt"><div class="container"><div class="row g-4"><?php foreach ($demo['portfolio'] as $project): ?><div class="col-md-6 col-lg-4"><article class="portfolio-card h-100"><div class="portfolio-thumb"></div><span class="badge-soft"><?= e($project['tag']) ?></span><h4><?= e($project['title']) ?></h4><p><?= e($project['description']) ?></p></article></div><?php endforeach; ?></div></div></section>
