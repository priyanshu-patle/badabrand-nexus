<?php

namespace App\Core;

class AdminNavigation
{
    public static function menu(string $role = 'admin'): array
    {
        return self::groups($role)[0]['items'] ?? [];
    }

    public static function groups(string $role = 'admin'): array
    {
        $items = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'href' => '/admin', 'match' => [['path' => '/admin', 'type' => 'exact'], ['path' => '/admin/search', 'type' => 'prefix']], 'sort_order' => 10],
            ['key' => 'users', 'label' => 'Clients', 'icon' => 'bi-people', 'href' => '/admin/users', 'match' => [['path' => '/admin/users', 'type' => 'prefix']], 'children' => self::tabs('users', $role), 'sort_order' => 20],
            ['key' => 'services', 'label' => 'Services', 'icon' => 'bi-grid', 'href' => '/admin/services', 'match' => [['path' => '/admin/services', 'type' => 'prefix']], 'children' => self::tabs('services', $role), 'sort_order' => 30],
            ['key' => 'marketplace', 'label' => 'Marketplace', 'icon' => 'bi-bag', 'href' => '/admin/marketplace', 'match' => [['path' => '/admin/marketplace', 'type' => 'prefix']], 'children' => self::tabs('marketplace', $role), 'sort_order' => 40],
            ['key' => 'billing', 'label' => 'Billing', 'icon' => 'bi-credit-card', 'href' => '/admin/billing/invoices', 'match' => [['path' => '/admin/billing', 'type' => 'prefix'], ['path' => '/admin/payments', 'type' => 'prefix'], ['path' => '/admin/invoice', 'type' => 'prefix']], 'children' => self::tabs('billing', $role), 'sort_order' => 50],
            ['key' => 'projects', 'label' => 'Projects', 'icon' => 'bi-kanban', 'href' => '/admin/projects', 'match' => [['path' => '/admin/projects', 'type' => 'prefix'], ['path' => '/admin/proposal', 'type' => 'prefix']], 'children' => self::tabs('projects', $role), 'sort_order' => 60],
            ['key' => 'support', 'label' => 'Support', 'icon' => 'bi-life-preserver', 'href' => '/admin/support/tickets', 'match' => [['path' => '/admin/support', 'type' => 'prefix'], ['path' => '/admin/tickets', 'type' => 'prefix']], 'children' => self::tabs('support', $role), 'sort_order' => 70],
            ['key' => 'marketing', 'label' => 'Marketing', 'icon' => 'bi-megaphone', 'href' => '/admin/marketing/campaigns', 'match' => [['path' => '/admin/marketing', 'type' => 'prefix']], 'children' => self::tabs('marketing', $role), 'sort_order' => 80],
            ['key' => 'modules', 'label' => 'Modules', 'icon' => 'bi-plugin', 'href' => '/admin/modules', 'match' => [['path' => '/admin/modules', 'type' => 'prefix']], 'children' => self::tabs('modules', $role), 'sort_order' => 90],
            ['key' => 'content', 'label' => 'Content', 'icon' => 'bi-collection', 'href' => '/admin/content/pages', 'match' => [['path' => '/admin/content', 'type' => 'prefix']], 'children' => self::tabs('content', $role), 'sort_order' => 110],
            ['key' => 'settings', 'label' => 'Settings', 'icon' => 'bi-sliders', 'href' => '/admin/settings/general', 'match' => [['path' => '/admin/settings', 'type' => 'prefix'], ['path' => '/admin/appearance', 'type' => 'prefix'], ['path' => '/admin/documentation', 'type' => 'prefix'], ['path' => '/admin/package', 'type' => 'prefix'], ['path' => '/admin/cms', 'type' => 'prefix']], 'children' => self::tabs('settings', $role), 'sort_order' => 120],
        ];

        foreach (self::moduleNavigationItems($role) as $item) {
            $parentKey = trim((string) ($item['parent_key'] ?? ''));
            if ($parentKey !== '') {
                foreach ($items as &$menuItem) {
                    if (($menuItem['key'] ?? '') !== $parentKey) {
                        continue;
                    }

                    $menuItem['children'] = $menuItem['children'] ?? [];
                    $menuItem['children'][] = $item;
                    continue 2;
                }
                unset($menuItem);
            }

            $items[] = $item;
        }

        $items = array_values(array_filter($items, static function (array $item) use ($role): bool {
            return self::roleAllowed($item['roles'] ?? $item['permission'] ?? ['admin'], $role);
        }));

        usort($items, static fn (array $left, array $right): int => ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999)));

        foreach ($items as &$item) {
            $item['module_source'] = (string) ($item['module_source'] ?? 'core');
            $item['badge'] = trim((string) ($item['badge'] ?? '')) ?: null;
            $children = array_values(array_filter($item['children'] ?? [], static function (array $child) use ($role): bool {
                return self::roleAllowed($child['roles'] ?? $child['permission'] ?? ['admin'], $role)
                    && self::routeAvailable((string) ($child['href'] ?? ''));
            }));

            if ($children !== []) {
                usort($children, static fn (array $left, array $right): int => ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999)));
                $item['children'] = $children;
                if (! self::routeAvailable((string) ($item['href'] ?? ''))) {
                    $item['href'] = (string) ($children[0]['href'] ?? $item['href'] ?? '');
                }
            } else {
                unset($item['children']);
            }
        }
        unset($item);

        $items = array_values(array_filter($items, static fn (array $item): bool => self::routeAvailable((string) ($item['href'] ?? ''))));

        return [['label' => 'Admin', 'items' => $items]];
    }

    public static function tabs(string $section, string $role = 'admin'): array
    {
        $tabs = match ($section) {
            'users' => [
                ['key' => 'users.index', 'label' => 'All Clients', 'href' => '/admin/users', 'match' => [['path' => '/admin/users', 'type' => 'exact'], ['path' => '/admin/users/edit', 'type' => 'prefix']], 'sort_order' => 10],
                ['key' => 'users.create', 'label' => 'Create Client', 'href' => '/admin/users/create', 'match' => [['path' => '/admin/users/create', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'users.roles', 'label' => 'Roles & Permissions', 'href' => '/admin/users/roles', 'match' => [['path' => '/admin/users/roles', 'type' => 'exact']], 'sort_order' => 30],
                ['key' => 'users.activity', 'label' => 'Activity Logs', 'href' => '/admin/users/activity', 'match' => [['path' => '/admin/users/activity', 'type' => 'exact']], 'sort_order' => 40],
            ],
            'services' => [
                ['key' => 'services.index', 'label' => 'All Services', 'href' => '/admin/services', 'match' => [['path' => '/admin/services', 'type' => 'exact'], ['path' => '/admin/services/edit', 'type' => 'prefix']], 'sort_order' => 10],
                ['key' => 'services.create', 'label' => 'Add Service', 'href' => '/admin/services/create', 'match' => [['path' => '/admin/services/create', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'services.categories', 'label' => 'Categories', 'href' => '/admin/services/categories', 'match' => [['path' => '/admin/services/categories', 'type' => 'exact']], 'sort_order' => 30],
                ['key' => 'services.plans', 'label' => 'Pricing Plans', 'href' => '/admin/services/plans', 'match' => [['path' => '/admin/services/plans', 'type' => 'exact']], 'sort_order' => 40],
            ],
            'marketplace' => [
                ['key' => 'marketplace.products', 'label' => 'Products', 'href' => '/admin/marketplace', 'match' => [['path' => '/admin/marketplace', 'type' => 'exact'], ['path' => '/admin/marketplace/edit', 'type' => 'prefix']], 'sort_order' => 10],
                ['key' => 'marketplace.create', 'label' => 'Add Product', 'href' => '/admin/marketplace/create', 'match' => [['path' => '/admin/marketplace/create', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'marketplace.vendors', 'label' => 'Vendors', 'href' => '/admin/marketplace/vendors', 'match' => [['path' => '/admin/marketplace/vendors', 'type' => 'exact']], 'sort_order' => 30],
                ['key' => 'marketplace.orders', 'label' => 'Orders', 'href' => '/admin/marketplace/orders', 'match' => [['path' => '/admin/marketplace/orders', 'type' => 'exact']], 'sort_order' => 40],
                ['key' => 'marketplace.reviews', 'label' => 'Reviews', 'href' => '/admin/marketplace/reviews', 'match' => [['path' => '/admin/marketplace/reviews', 'type' => 'exact']], 'sort_order' => 50],
            ],
            'billing' => [
                ['key' => 'billing.invoices', 'label' => 'Invoices', 'href' => '/admin/billing/invoices', 'match' => [['path' => '/admin/billing/invoices', 'type' => 'exact'], ['path' => '/admin/billing/invoices/create', 'type' => 'exact'], ['path' => '/admin/invoice/edit', 'type' => 'prefix']], 'sort_order' => 10],
                ['key' => 'billing.payments', 'label' => 'Payments', 'href' => '/admin/billing/payments', 'match' => [['path' => '/admin/billing/payments', 'type' => 'exact'], ['path' => '/admin/payments', 'type' => 'prefix']], 'sort_order' => 20],
                ['key' => 'billing.transactions', 'label' => 'Transactions', 'href' => '/admin/billing/transactions', 'match' => [['path' => '/admin/billing/transactions', 'type' => 'exact']], 'sort_order' => 30],
                ['key' => 'billing.subscriptions', 'label' => 'Subscriptions', 'href' => '/admin/billing/subscriptions', 'match' => [['path' => '/admin/billing/subscriptions', 'type' => 'exact']], 'sort_order' => 40],
            ],
            'projects' => [
                ['key' => 'projects.index', 'label' => 'All Projects', 'href' => '/admin/projects', 'match' => [['path' => '/admin/projects', 'type' => 'exact']], 'sort_order' => 10],
                ['key' => 'projects.tasks', 'label' => 'Tasks', 'href' => '/admin/projects/tasks', 'match' => [['path' => '/admin/projects/tasks', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'projects.proposals', 'label' => 'Proposals', 'href' => '/admin/projects/proposals', 'match' => [['path' => '/admin/projects/proposals', 'type' => 'exact'], ['path' => '/admin/proposal/edit', 'type' => 'prefix']], 'sort_order' => 30],
                ['key' => 'projects.contracts', 'label' => 'Contracts', 'href' => '/admin/projects/contracts', 'match' => [['path' => '/admin/projects/contracts', 'type' => 'exact']], 'sort_order' => 40],
                ['key' => 'projects.create', 'label' => 'Create Project', 'href' => '/admin/projects/create', 'match' => [['path' => '/admin/projects/create', 'type' => 'exact']], 'sort_order' => 50],
            ],
            'support' => [
                ['key' => 'support.tickets', 'label' => 'Tickets', 'href' => '/admin/support/tickets', 'match' => [['path' => '/admin/support/tickets', 'type' => 'exact'], ['path' => '/admin/tickets', 'type' => 'prefix']], 'sort_order' => 10],
                ['key' => 'support.knowledgebase', 'label' => 'Knowledgebase', 'href' => '/admin/support/knowledgebase', 'match' => [['path' => '/admin/support/knowledgebase', 'type' => 'exact']], 'sort_order' => 20],
            ],
            'marketing' => [
                ['key' => 'marketing.campaigns', 'label' => 'Campaigns', 'href' => '/admin/marketing/campaigns', 'match' => [['path' => '/admin/marketing/campaigns', 'type' => 'exact'], ['path' => '/admin/marketing', 'type' => 'exact']], 'sort_order' => 10],
                ['key' => 'marketing.coupons', 'label' => 'Coupons', 'href' => '/admin/marketing/coupons', 'match' => [['path' => '/admin/marketing/coupons', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'marketing.referrals', 'label' => 'Referrals', 'href' => '/admin/marketing/referrals', 'match' => [['path' => '/admin/marketing/referrals', 'type' => 'exact']], 'sort_order' => 30],
            ],
            'modules' => [
                ['key' => 'modules.installed', 'label' => 'Installed Modules', 'href' => '/admin/modules', 'match' => [['path' => '/admin/modules', 'type' => 'exact']], 'sort_order' => 10],
                ['key' => 'modules.upload', 'label' => 'Upload Module', 'href' => '/admin/modules/upload', 'match' => [['path' => '/admin/modules/upload', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'modules.hooks', 'label' => 'Hooks Explorer', 'href' => '/admin/modules/hooks', 'match' => [['path' => '/admin/modules/hooks', 'type' => 'exact']], 'sort_order' => 30],
            ],
            'content' => [
                ['key' => 'content.pages', 'label' => 'Pages', 'href' => '/admin/content/pages', 'match' => [['path' => '/admin/content/pages', 'type' => 'exact'], ['path' => '/admin/content', 'type' => 'exact']], 'sort_order' => 10],
                ['key' => 'content.blog', 'label' => 'Blog', 'href' => '/admin/content/blog', 'match' => [['path' => '/admin/content/blog', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'content.faq', 'label' => 'FAQ', 'href' => '/admin/content/faq', 'match' => [['path' => '/admin/content/faq', 'type' => 'exact']], 'sort_order' => 30],
                ['key' => 'content.careers', 'label' => 'Careers', 'href' => '/admin/content/careers', 'match' => [['path' => '/admin/content/careers', 'type' => 'exact']], 'sort_order' => 40],
                ['key' => 'content.portfolio', 'label' => 'Portfolio', 'href' => '/admin/content/portfolio', 'match' => [['path' => '/admin/content/portfolio', 'type' => 'exact']], 'sort_order' => 50],
            ],
            'settings' => [
                ['key' => 'settings.general', 'label' => 'General', 'href' => '/admin/settings/general', 'match' => [['path' => '/admin/settings/general', 'type' => 'exact'], ['path' => '/admin/settings', 'type' => 'exact']], 'sort_order' => 10],
                ['key' => 'settings.themes', 'label' => 'Themes', 'href' => '/admin/appearance/themes', 'match' => [['path' => '/admin/appearance/themes', 'type' => 'exact']], 'sort_order' => 20],
                ['key' => 'settings.branding', 'label' => 'Branding', 'href' => '/admin/appearance/customize', 'match' => [['path' => '/admin/appearance/customize', 'type' => 'exact'], ['path' => '/admin/cms', 'type' => 'prefix']], 'sort_order' => 30],
                ['key' => 'settings.menus', 'label' => 'Menus', 'href' => '/admin/appearance/menus', 'match' => [['path' => '/admin/appearance/menus', 'type' => 'exact']], 'sort_order' => 40],
                ['key' => 'settings.seo', 'label' => 'SEO', 'href' => '/admin/settings/seo', 'match' => [['path' => '/admin/settings/seo', 'type' => 'exact']], 'sort_order' => 50],
                ['key' => 'settings.smtp', 'label' => 'SMTP', 'href' => '/admin/settings/smtp', 'match' => [['path' => '/admin/settings/smtp', 'type' => 'exact']], 'sort_order' => 60],
                ['key' => 'settings.api', 'label' => 'API', 'href' => '/admin/settings/api', 'match' => [['path' => '/admin/settings/api', 'type' => 'exact']], 'sort_order' => 70],
                ['key' => 'settings.documentation', 'label' => 'Documentation', 'href' => '/admin/documentation', 'match' => [['path' => '/admin/documentation', 'type' => 'exact']], 'sort_order' => 80],
                ['key' => 'settings.package', 'label' => 'Package Center', 'href' => '/admin/package', 'match' => [['path' => '/admin/package', 'type' => 'exact']], 'sort_order' => 90],
            ],
            default => self::moduleTabs($section, $role),
        };

        return array_values(array_filter($tabs, static function (array $tab) use ($role): bool {
            return self::roleAllowed($tab['roles'] ?? ['admin'], $role)
                && self::routeAvailable((string) ($tab['href'] ?? ''));
        }));
    }

    public static function quickCreateLinks(string $role = 'admin'): array
    {
        $links = [
            ['label' => 'Service', 'href' => '/admin/services/create', 'icon' => 'bi-grid'],
            ['label' => 'Product', 'href' => '/admin/marketplace/create', 'icon' => 'bi-bag-plus'],
            ['label' => 'Client', 'href' => '/admin/users/create', 'icon' => 'bi-person-plus'],
            ['label' => 'Invoice', 'href' => '/admin/billing/invoices/create', 'icon' => 'bi-receipt'],
            ['label' => 'Project', 'href' => '/admin/projects/create', 'icon' => 'bi-kanban'],
            ['label' => 'Coupon', 'href' => '/admin/marketing/coupons', 'icon' => 'bi-ticket-perforated'],
            ['label' => 'Blog Post', 'href' => '/admin/content/blog', 'icon' => 'bi-journal-richtext'],
        ];

        foreach (self::moduleQuickCreateLinks($role) as $item) {
            $links[] = $item;
        }

        usort($links, static function (array $left, array $right): int {
            $sort = ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999));
            if ($sort !== 0) {
                return $sort;
            }

            return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
        });

        return array_values(array_filter($links, static function (array $item) use ($role): bool {
            return self::roleAllowed($item['roles'] ?? ['admin'], $role)
                && self::routeAvailable((string) ($item['href'] ?? ''));
        }));
    }

    public static function roleMatrix(): array
    {
        return [
            'admin' => [
                'Dashboard and KPIs',
                'User management',
                'Services, products, billing, projects, support',
                'Modules, appearance, content, settings',
            ],
            'developer' => [
                'Assigned projects',
                'Support queue',
                'Delivery updates',
            ],
            'customer' => [
                'Orders, invoices, payments',
                'Tickets, files, proposals, contracts',
                'Marketplace and portal notifications',
            ],
        ];
    }

    private static function moduleNavigationItems(string $role): array
    {
        try {
            return modules()->adminNavigationItems($role);
        } catch (\Throwable) {
            return [];
        }
    }

    private static function moduleQuickCreateLinks(string $role): array
    {
        try {
            return modules()->quickCreateLinks($role);
        } catch (\Throwable) {
            return [];
        }
    }

    private static function moduleTabs(string $section, string $role): array
    {
        foreach (self::moduleNavigationItems($role) as $item) {
            if (($item['section'] ?? '') === $section) {
                return $item['children'] ?? [];
            }
        }

        return [];
    }

    private static function pushItemIntoGroup(array &$groups, string $label, array $item): void
    {
        foreach ($groups as &$group) {
            if (strcasecmp((string) ($group['label'] ?? ''), $label) === 0) {
                $group['items'][] = $item;
                return;
            }
        }
        unset($group);

        $groups[] = [
            'label' => $label,
            'items' => [$item],
        ];
    }

    private static function roleAllowed(array $roles, string $role): bool
    {
        if ($roles === []) {
            return true;
        }

        return in_array(strtolower($role), array_map(static fn (string $value): string => strtolower($value), $roles), true);
    }

    public static function pathIsActive(array $item, string $currentPath): bool
    {
        $matchers = $item['match'] ?? [$item['href'] ?? null];

        foreach (is_array($matchers) ? $matchers : [$matchers] as $matcher) {
            if (self::matcherMatches($matcher, $currentPath)) {
                return true;
            }
        }

        foreach ($item['children'] ?? [] as $child) {
            if (self::pathIsActive($child, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    public static function routeAvailable(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || ! str_starts_with($href, '/')) {
            return false;
        }

        if (str_contains($href, '{')) {
            return true;
        }

        $app = app();
        if (! $app instanceof \App\Core\App) {
            return true;
        }

        return $app->router->has('GET', $href);
    }

    private static function matcherMatches(mixed $matcher, string $currentPath): bool
    {
        $currentPath = self::normalizePath($currentPath);

        if (is_string($matcher)) {
            $path = self::normalizePath($matcher);
            return $currentPath === $path || str_starts_with($currentPath, $path . '/');
        }

        if (! is_array($matcher)) {
            return false;
        }

        $path = self::normalizePath((string) ($matcher['path'] ?? $matcher['href'] ?? ''));
        if ($path === '') {
            return false;
        }

        $type = strtolower((string) ($matcher['type'] ?? 'exact'));

        return match ($type) {
            'prefix', 'nested' => $currentPath === $path || str_starts_with($currentPath, $path . '/'),
            default => $currentPath === $path,
        };
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        return $path === '/' ? $path : rtrim($path, '/');
    }
}
