<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Blog</span>
        <h1>SEO-friendly publishing system with clean URLs, meta tags, and schema support.</h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="blog-card h-100">
                        <span class="badge-soft"><?= e($post['category']) ?></span>
                        <h4><?= e($post['title']) ?></h4>
                        <p><?= e($post['excerpt']) ?></p>
                        <div class="card-foot">
                            <span><?= e($post['slug']) ?></span>
                            <span><?= e(!empty($post['published_at']) ? date('d M Y', strtotime($post['published_at'])) : 'Draft') ?></span>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php return; ?>
<?php $demo = $demo ?? require base_path('app/Config/demo.php'); ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">Blog</span><h1>SEO-friendly publishing system with clean URLs, meta tags, and schema support.</h1></div></section>
<section class="section-space"><div class="container"><div class="row g-4"><?php foreach ($demo['blog'] as $post): ?><div class="col-md-6 col-lg-4"><article class="blog-card h-100"><span class="badge-soft"><?= e($post['category']) ?></span><h4><?= e($post['title']) ?></h4><p>Slug: /blog/<?= e($post['slug']) ?></p><p>Published: <?= e($post['date']) ?></p><a href="#">Read article</a></article></div><?php endforeach; ?></div></div></section>
