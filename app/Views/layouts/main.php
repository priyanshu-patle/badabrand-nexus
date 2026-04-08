<?php
$settings = $settings ?? [];
$user = $user ?? null;
$theme = resolve_theme_name('public', $settings, is_array($user) ? $user : null);
?>
<!doctype html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? ($settings['seo_default_title'] ?? ($settings['site_title'] ?? config('app.name')))) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? ($settings['seo_default_description'] ?? config('app.tagline'))) ?>">
    <meta name="robots" content="index,follow">
    <meta property="og:title" content="<?= e($pageTitle ?? ($settings['seo_default_title'] ?? ($settings['site_title'] ?? config('app.name')))) ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? ($settings['seo_default_description'] ?? config('app.tagline'))) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(config('app.url')) ?>">
    <?php if (!empty($settings['seo_keywords'])): ?><meta name="keywords" content="<?= e($settings['seo_keywords']) ?>"><?php endif; ?>
    <link rel="canonical" href="<?= e(config('app.url')) ?>">
    <link rel="icon" href="<?= brand_asset_url($settings['company_favicon'] ?? null, 'images/badabrand-favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="site-shell public-body">
<?php require view_path('partials/public_nav.php'); ?>
<?php if ($message = flash('success')): ?><div class="flash-banner success"><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="flash-banner error"><?= e($message) ?></div><?php endif; ?>
<main><?= $content ?></main>
<?php require view_path('partials/public_footer.php'); ?>
<a class="whatsapp-float" target="_blank" rel="noreferrer" href="https://wa.me/<?= e($settings['company_whatsapp'] ?? config('app.company_whatsapp')) ?>"><i class="bi bi-whatsapp"></i></a>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
