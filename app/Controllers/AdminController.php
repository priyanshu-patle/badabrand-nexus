<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Content;
use App\Core\Database;
use App\Core\View;

class AdminController
{
    private function redirectTarget(string $fallback): string
    {
        $target = trim((string) input('_redirect', ''));

        if ($target !== '' && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return $target;
        }

        return $fallback;
    }

    private function markdownFiles(string $folder): array
    {
        $paths = glob(base_path(trim($folder, '/') . '/*.md')) ?: [];
        sort($paths);

        return array_values(array_filter(array_map(function (string $path): ?array {
            $content = file_get_contents($path);
            if ($content === false) {
                return null;
            }

            $slug = basename($path, '.md');
            preg_match('/^#\s+(.+)$/m', $content, $headingMatch);
            $title = $headingMatch[1] ?? ucwords(str_replace(['-', '_'], ' ', preg_replace('/^\d+[-_]?/', '', $slug)));

            $description = '';
            foreach (preg_split("/\r\n|\n|\r/", $content) as $line) {
                $trimmed = trim($line);
                if ($trimmed !== '' && ! str_starts_with($trimmed, '#')) {
                    $description = $trimmed;
                    break;
                }
            }

            return [
                'slug' => $slug,
                'title' => $title,
                'description' => $description,
                'content' => $content,
            ];
        }, $paths)));
    }

    private function documentationFiles(): array
    {
        return $this->markdownFiles('documentation');
    }

    private function packageFiles(): array
    {
        return $this->markdownFiles('marketplace-assets');
    }

    private function productCategoryFromType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'theme' => 'themes',
            'plugin' => 'plugins',
            'template' => 'templates',
            default => 'software',
        };
    }

    private function defaultVendorCommission(): float
    {
        return round((float) app_setting('vendor_default_commission', '15'), 2);
    }

    private function logVendorActivity(int $vendorId, ?int $actorUserId, string $action, string $description, array $context = []): void
    {
        Database::connection()->prepare('
            INSERT INTO vendor_activity_logs (vendor_id, actor_user_id, action, description, context, created_at)
            VALUES (:vendor_id, :actor_user_id, :action, :description, :context, NOW())
        ')->execute([
            'vendor_id' => $vendorId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'description' => $description,
            'context' => $context === [] ? null : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    private function ensureVendorRecordForUser(int $userId, array $payload = []): int
    {
        $db = Database::connection();
        $existing = $db->prepare('SELECT id FROM vendors WHERE user_id = :user LIMIT 1');
        $existing->execute(['user' => $userId]);
        $vendorId = (int) ($existing->fetchColumn() ?: 0);
        if ($vendorId > 0) {
            return $vendorId;
        }

        $storeName = trim((string) ($payload['store_name'] ?? (($payload['first_name'] ?? 'Vendor') . ' ' . ($payload['last_name'] ?? 'Store'))));
        $displayName = trim((string) ($payload['display_name'] ?? $storeName)) ?: $storeName;
        $slug = slugify((string) ($payload['slug'] ?? $storeName), 'vendor');
        $checkSlug = $db->prepare('SELECT id FROM vendors WHERE slug = :slug LIMIT 1');
        $checkSlug->execute(['slug' => $slug]);
        if ($checkSlug->fetch()) {
            $slug .= '-' . random_int(100, 999);
        }

        $db->prepare('
            INSERT INTO vendors (user_id, store_name, display_name, slug, email, phone, tax_gst, status, commission_percent, joined_at, created_at, updated_at)
            VALUES (:user_id, :store_name, :display_name, :slug, :email, :phone, :tax_gst, :status, :commission_percent, NOW(), NOW(), NOW())
        ')->execute([
            'user_id' => $userId,
            'store_name' => $storeName,
            'display_name' => $displayName,
            'slug' => $slug,
            'email' => (string) ($payload['email'] ?? ''),
            'phone' => (string) ($payload['phone'] ?? ''),
            'tax_gst' => (string) ($payload['tax_gst'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'approved'),
            'commission_percent' => $payload['commission_percent'] ?? null,
        ]);
        $vendorId = (int) $db->lastInsertId();

        $db->prepare('
            INSERT INTO vendor_profiles (vendor_id, business_name, legal_name, short_bio, support_email, support_phone, created_at, updated_at)
            VALUES (:vendor_id, :business_name, :legal_name, :short_bio, :support_email, :support_phone, NOW(), NOW())
        ')->execute([
            'vendor_id' => $vendorId,
            'business_name' => $storeName,
            'legal_name' => (string) ($payload['legal_name'] ?? ''),
            'short_bio' => (string) ($payload['short_bio'] ?? ''),
            'support_email' => (string) ($payload['email'] ?? ''),
            'support_phone' => (string) ($payload['phone'] ?? ''),
        ]);

        $db->prepare('
            INSERT INTO vendor_payout_accounts (vendor_id, payout_method, created_at, updated_at)
            VALUES (:vendor_id, :payout_method, NOW(), NOW())
        ')->execute([
            'vendor_id' => $vendorId,
            'payout_method' => (string) ($payload['payout_method'] ?? 'bank_transfer'),
        ]);

        return $vendorId;
    }

    private function syncVendorCommissionStatus(array $payment, string $status): void
    {
        $invoiceId = (int) ($payment['invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            return;
        }

        $db = Database::connection();
        $commissionStatus = match ($status) {
            'approved' => 'available',
            'refund' => 'refunded',
            'cancelled', 'rejected', 'invalid' => 'cancelled',
            default => 'pending',
        };

        $db->prepare('
            UPDATE vendor_commissions
            SET payment_id = :payment_id,
                payout_status = :payout_status,
                available_at = CASE WHEN :payout_status = "available" THEN NOW() ELSE available_at END,
                updated_at = NOW()
            WHERE invoice_id = :invoice_id
        ')->execute([
            'payment_id' => (int) ($payment['id'] ?? 0),
            'payout_status' => $commissionStatus,
            'invoice_id' => $invoiceId,
        ]);

        $db->prepare('
            UPDATE orders
            SET payout_status = :payout_status, updated_at = NOW()
            WHERE id IN (SELECT order_id FROM invoices WHERE id = :invoice_id)
        ')->execute([
            'payout_status' => $commissionStatus,
            'invoice_id' => $invoiceId,
        ]);
    }

    private function searchRows(array $rows, array $fields, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return $rows;
        }

        return array_values(array_filter($rows, function (array $row) use ($fields, $query): bool {
            foreach ($fields as $field) {
                $value = (string) ($row[$field] ?? '');
                if ($value !== '' && stripos($value, $query) !== false) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function exportCsv(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    private function render(string $view, string $title, array $data = []): void
    {
        Auth::requireRole(['admin']);
        View::render($view, [
            'settings' => Content::settings(),
            'pageTitle' => $title,
            'metaDescription' => $title,
            'currentUser' => Auth::user(),
        ] + $data, 'layouts/dashboard');
    }

    public function index(): void
    {
        $range = (string) input('range', 'month');
        if (! in_array($range, ['month', 'all'], true)) {
            $range = 'month';
        }

        $this->render('admin/dashboard', 'Admin Dashboard', [
            'viewName' => 'dashboard',
            'dashboardRange' => $range,
            'dashboardInsights' => Content::dashboardInsights($range),
        ]);
    }

    public function cms(): void
    {
        $this->render('admin/cms', 'CMS & Branding', [
            'viewName' => 'cms',
            'settings' => Content::settings(),
            'slider' => Content::slider(),
            'statsItems' => Content::stats(),
            'testimonials' => Content::testimonials(),
        ]);
    }

    public function content(): void
    {
        $this->render('admin/content', 'Content Hub', [
            'viewName' => 'content',
            'pages' => Content::pages(),
            'blogs' => Content::blogs(),
            'faqItems' => Content::faqs(),
            'jobs' => Content::careers(),
            'portfolioProjects' => Content::portfolioProjects(),
            'teamMembers' => Content::teamMembers(),
        ]);
    }

    public function services(): void
    {
        $query = trim((string) input('q', ''));
        $services = $this->searchRows(Content::services(), ['name', 'short_description', 'price_label', 'slug'], $query);
        $plans = $this->searchRows(Content::plans(), ['name', 'price', 'description'], $query);

        $this->render('admin/services', 'Services & Plans', [
            'viewName' => 'services',
            'search' => $query,
            'services' => $services,
            'plans' => $plans,
        ]);
    }

    public function documentation(): void
    {
        $docs = $this->documentationFiles();
        $requested = trim((string) input('doc', $docs[0]['slug'] ?? ''));
        $current = $docs[0] ?? [
            'slug' => 'empty',
            'title' => 'Documentation',
            'description' => 'No documentation files found.',
            'content' => "# Documentation\n\nNo documentation files were found in the `documentation` folder.",
        ];

        foreach ($docs as $doc) {
            if ($doc['slug'] === $requested) {
                $current = $doc;
                break;
            }
        }

        $this->render('admin/documentation', 'Documentation', [
            'viewName' => 'documentation',
            'docs' => $docs,
            'currentDoc' => $current,
            'currentDocHtml' => simple_markdown_to_html($current['content']),
        ]);
    }

    public function settingsPage(): void
    {
        $this->render('admin/settings', 'System Settings', [
            'viewName' => 'settings',
            'systemSettings' => Content::settings(),
        ]);
    }

    public function package(): void
    {
        $docs = $this->packageFiles();
        $requested = trim((string) input('doc', $docs[0]['slug'] ?? ''));
        $current = $docs[0] ?? [
            'slug' => 'empty',
            'title' => 'Package Center',
            'description' => 'No package files found.',
            'content' => "# Package Center\n\nNo package files were found in the `marketplace-assets` folder.",
        ];

        foreach ($docs as $doc) {
            if ($doc['slug'] === $requested) {
                $current = $doc;
                break;
            }
        }

        $this->render('admin/package', 'Package Center', [
            'viewName' => 'package',
            'docs' => $docs,
            'currentDoc' => $current,
            'currentDocHtml' => simple_markdown_to_html($current['content']),
        ]);
    }

    public function users(): void
    {
        $this->render('admin/users', 'Users', [
            'viewName' => 'users',
            'users' => Content::allUsers(),
        ]);
    }

    public function marketplace(): void
    {
        $query = trim((string) input('q', ''));
        $products = $this->searchRows(Content::products(), ['name', 'category', 'product_type', 'short_description', 'price_label', 'version_label', 'status'], $query);
        $this->render('admin/marketplace', 'Digital Marketplace', [
            'viewName' => 'marketplace',
            'search' => $query,
            'products' => $products,
        ]);
    }

    public function payments(): void
    {
        $query = trim((string) input('q', ''));
        $payments = $this->searchRows(Content::allPayments(), ['invoice_number', 'first_name', 'last_name', 'transaction_id', 'gateway', 'status', 'notes'], $query);
        $invoices = Database::connection()->query('SELECT invoices.*, users.first_name, users.last_name FROM invoices JOIN users ON users.id = invoices.user_id ORDER BY invoices.created_at DESC')->fetchAll();
        $invoices = $this->searchRows($invoices, ['invoice_number', 'billing_name', 'first_name', 'last_name', 'gst_number', 'status'], $query);

        $this->render('admin/payments', 'Payments & Billing', [
            'viewName' => 'payments',
            'search' => $query,
            'payments' => $payments,
            'invoices' => $invoices,
            'clients' => Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name")->fetchAll(),
        ]);
    }

    public function tickets(): void
    {
        $ticketId = (int) input('ticket', 0);
        $this->render('admin/tickets', 'Support System', [
            'viewName' => 'tickets',
            'tickets' => Content::allTickets(),
            'messages' => $ticketId > 0 ? Content::ticketMessages($ticketId) : [],
            'activeTicketId' => $ticketId,
        ]);
    }

    public function marketing(): void
    {
        $this->render('admin/marketing', 'Marketing Tools', [
            'viewName' => 'marketing',
            'contacts' => Content::allContacts(),
            'coupons' => Content::allCoupons(),
            'referrals' => Content::allReferrals(),
            'emailLogs' => Database::connection()->query('SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10')->fetchAll(),
        ]);
    }

    public function projects(): void
    {
        $query = trim((string) input('q', ''));
        $orders = $this->searchRows(Content::allOrders(), ['order_number', 'first_name', 'last_name', 'service_name', 'display_name', 'status'], $query);
        $updates = Database::connection()->query('SELECT project_updates.*, orders.order_number, users.first_name, users.last_name FROM project_updates JOIN orders ON orders.id = project_updates.order_id JOIN users ON users.id = project_updates.user_id ORDER BY project_updates.created_at DESC')->fetchAll();
        $updates = $this->searchRows($updates, ['order_number', 'first_name', 'last_name', 'title', 'details'], $query);
        $proposals = Database::connection()->query('SELECT proposals.*, users.first_name, users.last_name FROM proposals JOIN users ON users.id = proposals.user_id ORDER BY proposals.created_at DESC')->fetchAll();
        $proposals = $this->searchRows($proposals, ['title', 'description', 'first_name', 'last_name', 'status'], $query);
        $contracts = Database::connection()->query('SELECT contracts.*, users.first_name, users.last_name FROM contracts JOIN users ON users.id = contracts.user_id ORDER BY contracts.created_at DESC')->fetchAll();
        $contracts = $this->searchRows($contracts, ['title', 'contract_body', 'first_name', 'last_name', 'status'], $query);

        $this->render('admin/projects', 'Projects & Contracts', [
            'viewName' => 'projects',
            'search' => $query,
            'orders' => $orders,
            'updates' => $updates,
            'proposals' => $proposals,
            'contracts' => $contracts,
            'clients' => Database::connection()->query("SELECT id, first_name, last_name FROM users WHERE role = 'customer' ORDER BY first_name")->fetchAll(),
        ]);
    }

    public function profile(): void
    {
        $this->render('admin/profile', 'Admin Profile', [
            'viewName' => 'profile',
            'profile' => Auth::user(),
        ]);
    }

    public function notifications(): void
    {
        $user = Auth::user();
        $this->render('admin/notifications', 'Notifications', [
            'viewName' => 'notifications',
            'notifications' => Content::userNotifications((int) ($user['id'] ?? 0), 40),
            'unreadCount' => Content::unreadNotificationCount((int) ($user['id'] ?? 0)),
        ]);
    }

    public function saveCms(): void
    {
        Auth::requireRole(['admin']);
        $db = Database::connection();
        $pairs = [
            'site_title' => input('site_title', ''),
            'hero_title' => input('hero_title', ''),
            'hero_subtitle' => input('hero_subtitle', ''),
            'hero_cta_primary' => input('hero_cta_primary', ''),
            'hero_cta_secondary' => input('hero_cta_secondary', ''),
            'about_summary' => input('about_summary', ''),
            'contact_phone' => input('contact_phone', ''),
            'contact_email' => input('contact_email', ''),
            'contact_address' => input('contact_address', ''),
            'footer_company_name' => input('footer_company_name', ''),
            'footer_text' => input('footer_text', ''),
            'theme_default' => 'dark',
        ];
        $logo = upload_file('logo_file');
        $favicon = upload_file('favicon_file');
        if ($logo) {
            $pairs['company_logo'] = $logo;
        }
        if ($favicon) {
            $pairs['company_favicon'] = $favicon;
        }

        $stmt = $db->prepare('REPLACE INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())');
        foreach ($pairs as $key => $value) {
            $stmt->execute(['key' => $key, 'value' => (string) $value]);
        }

        flash('success', 'Branding and CMS settings updated.');
        redirect('/admin/cms');
    }

    public function saveSettings(): void
    {
        Auth::requireRole(['admin']);
        $db = Database::connection();
        $currentSettings = Content::settings();
        $pairs = [
            'support_email',
            'support_phone',
            'company_whatsapp',
            'theme_admin',
            'theme_public',
            'theme_default',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_from_name',
            'smtp_from_email',
            'seo_default_title',
            'seo_default_description',
            'seo_keywords',
            'social_facebook',
            'social_twitter',
            'social_linkedin',
            'social_instagram',
            'social_youtube',
            'product_version',
            'license_type',
            'buyer_support_window',
            'release_channel',
            'referral_enabled',
            'referral_reward_amount',
            'referral_percentage',
            'referral_minimum_payout',
            'vendor_enabled',
            'vendor_auto_approve',
            'vendor_default_commission',
            'vendor_product_requires_review',
            'vendor_minimum_payout',
            'api_enabled',
            'api_default_version',
            'api_token_ttl',
        ];

        $stmt = $db->prepare('REPLACE INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())');
        foreach ($pairs as $key) {
            $value = array_key_exists($key, $_POST) ? $_POST[$key] : ($currentSettings[$key] ?? '');
            $stmt->execute(['key' => $key, 'value' => (string) $value]);
        }

        flash('success', 'System settings updated.');
        redirect($this->redirectTarget('/admin/settings/general'));
    }

    public function addSlider(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO sliders (badge, title, subtitle, cta_text, cta_link, sort_order, created_at, updated_at)
            VALUES (:badge, :title, :subtitle, :cta_text, :cta_link, :sort_order, NOW(), NOW())
        ')->execute([
            'badge' => input('badge', ''),
            'title' => input('title', ''),
            'subtitle' => input('subtitle', ''),
            'cta_text' => input('cta_text', ''),
            'cta_link' => input('cta_link', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Homepage highlight added.');
        redirect('/admin/cms');
    }

    public function updateSlider(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE sliders
            SET badge = :badge, title = :title, subtitle = :subtitle, cta_text = :cta_text, cta_link = :cta_link, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('slider_id', 0),
            'badge' => input('badge', ''),
            'title' => input('title', ''),
            'subtitle' => input('subtitle', ''),
            'cta_text' => input('cta_text', ''),
            'cta_link' => input('cta_link', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Homepage highlight updated.');
        redirect('/admin/cms');
    }

    public function deleteSlider(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM sliders WHERE id = :id')->execute([
            'id' => (int) input('slider_id', 0),
        ]);
        flash('success', 'Homepage highlight deleted.');
        redirect('/admin/cms');
    }

    public function addStat(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO stats (label, value, suffix, sort_order)
            VALUES (:label, :value, :suffix, :sort_order)
        ')->execute([
            'label' => input('label', ''),
            'value' => input('value', ''),
            'suffix' => input('suffix', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Homepage stat added.');
        redirect('/admin/cms');
    }

    public function updateStat(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE stats
            SET label = :label, value = :value, suffix = :suffix, sort_order = :sort_order
            WHERE id = :id
        ')->execute([
            'id' => (int) input('stat_id', 0),
            'label' => input('label', ''),
            'value' => input('value', ''),
            'suffix' => input('suffix', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Homepage stat updated.');
        redirect('/admin/cms');
    }

    public function deleteStat(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM stats WHERE id = :id')->execute([
            'id' => (int) input('stat_id', 0),
        ]);
        flash('success', 'Homepage stat deleted.');
        redirect('/admin/cms');
    }

    public function addTestimonial(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO testimonials (name, role, quote, sort_order, created_at, updated_at)
            VALUES (:name, :role, :quote, :sort_order, NOW(), NOW())
        ')->execute([
            'name' => input('name', ''),
            'role' => input('role', ''),
            'quote' => input('quote', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Testimonial added.');
        redirect('/admin/cms');
    }

    public function updateTestimonial(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE testimonials
            SET name = :name, role = :role, quote = :quote, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('testimonial_id', 0),
            'name' => input('name', ''),
            'role' => input('role', ''),
            'quote' => input('quote', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Testimonial updated.');
        redirect('/admin/cms');
    }

    public function deleteTestimonial(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM testimonials WHERE id = :id')->execute([
            'id' => (int) input('testimonial_id', 0),
        ]);
        flash('success', 'Testimonial deleted.');
        redirect('/admin/cms');
    }

    public function addService(): void
    {
        Auth::requireRole(['admin']);
        $stmt = Database::connection()->prepare('
            INSERT INTO services (name, slug, short_description, icon, price_label, sort_order, created_at, updated_at)
            VALUES (:name, :slug, :description, :icon, :price, :sort_order, NOW(), NOW())
        ');
        $name = trim((string) input('name', ''));
        $stmt->execute([
            'name' => $name,
            'slug' => strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'description' => input('short_description', ''),
            'icon' => input('icon', 'bi-briefcase'),
            'price' => input('price_label', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);

        flash('success', 'Service added.');
        redirect('/admin/services');
    }

    public function updateService(): void
    {
        Auth::requireRole(['admin']);
        $id = (int) input('service_id', 0);
        $name = trim((string) input('name', ''));
        Database::connection()->prepare('
            UPDATE services
            SET name = :name, slug = :slug, short_description = :description, icon = :icon, price_label = :price, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => $id,
            'name' => $name,
            'slug' => strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'description' => input('short_description', ''),
            'icon' => input('icon', 'bi-briefcase'),
            'price' => input('price_label', ''),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Service updated.');
        redirect('/admin/services');
    }

    public function deleteService(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM services WHERE id = :id')->execute(['id' => (int) input('service_id', 0)]);
        flash('success', 'Service deleted.');
        redirect('/admin/services');
    }

    public function addUser(): void
    {
        Auth::requireRole(['admin']);
        $role = (string) input('role', 'customer');
        $clientId = match ($role) {
            'customer' => 'BBT-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'vendor' => 'VND-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            default => null,
        };
        $db = Database::connection();
        $db->prepare("
            INSERT INTO users (role, client_id, first_name, last_name, email, phone, password, status, created_at, updated_at)
            VALUES (:role, :client_id, :first_name, :last_name, :email, :phone, :password, :status, NOW(), NOW())
        ")->execute([
            'role' => $role,
            'client_id' => $clientId,
            'first_name' => input('first_name', ''),
            'last_name' => input('last_name', ''),
            'email' => input('email', ''),
            'phone' => input('phone', ''),
            'password' => password_hash((string) input('password', 'ChangeMe@123'), PASSWORD_DEFAULT),
            'status' => input('status', 'active'),
        ]);
        $userId = (int) $db->lastInsertId();
        if ($role === 'vendor') {
            $vendorId = $this->ensureVendorRecordForUser($userId, [
                'store_name' => trim((string) input('store_name', input('first_name', '') . ' ' . input('last_name', '') . ' Store')),
                'display_name' => trim((string) input('display_name', input('first_name', '') . ' ' . input('last_name', ''))),
                'email' => input('email', ''),
                'phone' => input('phone', ''),
                'tax_gst' => input('tax_gst', ''),
                'status' => input('vendor_status', input('status', 'approved')),
                'commission_percent' => input('commission_percent', $this->defaultVendorCommission()),
                'payout_method' => input('payout_method', 'bank_transfer'),
            ]);
            $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'vendor.created', 'Vendor account created from admin user manager.');
        }
        flash('success', 'User created.');
        redirect('/admin/users');
    }

    public function updateUser(): void
    {
        Auth::requireRole(['admin']);
        $params = [
            'id' => (int) input('user_id', 0),
            'first_name' => input('first_name', ''),
            'last_name' => input('last_name', ''),
            'email' => input('email', ''),
            'phone' => input('phone', ''),
            'role' => input('role', 'customer'),
            'status' => input('status', 'active'),
        ];
        $db = Database::connection();
        $db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, role = :role, status = :status, updated_at = NOW() WHERE id = :id')->execute($params);
        if ($params['role'] === 'vendor') {
            $vendorId = $this->ensureVendorRecordForUser((int) $params['id'], [
                'store_name' => trim((string) input('store_name', $params['first_name'] . ' ' . $params['last_name'] . ' Store')),
                'display_name' => trim((string) input('display_name', $params['first_name'] . ' ' . $params['last_name'])),
                'email' => $params['email'],
                'phone' => $params['phone'],
                'tax_gst' => input('tax_gst', ''),
                'status' => input('vendor_status', $params['status']),
                'commission_percent' => input('commission_percent', $this->defaultVendorCommission()),
                'payout_method' => input('payout_method', 'bank_transfer'),
            ]);
            $db->prepare('UPDATE vendors SET email = :email, phone = :phone, tax_gst = :tax_gst, status = :status, commission_percent = :commission_percent, updated_at = NOW() WHERE id = :id')->execute([
                'id' => $vendorId,
                'email' => $params['email'],
                'phone' => $params['phone'],
                'tax_gst' => input('tax_gst', ''),
                'status' => input('vendor_status', $params['status']),
                'commission_percent' => (float) input('commission_percent', $this->defaultVendorCommission()),
            ]);
            $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'vendor.updated', 'Vendor account updated from admin user manager.');
        }
        flash('success', 'User updated.');
        redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => (int) input('user_id', 0)]);
        flash('success', 'User deleted.');
        redirect('/admin/users');
    }

    public function updateUserStatus(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'status' => input('status', 'active'),
            'id' => (int) input('user_id', 0),
        ]);
        flash('success', 'User status updated.');
        redirect('/admin/users');
    }

    public function addPlan(): void
    {
        Auth::requireRole(['admin']);
        $stmt = Database::connection()->prepare('INSERT INTO pricing_plans (name, price, description, is_featured, sort_order, created_at, updated_at) VALUES (:name, :price, :description, :featured, :sort_order, NOW(), NOW())');
        $stmt->execute([
            'name' => input('name', ''),
            'price' => input('price', ''),
            'description' => input('description', ''),
            'featured' => input('is_featured') ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Pricing plan added.');
        redirect('/admin/services');
    }

    public function addProduct(): void
    {
        Auth::requireRole(['admin']);
        $name = trim((string) input('name', ''));
        $productType = strtolower(trim((string) input('product_type', 'software')));
        $thumbnail = upload_file('thumbnail_file');
        $vendorId = max(0, (int) input('vendor_id', 0));
        $productRequiresReview = app_setting('vendor_product_requires_review', '1') === '1';
        $approvalStatus = $vendorId > 0 ? (string) input('approval_status', ($productRequiresReview ? 'pending' : 'approved')) : 'approved';
        $commissionPercent = input('commission_percent', $vendorId > 0 ? $this->defaultVendorCommission() : null);
        Database::connection()->prepare('
            INSERT INTO products (name, slug, vendor_id, category, product_type, short_description, description, features_text, price, price_label, version_label, thumbnail_path, download_link, status, approval_status, commission_percent, requires_review, sort_order, created_at, updated_at)
            VALUES (:name, :slug, :vendor_id, :category, :product_type, :short_description, :description, :features_text, :price, :price_label, :version_label, :thumbnail_path, :download_link, :status, :approval_status, :commission_percent, :requires_review, :sort_order, NOW(), NOW())
        ')->execute([
            'name' => $name,
            'slug' => strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'vendor_id' => $vendorId > 0 ? $vendorId : null,
            'category' => $this->productCategoryFromType($productType),
            'product_type' => $productType,
            'short_description' => input('short_description', ''),
            'description' => input('description', ''),
            'features_text' => input('features_text', ''),
            'price' => (float) input('price', 0),
            'price_label' => input('price_label', ''),
            'version_label' => input('version_label', ''),
            'thumbnail_path' => $thumbnail,
            'download_link' => input('download_link', ''),
            'status' => input('status', 'active'),
            'approval_status' => $approvalStatus,
            'commission_percent' => $vendorId > 0 ? (float) $commissionPercent : null,
            'requires_review' => $vendorId > 0 ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        $productId = (int) Database::connection()->lastInsertId();
        if ($vendorId > 0) {
            $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'product.created', 'Vendor product created from admin marketplace.', [
                'product_id' => $productId,
                'approval_status' => $approvalStatus,
            ]);
        }
        flash('success', 'Product added to marketplace.');
        redirect('/admin/marketplace');
    }

    public function updateProduct(): void
    {
        Auth::requireRole(['admin']);
        $productId = (int) input('product_id', 0);
        $current = Content::product($productId);
        $thumbnail = upload_file('thumbnail_file') ?: ($current['thumbnail_path'] ?? null);
        $name = trim((string) input('name', ''));
        $productType = strtolower(trim((string) input('product_type', 'software')));
        $vendorId = max(0, (int) input('vendor_id', (int) ($current['vendor_id'] ?? 0)));
        $productRequiresReview = app_setting('vendor_product_requires_review', '1') === '1';
        $approvalStatus = $vendorId > 0 ? (string) input('approval_status', (($current['approval_status'] ?? '') ?: ($productRequiresReview ? 'pending' : 'approved'))) : 'approved';
        Database::connection()->prepare('
            UPDATE products
            SET name = :name, slug = :slug, vendor_id = :vendor_id, category = :category, product_type = :product_type, short_description = :short_description, description = :description, features_text = :features_text, price = :price, price_label = :price_label, version_label = :version_label, thumbnail_path = :thumbnail_path, download_link = :download_link, status = :status, approval_status = :approval_status, commission_percent = :commission_percent, requires_review = :requires_review, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => $productId,
            'name' => $name,
            'slug' => strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'vendor_id' => $vendorId > 0 ? $vendorId : null,
            'category' => $this->productCategoryFromType($productType),
            'product_type' => $productType,
            'short_description' => input('short_description', ''),
            'description' => input('description', ''),
            'features_text' => input('features_text', ''),
            'price' => (float) input('price', 0),
            'price_label' => input('price_label', ''),
            'version_label' => input('version_label', ''),
            'thumbnail_path' => $thumbnail,
            'download_link' => input('download_link', ''),
            'status' => input('status', 'active'),
            'approval_status' => $approvalStatus,
            'commission_percent' => $vendorId > 0 ? (float) input('commission_percent', $current['commission_percent'] ?? $this->defaultVendorCommission()) : null,
            'requires_review' => $vendorId > 0 ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        if ($vendorId > 0) {
            $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'product.updated', 'Vendor product updated from admin marketplace.', [
                'product_id' => $productId,
                'approval_status' => $approvalStatus,
            ]);
        }
        flash('success', 'Marketplace product updated.');
        redirect('/admin/marketplace');
    }

    public function deleteProduct(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM products WHERE id = :id')->execute(['id' => (int) input('product_id', 0)]);
        flash('success', 'Product removed from marketplace.');
        redirect('/admin/marketplace');
    }

    public function updatePlan(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE pricing_plans
            SET name = :name, price = :price, description = :description, is_featured = :featured, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('plan_id', 0),
            'name' => input('name', ''),
            'price' => input('price', ''),
            'description' => input('description', ''),
            'featured' => input('is_featured') ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Pricing plan updated.');
        redirect('/admin/services');
    }

    public function deletePlan(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM pricing_plans WHERE id = :id')->execute(['id' => (int) input('plan_id', 0)]);
        flash('success', 'Pricing plan deleted.');
        redirect('/admin/services');
    }

    public function updatePaymentStatus(): void
    {
        Auth::requireRole(['admin']);
        $status = (string) input('status', 'pending');
        $paymentId = (int) input('payment_id', 0);
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM payments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();
        if (! $payment) {
            flash('error', 'Payment not found.');
            redirect($this->redirectTarget('/admin/billing/payments'));
        }
        $db->prepare('UPDATE payments SET status = :status, notes = :notes, updated_at = NOW(), paid_at = IF(:status = "approved", NOW(), paid_at) WHERE id = :id')->execute([
            'status' => $status,
            'notes' => input('notes', ''),
            'id' => $paymentId,
        ]);
        if (!empty($payment['invoice_id'])) {
            $invoiceStatus = match ($status) {
                'approved' => 'paid',
                'refund' => 'refunded',
                'cancelled' => 'cancelled',
                default => 'unpaid',
            };
            $db->prepare('UPDATE invoices SET status = :status, updated_at = NOW() WHERE id = :id')->execute([
                'status' => $invoiceStatus,
                'id' => (int) $payment['invoice_id'],
            ]);
        }

        $payment['id'] = $paymentId;
        $this->syncVendorCommissionStatus($payment, $status);

        if ($status === 'approved') {
            $paymentEvent = $payment;
            $paymentEvent['status'] = $status;
            $paymentEvent['notes'] = (string) input('notes', '');

            do_action('onPaymentSuccess', [
                'payment_id' => $paymentId,
                'invoice_id' => ! empty($payment['invoice_id']) ? (int) $payment['invoice_id'] : null,
                'user_id' => ! empty($payment['user_id']) ? (int) $payment['user_id'] : null,
                'payment' => $paymentEvent,
            ]);
        }

        notify_user((int) ($payment['user_id'] ?? 0), 'payment', 'Payment status updated', 'Your payment has been marked as ' . $status . '.', '/client/payments');
        flash('success', 'Payment status updated.');
        redirect($this->redirectTarget('/admin/billing/payments'));
    }

    public function updateVendorStatus(): void
    {
        Auth::requireRole(['admin']);
        $vendorId = (int) input('vendor_id', 0);
        $status = (string) input('status', 'pending');
        $allowed = ['pending', 'approved', 'rejected', 'suspended', 'inactive'];
        if (! in_array($status, $allowed, true)) {
            $status = 'pending';
        }

        $db = Database::connection();
        $vendor = Content::vendorById($vendorId);
        if (! $vendor) {
            flash('error', 'Vendor not found.');
            redirect('/admin/marketplace/vendors');
        }

        $db->prepare('UPDATE vendors SET status = :status, admin_notes = :notes, verification_badge = :verification_badge, updated_at = NOW() WHERE id = :id')->execute([
            'id' => $vendorId,
            'status' => $status,
            'notes' => input('admin_notes', $vendor['admin_notes'] ?? ''),
            'verification_badge' => input('verification_badge') ? 1 : 0,
        ]);
        $db->prepare('UPDATE users SET status = :user_status, updated_at = NOW() WHERE id = :id')->execute([
            'id' => (int) $vendor['user_id'],
            'user_status' => in_array($status, ['approved', 'pending'], true) ? 'active' : $status,
        ]);
        $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'vendor.status', 'Vendor status changed to ' . $status . '.', [
            'verification_badge' => input('verification_badge') ? 1 : 0,
        ]);
        notify_user((int) $vendor['user_id'], 'vendor', 'Vendor application updated', 'Your vendor account status is now ' . ucwords($status) . '.', '/vendor/dashboard');
        flash('success', 'Vendor status updated.');
        redirect($this->redirectTarget('/admin/marketplace/vendors/' . $vendorId));
    }

    public function updateVendorProfile(): void
    {
        Auth::requireRole(['admin']);
        $vendorId = (int) input('vendor_id', 0);
        $vendor = Content::vendorById($vendorId);
        if (! $vendor) {
            flash('error', 'Vendor not found.');
            redirect('/admin/marketplace/vendors');
        }

        $db = Database::connection();
        $logo = upload_file('logo_file', 'assets/uploads/vendors') ?: ($vendor['logo_path'] ?? null);
        $banner = upload_file('banner_file', 'assets/uploads/vendors') ?: ($vendor['banner_path'] ?? null);
        $db->prepare('
            UPDATE vendors
            SET store_name = :store_name,
                display_name = :display_name,
                email = :email,
                phone = :phone,
                tax_gst = :tax_gst,
                commission_percent = :commission_percent,
                admin_notes = :admin_notes,
                updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => $vendorId,
            'store_name' => input('store_name', $vendor['store_name'] ?? ''),
            'display_name' => input('display_name', $vendor['display_name'] ?? ''),
            'email' => input('email', $vendor['email'] ?? ''),
            'phone' => input('phone', $vendor['phone'] ?? ''),
            'tax_gst' => input('tax_gst', $vendor['tax_gst'] ?? ''),
            'commission_percent' => (float) input('commission_percent', $vendor['commission_percent'] ?? $this->defaultVendorCommission()),
            'admin_notes' => input('admin_notes', $vendor['admin_notes'] ?? ''),
        ]);
        $db->prepare('
            UPDATE vendor_profiles
            SET business_name = :business_name,
                legal_name = :legal_name,
                short_bio = :short_bio,
                address_line1 = :address_line1,
                address_line2 = :address_line2,
                city = :city,
                state = :state,
                country = :country,
                postal_code = :postal_code,
                website = :website,
                support_email = :support_email,
                support_phone = :support_phone,
                logo_path = :logo_path,
                banner_path = :banner_path,
                updated_at = NOW()
            WHERE vendor_id = :vendor_id
        ')->execute([
            'vendor_id' => $vendorId,
            'business_name' => input('business_name', $vendor['business_name'] ?? ''),
            'legal_name' => input('legal_name', $vendor['legal_name'] ?? ''),
            'short_bio' => input('short_bio', $vendor['short_bio'] ?? ''),
            'address_line1' => input('address_line1', $vendor['address_line1'] ?? ''),
            'address_line2' => input('address_line2', $vendor['address_line2'] ?? ''),
            'city' => input('city', $vendor['city'] ?? ''),
            'state' => input('state', $vendor['state'] ?? ''),
            'country' => input('country', $vendor['country'] ?? ''),
            'postal_code' => input('postal_code', $vendor['postal_code'] ?? ''),
            'website' => input('website', $vendor['website'] ?? ''),
            'support_email' => input('support_email', $vendor['support_email'] ?? ''),
            'support_phone' => input('support_phone', $vendor['support_phone'] ?? ''),
            'logo_path' => $logo,
            'banner_path' => $banner,
        ]);
        $db->prepare('
            UPDATE vendor_payout_accounts
            SET payout_method = :payout_method,
                account_name = :account_name,
                account_number = :account_number,
                ifsc_swift = :ifsc_swift,
                upi_id = :upi_id,
                paypal_email = :paypal_email,
                notes = :notes,
                updated_at = NOW()
            WHERE vendor_id = :vendor_id
        ')->execute([
            'vendor_id' => $vendorId,
            'payout_method' => input('payout_method', $vendor['payout_method'] ?? 'bank_transfer'),
            'account_name' => input('account_name', $vendor['account_name'] ?? ''),
            'account_number' => input('account_number', $vendor['account_number'] ?? ''),
            'ifsc_swift' => input('ifsc_swift', $vendor['ifsc_swift'] ?? ''),
            'upi_id' => input('upi_id', $vendor['upi_id'] ?? ''),
            'paypal_email' => input('paypal_email', $vendor['paypal_email'] ?? ''),
            'notes' => input('payout_notes', $vendor['payout_notes'] ?? ''),
        ]);

        $this->logVendorActivity($vendorId, (int) (Auth::user()['id'] ?? 0), 'vendor.profile', 'Vendor profile and payout details updated by admin.');
        flash('success', 'Vendor profile updated.');
        redirect($this->redirectTarget('/admin/marketplace/vendors/' . $vendorId));
    }

    public function updateVendorPayoutStatus(): void
    {
        Auth::requireRole(['admin']);
        $payoutId = (int) input('payout_id', 0);
        $status = (string) input('status', 'requested');
        $db = Database::connection();
        $stmt = $db->prepare('SELECT * FROM vendor_payouts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $payoutId]);
        $payout = $stmt->fetch();
        if (! $payout) {
            flash('error', 'Payout record not found.');
            redirect('/admin/marketplace/payouts');
        }

        $db->prepare('
            UPDATE vendor_payouts
            SET status = :status,
                reference_number = :reference_number,
                admin_note = :admin_note,
                processed_at = CASE WHEN :status = "paid" THEN NOW() ELSE processed_at END,
                updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => $payoutId,
            'status' => $status,
            'reference_number' => input('reference_number', $payout['reference_number'] ?? ''),
            'admin_note' => input('admin_note', $payout['admin_note'] ?? ''),
        ]);

        $commissionStatus = match ($status) {
            'paid' => 'paid',
            'rejected', 'cancelled' => 'available',
            'processing' => 'processing',
            default => 'requested',
        };
        $db->prepare('
            UPDATE vendor_commissions
            SET payout_status = :payout_status, payout_id = :payout_id, updated_at = NOW()
            WHERE vendor_id = :vendor_id AND payout_status IN ("available", "requested", "processing")
        ')->execute([
            'payout_status' => $commissionStatus,
            'payout_id' => $payoutId,
            'vendor_id' => (int) $payout['vendor_id'],
        ]);

        $this->logVendorActivity((int) $payout['vendor_id'], (int) (Auth::user()['id'] ?? 0), 'vendor.payout', 'Vendor payout status updated to ' . $status . '.', [
            'payout_id' => $payoutId,
            'reference_number' => input('reference_number', ''),
        ]);
        flash('success', 'Vendor payout updated.');
        redirect($this->redirectTarget('/admin/marketplace/payouts'));
    }

    public function updateProductReviewStatus(): void
    {
        Auth::requireRole(['admin']);
        $reviewId = (int) input('review_id', 0);
        $status = (string) input('status', 'approved');
        Database::connection()->prepare('UPDATE product_reviews SET status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'id' => $reviewId,
            'status' => $status,
        ]);
        flash('success', 'Review status updated.');
        redirect($this->redirectTarget('/admin/marketplace/reviews'));
    }

    public function createInvoice(): void
    {
        Auth::requireRole(['admin']);
        $subtotal = (float) input('subtotal', 0);
        $gst = round($subtotal * 0.18, 2);
        $total = $subtotal + $gst;
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . random_int(100, 999);
        Database::connection()->prepare('
            INSERT INTO invoices (user_id, order_id, invoice_number, billing_name, gst_number, subtotal, gst_percent, gst_amount, total, due_date, status, created_at, updated_at)
            VALUES (:user_id, :order_id, :invoice_number, :billing_name, :gst_number, :subtotal, 18, :gst_amount, :total, :due_date, :status, NOW(), NOW())
        ')->execute([
            'user_id' => (int) input('user_id', 0),
            'order_id' => input('order_id') ?: null,
            'invoice_number' => $invoiceNumber,
            'billing_name' => input('billing_name', ''),
            'gst_number' => input('gst_number', ''),
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total' => $total,
            'due_date' => input('due_date', null),
            'status' => 'unpaid',
        ]);
        flash('success', 'GST-ready invoice created.');
        redirect($this->redirectTarget('/admin/billing/invoices'));
    }

    public function invoiceEdit(): void
    {
        Auth::requireRole(['admin']);
        $invoice = Content::invoice((int) input('id', 0));
        if (! $invoice) {
            flash('error', 'Invoice not found.');
            redirect('/admin/payments');
        }
        $this->render('admin/invoice-edit', 'Edit Invoice', [
            'invoice' => $invoice,
            'items' => Content::invoiceItems((int) $invoice['id']),
        ]);
    }

    public function updateInvoice(): void
    {
        Auth::requireRole(['admin']);
        $invoiceId = (int) input('invoice_id', 0);
        $db = Database::connection();
        $db->prepare('UPDATE invoices SET billing_name = :billing_name, gst_number = :gst_number, due_date = :due_date, status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'billing_name' => input('billing_name', ''),
            'gst_number' => input('gst_number', ''),
            'due_date' => input('due_date', null),
            'status' => input('status', 'unpaid'),
            'id' => $invoiceId,
        ]);
        $db->prepare('DELETE FROM invoice_items WHERE invoice_id = :invoice')->execute(['invoice' => $invoiceId]);
        $names = $_POST['item_name'] ?? [];
        $descriptions = $_POST['item_description'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $prices = $_POST['item_price'] ?? [];
        $subtotal = 0.0;
        $insert = $db->prepare('INSERT INTO invoice_items (invoice_id, item_name, description, quantity, unit_price, line_total, created_at, updated_at) VALUES (:invoice_id, :item_name, :description, :quantity, :unit_price, :line_total, NOW(), NOW())');
        foreach ($names as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $qty = max(1, (int) ($quantities[$index] ?? 1));
            $price = (float) ($prices[$index] ?? 0);
            $lineTotal = $qty * $price;
            $subtotal += $lineTotal;
            $insert->execute([
                'invoice_id' => $invoiceId,
                'item_name' => $name,
                'description' => $descriptions[$index] ?? '',
                'quantity' => $qty,
                'unit_price' => $price,
                'line_total' => $lineTotal,
            ]);
        }
        $gst = round($subtotal * 0.18, 2);
        $total = $subtotal + $gst;
        $db->prepare('UPDATE invoices SET subtotal = :subtotal, gst_amount = :gst_amount, total = :total, updated_at = NOW() WHERE id = :id')->execute([
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total' => $total,
            'id' => $invoiceId,
        ]);
        flash('success', 'Invoice updated.');
        redirect('/admin/invoice/edit?id=' . $invoiceId);
    }

    public function replyTicket(): void
    {
        Auth::requireRole(['admin']);
        $db = Database::connection();
        $ticketId = (int) input('ticket_id', 0);
        $status = input('status', 'answered');
        $db->prepare('UPDATE tickets SET status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'status' => $status,
            'id' => $ticketId,
        ]);
        $db->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, created_at) VALUES (:ticket_id, :sender_type, :sender_id, :message, NOW())')->execute([
            'ticket_id' => $ticketId,
            'sender_type' => 'admin',
            'sender_id' => Auth::user()['id'],
            'message' => input('message', ''),
        ]);
        flash('success', 'Ticket reply sent.');
        redirect('/admin/tickets?ticket=' . $ticketId);
    }

    public function sendBroadcast(): void
    {
        Auth::requireRole(['admin']);
        $db = Database::connection();
        $users = $db->query('SELECT id FROM users')->fetchAll();
        $stmt = $db->prepare('INSERT INTO notifications (user_id, type, title, body, action_url, created_at, updated_at) VALUES (:user_id, :type, :title, :body, :action_url, NOW(), NOW())');
        foreach ($users as $user) {
            $stmt->execute([
                'user_id' => $user['id'],
                'type' => input('type', 'broadcast'),
                'title' => input('title', ''),
                'body' => input('body', ''),
                'action_url' => input('action_url', ''),
            ]);
        }
        flash('success', 'Broadcast notification sent.');
        redirect($this->redirectTarget('/admin/marketing/campaigns'));
    }

    public function createCoupon(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('INSERT INTO coupons (code, discount_type, discount_value, is_active, created_at, updated_at) VALUES (:code, :discount_type, :discount_value, :is_active, NOW(), NOW())')->execute([
            'code' => strtoupper((string) input('code', '')),
            'discount_type' => input('discount_type', 'percent'),
            'discount_value' => (float) input('discount_value', 0),
            'is_active' => input('is_active') ? 1 : 0,
        ]);
        flash('success', 'Coupon created.');
        redirect($this->redirectTarget('/admin/marketing/coupons'));
    }

    public function updateReferral(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE referrals
            SET payout_status = :payout_status,
                notes = :notes,
                reward_balance = :reward_balance,
                updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('referral_id', 0),
            'payout_status' => input('payout_status', 'unpaid'),
            'notes' => trim((string) input('notes', '')),
            'reward_balance' => money_to_float(input('reward_balance', 0)),
        ]);

        flash('success', 'Referral updated.');
        redirect('/admin/marketing/referrals');
    }

    public function createProjectUpdate(): void
    {
        Auth::requireRole(['admin', 'developer']);
        Database::connection()->prepare('INSERT INTO project_updates (order_id, user_id, title, details, created_at) VALUES (:order_id, :user_id, :title, :details, NOW())')->execute([
            'order_id' => (int) input('order_id', 0),
            'user_id' => (int) input('user_id', 0),
            'title' => input('title', ''),
            'details' => input('details', ''),
        ]);
        flash('success', 'Project update added.');
        redirect($this->redirectTarget('/admin/projects/tasks'));
    }

    public function createProposal(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('INSERT INTO proposals (user_id, title, description, amount, valid_until, status, created_at, updated_at) VALUES (:user_id, :title, :description, :amount, :valid_until, :status, NOW(), NOW())')->execute([
            'user_id' => (int) input('user_id', 0),
            'title' => input('title', ''),
            'description' => input('description', ''),
            'amount' => (float) input('amount', 0),
            'valid_until' => input('valid_until', null),
            'status' => 'sent',
        ]);
        flash('success', 'Proposal generated.');
        redirect($this->redirectTarget('/admin/projects/proposals'));
    }

    public function proposalEdit(): void
    {
        Auth::requireRole(['admin']);
        $stmt = Database::connection()->prepare('SELECT proposals.*, users.first_name, users.last_name FROM proposals JOIN users ON users.id = proposals.user_id WHERE proposals.id = :id LIMIT 1');
        $stmt->execute(['id' => (int) input('id', 0)]);
        $proposal = $stmt->fetch();
        if (! $proposal) {
            flash('error', 'Proposal not found.');
            redirect('/admin/projects');
        }
        $this->render('admin/proposal-edit', 'Edit Proposal', ['proposal' => $proposal]);
    }

    public function updateProposal(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('UPDATE proposals SET title = :title, description = :description, amount = :amount, valid_until = :valid_until, status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'title' => input('title', ''),
            'description' => input('description', ''),
            'amount' => (float) input('amount', 0),
            'valid_until' => input('valid_until', null),
            'status' => input('status', 'sent'),
            'id' => (int) input('proposal_id', 0),
        ]);
        flash('success', 'Proposal updated.');
        redirect($this->redirectTarget('/admin/projects/proposals'));
    }

    public function createContract(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('INSERT INTO contracts (user_id, title, contract_body, status, created_at, updated_at) VALUES (:user_id, :title, :contract_body, :status, NOW(), NOW())')->execute([
            'user_id' => (int) input('user_id', 0),
            'title' => input('title', ''),
            'contract_body' => input('contract_body', ''),
            'status' => 'sent',
        ]);
        flash('success', 'Contract created.');
        redirect($this->redirectTarget('/admin/projects/contracts'));
    }

    public function updateContract(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('UPDATE contracts SET title = :title, contract_body = :contract_body, status = :status, updated_at = NOW() WHERE id = :id')->execute([
            'title' => input('title', ''),
            'contract_body' => input('contract_body', ''),
            'status' => input('status', 'sent'),
            'id' => (int) input('contract_id', 0),
        ]);
        flash('success', 'Contract updated.');
        redirect($this->redirectTarget('/admin/projects/contracts'));
    }

    public function updateOrder(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('UPDATE orders SET status = :status, progress_percent = :progress_percent, total = :total, updated_at = NOW() WHERE id = :id')->execute([
            'status' => input('status', 'pending'),
            'progress_percent' => (int) input('progress_percent', 0),
            'total' => (float) input('total', 0),
            'id' => (int) input('order_id', 0),
        ]);
        flash('success', 'Project updated.');
        redirect('/admin/projects');
    }

    public function deleteOrder(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM orders WHERE id = :id')->execute(['id' => (int) input('order_id', 0)]);
        flash('success', 'Project deleted.');
        redirect('/admin/projects');
    }

    public function updatePage(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE pages
            SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, meta_title = :meta_title, meta_description = :meta_description, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('page_id', 0),
            'title' => trim((string) input('title', '')),
            'slug' => slugify((string) input('slug', input('title', '')), 'page'),
            'excerpt' => trim((string) input('excerpt', '')),
            'content' => safe_rich_text((string) input('content', '')),
            'meta_title' => trim((string) input('meta_title', '')),
            'meta_description' => trim((string) input('meta_description', '')),
        ]);
        flash('success', 'Page updated.');
        redirect('/admin/content');
    }

    public function addBlog(): void
    {
        Auth::requireRole(['admin']);
        $title = trim((string) input('title', ''));
        Database::connection()->prepare('
            INSERT INTO blogs (title, slug, category, excerpt, content, meta_title, meta_description, status, published_at, created_at, updated_at)
            VALUES (:title, :slug, :category, :excerpt, :content, :meta_title, :meta_description, :status, :published_at, NOW(), NOW())
        ')->execute([
            'title' => $title,
            'slug' => slugify((string) input('slug', $title), 'blog'),
            'category' => trim((string) input('category', 'General')),
            'excerpt' => trim((string) input('excerpt', '')),
            'content' => safe_rich_text((string) input('content', '')),
            'meta_title' => trim((string) input('meta_title', $title)),
            'meta_description' => trim((string) input('meta_description', '')),
            'status' => (string) input('status', 'draft'),
            'published_at' => input('status', 'draft') === 'published' ? date('Y-m-d H:i:s') : null,
        ]);
        flash('success', 'Blog post added.');
        redirect('/admin/content');
    }

    public function updateBlog(): void
    {
        Auth::requireRole(['admin']);
        $status = (string) input('status', 'draft');
        Database::connection()->prepare('
            UPDATE blogs
            SET title = :title, slug = :slug, category = :category, excerpt = :excerpt, content = :content,
                meta_title = :meta_title, meta_description = :meta_description, status = :status,
                published_at = CASE
                    WHEN :status = "published" AND published_at IS NULL THEN NOW()
                    WHEN :status <> "published" THEN NULL
                    ELSE published_at
                END,
                updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('blog_id', 0),
            'title' => trim((string) input('title', '')),
            'slug' => slugify((string) input('slug', input('title', '')), 'blog'),
            'category' => trim((string) input('category', 'General')),
            'excerpt' => trim((string) input('excerpt', '')),
            'content' => safe_rich_text((string) input('content', '')),
            'meta_title' => trim((string) input('meta_title', '')),
            'meta_description' => trim((string) input('meta_description', '')),
            'status' => $status,
        ]);
        flash('success', 'Blog post updated.');
        redirect('/admin/content');
    }

    public function deleteBlog(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM blogs WHERE id = :id')->execute(['id' => (int) input('blog_id', 0)]);
        flash('success', 'Blog post deleted.');
        redirect('/admin/content');
    }

    public function addFaq(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO faqs (question, answer, sort_order, created_at, updated_at)
            VALUES (:question, :answer, :sort_order, NOW(), NOW())
        ')->execute([
            'question' => trim((string) input('question', '')),
            'answer' => trim((string) input('answer', '')),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'FAQ item added.');
        redirect('/admin/content');
    }

    public function updateFaq(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE faqs
            SET question = :question, answer = :answer, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('faq_id', 0),
            'question' => trim((string) input('question', '')),
            'answer' => trim((string) input('answer', '')),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'FAQ item updated.');
        redirect('/admin/content');
    }

    public function deleteFaq(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM faqs WHERE id = :id')->execute(['id' => (int) input('faq_id', 0)]);
        flash('success', 'FAQ item deleted.');
        redirect('/admin/content');
    }

    public function addCareer(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO careers (title, summary, location, employment_type, status, sort_order, created_at, updated_at)
            VALUES (:title, :summary, :location, :employment_type, :status, :sort_order, NOW(), NOW())
        ')->execute([
            'title' => trim((string) input('title', '')),
            'summary' => trim((string) input('summary', '')),
            'location' => trim((string) input('location', 'Remote')),
            'employment_type' => trim((string) input('employment_type', 'Full Time')),
            'status' => trim((string) input('status', 'open')),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Career role added.');
        redirect('/admin/content');
    }

    public function updateCareer(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE careers
            SET title = :title, summary = :summary, location = :location, employment_type = :employment_type, status = :status, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('career_id', 0),
            'title' => trim((string) input('title', '')),
            'summary' => trim((string) input('summary', '')),
            'location' => trim((string) input('location', 'Remote')),
            'employment_type' => trim((string) input('employment_type', 'Full Time')),
            'status' => trim((string) input('status', 'open')),
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Career role updated.');
        redirect('/admin/content');
    }

    public function deleteCareer(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM careers WHERE id = :id')->execute(['id' => (int) input('career_id', 0)]);
        flash('success', 'Career role deleted.');
        redirect('/admin/content');
    }

    public function addPortfolioProject(): void
    {
        Auth::requireRole(['admin']);
        $title = trim((string) input('title', ''));
        Database::connection()->prepare('
            INSERT INTO portfolio_projects (title, slug, category, client_name, summary, tech_stack, is_featured, sort_order, created_at, updated_at)
            VALUES (:title, :slug, :category, :client_name, :summary, :tech_stack, :is_featured, :sort_order, NOW(), NOW())
        ')->execute([
            'title' => $title,
            'slug' => slugify((string) input('slug', $title), 'project'),
            'category' => trim((string) input('category', 'Web Platform')),
            'client_name' => trim((string) input('client_name', '')),
            'summary' => trim((string) input('summary', '')),
            'tech_stack' => trim((string) input('tech_stack', '')),
            'is_featured' => input('is_featured') ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Portfolio project added.');
        redirect('/admin/content');
    }

    public function updatePortfolioProject(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE portfolio_projects
            SET title = :title, slug = :slug, category = :category, client_name = :client_name, summary = :summary, tech_stack = :tech_stack, is_featured = :is_featured, sort_order = :sort_order, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('project_id', 0),
            'title' => trim((string) input('title', '')),
            'slug' => slugify((string) input('slug', input('title', '')), 'project'),
            'category' => trim((string) input('category', 'Web Platform')),
            'client_name' => trim((string) input('client_name', '')),
            'summary' => trim((string) input('summary', '')),
            'tech_stack' => trim((string) input('tech_stack', '')),
            'is_featured' => input('is_featured') ? 1 : 0,
            'sort_order' => (int) input('sort_order', 0),
        ]);
        flash('success', 'Portfolio project updated.');
        redirect('/admin/content');
    }

    public function deletePortfolioProject(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM portfolio_projects WHERE id = :id')->execute(['id' => (int) input('project_id', 0)]);
        flash('success', 'Portfolio project deleted.');
        redirect('/admin/content');
    }

    public function addTeamMember(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            INSERT INTO team_members (user_id, name, role, email, created_at, updated_at)
            VALUES (:user_id, :name, :role, :email, NOW(), NOW())
        ')->execute([
            'user_id' => (int) (Auth::user()['id'] ?? 0),
            'name' => trim((string) input('name', '')),
            'role' => trim((string) input('role', '')),
            'email' => trim((string) input('email', '')),
        ]);
        flash('success', 'Team member added.');
        redirect('/admin/content');
    }

    public function updateTeamMember(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('
            UPDATE team_members
            SET name = :name, role = :role, email = :email, updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) input('team_id', 0),
            'name' => trim((string) input('name', '')),
            'role' => trim((string) input('role', '')),
            'email' => trim((string) input('email', '')),
        ]);
        flash('success', 'Team member updated.');
        redirect('/admin/content');
    }

    public function deleteTeamMember(): void
    {
        Auth::requireRole(['admin']);
        Database::connection()->prepare('DELETE FROM team_members WHERE id = :id')->execute(['id' => (int) input('team_id', 0)]);
        flash('success', 'Team member deleted.');
        redirect('/admin/content');
    }

    public function updateProfile(): void
    {
        Auth::requireRole(['admin']);
        $user = Auth::user();
        $db = Database::connection();
        $params = [
            'id' => (int) $user['id'],
            'first_name' => input('first_name', ''),
            'last_name' => input('last_name', ''),
            'email' => input('email', ''),
            'phone' => input('phone', ''),
        ];
        $db->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, updated_at = NOW() WHERE id = :id')->execute($params);
        if ((string) input('password', '') !== '') {
            $db->prepare('UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id')->execute([
                'password' => password_hash((string) input('password', ''), PASSWORD_DEFAULT),
                'id' => (int) $user['id'],
            ]);
        }
        flash('success', 'Profile updated.');
        redirect('/admin/profile');
    }

    public function markNotificationsRead(): void
    {
        Auth::requireRole(['admin']);
        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);
        $notificationId = (int) input('notification_id', 0);
        $redirect = (string) input('redirect_to', '/admin/notifications');
        $db = Database::connection();

        if ($notificationId > 0) {
            $stmt = $db->prepare('UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)');
            $stmt->execute([
                'id' => $notificationId,
                'user_id' => $userId,
            ]);
        } else {
            $stmt = $db->prepare('UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE (user_id = :user_id OR user_id IS NULL) AND read_at IS NULL');
            $stmt->execute([
                'user_id' => $userId,
            ]);
        }

        flash('success', 'Notifications updated.');
        redirect($redirect ?: '/admin/notifications');
    }

    public function export(): void
    {
        Auth::requireRole(['admin']);
        $type = (string) input('type', '');
        $query = trim((string) input('q', ''));

        switch ($type) {
            case 'services':
                $rows = $this->searchRows(Content::services(), ['name', 'short_description', 'price_label', 'slug'], $query);
                $this->exportCsv('services.csv', ['ID', 'Name', 'Slug', 'Price Label', 'Description', 'Sort Order'], array_map(fn (array $row) => [
                    $row['id'],
                    $row['name'],
                    $row['slug'],
                    $row['price_label'],
                    $row['short_description'],
                    $row['sort_order'],
                ], $rows));
                break;
            case 'plans':
                $rows = $this->searchRows(Content::plans(), ['name', 'price', 'description'], $query);
                $this->exportCsv('pricing-plans.csv', ['ID', 'Name', 'Price', 'Description', 'Featured', 'Sort Order'], array_map(fn (array $row) => [
                    $row['id'],
                    $row['name'],
                    $row['price'],
                    $row['description'],
                    $row['is_featured'],
                    $row['sort_order'],
                ], $rows));
                break;
            case 'payments':
                $rows = $this->searchRows(Content::allPayments(), ['invoice_number', 'first_name', 'last_name', 'transaction_id', 'gateway', 'status', 'notes'], $query);
                $this->exportCsv('payments.csv', ['ID', 'Client', 'Invoice', 'Gateway', 'Transaction ID', 'Amount', 'Status', 'Proof', 'Notes'], array_map(fn (array $row) => [
                    $row['id'],
                    trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    $row['invoice_number'] ?? '',
                    $row['gateway'] ?? '',
                    $row['transaction_id'] ?? '',
                    $row['amount'] ?? '',
                    $row['status'] ?? '',
                    $row['proof_path'] ?? '',
                    $row['notes'] ?? '',
                ], $rows));
                break;
            case 'projects':
                $rows = $this->searchRows(Content::allOrders(), ['order_number', 'first_name', 'last_name', 'service_name', 'display_name', 'status'], $query);
                $this->exportCsv('projects.csv', ['ID', 'Order Number', 'Client', 'Service', 'Status', 'Progress', 'Total'], array_map(fn (array $row) => [
                    $row['id'],
                    $row['order_number'],
                    trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
                    $row['display_name'] ?? $row['service_name'] ?? '',
                    $row['status'],
                    $row['progress_percent'],
                    $row['total'],
                ], $rows));
                break;
            case 'products':
                $rows = $this->searchRows(Content::products(), ['name', 'category', 'product_type', 'short_description', 'price_label', 'version_label', 'status'], $query);
                $this->exportCsv('products.csv', ['ID', 'Name', 'Category', 'Type', 'Price', 'Version', 'Status'], array_map(fn (array $row) => [
                    $row['id'],
                    $row['name'],
                    $row['category'],
                    $row['product_type'],
                    $row['price_label'],
                    $row['version_label'],
                    $row['status'],
                ], $rows));
                break;
            default:
                flash('error', 'Export type not found.');
                redirect('/admin');
        }
    }
}
