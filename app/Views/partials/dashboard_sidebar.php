<?php
$userRole = $currentUser['role'] ?? $user['role'] ?? 'customer';
$normalizedPath = current_request_path();
$menu = [];
$adminMenu = [];
if ($userRole === 'admin') {
    $adminMenu = \App\Core\AdminNavigation::menu((string) $userRole);
} elseif ($userRole === 'developer') {
    $menu = [
        '/developer' => ['Dashboard', 'bi-speedometer2'],
        '/developer/projects' => ['Assigned Projects', 'bi-kanban'],
        '/developer/tickets' => ['Support Queue', 'bi-life-preserver'],
    ];
} elseif ($userRole === 'vendor') {
    $menu = [
        '/vendor/dashboard' => ['Dashboard', 'bi-speedometer2'],
        '/vendor/products' => ['Products', 'bi-bag'],
        '/vendor/orders' => ['Orders', 'bi-receipt'],
        '/vendor/payouts' => ['Payouts', 'bi-wallet2'],
        '/vendor/profile' => ['Store Profile', 'bi-shop'],
        '/vendor/settings' => ['Settings', 'bi-sliders'],
    ];
} else {
    $menu = [
        '/client' => ['Dashboard', 'bi-speedometer2'],
        '/client/services' => ['Orders & Services', 'bi-grid'],
        '/client/marketplace' => ['Marketplace', 'bi-bag'],
        '/client/invoices' => ['Invoices', 'bi-receipt'],
        '/client/payments' => ['Payments', 'bi-credit-card'],
        '/client/tickets' => ['Support Tickets', 'bi-life-preserver'],
        '/client/projects' => ['Project Tracker', 'bi-kanban'],
        '/client/proposals' => ['Proposals', 'bi-file-earmark-text'],
        '/client/contracts' => ['Contracts', 'bi-vector-pen'],
        '/client/files' => ['Files', 'bi-folder2-open'],
    ];
}
?>
<aside class="dashboard-sidebar" data-dashboard-sidebar>
    <div class="sidebar-header">
        <a class="brand-mark" href="<?= route_url('/') ?>">
            <img src="<?= brand_asset_url($settings['company_logo'] ?? null, 'images/badabrand-logo.svg') ?>" alt="Badabrand Logo" class="brand-logo">
            <span class="brand-copy">
                <strong><?= e($settings['site_title'] ?? 'Badabrand Technologies') ?></strong>
                <small class="brand-caption">SaaS workspace</small>
            </span>
        </a>
        <div class="sidebar-header-meta">
            <span class="sidebar-role-pill"><?= e(ucfirst($userRole)) ?> panel</span>
        </div>
    </div>
    <div class="sidebar-scroll" data-sidebar-scroll>
        <a class="sidebar-link-utility" href="<?= route_url('/') ?>">
            <i class="bi bi-globe2"></i>
            <span>Website</span>
        </a>
        <nav class="dashboard-nav" data-sidebar-nav>
            <?php if ($userRole === 'admin'): ?>
                <div class="sidebar-group sidebar-group-single">
                    <div class="sidebar-section-label">Main navigation</div>
                    <div class="sidebar-group-links">
                        <?php foreach ($adminMenu as $item): ?>
                            <?php
                            $isActive = \App\Core\AdminNavigation::pathIsActive($item, $normalizedPath);
                            $groupKeySeed = (string) (($item['key'] ?? '') ?: ($item['section'] ?? '') ?: ($item['href'] ?? ($item['label'] ?? 'group')));
                            $groupKey = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $groupKeySeed), '-')) ?: 'group';
                            ?>
                            <?php if (! empty($item['children'])): ?>
                                <div class="sidebar-menu-card <?= $isActive ? 'is-active is-open' : '' ?>" data-sidebar-group data-group-key="<?= e($groupKey) ?>" data-group-active="<?= $isActive ? 'true' : 'false' ?>">
                                    <div class="sidebar-parent-row">
                                        <a class="sidebar-parent-link <?= $isActive ? 'active' : '' ?>" href="<?= route_url($item['href']) ?>" title="<?= e($item['label']) ?>">
                                            <span class="sidebar-parent-icon"><i class="bi <?= e($item['icon']) ?>"></i></span>
                                            <span class="sidebar-parent-copy">
                                                <strong><?= e($item['label']) ?></strong>
                                                <small>Open workspace</small>
                                            </span>
                                        </a>
                                        <?php if (! empty($item['badge'])): ?>
                                            <span class="sidebar-item-badge"><?= e((string) $item['badge']) ?></span>
                                        <?php endif; ?>
                                        <button class="sidebar-parent-toggle" type="button" data-sidebar-toggle aria-expanded="<?= $isActive ? 'true' : 'false' ?>" aria-controls="sidebar-panel-<?= e($groupKey) ?>" aria-label="Toggle <?= e($item['label']) ?> submenu">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="sidebar-child-links" id="sidebar-panel-<?= e($groupKey) ?>">
                                        <?php foreach ($item['children'] as $child): ?>
                                            <?php $childActive = \App\Core\AdminNavigation::pathIsActive($child, $normalizedPath); ?>
                                            <a class="<?= $childActive ? 'active' : '' ?>" href="<?= route_url($child['href']) ?>">
                                                <span><?= e($child['label']) ?></span>
                                                <?php if (! empty($child['badge'])): ?>
                                                    <span class="sidebar-item-badge"><?= e((string) $child['badge']) ?></span>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a class="sidebar-parent-link <?= $isActive ? 'active' : '' ?>" href="<?= route_url($item['href']) ?>" title="<?= e($item['label']) ?>">
                                    <span class="sidebar-parent-icon"><i class="bi <?= e($item['icon']) ?>"></i></span>
                                    <span class="sidebar-parent-copy">
                                        <strong><?= e($item['label']) ?></strong>
                                    </span>
                                    <?php if (! empty($item['badge'])): ?>
                                        <span class="sidebar-item-badge"><?= e((string) $item['badge']) ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="sidebar-section-label">Workspace</div>
                <?php foreach ($menu as $path => $item): ?>
                    <?php $isActive = $path === '/client' || $path === '/developer' ? $normalizedPath === $path : str_starts_with($normalizedPath, $path); ?>
                    <a class="<?= $isActive ? 'active' : '' ?>" href="<?= route_url($path) ?>">
                        <i class="bi <?= e($item[1]) ?>"></i>
                        <span><?= e($item[0]) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            <a class="sidebar-logout" href="<?= route_url('/logout') ?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
</aside>
