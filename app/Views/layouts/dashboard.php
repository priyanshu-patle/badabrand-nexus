<?php
$settings = $settings ?? [];
$currentUser = $currentUser ?? null;
$theme = resolve_theme_name('admin', $settings, is_array($currentUser) ? $currentUser : null);
?>
<!doctype html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? config('app.name')) ?></title>
    <meta name="description" content="<?= e($metaDescription ?? config('app.tagline')) ?>">
    <link rel="icon" href="<?= brand_asset_url($settings['company_favicon'] ?? null, 'images/badabrand-favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="dashboard-shell" data-dashboard-shell>
    <?php if ($message = flash('success')): ?><div class="flash-banner success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="flash-banner error"><?= e($message) ?></div><?php endif; ?>
    <div class="dashboard-frame" data-dashboard-frame>
        <?php require view_path('partials/dashboard_sidebar.php'); ?>
        <button class="dashboard-overlay" type="button" data-dashboard-overlay aria-label="Close sidebar"></button>
        <div class="dashboard-main">
            <?php require view_path('partials/dashboard_topbar.php'); ?>
            <main class="dashboard-content" data-dashboard-content>
                <?php require view_path('partials/admin_tabs.php'); ?>
                <div class="dashboard-page-shell"><?= $content ?></div>
            </main>
            <footer class="dashboard-footer-brand" data-dashboard-footer-brand>
                <span><strong>Developed By:</strong> Priyanshu Patle</span>
                <span><strong>Call &amp; WhatsApp Number:</strong> +91 9109566312</span>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php foreach (($extraScripts ?? []) as $script): ?>
        <script src="<?= e($script) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
