<?php
$adminSection = $adminSection ?? null;
$adminTab = $adminTab ?? null;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = parse_url(config('app.url'), PHP_URL_PATH) ?: '';
$normalizedPath = $basePath && str_starts_with($currentPath, $basePath) ? substr($currentPath, strlen($basePath)) ?: '/' : $currentPath;
$role = (string) ($currentUser['role'] ?? $user['role'] ?? 'admin');

if (! $adminSection) {
    return;
}

$tabs = \App\Core\AdminNavigation::tabs((string) $adminSection, $role);
if ($tabs === []) {
    return;
}
?>
<div class="admin-tabs-shell">
    <nav class="admin-tabs" aria-label="Section navigation">
        <?php foreach ($tabs as $tabItem): ?>
            <?php $isActive = ($adminTab ?? '') === $tabItem['label'] || \App\Core\AdminNavigation::pathIsActive($tabItem, $normalizedPath); ?>
            <a class="admin-tab-link <?= $isActive ? 'active' : '' ?>" href="<?= route_url($tabItem['href']) ?>">
                <span><?= e($tabItem['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
