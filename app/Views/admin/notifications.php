<?php
$notifications = $notifications ?? [];
$unreadCount = (int) ($unreadCount ?? 0);
?>
<div class="dash-card">
    <div class="card-title-row">
        <h4>Notifications</h4>
        <span><?= $unreadCount ?> unread</span>
    </div>

    <?php if ($notifications === []): ?>
        <div class="dash-empty-state compact-empty-state">
            <div class="dash-empty-state-icon"><i class="bi bi-bell-slash"></i></div>
            <div class="dash-empty-state-copy">
                <h5>No notifications yet</h5>
                <p>New registrations, payments, tickets, and marketplace activity will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="topbar-notification-list notifications-page-list">
            <?php foreach ($notifications as $notification): ?>
                <?php
                $actionUrl = trim((string) ($notification['action_url'] ?? ''));
                $isRead = ! empty($notification['read_at']);
                ?>
                <article class="topbar-notification-item <?= $isRead ? 'is-read' : 'is-unread' ?>">
                    <div class="topbar-notification-copy">
                        <div class="topbar-notification-header">
                            <strong><?= e($notification['title'] ?? 'Notification') ?></strong>
                            <span><?= e(date('d M, h:i A', strtotime((string) ($notification['created_at'] ?? 'now')))) ?></span>
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
                                <button class="btn btn-sm btn-primary rounded-pill" type="submit">Mark read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
