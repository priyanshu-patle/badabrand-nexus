<?php
use App\Core\Content;

$profile = $currentUser ?? $user ?? [];
$firstName = trim((string) ($profile['first_name'] ?? 'User'));
$lastName = trim((string) ($profile['last_name'] ?? ''));
$role = ucfirst((string) ($profile['role'] ?? 'account'));
$fullName = trim($firstName . ' ' . $lastName) ?: 'User Account';
$initials = strtoupper(substr($firstName ?: 'U', 0, 1) . substr($lastName ?: '', 0, 1));
$isAdmin = ($profile['role'] ?? '') === 'admin';
$profileRoute = $isAdmin ? '/admin/profile' : (($profile['role'] ?? '') === 'vendor' ? '/vendor/profile' : '/client');
$settingsRoute = $isAdmin ? '/admin/settings/general' : (($profile['role'] ?? '') === 'vendor' ? '/vendor/settings' : '/client');
$changePasswordRoute = $profileRoute . '#security';
$searchAction = $isAdmin ? route_url('/admin/search') : '';
$searchPlaceholder = $isAdmin ? 'Search users, services, orders, tickets, and projects' : 'Search this workspace';
$quickCreateLinks = $isAdmin ? \App\Core\AdminNavigation::quickCreateLinks((string) ($profile['role'] ?? 'admin')) : [];
$notificationItems = $isAdmin ? Content::userNotifications((int) ($profile['id'] ?? 0), 6) : [];
$unreadCount = $isAdmin ? Content::unreadNotificationCount((int) ($profile['id'] ?? 0)) : 0;
?>
<header class="dashboard-topbar" data-dashboard-topbar>
    <div class="topbar-left">
        <div class="topbar-rail-controls">
            <button class="topbar-icon topbar-menu-toggle" type="button" data-dashboard-menu-toggle aria-label="Toggle dashboard menu">
                <i class="bi bi-list"></i>
            </button>
            <button class="topbar-icon topbar-sidebar-toggle" type="button" data-sidebar-collapse-toggle aria-label="Collapse sidebar" title="Collapse sidebar">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>
        </div>
        <form class="topbar-search" method="get" action="<?= e($searchAction) ?>">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= e($globalSearch ?? $search ?? '') ?>" placeholder="<?= e($searchPlaceholder) ?>">
        </form>
    </div>
    <div class="topbar-actions">
        <button class="topbar-icon" type="button" data-theme-toggle aria-label="Switch admin theme" title="Switch admin theme">
            <i class="bi bi-circle-half"></i>
        </button>
        <?php if ($isAdmin && $quickCreateLinks !== []): ?>
            <div class="dropdown topbar-create-dropdown" data-bs-auto-close="outside">
                <button class="btn btn-primary topbar-create-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end topbar-create-menu">
                    <?php foreach ($quickCreateLinks as $link): ?>
                        <li>
                            <a class="dropdown-item" href="<?= route_url($link['href']) ?>">
                                <i class="bi <?= e($link['icon']) ?>"></i>
                                <span><?= e($link['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <div class="dropdown topbar-notification-dropdown" data-bs-auto-close="outside">
                <button class="topbar-icon topbar-notification-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="topbar-notification-badge"><?= e($unreadCount > 9 ? '9+' : (string) $unreadCount) ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end topbar-notification-menu">
                    <div class="topbar-menu-header">
                        <div>
                            <strong>Notifications</strong>
                            <small><?= e($unreadCount) ?> unread</small>
                        </div>
                        <form method="post" action="<?= route_url('/admin/notifications/read') ?>">
                            <input type="hidden" name="redirect_to" value="/admin">
                            <button class="btn btn-sm btn-link text-decoration-none" type="submit">Mark all read</button>
                        </form>
                    </div>
                    <?php if ($notificationItems === []): ?>
                        <div class="topbar-notification-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications right now.</p>
                        </div>
                    <?php else: ?>
                        <div class="topbar-notification-list">
                            <?php foreach ($notificationItems as $notification): ?>
                                <?php
                                $actionUrl = trim((string) ($notification['action_url'] ?? ''));
                                $isRead = ! empty($notification['read_at']);
                                ?>
                                <div class="topbar-notification-item <?= $isRead ? 'is-read' : 'is-unread' ?>">
                                    <div class="topbar-notification-copy">
                                        <div class="topbar-notification-header">
                                            <strong><?= e($notification['title'] ?? 'Notification') ?></strong>
                                            <span><?= e(date('d M', strtotime((string) ($notification['created_at'] ?? 'now')))) ?></span>
                                        </div>
                                        <p><?= e($notification['body'] ?? '') ?></p>
                                    </div>
                                    <div class="topbar-notification-footer">
                                        <?php if ($actionUrl !== ''): ?>
                                            <a class="btn btn-sm btn-outline-light rounded-pill" href="<?= route_url($actionUrl) ?>">Open</a>
                                        <?php endif; ?>
                                        <?php if (! $isRead): ?>
                                            <form method="post" action="<?= route_url('/admin/notifications/read') ?>">
                                                <input type="hidden" name="notification_id" value="<?= (int) ($notification['id'] ?? 0) ?>">
                                                <input type="hidden" name="redirect_to" value="/admin">
                                                <button class="btn btn-sm btn-primary rounded-pill" type="submit">Read</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="topbar-menu-footer">
                        <a class="btn btn-sm btn-outline-light rounded-pill" href="<?= route_url('/admin/notifications') ?>">View all notifications</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="dropdown topbar-profile-dropdown" data-bs-auto-close="outside">
            <button class="topbar-profile topbar-profile-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?= e($initials ?: 'U') ?></span>
                <div>
                    <strong><?= e($fullName) ?></strong>
                    <small><?= e($role) ?></small>
                </div>
            </button>
            <div class="dropdown-menu dropdown-menu-end topbar-profile-menu">
                <div class="topbar-menu-header">
                    <div>
                        <strong><?= e($fullName) ?></strong>
                        <small><?= e($profile['email'] ?? '') ?></small>
                    </div>
                </div>
                <a class="dropdown-item" href="<?= route_url($profileRoute) ?>">
                    <i class="bi bi-person-circle"></i>
                    <span>My Profile</span>
                </a>
                <a class="dropdown-item" href="<?= route_url($profileRoute) ?>">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit Profile</span>
                </a>
                <a class="dropdown-item" href="<?= route_url($settingsRoute) ?>">
                    <i class="bi bi-gear"></i>
                    <span>Account Settings</span>
                </a>
                <a class="dropdown-item" href="<?= route_url($changePasswordRoute) ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Change Password</span>
                </a>
                <?php if ($isAdmin): ?>
                    <a class="dropdown-item" href="<?= route_url('/admin/appearance/themes') ?>">
                        <i class="bi bi-palette"></i>
                        <span>Theme Preference</span>
                    </a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?= route_url('/logout') ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>
