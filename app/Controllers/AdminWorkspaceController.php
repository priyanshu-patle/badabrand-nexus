<?php

namespace App\Controllers;

use App\Core\ActivityLog;
use App\Core\AdminNavigation;
use App\Core\Auth;
use App\Core\Content;
use App\Core\Database;
use App\Core\View;

class AdminWorkspaceController
{
    private function render(string $view, string $title, string $section, string $tab, array $data = []): void
    {
        Auth::requireRole(['admin']);

        View::render($view, [
            'settings' => Content::settings(),
            'pageTitle' => $title,
            'metaDescription' => $title,
            'currentUser' => Auth::user(),
            'adminSection' => $section,
            'adminTab' => $tab,
        ] + $data, 'layouts/dashboard');
    }

    private function renderPlaceholder(string $title, string $section, string $tab, string $summary, array $bullets = [], array $actions = []): void
    {
        $this->render('admin/workspace-placeholder', $title, $section, $tab, [
            'summary' => $summary,
            'bullets' => $bullets,
            'actions' => $actions,
        ]);
    }

    private function findRecord(string $table, int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM ' . $table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    private function documentationFiles(): array
    {
        $paths = glob(base_path('documentation/*.md')) ?: [];
        sort($paths);

        return array_values(array_filter(array_map(function (string $path): ?array {
            $content = file_get_contents($path);
            if ($content === false) {
                return null;
            }

            $slug = basename($path, '.md');
            preg_match('/^#\s+(.+)$/m', $content, $headingMatch);
            $title = $headingMatch[1] ?? ucwords(str_replace(['-', '_'], ' ', $slug));

            return [
                'slug' => $slug,
                'title' => $title,
                'content' => $content,
            ];
        }, $paths)));
    }

    private function builtInPublicMenus(): array
    {
        return [
            [
                'label' => 'Primary Navigation',
                'items' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'About', 'href' => '/about'],
                    ['label' => 'Services', 'href' => '/services'],
                    ['label' => 'Marketplace', 'href' => '/marketplace'],
                    ['label' => 'Pricing', 'href' => '/pricing'],
                    ['label' => 'Portfolio', 'href' => '/portfolio'],
                    ['label' => 'Blog', 'href' => '/blog'],
                    ['label' => 'Contact', 'href' => '/contact'],
                ],
            ],
            [
                'label' => 'Footer / Utility Links',
                'items' => [
                    ['label' => 'FAQ', 'href' => '/faq'],
                    ['label' => 'Careers', 'href' => '/careers'],
                    ['label' => 'Privacy Policy', 'href' => '/privacy'],
                    ['label' => 'Terms & Conditions', 'href' => '/terms'],
                ],
            ],
        ];
    }

    private function customMenuLinks(): array
    {
        $raw = trim((string) Content::setting('menu_custom_links', '[]'));
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $links = [];
        foreach ($decoded as $link) {
            if (! is_array($link)) {
                continue;
            }

            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }

            $links[] = [
                'id' => trim((string) ($link['id'] ?? uniqid('menu_', true))),
                'scope' => in_array(($link['scope'] ?? 'public'), ['public', 'admin'], true) ? (string) $link['scope'] : 'public',
                'group' => trim((string) ($link['group'] ?? 'Custom Links')) ?: 'Custom Links',
                'label' => $label,
                'url' => $url,
                'target' => ($link['target'] ?? '_self') === '_blank' ? '_blank' : '_self',
                'visibility' => in_array(($link['visibility'] ?? 'all'), ['all', 'admin', 'customer', 'vendor'], true) ? (string) $link['visibility'] : 'all',
                'sort_order' => (int) ($link['sort_order'] ?? 100),
                'notes' => trim((string) ($link['notes'] ?? '')),
            ];
        }

        usort($links, static function (array $left, array $right): int {
            $scope = strcmp((string) ($left['scope'] ?? ''), (string) ($right['scope'] ?? ''));
            if ($scope !== 0) {
                return $scope;
            }

            $group = strcmp((string) ($left['group'] ?? ''), (string) ($right['group'] ?? ''));
            if ($group !== 0) {
                return $group;
            }

            return ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999));
        });

        return $links;
    }

    private function saveSettingValue(string $key, string $value): void
    {
        Database::connection()->prepare('
            REPLACE INTO settings (setting_key, setting_value, created_at, updated_at)
            VALUES (:key, :value, NOW(), NOW())
        ')->execute([
            'key' => $key,
            'value' => $value,
        ]);
    }

    private function storeCustomMenuLinks(array $links): void
    {
        $this->saveSettingValue('menu_custom_links', json_encode(array_values($links), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function usersIndex(): void
    {
        $this->render('admin/workspace-users-index', 'All Users', 'users', 'All Users', [
            'users' => Content::allUsers(),
        ]);
    }

    public function usersCreate(): void
    {
        $this->render('admin/workspace-user-form', 'Create User', 'users', 'Create User', [
            'mode' => 'create',
            'userRecord' => null,
        ]);
    }

    public function usersEdit(): void
    {
        $user = Content::userById((int) input('id', 0));
        if (! $user) {
            flash('error', 'User not found.');
            redirect('/admin/users');
        }

        $this->render('admin/workspace-user-form', 'Edit User', 'users', 'All Users', [
            'mode' => 'edit',
            'userRecord' => $user,
        ]);
    }

    public function usersRoles(): void
    {
        $db = Database::connection();
        $roleStats = $db->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role ORDER BY role')->fetchAll();

        $this->render('admin/workspace-user-roles', 'Roles & Permissions', 'users', 'Roles & Permissions', [
            'roleMatrix' => AdminNavigation::roleMatrix(),
            'roleStats' => $roleStats,
        ]);
    }

    public function usersActivity(): void
    {
        $this->render('admin/workspace-activity', 'Activity Logs', 'users', 'Activity Logs', [
            'activityLogs' => ActivityLog::latest(100),
        ]);
    }

    public function servicesIndex(): void
    {
        $this->render('admin/workspace-services-index', 'All Services', 'services', 'All Services', [
            'services' => Content::services(),
        ]);
    }

    public function servicesCreate(): void
    {
        $this->render('admin/workspace-service-form', 'Add Service', 'services', 'Add Service', [
            'mode' => 'create',
            'service' => null,
        ]);
    }

    public function servicesEdit(): void
    {
        $service = $this->findRecord('services', (int) input('id', 0));
        if (! $service) {
            flash('error', 'Service not found.');
            redirect('/admin/services');
        }

        $this->render('admin/workspace-service-form', 'Edit Service', 'services', 'All Services', [
            'mode' => 'edit',
            'service' => $service,
        ]);
    }

    public function servicesCategories(): void
    {
        $this->renderPlaceholder(
            'Service Categories',
            'services',
            'Categories',
            'Categories are scaffolded as a dedicated admin destination so the catalog can grow into a richer taxonomy without changing current service records.',
            [
                'Create nested service taxonomies in a future extension table.',
                'Map services to categories through module-safe relationship tables.',
                'Expose category filters later on public service pages and search.',
            ],
            [
                ['label' => 'Create Service', 'href' => '/admin/services/create'],
                ['label' => 'View Services', 'href' => '/admin/services'],
            ]
        );
    }

    public function servicesPlans(): void
    {
        $this->render('admin/workspace-service-plans', 'Pricing Plans', 'services', 'Pricing Plans', [
            'plans' => Content::plans(),
        ]);
    }

    public function marketplaceProducts(): void
    {
        $this->render('admin/workspace-marketplace-products', 'Marketplace Products', 'marketplace', 'Products', [
            'products' => Content::products(),
            'vendors' => Content::vendors(),
        ]);
    }

    public function marketplaceCreate(): void
    {
        $this->render('admin/workspace-product-form', 'Add Product', 'marketplace', 'Add Product', [
            'mode' => 'create',
            'product' => null,
            'vendors' => Content::vendors('approved'),
        ]);
    }

    public function marketplaceEdit(): void
    {
        $product = Content::product((int) input('id', 0));
        if (! $product) {
            flash('error', 'Product not found.');
            redirect('/admin/marketplace');
        }

        $this->render('admin/workspace-product-form', 'Edit Product', 'marketplace', 'Products', [
            'mode' => 'edit',
            'product' => $product,
            'vendors' => Content::vendors(),
        ]);
    }

    public function marketplaceVendors(): void
    {
        $this->render('admin/workspace-marketplace-vendors', 'Vendors', 'marketplace', 'Vendors', [
            'vendors' => Content::vendors(),
            'vendorSummary' => Content::vendorProgramSummary(),
        ]);
    }

    public function marketplaceVendorDetail(): void
    {
        $vendor = Content::vendorById((int) input('id', 0));
        if (! $vendor) {
            flash('error', 'Vendor not found.');
            redirect('/admin/marketplace/vendors');
        }

        $this->render('admin/workspace-marketplace-vendor-detail', 'Vendor Details', 'marketplace', 'Vendors', [
            'vendor' => $vendor,
            'vendorSummary' => Content::vendorSummary((int) $vendor['id']),
            'products' => Content::vendorProducts((int) $vendor['id']),
            'orders' => Content::vendorOrders((int) $vendor['id']),
            'commissions' => Content::vendorCommissions((int) $vendor['id']),
            'payouts' => Content::vendorPayouts((int) $vendor['id']),
            'documents' => Content::vendorDocuments((int) $vendor['id']),
            'reviews' => Content::vendorReviews((int) $vendor['id']),
        ]);
    }

    public function marketplaceOrders(): void
    {
        $orders = array_values(array_filter(Content::allOrders(), static function (array $order): bool {
            return ! empty($order['product_id']) || ($order['order_type'] ?? '') === 'product';
        }));

        $this->render('admin/workspace-marketplace-orders', 'Marketplace Orders', 'marketplace', 'Orders', [
            'orders' => $orders,
        ]);
    }

    public function marketplaceReviews(): void
    {
        $this->render('admin/workspace-marketplace-reviews', 'Reviews', 'marketplace', 'Reviews', [
            'reviews' => Content::productReviews(),
        ]);
    }

    public function marketplacePayouts(): void
    {
        $this->render('admin/workspace-marketplace-payouts', 'Vendor Payouts', 'marketplace', 'Vendors', [
            'vendors' => Content::vendors(),
        ]);
    }

    public function billingInvoices(): void
    {
        $invoices = Database::connection()->query('SELECT invoices.*, users.first_name, users.last_name FROM invoices JOIN users ON users.id = invoices.user_id ORDER BY invoices.created_at DESC')->fetchAll();

        $this->render('admin/workspace-billing-invoices', 'Invoices', 'billing', 'Invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function billingCreateInvoice(): void
    {
        $clients = Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name, last_name")->fetchAll();

        $this->render('admin/workspace-invoice-form', 'Create Invoice', 'billing', 'Invoices', [
            'clients' => $clients,
        ]);
    }

    public function billingPayments(): void
    {
        $this->render('admin/workspace-billing-payments', 'Payments', 'billing', 'Payments', [
            'payments' => Content::allPayments(),
        ]);
    }

    public function billingTransactions(): void
    {
        $payments = Content::allPayments();
        $this->render('admin/workspace-billing-transactions', 'Transactions', 'billing', 'Transactions', [
            'payments' => $payments,
        ]);
    }

    public function billingSubscriptions(): void
    {
        $this->renderPlaceholder(
            'Subscriptions',
            'billing',
            'Subscriptions',
            'Recurring billing is now separated as a first-class billing destination, ready for a future subscription module with plans, renewals, and dunning.'
        );
    }

    public function projectsIndex(): void
    {
        $this->render('admin/workspace-projects-index', 'All Projects', 'projects', 'All Projects', [
            'orders' => Content::allOrders(),
        ]);
    }

    public function projectsCreate(): void
    {
        $clients = Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name, last_name")->fetchAll();

        $this->render('admin/workspace-project-form', 'Create Project', 'projects', 'Create Project', [
            'clients' => $clients,
        ]);
    }

    public function storeProject(): void
    {
        Auth::requireRole(['admin']);
        $userId = (int) input('user_id', 0);
        $title = trim((string) input('title', ''));

        if ($userId <= 0 || $title === '') {
            flash('error', 'Client and project title are required.');
            redirect('/admin/projects/create');
        }

        Database::connection()->prepare('
            INSERT INTO orders (user_id, service_id, product_id, order_type, item_name, pricing_plan_name, notes, receipt_number, expected_delivery, order_number, status, total, progress_percent, due_at, created_at, updated_at)
            VALUES (:user_id, NULL, NULL, :order_type, :item_name, NULL, :notes, :receipt_number, :expected_delivery, :order_number, :status, :total, :progress_percent, :due_at, NOW(), NOW())
        ')->execute([
            'user_id' => $userId,
            'order_type' => 'admin_project',
            'item_name' => $title,
            'notes' => trim((string) input('notes', '')),
            'receipt_number' => generate_reference('RCT'),
            'expected_delivery' => input('expected_delivery', date('Y-m-d', strtotime('+14 days'))),
            'order_number' => generate_reference('ORD'),
            'status' => input('status', 'approved'),
            'total' => (float) input('total', 0),
            'progress_percent' => (int) input('progress_percent', 0),
            'due_at' => input('due_at', date('Y-m-d H:i:s', strtotime('+14 days'))),
        ]);

        $orderId = (int) Database::connection()->lastInsertId();
        ActivityLog::record('project.created', 'Admin created project: ' . $title, [
            'order_id' => $orderId,
            'user_id' => $userId,
            'title' => $title,
        ], 'order', $orderId, Auth::user()['id'] ?? null, Auth::user()['email'] ?? null);

        flash('success', 'Project created successfully.');
        redirect('/admin/projects');
    }

    public function projectsTasks(): void
    {
        $updates = Database::connection()->query('SELECT project_updates.*, orders.order_number, users.first_name, users.last_name FROM project_updates JOIN orders ON orders.id = project_updates.order_id JOIN users ON users.id = project_updates.user_id ORDER BY project_updates.created_at DESC')->fetchAll();
        $clients = Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name, last_name")->fetchAll();

        $this->render('admin/workspace-project-tasks', 'Tasks & Updates', 'projects', 'Tasks', [
            'updates' => $updates,
            'orders' => Content::allOrders(),
            'clients' => $clients,
        ]);
    }

    public function projectsProposals(): void
    {
        $proposals = Database::connection()->query('SELECT proposals.*, users.first_name, users.last_name FROM proposals JOIN users ON users.id = proposals.user_id ORDER BY proposals.created_at DESC')->fetchAll();
        $clients = Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name, last_name")->fetchAll();

        $this->render('admin/workspace-project-proposals', 'Proposals', 'projects', 'Proposals', [
            'proposals' => $proposals,
            'clients' => $clients,
        ]);
    }

    public function projectsContracts(): void
    {
        $contracts = Database::connection()->query('SELECT contracts.*, users.first_name, users.last_name FROM contracts JOIN users ON users.id = contracts.user_id ORDER BY contracts.created_at DESC')->fetchAll();
        $clients = Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name, last_name")->fetchAll();

        $this->render('admin/workspace-project-contracts', 'Contracts', 'projects', 'Contracts', [
            'contracts' => $contracts,
            'clients' => $clients,
        ]);
    }

    public function supportTickets(): void
    {
        $ticketId = (int) input('ticket', 0);
        $this->render('admin/workspace-support-tickets', 'Tickets', 'support', 'Tickets', [
            'tickets' => Content::allTickets(),
            'messages' => $ticketId > 0 ? Content::ticketMessages($ticketId) : [],
            'activeTicketId' => $ticketId,
        ]);
    }

    public function supportKnowledgebase(): void
    {
        $docs = $this->documentationFiles();
        $requested = trim((string) input('doc', $docs[0]['slug'] ?? ''));
        $current = $docs[0] ?? ['title' => 'Knowledgebase', 'content' => "# Knowledgebase\n\nNo documentation files found."];

        foreach ($docs as $doc) {
            if ($doc['slug'] === $requested) {
                $current = $doc;
                break;
            }
        }

        $this->render('admin/workspace-knowledgebase', 'Knowledgebase', 'support', 'Knowledgebase', [
            'docs' => $docs,
            'currentDoc' => $current,
            'currentDocHtml' => simple_markdown_to_html($current['content']),
        ]);
    }

    public function marketingCampaigns(): void
    {
        $emailLogs = Database::connection()->query('SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 20')->fetchAll();

        $this->render('admin/workspace-marketing-campaigns', 'Campaigns', 'marketing', 'Campaigns', [
            'emailLogs' => $emailLogs,
        ]);
    }

    public function marketingCoupons(): void
    {
        $this->render('admin/workspace-marketing-coupons', 'Coupons', 'marketing', 'Coupons', [
            'coupons' => Content::allCoupons(),
        ]);
    }

    public function marketingReferrals(): void
    {
        $this->render('admin/workspace-marketing-referrals', 'Referrals', 'marketing', 'Referrals', [
            'referrals' => Content::allReferrals(),
            'contacts' => Content::allContacts(),
            'referralSummary' => Content::referralSummary(),
            'systemSettings' => Content::settings(),
        ]);
    }

    public function modulesInstalled(): void
    {
        $manager = modules();
        $this->render('admin/workspace-modules-installed', 'Installed Modules', 'modules', 'Installed Modules', [
            'moduleStats' => $manager->stats(),
            'modules' => $manager->allModules(),
            'activity' => $manager->activity(),
        ]);
    }

    public function modulesUpload(): void
    {
        $manager = modules();
        $this->render('admin/workspace-modules-upload', 'Upload Module', 'modules', 'Upload Module', [
            'moduleStats' => $manager->stats(),
            'activity' => $manager->activity(),
        ]);
    }

    public function modulesHooks(): void
    {
        $this->render('admin/workspace-modules-hooks', 'Hooks Explorer', 'modules', 'Hooks Explorer', [
            'coreEvents' => [
                'onUserRegister',
                'onUserLogin',
                'onOrderCreated',
                'onPaymentSuccess',
                'onTicketCreated',
                'onPageRender',
                'onModuleInstall',
            ],
            'quickCreateLinks' => AdminNavigation::quickCreateLinks(),
        ]);
    }

    public function appearanceThemes(): void
    {
        $this->render('admin/workspace-appearance-themes', 'Themes', 'appearance', 'Themes', [
            'themePresets' => theme_presets(),
            'systemSettings' => Content::settings(),
        ]);
    }

    public function appearanceCustomize(): void
    {
        $this->render('admin/workspace-appearance-customize', 'Customize', 'appearance', 'Customize', [
            'settings' => Content::settings(),
            'slider' => Content::slider(),
            'statsItems' => Content::stats(),
            'testimonials' => Content::testimonials(),
        ]);
    }

    public function appearanceMenus(): void
    {
        $customLinks = $this->customMenuLinks();
        $this->render('admin/workspace-appearance-menus', 'Menus', 'appearance', 'Menus', [
            'publicMenuGroups' => $this->builtInPublicMenus(),
            'adminMenuGroups' => AdminNavigation::groups('admin'),
            'customMenuLinks' => $customLinks,
            'publicCustomLinks' => array_values(array_filter($customLinks, static fn (array $item): bool => $item['scope'] === 'public')),
            'adminCustomLinks' => array_values(array_filter($customLinks, static fn (array $item): bool => $item['scope'] === 'admin')),
        ]);
    }

    public function saveAppearanceMenu(): void
    {
        Auth::requireRole(['admin']);

        $label = trim((string) input('label', ''));
        $url = trim((string) input('url', ''));
        if ($label === '' || $url === '') {
            flash('error', 'Menu label and target URL are required.');
            redirect('/admin/appearance/menus');
        }

        $links = $this->customMenuLinks();
        $links[] = [
            'id' => uniqid('menu_', true),
            'scope' => in_array((string) input('scope', 'public'), ['public', 'admin'], true) ? (string) input('scope', 'public') : 'public',
            'group' => trim((string) input('group', 'Custom Links')) ?: 'Custom Links',
            'label' => $label,
            'url' => $url,
            'target' => input('target', '_self') === '_blank' ? '_blank' : '_self',
            'visibility' => in_array((string) input('visibility', 'all'), ['all', 'admin', 'customer', 'vendor'], true) ? (string) input('visibility', 'all') : 'all',
            'sort_order' => (int) input('sort_order', 100),
            'notes' => trim((string) input('notes', '')),
        ];

        $this->storeCustomMenuLinks($links);
        flash('success', 'Menu item saved.');
        redirect('/admin/appearance/menus');
    }

    public function deleteAppearanceMenu(): void
    {
        Auth::requireRole(['admin']);
        $menuId = trim((string) input('menu_id', ''));
        $links = array_values(array_filter($this->customMenuLinks(), static fn (array $item): bool => (string) ($item['id'] ?? '') !== $menuId));
        $this->storeCustomMenuLinks($links);
        flash('success', 'Menu item removed.');
        redirect('/admin/appearance/menus');
    }

    public function contentPages(): void
    {
        $this->render('admin/workspace-content-pages', 'Pages', 'content', 'Pages', [
            'pages' => Content::pages(),
        ]);
    }

    public function contentBlog(): void
    {
        $this->render('admin/workspace-content-blog', 'Blog', 'content', 'Blog', [
            'blogs' => Content::blogs(),
        ]);
    }

    public function contentFaq(): void
    {
        $this->render('admin/workspace-content-faq', 'FAQ', 'content', 'FAQ', [
            'faqItems' => Content::faqs(),
        ]);
    }

    public function contentCareers(): void
    {
        $this->render('admin/workspace-content-careers', 'Careers', 'content', 'Careers', [
            'jobs' => Content::careers(),
        ]);
    }

    public function contentPortfolio(): void
    {
        $this->render('admin/workspace-content-portfolio', 'Portfolio', 'content', 'Portfolio', [
            'portfolioProjects' => Content::portfolioProjects(),
            'teamMembers' => Content::teamMembers(),
        ]);
    }

    public function settingsGeneral(): void
    {
        $this->render('admin/workspace-settings-general', 'General Settings', 'settings', 'General', [
            'systemSettings' => Content::settings(),
        ]);
    }

    public function settingsSeo(): void
    {
        $this->render('admin/workspace-settings-seo', 'SEO Settings', 'settings', 'SEO', [
            'systemSettings' => Content::settings(),
        ]);
    }

    public function settingsSmtp(): void
    {
        $this->render('admin/workspace-settings-smtp', 'SMTP Settings', 'settings', 'SMTP', [
            'systemSettings' => Content::settings(),
        ]);
    }

    public function settingsApi(): void
    {
        $this->render('admin/workspace-settings-api', 'API Settings', 'settings', 'API', [
            'systemSettings' => Content::settings(),
        ]);
    }

    public function search(): void
    {
        $query = trim((string) input('q', ''));
        $results = [
            'Users' => [],
            'Services' => [],
            'Orders' => [],
            'Tickets' => [],
            'Projects' => [],
        ];

        if ($query !== '') {
            foreach (Content::allUsers() as $user) {
                if (stripos(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') . ' ' . ($user['email'] ?? '')), $query) !== false) {
                    $results['Users'][] = ['label' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')), 'meta' => $user['email'], 'href' => '/admin/users/edit/' . $user['id']];
                }
            }

            foreach (Content::services() as $service) {
                if (stripos(($service['name'] ?? '') . ' ' . ($service['short_description'] ?? ''), $query) !== false) {
                    $results['Services'][] = ['label' => $service['name'], 'meta' => $service['price_label'] ?? '', 'href' => '/admin/services/edit/' . $service['id']];
                }
            }

            foreach (Content::allOrders() as $order) {
                $haystack = ($order['order_number'] ?? '') . ' ' . ($order['display_name'] ?? '') . ' ' . ($order['status'] ?? '');
                if (stripos($haystack, $query) !== false) {
                    $results['Orders'][] = ['label' => $order['order_number'], 'meta' => $order['display_name'] ?? '', 'href' => '/admin/projects'];
                }
            }

            foreach (Content::allTickets() as $ticket) {
                $haystack = ($ticket['subject'] ?? '') . ' ' . ($ticket['email'] ?? '') . ' ' . ($ticket['status'] ?? '');
                if (stripos($haystack, $query) !== false) {
                    $results['Tickets'][] = ['label' => $ticket['subject'], 'meta' => $ticket['email'] ?? '', 'href' => '/admin/support/tickets?ticket=' . $ticket['id']];
                }
            }

            $updates = Database::connection()->query('SELECT project_updates.*, orders.order_number FROM project_updates JOIN orders ON orders.id = project_updates.order_id ORDER BY project_updates.created_at DESC')->fetchAll();
            foreach ($updates as $update) {
                $haystack = ($update['title'] ?? '') . ' ' . ($update['details'] ?? '') . ' ' . ($update['order_number'] ?? '');
                if (stripos($haystack, $query) !== false) {
                    $results['Projects'][] = ['label' => $update['title'], 'meta' => $update['order_number'] ?? '', 'href' => '/admin/projects/tasks'];
                }
            }
        }

        $this->render('admin/workspace-search-results', 'Global Search', 'dashboard', 'Dashboard', [
            'globalSearch' => $query,
            'searchResults' => $results,
        ]);
    }
}
