<?php

namespace App\Core;

class Content
{
    private static function dashboardRangeMeta(string $range = 'month'): array
    {
        $range = strtolower(trim($range));

        if ($range === 'all') {
            return [
                'key' => 'all',
                'label' => 'All Time',
                'start' => null,
                'previous_start' => null,
                'previous_end' => null,
            ];
        }

        $currentStart = new \DateTimeImmutable('first day of this month 00:00:00');

        return [
            'key' => 'month',
            'label' => 'This Month',
            'start' => $currentStart->format('Y-m-d H:i:s'),
            'previous_start' => $currentStart->modify('-1 month')->format('Y-m-d H:i:s'),
            'previous_end' => $currentStart->format('Y-m-d H:i:s'),
        ];
    }

    private static function dashboardDateClause(?string $start, string $column, string $param = 'start'): array
    {
        if (! $start) {
            return ['', []];
        }

        return [" WHERE {$column} >= :{$param}", [$param => $start]];
    }

    private static function dashboardClassifyOrder(array $order): string
    {
        $status = strtolower((string) ($order['status'] ?? ''));
        $progress = (int) ($order['progress_percent'] ?? 0);

        if (in_array($status, ['cancelled', 'rejected', 'on_hold', 'hold', 'paused', 'refunded'], true)) {
            return 'on_hold';
        }

        if ($progress >= 100 || in_array($status, ['completed', 'complete', 'delivered', 'done', 'closed'], true)) {
            return 'completed';
        }

        if (in_array($status, ['pending_approval', 'pending', 'new'], true) || $progress === 0) {
            return 'new';
        }

        return 'in_progress';
    }

    private static function dashboardRoleCounts(): array
    {
        $roles = [
            'admin' => 0,
            'customer' => 0,
            'developer' => 0,
            'vendor' => 0,
        ];

        foreach (Database::connection()->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role')->fetchAll() as $row) {
            $role = strtolower((string) ($row['role'] ?? ''));
            if (array_key_exists($role, $roles)) {
                $roles[$role] = (int) ($row['total'] ?? 0);
            }
        }

        return $roles;
    }

    private static function dashboardFallbackActivity(int $limit = 12): array
    {
        $db = Database::connection();
        $events = [];

        $builders = [
            [
                'sql' => 'SELECT id, CONCAT(first_name, " ", COALESCE(last_name, "")) AS label, created_at FROM users ORDER BY created_at DESC LIMIT 5',
                'type' => 'user',
                'summary' => static fn (array $row): string => trim((string) ($row['label'] ?? 'New user')) . ' registered',
                'meta' => 'User registration',
                'route' => '/admin/users',
            ],
            [
                'sql' => 'SELECT id, order_number AS label, created_at FROM orders ORDER BY created_at DESC LIMIT 5',
                'type' => 'order',
                'summary' => static fn (array $row): string => 'Order ' . (string) ($row['label'] ?? '#') . ' placed',
                'meta' => 'Marketplace or service order',
                'route' => '/admin/projects',
            ],
            [
                'sql' => 'SELECT id, subject AS label, created_at FROM tickets ORDER BY created_at DESC LIMIT 5',
                'type' => 'ticket',
                'summary' => static fn (array $row): string => 'Ticket created: ' . (string) ($row['label'] ?? 'Support request'),
                'meta' => 'Support system',
                'route' => '/admin/tickets',
            ],
            [
                'sql' => 'SELECT id, transaction_id AS label, created_at FROM payments ORDER BY created_at DESC LIMIT 5',
                'type' => 'payment',
                'summary' => static fn (array $row): string => 'Payment proof uploaded' . ((string) ($row['label'] ?? '') !== '' ? ' (' . (string) $row['label'] . ')' : ''),
                'meta' => 'Billing workflow',
                'route' => '/admin/payments',
            ],
            [
                'sql' => 'SELECT id, title AS label, created_at FROM proposals ORDER BY created_at DESC LIMIT 5',
                'type' => 'proposal',
                'summary' => static fn (array $row): string => 'Proposal updated: ' . (string) ($row['label'] ?? 'Proposal'),
                'meta' => 'Projects & contracts',
                'route' => '/admin/projects/proposals',
            ],
            [
                'sql' => 'SELECT id, title AS label, updated_at AS created_at FROM contracts ORDER BY updated_at DESC LIMIT 5',
                'type' => 'contract',
                'summary' => static fn (array $row): string => 'Contract activity: ' . (string) ($row['label'] ?? 'Contract'),
                'meta' => 'Projects & contracts',
                'route' => '/admin/projects/contracts',
            ],
        ];

        foreach ($builders as $builder) {
            try {
                foreach ($db->query($builder['sql'])->fetchAll() as $row) {
                    $events[] = [
                        'type' => $builder['type'],
                        'summary' => $builder['summary']($row),
                        'meta' => $builder['meta'],
                        'created_at' => (string) ($row['created_at'] ?? ''),
                        'route' => $builder['route'],
                    ];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        usort($events, static fn (array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return array_slice($events, 0, $limit);
    }

    public static function dashboardInsights(string $range = 'month'): array
    {
        $meta = self::dashboardRangeMeta($range);
        $db = Database::connection();

        [$invoiceWhere, $invoiceParams] = self::dashboardDateClause($meta['start'], 'created_at', 'invoice_start');
        $invoiceStmt = $db->prepare("
            SELECT
                COUNT(*) AS invoice_count,
                COALESCE(SUM(total), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN status IN ('paid','approved') THEN total ELSE 0 END), 0) AS paid_revenue,
                COALESCE(SUM(CASE WHEN status IN ('unpaid','payment_review','pending') THEN total ELSE 0 END), 0) AS pending_revenue
            FROM invoices{$invoiceWhere}
        ");
        $invoiceStmt->execute($invoiceParams);
        $revenue = $invoiceStmt->fetch() ?: [];

        $growth = null;
        if ($meta['previous_start'] && $meta['previous_end']) {
            $currentRevenueStmt = $db->prepare("
                SELECT COALESCE(SUM(total), 0)
                FROM invoices
                WHERE created_at >= :start AND status IN ('paid','approved')
            ");
            $currentRevenueStmt->execute(['start' => $meta['start']]);
            $currentRevenue = (float) $currentRevenueStmt->fetchColumn();

            $previousRevenueStmt = $db->prepare("
                SELECT COALESCE(SUM(total), 0)
                FROM invoices
                WHERE created_at >= :previous_start
                  AND created_at < :previous_end
                  AND status IN ('paid','approved')
            ");
            $previousRevenueStmt->execute([
                'previous_start' => $meta['previous_start'],
                'previous_end' => $meta['previous_end'],
            ]);
            $previousRevenue = (float) $previousRevenueStmt->fetchColumn();

            if ($previousRevenue > 0) {
                $growth = round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1);
            } elseif ($currentRevenue > 0) {
                $growth = 100.0;
            } else {
                $growth = 0.0;
            }
        }

        [$orderWhere, $orderParams] = self::dashboardDateClause($meta['start'], 'created_at', 'order_start');
        $orderStmt = $db->prepare("SELECT status, progress_percent, total FROM orders{$orderWhere}");
        $orderStmt->execute($orderParams);
        $orderRows = $orderStmt->fetchAll();
        $orderSummary = [
            'total' => count($orderRows),
            'new' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'on_hold' => 0,
        ];
        foreach ($orderRows as $order) {
            $bucket = self::dashboardClassifyOrder($order);
            $orderSummary[$bucket] = ($orderSummary[$bucket] ?? 0) + 1;
        }

        $userSummary = [
            'total' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'active' => (int) $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn(),
            'new_period' => 0,
            'new_week' => (int) $db->query('SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn(),
            'new_today' => (int) $db->query('SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()')->fetchColumn(),
            'roles' => self::dashboardRoleCounts(),
        ];
        if ($meta['start']) {
            $periodUserStmt = $db->prepare('SELECT COUNT(*) FROM users WHERE created_at >= :start');
            $periodUserStmt->execute(['start' => $meta['start']]);
            $userSummary['new_period'] = (int) $periodUserStmt->fetchColumn();
        } else {
            $userSummary['new_period'] = $userSummary['total'];
        }

        $supportSummary = [
            'open' => (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status NOT IN ('closed','resolved')")->fetchColumn(),
            'high_priority' => (int) $db->query("SELECT COUNT(*) FROM tickets WHERE priority IN ('high','critical') AND status NOT IN ('closed','resolved')")->fetchColumn(),
            'recently_updated' => (int) $db->query("SELECT COUNT(*) FROM tickets WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
            'pending_replies' => 0,
        ];
        try {
            $supportSummary['pending_replies'] = (int) $db->query("
                SELECT COUNT(*)
                FROM tickets t
                JOIN (
                    SELECT ticket_id, MAX(id) AS latest_message_id
                    FROM ticket_messages
                    GROUP BY ticket_id
                ) latest ON latest.ticket_id = t.id
                JOIN ticket_messages tm ON tm.id = latest.latest_message_id
                WHERE t.status NOT IN ('closed','resolved')
                  AND tm.sender_type <> 'admin'
            ")->fetchColumn();
        } catch (\Throwable) {
            $supportSummary['pending_replies'] = $supportSummary['open'];
        }

        [$paymentWhere, $paymentParams] = self::dashboardDateClause($meta['start'], 'created_at', 'payment_start');
        $paymentStmt = $db->prepare("
            SELECT
                COUNT(*) AS total_transactions,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status IN ('rejected','invalid','cancelled') THEN 1 ELSE 0 END) AS rejected_count
            FROM payments{$paymentWhere}
        ");
        $paymentStmt->execute($paymentParams);
        $payments = $paymentStmt->fetch() ?: [];

        [$serviceWhere, $serviceParams] = self::dashboardDateClause($meta['start'], 'orders.created_at', 'service_start');
        $serviceStmt = $db->prepare("
            SELECT services.id, services.name,
                COUNT(orders.id) AS order_count,
                COALESCE(SUM(orders.total), 0) AS revenue_total
            FROM orders
            JOIN services ON services.id = orders.service_id
            {$serviceWhere}
            GROUP BY services.id, services.name
            ORDER BY revenue_total DESC, order_count DESC, services.name ASC
            LIMIT 3
        ");
        $serviceStmt->execute($serviceParams);
        $topServices = $serviceStmt->fetchAll();

        [$productWhere, $productParams] = self::dashboardDateClause($meta['start'], 'orders.created_at', 'product_start');
        $productStmt = $db->prepare("
            SELECT products.id, products.name,
                COUNT(orders.id) AS order_count,
                COALESCE(SUM(orders.total), 0) AS revenue_total
            FROM orders
            JOIN products ON products.id = orders.product_id
            {$productWhere}
            GROUP BY products.id, products.name
            ORDER BY revenue_total DESC, order_count DESC, products.name ASC
            LIMIT 3
        ");
        $productStmt->execute($productParams);
        $topProducts = $productStmt->fetchAll();

        $alerts = [
            [
                'label' => 'Pending order approvals',
                'count' => (int) $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending_approval'")->fetchColumn(),
                'route' => '/admin/projects',
            ],
            [
                'label' => 'Pending vendor approvals',
                'count' => (int) $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'pending'")->fetchColumn(),
                'route' => '/admin/marketplace/vendors',
            ],
            [
                'label' => 'Product reviews required',
                'count' => (int) $db->query("SELECT COUNT(*) FROM products WHERE approval_status = 'pending'")->fetchColumn(),
                'route' => '/admin/marketplace',
            ],
            [
                'label' => 'Proposal and contract reviews',
                'count' => (int) $db->query("SELECT (SELECT COUNT(*) FROM proposals WHERE status IN ('submitted','sent')) + (SELECT COUNT(*) FROM contracts WHERE status NOT IN ('signed','completed'))")->fetchColumn(),
                'route' => '/admin/projects',
            ],
        ];

        $activity = array_map(static function (array $row): array {
            return [
                'type' => (string) ($row['event_type'] ?? 'system'),
                'summary' => (string) ($row['summary'] ?? 'System activity'),
                'meta' => (string) ($row['actor_label'] ?? ($row['entity_type'] ?? 'Activity')),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'route' => null,
            ];
        }, ActivityLog::latest(12));

        if ($activity === []) {
            $activity = self::dashboardFallbackActivity(12);
        }

        return [
            'range' => $meta['key'],
            'range_label' => $meta['label'],
            'revenue' => [
                'invoice_count' => (int) ($revenue['invoice_count'] ?? 0),
                'total' => (float) ($revenue['total_revenue'] ?? 0),
                'paid' => (float) ($revenue['paid_revenue'] ?? 0),
                'pending' => (float) ($revenue['pending_revenue'] ?? 0),
                'growth_percent' => $growth,
            ],
            'orders' => $orderSummary,
            'users' => $userSummary,
            'support' => $supportSummary,
            'payments' => [
                'pending' => (int) ($payments['pending_count'] ?? 0),
                'approved' => (int) ($payments['approved_count'] ?? 0),
                'rejected' => (int) ($payments['rejected_count'] ?? 0),
                'recent_transactions' => (int) ($payments['total_transactions'] ?? 0),
            ],
            'top_services' => $topServices,
            'top_products' => $topProducts,
            'alerts' => $alerts,
            'activity' => $activity,
        ];
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        $settings = self::settings();
        return $settings[$key] ?? $default;
    }

    public static function settings(): array
    {
        $rows = Database::connection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function services(): array
    {
        return Database::connection()->query('SELECT * FROM services ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function plans(): array
    {
        return Database::connection()->query('SELECT * FROM pricing_plans ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function products(bool $activeOnly = false): array
    {
        $query = '
            SELECT products.*,
                vendors.store_name AS vendor_store_name,
                vendors.display_name AS vendor_display_name,
                vendors.slug AS vendor_slug,
                vendors.status AS vendor_status,
                vendors.verification_badge,
                vendor_profiles.logo_path AS vendor_logo_path,
                COALESCE(vendors.display_name, "Badabrand Technologies") AS seller_name,
                COALESCE(review_summary.review_count, 0) AS review_count,
                COALESCE(review_summary.average_rating, 0) AS average_rating
            FROM products
            LEFT JOIN vendors ON vendors.id = products.vendor_id
            LEFT JOIN vendor_profiles ON vendor_profiles.vendor_id = vendors.id
            LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, ROUND(AVG(rating), 1) AS average_rating
                FROM product_reviews
                WHERE status = "approved"
                GROUP BY product_id
            ) AS review_summary ON review_summary.product_id = products.id
        ';
        if ($activeOnly) {
            $query .= ' WHERE products.status = "active" AND (products.vendor_id IS NULL OR (products.approval_status = "approved" AND COALESCE(vendors.status, "approved") = "approved"))';
        }

        return Database::connection()->query($query . ' ORDER BY products.sort_order ASC, products.id ASC')->fetchAll();
    }

    public static function product(int $productId): ?array
    {
        $stmt = Database::connection()->prepare('
            SELECT products.*,
                vendors.store_name AS vendor_store_name,
                vendors.display_name AS vendor_display_name,
                vendors.slug AS vendor_slug,
                vendors.status AS vendor_status,
                vendors.verification_badge,
                vendor_profiles.logo_path AS vendor_logo_path,
                vendor_profiles.banner_path AS vendor_banner_path,
                vendor_profiles.short_bio AS vendor_short_bio,
                COALESCE(vendors.display_name, "Badabrand Technologies") AS seller_name,
                COALESCE(review_summary.review_count, 0) AS review_count,
                COALESCE(review_summary.average_rating, 0) AS average_rating
            FROM products
            LEFT JOIN vendors ON vendors.id = products.vendor_id
            LEFT JOIN vendor_profiles ON vendor_profiles.vendor_id = vendors.id
            LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, ROUND(AVG(rating), 1) AS average_rating
                FROM product_reviews
                WHERE status = "approved"
                GROUP BY product_id
            ) AS review_summary ON review_summary.product_id = products.id
            WHERE products.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $productId]);
        return $stmt->fetch() ?: null;
    }

    public static function testimonials(): array
    {
        return Database::connection()->query('SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function slider(): array
    {
        return Database::connection()->query('SELECT * FROM sliders ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function stats(): array
    {
        return Database::connection()->query('SELECT * FROM stats ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function pages(): array
    {
        return Database::connection()->query('SELECT * FROM pages ORDER BY id ASC')->fetchAll();
    }

    public static function pageBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pages WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public static function blogs(bool $publishedOnly = false): array
    {
        $query = 'SELECT * FROM blogs';
        if ($publishedOnly) {
            $query .= " WHERE status = 'published'";
        }

        return Database::connection()->query($query . ' ORDER BY COALESCE(published_at, created_at) DESC, id DESC')->fetchAll();
    }

    public static function careers(bool $openOnly = false): array
    {
        $query = 'SELECT * FROM careers';
        if ($openOnly) {
            $query .= " WHERE status = 'open'";
        }

        return Database::connection()->query($query . ' ORDER BY sort_order ASC, id DESC')->fetchAll();
    }

    public static function faqs(): array
    {
        return Database::connection()->query('SELECT * FROM faqs ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function portfolioProjects(bool $featuredOnly = false): array
    {
        $query = 'SELECT * FROM portfolio_projects';
        if ($featuredOnly) {
            $query .= ' WHERE is_featured = 1';
        }

        return Database::connection()->query($query . ' ORDER BY is_featured DESC, sort_order ASC, id DESC')->fetchAll();
    }

    public static function teamMembers(): array
    {
        return Database::connection()->query('SELECT * FROM team_members ORDER BY id ASC')->fetchAll();
    }

    public static function vendors(string $status = ''): array
    {
        $query = '
            SELECT vendors.*,
                users.first_name,
                users.last_name,
                users.email AS user_email,
                users.phone AS user_phone,
                users.status AS user_status,
                vendor_profiles.short_bio,
                vendor_profiles.logo_path,
                vendor_profiles.banner_path,
                vendor_profiles.website,
                vendor_profiles.support_email,
                vendor_profiles.support_phone,
                vendor_payout_accounts.payout_method,
                vendor_payout_accounts.account_name,
                vendor_payout_accounts.account_number,
                vendor_payout_accounts.ifsc_swift,
                vendor_payout_accounts.upi_id,
                (SELECT COUNT(*) FROM products WHERE products.vendor_id = vendors.id) AS product_count,
                (SELECT COUNT(*) FROM orders WHERE orders.vendor_id = vendors.id) AS order_count,
                (SELECT COALESCE(SUM(vendor_net_amount), 0) FROM vendor_commissions WHERE vendor_commissions.vendor_id = vendors.id) AS total_earned,
                (SELECT COALESCE(SUM(vendor_net_amount), 0) FROM vendor_commissions WHERE vendor_commissions.vendor_id = vendors.id AND vendor_commissions.payout_status IN ("available", "requested", "processing")) AS pending_balance
            FROM vendors
            JOIN users ON users.id = vendors.user_id
            LEFT JOIN vendor_profiles ON vendor_profiles.vendor_id = vendors.id
            LEFT JOIN vendor_payout_accounts ON vendor_payout_accounts.vendor_id = vendors.id
        ';

        $params = [];
        if ($status !== '') {
            $query .= ' WHERE vendors.status = :status';
            $params['status'] = $status;
        }

        $query .= ' ORDER BY vendors.created_at DESC, vendors.id DESC';
        $stmt = Database::connection()->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function vendorById(int $vendorId): ?array
    {
        $stmt = Database::connection()->prepare('
            SELECT vendors.*,
                users.first_name,
                users.last_name,
                users.email AS user_email,
                users.phone AS user_phone,
                users.status AS user_status,
                vendor_profiles.business_name,
                vendor_profiles.legal_name,
                vendor_profiles.short_bio,
                vendor_profiles.address_line1,
                vendor_profiles.address_line2,
                vendor_profiles.city,
                vendor_profiles.state,
                vendor_profiles.country,
                vendor_profiles.postal_code,
                vendor_profiles.website,
                vendor_profiles.support_email,
                vendor_profiles.support_phone,
                vendor_profiles.logo_path,
                vendor_profiles.banner_path,
                vendor_payout_accounts.payout_method,
                vendor_payout_accounts.account_name,
                vendor_payout_accounts.account_number,
                vendor_payout_accounts.ifsc_swift,
                vendor_payout_accounts.upi_id,
                vendor_payout_accounts.paypal_email,
                vendor_payout_accounts.notes AS payout_notes
            FROM vendors
            JOIN users ON users.id = vendors.user_id
            LEFT JOIN vendor_profiles ON vendor_profiles.vendor_id = vendors.id
            LEFT JOIN vendor_payout_accounts ON vendor_payout_accounts.vendor_id = vendors.id
            WHERE vendors.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $vendorId]);

        return $stmt->fetch() ?: null;
    }

    public static function vendorByUserId(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id FROM vendors WHERE user_id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $vendorId = (int) ($stmt->fetchColumn() ?: 0);

        return $vendorId > 0 ? self::vendorById($vendorId) : null;
    }

    public static function publicVendorBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id FROM vendors WHERE slug = :slug AND status = "approved" LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $vendorId = (int) ($stmt->fetchColumn() ?: 0);

        return $vendorId > 0 ? self::vendorById($vendorId) : null;
    }

    public static function vendorProducts(int $vendorId, bool $includeDraft = true): array
    {
        $query = '
            SELECT products.*,
                COALESCE(review_summary.review_count, 0) AS review_count,
                COALESCE(review_summary.average_rating, 0) AS average_rating
            FROM products
            LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, ROUND(AVG(rating), 1) AS average_rating
                FROM product_reviews
                WHERE status = "approved"
                GROUP BY product_id
            ) AS review_summary ON review_summary.product_id = products.id
            WHERE products.vendor_id = :vendor
        ';
        if (! $includeDraft) {
            $query .= ' AND products.status = "active" AND products.approval_status = "approved"';
        }
        $query .= ' ORDER BY products.updated_at DESC, products.id DESC';
        $stmt = Database::connection()->prepare($query);
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function vendorOrders(int $vendorId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT orders.*, users.first_name, users.last_name, users.email, products.name AS product_name,
                invoices.id AS invoice_id, invoices.invoice_number, invoices.status AS invoice_status, invoices.total AS invoice_total,
                COALESCE(products.name, orders.item_name) AS display_name
            FROM orders
            JOIN users ON users.id = orders.user_id
            LEFT JOIN products ON products.id = orders.product_id
            LEFT JOIN invoices ON invoices.order_id = orders.id
            WHERE orders.vendor_id = :vendor
            ORDER BY orders.updated_at DESC
        ');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function vendorCommissions(int $vendorId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT vendor_commissions.*, orders.order_number, invoices.invoice_number, products.name AS product_name, users.first_name, users.last_name
            FROM vendor_commissions
            LEFT JOIN orders ON orders.id = vendor_commissions.order_id
            LEFT JOIN invoices ON invoices.id = vendor_commissions.invoice_id
            LEFT JOIN products ON products.id = vendor_commissions.product_id
            LEFT JOIN users ON users.id = orders.user_id
            WHERE vendor_commissions.vendor_id = :vendor
            ORDER BY vendor_commissions.created_at DESC, vendor_commissions.id DESC
        ');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function vendorPayouts(int $vendorId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM vendor_payouts WHERE vendor_id = :vendor ORDER BY created_at DESC, id DESC');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function vendorPayoutAccount(int $vendorId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM vendor_payout_accounts WHERE vendor_id = :vendor LIMIT 1');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetch() ?: null;
    }

    public static function vendorDocuments(int $vendorId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM vendor_documents WHERE vendor_id = :vendor ORDER BY created_at DESC, id DESC');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function vendorReviews(int $vendorId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT product_reviews.*, products.name AS product_name, users.first_name, users.last_name
            FROM product_reviews
            LEFT JOIN products ON products.id = product_reviews.product_id
            LEFT JOIN users ON users.id = product_reviews.user_id
            WHERE product_reviews.vendor_id = :vendor
            ORDER BY product_reviews.created_at DESC, product_reviews.id DESC
        ');
        $stmt->execute(['vendor' => $vendorId]);

        return $stmt->fetchAll();
    }

    public static function productReviews(?int $vendorId = null): array
    {
        $query = '
            SELECT product_reviews.*, products.name AS product_name, users.first_name, users.last_name, vendors.display_name AS vendor_display_name
            FROM product_reviews
            LEFT JOIN products ON products.id = product_reviews.product_id
            LEFT JOIN users ON users.id = product_reviews.user_id
            LEFT JOIN vendors ON vendors.id = product_reviews.vendor_id
        ';
        $params = [];
        if ($vendorId !== null) {
            $query .= ' WHERE product_reviews.vendor_id = :vendor';
            $params['vendor'] = $vendorId;
        }
        $query .= ' ORDER BY product_reviews.created_at DESC, product_reviews.id DESC';
        $stmt = Database::connection()->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function vendorSummary(int $vendorId): array
    {
        $db = Database::connection();
        $available = $db->prepare('SELECT COALESCE(SUM(vendor_net_amount), 0) FROM vendor_commissions WHERE vendor_id = :vendor AND payout_status = "available"');
        $available->execute(['vendor' => $vendorId]);

        $requested = $db->prepare('SELECT COALESCE(SUM(vendor_net_amount), 0) FROM vendor_commissions WHERE vendor_id = :vendor AND payout_status IN ("requested", "processing")');
        $requested->execute(['vendor' => $vendorId]);

        $paid = $db->prepare('SELECT COALESCE(SUM(vendor_net_amount), 0) FROM vendor_commissions WHERE vendor_id = :vendor AND payout_status = "paid"');
        $paid->execute(['vendor' => $vendorId]);

        $productCount = $db->prepare('SELECT COUNT(*) FROM products WHERE vendor_id = :vendor');
        $productCount->execute(['vendor' => $vendorId]);

        $orderCount = $db->prepare('SELECT COUNT(*) FROM orders WHERE vendor_id = :vendor');
        $orderCount->execute(['vendor' => $vendorId]);

        $reviewQuery = $db->prepare('SELECT COUNT(*) AS total_reviews, COALESCE(ROUND(AVG(rating), 1), 0) AS average_rating FROM product_reviews WHERE vendor_id = :vendor AND status = "approved"');
        $reviewQuery->execute(['vendor' => $vendorId]);
        $reviews = $reviewQuery->fetch() ?: ['total_reviews' => 0, 'average_rating' => 0];

        return [
            'products' => (int) $productCount->fetchColumn(),
            'orders' => (int) $orderCount->fetchColumn(),
            'available_balance' => (float) $available->fetchColumn(),
            'requested_balance' => (float) $requested->fetchColumn(),
            'paid_earnings' => (float) $paid->fetchColumn(),
            'total_reviews' => (int) ($reviews['total_reviews'] ?? 0),
            'average_rating' => (float) ($reviews['average_rating'] ?? 0),
        ];
    }

    public static function vendorProgramSummary(): array
    {
        $db = Database::connection();

        return [
            'vendors' => (int) $db->query('SELECT COUNT(*) FROM vendors')->fetchColumn(),
            'approved_vendors' => (int) $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'approved'")->fetchColumn(),
            'pending_vendors' => (int) $db->query("SELECT COUNT(*) FROM vendors WHERE status = 'pending'")->fetchColumn(),
            'pending_payouts' => (float) $db->query("SELECT COALESCE(SUM(request_amount), 0) FROM vendor_payouts WHERE status IN ('requested','processing')")->fetchColumn(),
        ];
    }

    public static function userInvoices(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT invoices.*, orders.order_number, COALESCE(orders.item_name, orders.pricing_plan_name) AS order_label FROM invoices LEFT JOIN orders ON orders.id = invoices.order_id WHERE invoices.user_id = :user ORDER BY invoices.created_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userPayments(int $userId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT payments.*, invoices.invoice_number, orders.vendor_id, vendors.display_name AS vendor_display_name
            FROM payments
            LEFT JOIN invoices ON invoices.id = payments.invoice_id
            LEFT JOIN orders ON orders.id = invoices.order_id
            LEFT JOIN vendors ON vendors.id = orders.vendor_id
            WHERE payments.user_id = :user
            ORDER BY payments.created_at DESC
        ');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userOrders(int $userId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT orders.*, services.name AS service_name, products.name AS product_name, invoices.id AS invoice_id, invoices.invoice_number, invoices.status AS invoice_status, invoices.total AS invoice_total,
                vendors.display_name AS vendor_display_name,
                COALESCE(products.name, services.name, orders.pricing_plan_name, orders.item_name) AS display_name
            FROM orders
            LEFT JOIN services ON services.id = orders.service_id
            LEFT JOIN products ON products.id = orders.product_id
            LEFT JOIN vendors ON vendors.id = orders.vendor_id
            LEFT JOIN invoices ON invoices.order_id = orders.id
            WHERE orders.user_id = :user
            ORDER BY orders.created_at DESC
        ');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userTickets(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM tickets WHERE user_id = :user ORDER BY updated_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function ticketMessages(int $ticketId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM ticket_messages WHERE ticket_id = :ticket ORDER BY created_at ASC');
        $stmt->execute(['ticket' => $ticketId]);
        return $stmt->fetchAll();
    }

    public static function userFiles(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM user_files WHERE user_id = :user ORDER BY created_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userProposals(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM proposals WHERE user_id = :user ORDER BY created_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userContracts(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM contracts WHERE user_id = :user ORDER BY created_at DESC');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function userNotifications(int $userId, int $limit = 12): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = Database::connection()->prepare("SELECT * FROM notifications WHERE user_id = :user OR user_id IS NULL ORDER BY CASE WHEN read_at IS NULL THEN 0 ELSE 1 END ASC, created_at DESC LIMIT {$limit}");
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }

    public static function unreadNotificationCount(int $userId): int
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM notifications WHERE (user_id = :user OR user_id IS NULL) AND read_at IS NULL');
        $stmt->execute(['user' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function userReferral(int $userId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT referrals.*, users.first_name, users.last_name, users.client_id FROM referrals JOIN users ON users.id = referrals.user_id WHERE referrals.user_id = :user LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $referral = $stmt->fetch() ?: null;

        if ($referral) {
            return $referral;
        }

        $user = $db->prepare('SELECT id, client_id, first_name, last_name FROM users WHERE id = :user LIMIT 1');
        $user->execute(['user' => $userId]);
        $record = $user->fetch();
        if (! $record) {
            return null;
        }

        $baseCode = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', (string) ($record['client_id'] ?? ('BBT' . $userId))) ?: 'BBT');
        $code = substr($baseCode, 0, 12) . '-' . strtoupper(substr(md5((string) $userId . '-referral'), 0, 6));

        $db->prepare('
            INSERT INTO referrals (user_id, referral_code, payout_status, created_at, updated_at)
            VALUES (:user_id, :referral_code, :payout_status, NOW(), NOW())
        ')->execute([
            'user_id' => $userId,
            'referral_code' => $code,
            'payout_status' => 'unpaid',
        ]);

        $stmt->execute(['user' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function referralSummary(): array
    {
        $db = Database::connection();

        return [
            'accounts' => (int) $db->query('SELECT COUNT(*) FROM referrals')->fetchColumn(),
            'signups' => (int) $db->query('SELECT COALESCE(SUM(total_signups), 0) FROM referrals')->fetchColumn(),
            'earned' => (float) $db->query('SELECT COALESCE(SUM(total_earned), 0) FROM referrals')->fetchColumn(),
            'unpaid_balance' => (float) $db->query("SELECT COALESCE(SUM(reward_balance), 0) FROM referrals WHERE payout_status <> 'paid'")->fetchColumn(),
        ];
    }

    public static function invoice(int $invoiceId, ?int $userId = null): ?array
    {
        $query = 'SELECT invoices.*, users.first_name, users.last_name, users.email, orders.order_number, orders.vendor_id, vendors.display_name AS vendor_display_name, services.name AS service_name, products.name AS product_name, COALESCE(products.name, services.name, orders.item_name, orders.pricing_plan_name) AS order_label
            FROM invoices
            JOIN users ON users.id = invoices.user_id
            LEFT JOIN orders ON orders.id = invoices.order_id
            LEFT JOIN vendors ON vendors.id = orders.vendor_id
            LEFT JOIN services ON services.id = orders.service_id
            LEFT JOIN products ON products.id = orders.product_id
            WHERE invoices.id = :invoice';
        $params = ['invoice' => $invoiceId];
        if ($userId !== null) {
            $query .= ' AND invoices.user_id = :user';
            $params['user'] = $userId;
        }
        $stmt = Database::connection()->prepare($query . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public static function invoiceItems(int $invoiceId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM invoice_items WHERE invoice_id = :invoice ORDER BY id ASC');
        $stmt->execute(['invoice' => $invoiceId]);
        return $stmt->fetchAll();
    }

    public static function adminStats(): array
    {
        $db = Database::connection();
        return [
            'users' => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'revenue' => (float) $db->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status IN ('paid','approved')")->fetchColumn(),
            'orders' => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'tickets' => (int) $db->query("SELECT COUNT(*) FROM tickets WHERE status <> 'closed'")->fetchColumn(),
            'payments_pending' => (int) $db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn(),
        ];
    }

    public static function allUsers(): array
    {
        return Database::connection()->query('SELECT id, first_name, last_name, email, role, client_id, status, terms_accepted_at, created_at FROM users ORDER BY id DESC')->fetchAll();
    }

    public static function userById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function allPayments(): array
    {
        return Database::connection()->query('
            SELECT payments.*, users.first_name, users.last_name, invoices.invoice_number, orders.vendor_id, vendors.display_name AS vendor_display_name
            FROM payments
            LEFT JOIN users ON users.id = payments.user_id
            LEFT JOIN invoices ON invoices.id = payments.invoice_id
            LEFT JOIN orders ON orders.id = invoices.order_id
            LEFT JOIN vendors ON vendors.id = orders.vendor_id
            ORDER BY payments.created_at DESC
        ')->fetchAll();
    }

    public static function allTickets(): array
    {
        return Database::connection()->query('SELECT tickets.*, users.first_name, users.last_name, users.email FROM tickets JOIN users ON users.id = tickets.user_id ORDER BY tickets.updated_at DESC')->fetchAll();
    }

    public static function allOrders(): array
    {
        return Database::connection()->query('
            SELECT orders.*, users.first_name, users.last_name, users.email, vendors.display_name AS vendor_display_name,
                services.name AS service_name, products.name AS product_name, invoices.id AS invoice_id, invoices.invoice_number, invoices.status AS invoice_status, invoices.total AS invoice_total,
                COALESCE(products.name, services.name, orders.pricing_plan_name, orders.item_name) AS display_name
            FROM orders
            JOIN users ON users.id = orders.user_id
            LEFT JOIN vendors ON vendors.id = orders.vendor_id
            LEFT JOIN services ON services.id = orders.service_id
            LEFT JOIN products ON products.id = orders.product_id
            LEFT JOIN invoices ON invoices.order_id = orders.id
            ORDER BY orders.updated_at DESC
        ')->fetchAll();
    }

    public static function allContacts(): array
    {
        return Database::connection()->query('SELECT * FROM contacts ORDER BY created_at DESC')->fetchAll();
    }

    public static function allCoupons(): array
    {
        return Database::connection()->query('SELECT * FROM coupons ORDER BY created_at DESC')->fetchAll();
    }

    public static function allReferrals(): array
    {
        return Database::connection()->query('
            SELECT referrals.*, users.first_name, users.last_name, users.email, sponsor.first_name AS sponsor_first_name, sponsor.last_name AS sponsor_last_name
            FROM referrals
            JOIN users ON users.id = referrals.user_id
            LEFT JOIN users AS sponsor ON sponsor.id = referrals.referred_by_user_id
            ORDER BY referrals.reward_balance DESC, referrals.total_signups DESC, referrals.id DESC
        ')->fetchAll();
    }

    public static function developerOrders(int $userId): array
    {
        $stmt = Database::connection()->prepare('
            SELECT orders.*, users.first_name, users.last_name, services.name AS service_name, products.name AS product_name, invoices.id AS invoice_id, invoices.invoice_number, invoices.status AS invoice_status, invoices.total AS invoice_total,
                COALESCE(products.name, services.name, orders.pricing_plan_name, orders.item_name) AS display_name
            FROM orders
            JOIN users ON users.id = orders.user_id
            LEFT JOIN services ON services.id = orders.service_id
            LEFT JOIN products ON products.id = orders.product_id
            LEFT JOIN invoices ON invoices.order_id = orders.id
            WHERE orders.user_id = :user
            ORDER BY orders.updated_at DESC
        ');
        $stmt->execute(['user' => $userId]);
        return $stmt->fetchAll();
    }
}
