<?php
$theme = 'dark';
?>
<!doctype html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Installer') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? 'Badabrand Technologies installer') ?>">
    <link rel="icon" href="<?= asset('images/badabrand-favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="installer-shell">
    <?php if ($message = flash('success')): ?><div class="flash-banner success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="flash-banner error"><?= e($message) ?></div><?php endif; ?>
    <?= $content ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
