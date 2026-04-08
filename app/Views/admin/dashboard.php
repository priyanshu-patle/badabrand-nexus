<?php
$dashboardInsights = $dashboardInsights ?? [];
$dashboardRange = $dashboardRange ?? ($dashboardInsights['range'] ?? 'month');
$revenue = $dashboardInsights['revenue'] ?? ['total' => 0, 'paid' => 0, 'pending' => 0, 'invoice_count' => 0, 'growth_percent' => null];
$orders = $dashboardInsights['orders'] ?? ['total' => 0, 'new' => 0, 'in_progress' => 0, 'completed' => 0, 'on_hold' => 0];
$users = $dashboardInsights['users'] ?? ['total' => 0, 'active' => 0, 'new_period' => 0, 'new_week' => 0, 'new_today' => 0, 'roles' => []];
$support = $dashboardInsights['support'] ?? ['open' => 0, 'pending_replies' => 0, 'high_priority' => 0, 'recently_updated' => 0];
$payments = $dashboardInsights['payments'] ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'recent_transactions' => 0];
$topServices = $dashboardInsights['top_services'] ?? [];
$topProducts = $dashboardInsights['top_products'] ?? [];
$alerts = $dashboardInsights['alerts'] ?? [];
$activity = $dashboardInsights['activity'] ?? [];
$rangeLabel = $dashboardInsights['range_label'] ?? ($dashboardRange === 'all' ? 'All Time' : 'This Month');
$growth = $revenue['growth_percent'] ?? null;
?>

<section class="dashboard-controlbar">
    <div class="dashboard-controlbar-copy">
        <span class="section-kicker">Overview</span>
        <h2>Business command center</h2>
        <p>Track revenue, delivery load, clients, support pressure, and urgent admin actions from one focused workspace.</p>
    </div>
    <div class="dashboard-filter-actions">
        <a class="dashboard-filter-pill <?= $dashboardRange === 'month' ? 'active' : '' ?>" href="<?= route_url('/admin?range=month') ?>">This Month</a>
        <a class="dashboard-filter-pill <?= $dashboardRange === 'all' ? 'active' : '' ?>" href="<?= route_url('/admin?range=all') ?>">All Time</a>
    </div>
</section>

<section class="dashboard-kpi-grid">
    <article class="dashboard-kpi-card accent-primary">
        <div class="dashboard-kpi-head">
            <span class="dashboard-kpi-label">Revenue</span>
            <a href="<?= route_url('/admin/billing/invoices') ?>">Invoices</a>
        </div>
        <strong class="dashboard-kpi-value"><?= e(money_format_inr($revenue['total'] ?? 0)) ?></strong>
        <div class="dashboard-kpi-meta">
            <span><?= e((string) ($revenue['invoice_count'] ?? 0)) ?> invoices in <?= e(strtolower($rangeLabel)) ?></span>
            <?php if ($growth !== null): ?>
                <span class="dashboard-trend <?= $growth >= 0 ? 'positive' : 'negative' ?>"><?= e(($growth >= 0 ? '+' : '') . number_format((float) $growth, 1)) ?>%</span>
            <?php else: ?>
                <span class="dashboard-trend neutral">No comparison</span>
            <?php endif; ?>
        </div>
        <div class="dashboard-kpi-split">
            <div>
                <span>Paid</span>
                <strong><?= e(money_format_inr($revenue['paid'] ?? 0)) ?></strong>
            </div>
            <div>
                <span>Pending</span>
                <strong><?= e(money_format_inr($revenue['pending'] ?? 0)) ?></strong>
            </div>
        </div>
    </article>

    <article class="dashboard-kpi-card">
        <div class="dashboard-kpi-head">
            <span class="dashboard-kpi-label">Orders</span>
            <a href="<?= route_url('/admin/projects') ?>">Projects</a>
        </div>
        <strong class="dashboard-kpi-value"><?= e((string) ($orders['total'] ?? 0)) ?></strong>
        <div class="dashboard-kpi-meta">
            <span><?= e((string) ($orders['new'] ?? 0)) ?> new</span>
            <span><?= e((string) ($orders['in_progress'] ?? 0)) ?> in progress</span>
        </div>
        <div class="dashboard-mini-stat-grid">
            <div><span>Completed</span><strong><?= e((string) ($orders['completed'] ?? 0)) ?></strong></div>
            <div><span>On hold</span><strong><?= e((string) ($orders['on_hold'] ?? 0)) ?></strong></div>
        </div>
    </article>

    <article class="dashboard-kpi-card">
        <div class="dashboard-kpi-head">
            <span class="dashboard-kpi-label">Clients</span>
            <a href="<?= route_url('/admin/users') ?>">Directory</a>
        </div>
        <strong class="dashboard-kpi-value"><?= e((string) ($users['total'] ?? 0)) ?></strong>
        <div class="dashboard-kpi-meta">
            <span><?= e((string) ($users['active'] ?? 0)) ?> active</span>
            <span><?= e((string) ($users['new_period'] ?? 0)) ?> new in <?= e(strtolower($rangeLabel)) ?></span>
        </div>
        <div class="role-chip-row">
            <?php foreach (($users['roles'] ?? []) as $role => $count): ?>
                <span class="role-chip"><strong><?= e((string) $count) ?></strong><?= e(ucfirst($role)) ?></span>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="dashboard-kpi-card">
        <div class="dashboard-kpi-head">
            <span class="dashboard-kpi-label">Support</span>
            <a href="<?= route_url('/admin/support/tickets') ?>">Tickets</a>
        </div>
        <strong class="dashboard-kpi-value"><?= e((string) ($support['open'] ?? 0)) ?></strong>
        <div class="dashboard-kpi-meta">
            <span><?= e((string) ($support['pending_replies'] ?? 0)) ?> pending replies</span>
            <span><?= e((string) ($support['high_priority'] ?? 0)) ?> priority</span>
        </div>
        <div class="dashboard-mini-stat-grid">
            <div><span>Updated 7d</span><strong><?= e((string) ($support['recently_updated'] ?? 0)) ?></strong></div>
            <div><span>Today</span><strong><?= e((string) ($users['new_today'] ?? 0)) ?></strong></div>
        </div>
    </article>
</section>

<section class="dashboard-main-grid">
    <div class="dashboard-main-column">
        <article class="dash-card dashboard-panel-card">
            <div class="dashboard-panel-head">
                <div>
                    <span class="section-kicker">Billing</span>
                    <h3>Payments and collections</h3>
                </div>
                <a class="badge-soft" href="<?= route_url('/admin/billing/payments') ?>">Open billing</a>
            </div>
            <div class="dashboard-list-grid">
                <div class="dashboard-list-item">
                    <span>Pending payments</span>
                    <strong><?= e((string) ($payments['pending'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>Approved payments</span>
                    <strong><?= e((string) ($payments['approved'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>Rejected</span>
                    <strong><?= e((string) ($payments['rejected'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>Recent transactions</span>
                    <strong><?= e((string) ($payments['recent_transactions'] ?? 0)) ?></strong>
                </div>
            </div>
        </article>

        <article class="dash-card dashboard-panel-card">
            <div class="dashboard-panel-head">
                <div>
                    <span class="section-kicker">Performance</span>
                    <h3>Top services and marketplace products</h3>
                </div>
                <a class="badge-soft" href="<?= route_url('/admin/marketplace') ?>">View marketplace</a>
            </div>
            <div class="dashboard-performance-grid">
                <div>
                    <h4>Services</h4>
                    <?php if ($topServices): ?>
                        <div class="dashboard-ranked-list">
                            <?php foreach ($topServices as $service): ?>
                                <div class="dashboard-ranked-item">
                                    <div>
                                        <strong><?= e($service['name']) ?></strong>
                                        <span><?= e((string) $service['order_count']) ?> orders</span>
                                    </div>
                                    <span><?= e(money_format_inr($service['revenue_total'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="widget-empty">No service sales recorded for this range.</div>
                    <?php endif; ?>
                </div>
                <div>
                    <h4>Products</h4>
                    <?php if ($topProducts): ?>
                        <div class="dashboard-ranked-list">
                            <?php foreach ($topProducts as $product): ?>
                                <div class="dashboard-ranked-item">
                                    <div>
                                        <strong><?= e($product['name']) ?></strong>
                                        <span><?= e((string) $product['order_count']) ?> orders</span>
                                    </div>
                                    <span><?= e(money_format_inr($product['revenue_total'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="widget-empty">No product sales recorded for this range.</div>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </div>

    <div class="dashboard-side-column">
        <article class="dash-card dashboard-panel-card">
            <div class="dashboard-panel-head">
                <div>
                    <span class="section-kicker">Alerts</span>
                    <h3>Admin tasks waiting</h3>
                </div>
                <a class="badge-soft" href="<?= route_url('/admin/search') ?>">Search</a>
            </div>
            <div class="widget-alert-list">
                <?php foreach ($alerts as $alert): ?>
                    <a class="widget-alert-item" href="<?= route_url($alert['route'] ?? '/admin') ?>">
                        <div>
                            <strong><?= e($alert['label'] ?? 'Admin task') ?></strong>
                            <span>Open related workflow</span>
                        </div>
                        <span class="status-pill neutral"><?= e((string) ($alert['count'] ?? 0)) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="dash-card dashboard-panel-card">
            <div class="dashboard-panel-head">
                <div>
                    <span class="section-kicker">Snapshot</span>
                    <h3>Fast workspace context</h3>
                </div>
            </div>
            <div class="dashboard-list-grid dashboard-list-grid-tight">
                <div class="dashboard-list-item">
                    <span>New today</span>
                    <strong><?= e((string) ($users['new_today'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>New this week</span>
                    <strong><?= e((string) ($users['new_week'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>Active accounts</span>
                    <strong><?= e((string) ($users['active'] ?? 0)) ?></strong>
                </div>
                <div class="dashboard-list-item">
                    <span>Open support load</span>
                    <strong><?= e((string) ($support['open'] ?? 0)) ?></strong>
                </div>
            </div>
        </article>
    </div>
</section>

<section class="dash-card dashboard-panel-card">
    <div class="dashboard-panel-head">
        <div>
            <span class="section-kicker">Activity</span>
            <h3>Recent activity feed</h3>
        </div>
        <span class="badge-soft"><?= e((string) count($activity)) ?> events</span>
    </div>
    <?php if ($activity): ?>
        <div class="widget-timeline">
            <?php foreach ($activity as $event): ?>
                <div class="widget-timeline-item">
                    <span class="widget-timeline-dot"></span>
                    <div class="widget-timeline-content">
                        <div class="widget-timeline-head">
                            <strong><?= e($event['summary'] ?? 'System activity') ?></strong>
                            <span><?= e(date('d M Y, h:i A', strtotime((string) ($event['created_at'] ?? 'now')))) ?></span>
                        </div>
                        <p><?= e($event['meta'] ?? 'Activity log') ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="widget-empty mt-3">No activity has been recorded yet. New signups, orders, payments, and workflow updates will appear here automatically.</div>
    <?php endif; ?>
</section>
