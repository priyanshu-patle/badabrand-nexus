<?php

namespace App\Core;

use PDO;

class SystemHooks
{
    private static bool $booted = false;

    private static function ensureCoreBusinessSchema(): void
    {
        $db = Database::connection();

        if (self::tableExists($db, 'users')) {
            $userColumns = self::tableColumns($db, 'users');
            $userRequired = [
                'terms_accepted_at' => 'ALTER TABLE users ADD COLUMN terms_accepted_at DATETIME NULL AFTER status',
                'theme_preference' => 'ALTER TABLE users ADD COLUMN theme_preference VARCHAR(40) NULL AFTER terms_accepted_at',
            ];

            foreach ($userRequired as $column => $statement) {
                if (! in_array($column, $userColumns, true)) {
                    $db->exec($statement);
                }
            }
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NULL,
                name VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                category VARCHAR(120) NOT NULL DEFAULT 'software',
                product_type VARCHAR(60) NOT NULL DEFAULT 'software',
                short_description TEXT NULL,
                description LONGTEXT NULL,
                features_text LONGTEXT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0,
                price_label VARCHAR(190) NULL,
                version_label VARCHAR(120) NULL,
                thumbnail_path VARCHAR(255) NULL,
                download_link VARCHAR(255) NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'active',
                approval_status VARCHAR(40) NOT NULL DEFAULT 'approved',
                commission_percent DECIMAL(5,2) NULL,
                requires_review TINYINT(1) NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                service_id BIGINT UNSIGNED NULL,
                product_id BIGINT UNSIGNED NULL,
                vendor_id BIGINT UNSIGNED NULL,
                order_type VARCHAR(60) NOT NULL DEFAULT 'service',
                item_name VARCHAR(190) NULL,
                pricing_plan_name VARCHAR(190) NULL,
                notes TEXT NULL,
                receipt_number VARCHAR(120) NULL,
                expected_delivery DATE NULL,
                order_number VARCHAR(120) NOT NULL UNIQUE,
                status VARCHAR(40) NOT NULL DEFAULT 'pending',
                total DECIMAL(12,2) NOT NULL DEFAULT 0,
                progress_percent INT NOT NULL DEFAULT 0,
                due_at DATETIME NULL,
                commission_percent DECIMAL(5,2) NULL,
                platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                vendor_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                payout_status VARCHAR(40) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                order_id BIGINT UNSIGNED NULL,
                invoice_number VARCHAR(120) NOT NULL UNIQUE,
                billing_name VARCHAR(190) NULL,
                gst_number VARCHAR(120) NULL,
                subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
                gst_percent DECIMAL(5,2) NOT NULL DEFAULT 18,
                gst_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                total DECIMAL(12,2) NOT NULL DEFAULT 0,
                due_date DATE NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'unpaid',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS invoice_items (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                invoice_id BIGINT UNSIGNED NOT NULL,
                item_name VARCHAR(190) NOT NULL,
                description TEXT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
                line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                invoice_id BIGINT UNSIGNED NULL,
                gateway VARCHAR(60) NOT NULL DEFAULT 'manual_bank',
                transaction_id VARCHAR(190) NULL,
                proof_path VARCHAR(255) NULL,
                notes TEXT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                currency VARCHAR(10) NOT NULL DEFAULT 'INR',
                status VARCHAR(40) NOT NULL DEFAULT 'pending',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS tickets (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                subject VARCHAR(190) NOT NULL,
                priority VARCHAR(40) NOT NULL DEFAULT 'medium',
                status VARCHAR(40) NOT NULL DEFAULT 'open',
                message LONGTEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS ticket_messages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ticket_id BIGINT UNSIGNED NOT NULL,
                sender_type VARCHAR(30) NOT NULL DEFAULT 'customer',
                sender_id BIGINT UNSIGNED NULL,
                message LONGTEXT NOT NULL,
                created_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS project_updates (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                details LONGTEXT NULL,
                created_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS proposals (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                description LONGTEXT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                valid_until DATE NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'draft',
                submitted_by_customer TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS contracts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                contract_body LONGTEXT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'draft',
                signature_name VARCHAR(190) NULL,
                signed_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS user_files (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                order_id BIGINT UNSIGNED NULL,
                file_name VARCHAR(190) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                file_type VARCHAR(80) NULL,
                created_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS contacts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(190) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(60) NULL,
                service VARCHAR(190) NULL,
                message LONGTEXT NULL,
                status VARCHAR(40) NOT NULL DEFAULT 'new',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                code VARCHAR(80) NOT NULL UNIQUE,
                discount_type VARCHAR(40) NOT NULL DEFAULT 'percent',
                discount_value DECIMAL(12,2) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS email_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                recipient_email VARCHAR(190) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                html_body LONGTEXT NULL,
                text_body LONGTEXT NULL,
                related_type VARCHAR(80) NULL,
                related_id BIGINT UNSIGNED NULL,
                delivery_status VARCHAR(40) NOT NULL DEFAULT 'queued',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        self::ensureSettings([
            'marketplace_enabled' => '1',
            'documentation_enabled' => '1',
            'module_system_enabled' => '1',
        ]);
    }

    private static function tableExists(PDO $db, string $table): bool
    {
        $stmt = $db->prepare('SHOW TABLES LIKE :table');
        $stmt->execute(['table' => $table]);

        return (bool) $stmt->fetchColumn();
    }

    private static function tableColumns(PDO $db, string $table): array
    {
        if (! self::tableExists($db, $table)) {
            return [];
        }

        $columns = [];
        foreach ($db->query('SHOW COLUMNS FROM ' . $table)->fetchAll() as $column) {
            $columns[] = (string) ($column['Field'] ?? '');
        }

        return $columns;
    }

    private static function ensureSettings(array $defaults): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM settings WHERE setting_key = :key');
        $insert = $db->prepare('INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())');

        foreach ($defaults as $key => $value) {
            $stmt->execute(['key' => $key]);
            if ((int) $stmt->fetchColumn() > 0) {
                continue;
            }

            $insert->execute([
                'key' => $key,
                'value' => (string) $value,
            ]);
        }
    }

    private static function ensureReferralSchema(): void
    {
        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS referrals (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL UNIQUE,
                referral_code VARCHAR(64) NOT NULL UNIQUE,
                referred_by_user_id BIGINT UNSIGNED NULL,
                referred_by_code VARCHAR(64) NULL,
                total_signups INT NOT NULL DEFAULT 0,
                total_earned DECIMAL(12,2) NOT NULL DEFAULT 0,
                reward_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
                payout_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
                notes TEXT NULL,
                last_referred_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $columns = self::tableColumns($db, 'referrals');

        $requiredColumns = [
            'user_id' => 'ALTER TABLE referrals ADD COLUMN user_id BIGINT UNSIGNED NOT NULL UNIQUE',
            'referral_code' => 'ALTER TABLE referrals ADD COLUMN referral_code VARCHAR(64) NOT NULL UNIQUE',
            'referred_by_user_id' => 'ALTER TABLE referrals ADD COLUMN referred_by_user_id BIGINT UNSIGNED NULL',
            'referred_by_code' => 'ALTER TABLE referrals ADD COLUMN referred_by_code VARCHAR(64) NULL',
            'total_signups' => 'ALTER TABLE referrals ADD COLUMN total_signups INT NOT NULL DEFAULT 0',
            'total_earned' => 'ALTER TABLE referrals ADD COLUMN total_earned DECIMAL(12,2) NOT NULL DEFAULT 0',
            'reward_balance' => 'ALTER TABLE referrals ADD COLUMN reward_balance DECIMAL(12,2) NOT NULL DEFAULT 0',
            'payout_status' => 'ALTER TABLE referrals ADD COLUMN payout_status VARCHAR(30) NOT NULL DEFAULT "unpaid"',
            'notes' => 'ALTER TABLE referrals ADD COLUMN notes TEXT NULL',
            'last_referred_at' => 'ALTER TABLE referrals ADD COLUMN last_referred_at DATETIME NULL',
            'created_at' => 'ALTER TABLE referrals ADD COLUMN created_at DATETIME NULL',
            'updated_at' => 'ALTER TABLE referrals ADD COLUMN updated_at DATETIME NULL',
        ];

        foreach ($requiredColumns as $column => $statement) {
            if (! in_array($column, $columns, true)) {
                $db->exec($statement);
            }
        }
    }

    private static function ensureNotificationsSchema(): void
    {
        $db = Database::connection();
        $db->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                type VARCHAR(60) NOT NULL DEFAULT 'system',
                title VARCHAR(190) NOT NULL,
                body TEXT NULL,
                action_url VARCHAR(255) NULL,
                read_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $columns = self::tableColumns($db, 'notifications');
        $requiredColumns = [
            'user_id' => 'ALTER TABLE notifications ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id',
            'type' => 'ALTER TABLE notifications ADD COLUMN type VARCHAR(60) NOT NULL DEFAULT "system" AFTER user_id',
            'title' => 'ALTER TABLE notifications ADD COLUMN title VARCHAR(190) NOT NULL AFTER type',
            'body' => 'ALTER TABLE notifications ADD COLUMN body TEXT NULL AFTER title',
            'action_url' => 'ALTER TABLE notifications ADD COLUMN action_url VARCHAR(255) NULL AFTER body',
            'read_at' => 'ALTER TABLE notifications ADD COLUMN read_at DATETIME NULL AFTER action_url',
            'created_at' => 'ALTER TABLE notifications ADD COLUMN created_at DATETIME NULL AFTER read_at',
            'updated_at' => 'ALTER TABLE notifications ADD COLUMN updated_at DATETIME NULL AFTER created_at',
        ];

        foreach ($requiredColumns as $column => $statement) {
            if (! in_array($column, $columns, true)) {
                $db->exec($statement);
            }
        }
    }

    private static function ensureVendorSchema(): void
    {
        $db = Database::connection();

        $userRoleColumn = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
        $userRoleType = strtolower((string) ($userRoleColumn['Type'] ?? ''));
        if ($userRoleType !== '' && ! str_contains($userRoleType, "'vendor'")) {
            $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','customer','developer','vendor') NOT NULL DEFAULT 'customer'");
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendors (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL UNIQUE,
                store_name VARCHAR(190) NOT NULL,
                display_name VARCHAR(190) NOT NULL,
                slug VARCHAR(190) NOT NULL UNIQUE,
                email VARCHAR(190) NULL,
                phone VARCHAR(40) NULL,
                tax_gst VARCHAR(120) NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'pending',
                commission_percent DECIMAL(5,2) NULL,
                verification_badge TINYINT(1) NOT NULL DEFAULT 0,
                joined_at DATETIME NULL,
                approved_at DATETIME NULL,
                suspended_at DATETIME NULL,
                admin_notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_profiles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL UNIQUE,
                business_name VARCHAR(190) NULL,
                legal_name VARCHAR(190) NULL,
                short_bio TEXT NULL,
                address_line1 VARCHAR(190) NULL,
                address_line2 VARCHAR(190) NULL,
                city VARCHAR(120) NULL,
                state VARCHAR(120) NULL,
                country VARCHAR(120) NULL,
                postal_code VARCHAR(40) NULL,
                website VARCHAR(255) NULL,
                support_email VARCHAR(190) NULL,
                support_phone VARCHAR(40) NULL,
                logo_path VARCHAR(255) NULL,
                banner_path VARCHAR(255) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_payout_accounts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL UNIQUE,
                payout_method VARCHAR(60) NOT NULL DEFAULT 'bank_transfer',
                account_name VARCHAR(190) NULL,
                account_number VARCHAR(120) NULL,
                ifsc_swift VARCHAR(80) NULL,
                upi_id VARCHAR(120) NULL,
                paypal_email VARCHAR(190) NULL,
                notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_commissions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL,
                order_id BIGINT UNSIGNED NOT NULL,
                invoice_id BIGINT UNSIGNED NULL,
                payment_id BIGINT UNSIGNED NULL,
                payout_id BIGINT UNSIGNED NULL,
                product_id BIGINT UNSIGNED NULL,
                gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
                commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                vendor_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                payout_status VARCHAR(30) NOT NULL DEFAULT 'pending',
                available_at DATETIME NULL,
                paid_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_payouts (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL,
                request_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                approved_amount DECIMAL(12,2) NULL,
                currency VARCHAR(10) NOT NULL DEFAULT 'INR',
                status VARCHAR(30) NOT NULL DEFAULT 'requested',
                reference_number VARCHAR(120) NULL,
                admin_note TEXT NULL,
                payout_note TEXT NULL,
                requested_at DATETIME NULL,
                processed_at DATETIME NULL,
                paid_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_documents (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL,
                document_type VARCHAR(80) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'pending',
                review_notes TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS vendor_activity_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                vendor_id BIGINT UNSIGNED NOT NULL,
                actor_user_id BIGINT UNSIGNED NULL,
                action VARCHAR(120) NOT NULL,
                description TEXT NULL,
                context LONGTEXT NULL,
                created_at DATETIME NULL
            )
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS product_reviews (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                product_id BIGINT UNSIGNED NOT NULL,
                vendor_id BIGINT UNSIGNED NULL,
                user_id BIGINT UNSIGNED NULL,
                rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
                title VARCHAR(190) NULL,
                review TEXT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'approved',
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ");

        $productColumns = self::tableColumns($db, 'products');
        $productRequired = [
            'vendor_id' => 'ALTER TABLE products ADD COLUMN vendor_id BIGINT UNSIGNED NULL AFTER id',
            'approval_status' => 'ALTER TABLE products ADD COLUMN approval_status VARCHAR(30) NOT NULL DEFAULT "approved" AFTER status',
            'commission_percent' => 'ALTER TABLE products ADD COLUMN commission_percent DECIMAL(5,2) NULL AFTER price',
            'requires_review' => 'ALTER TABLE products ADD COLUMN requires_review TINYINT(1) NOT NULL DEFAULT 0 AFTER approval_status',
        ];

        foreach ($productRequired as $column => $statement) {
            if (! in_array($column, $productColumns, true)) {
                $db->exec($statement);
            }
        }

        $orderColumns = self::tableColumns($db, 'orders');
        $orderRequired = [
            'vendor_id' => 'ALTER TABLE orders ADD COLUMN vendor_id BIGINT UNSIGNED NULL AFTER user_id',
            'commission_percent' => 'ALTER TABLE orders ADD COLUMN commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER total',
            'platform_fee_amount' => 'ALTER TABLE orders ADD COLUMN platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER commission_percent',
            'vendor_net_amount' => 'ALTER TABLE orders ADD COLUMN vendor_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER platform_fee_amount',
            'payout_status' => 'ALTER TABLE orders ADD COLUMN payout_status VARCHAR(30) NOT NULL DEFAULT "pending" AFTER vendor_net_amount',
        ];

        foreach ($orderRequired as $column => $statement) {
            if (! in_array($column, $orderColumns, true)) {
                $db->exec($statement);
            }
        }

        $vendorCommissionColumns = self::tableColumns($db, 'vendor_commissions');
        $vendorCommissionRequired = [
            'platform_fee_amount' => 'ALTER TABLE vendor_commissions ADD COLUMN platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER commission_amount',
        ];

        foreach ($vendorCommissionRequired as $column => $statement) {
            if (! in_array($column, $vendorCommissionColumns, true)) {
                $db->exec($statement);
            }
        }

        $vendorCommissionColumns = self::tableColumns($db, 'vendor_commissions');
        if (in_array('commission_amount', $vendorCommissionColumns, true) && in_array('platform_fee_amount', $vendorCommissionColumns, true)) {
            $db->exec('UPDATE vendor_commissions SET platform_fee_amount = commission_amount WHERE platform_fee_amount = 0 AND commission_amount > 0');
        }

        $vendorPayoutColumns = self::tableColumns($db, 'vendor_payouts');
        $vendorPayoutRequired = [
            'currency' => 'ALTER TABLE vendor_payouts ADD COLUMN currency VARCHAR(10) NOT NULL DEFAULT "INR" AFTER approved_amount',
            'admin_note' => 'ALTER TABLE vendor_payouts ADD COLUMN admin_note TEXT NULL AFTER reference_number',
            'processed_at' => 'ALTER TABLE vendor_payouts ADD COLUMN processed_at DATETIME NULL AFTER requested_at',
        ];

        foreach ($vendorPayoutRequired as $column => $statement) {
            if (! in_array($column, $vendorPayoutColumns, true)) {
                $db->exec($statement);
            }
        }

        $vendorPayoutColumns = self::tableColumns($db, 'vendor_payouts');
        if (in_array('payout_note', $vendorPayoutColumns, true) && in_array('admin_note', $vendorPayoutColumns, true)) {
            $db->exec('UPDATE vendor_payouts SET admin_note = payout_note WHERE (admin_note IS NULL OR admin_note = "") AND payout_note IS NOT NULL AND payout_note <> ""');
        }

        self::ensureSettings([
            'vendor_enabled' => '1',
            'vendor_auto_approve' => '0',
            'vendor_default_commission' => '15',
            'vendor_product_requires_review' => '1',
            'vendor_minimum_payout' => '1000',
        ]);
    }

    private static function issueReferralCode(array $user): string
    {
        $seed = trim((string) ($user['client_id'] ?? '')) ?: ('USR' . ($user['id'] ?? random_int(1000, 9999)));
        $seed = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $seed) ?: 'BBT');

        return substr($seed, 0, 12) . '-' . strtoupper(substr(md5((string) microtime(true) . random_int(1000, 9999)), 0, 6));
    }

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;
        self::ensureCoreBusinessSchema();
        ActivityLog::ensureSchema();
        self::ensureNotificationsSchema();
        self::ensureReferralSchema();
        self::ensureVendorSchema();

        add_action('onUserRegister', static function (array $payload): void {
            $user = $payload['user'] ?? [];
            $db = Database::connection();
            $userId = isset($payload['user_id']) ? (int) $payload['user_id'] : (int) ($user['id'] ?? 0);
            $referralCode = strtoupper(trim((string) ($payload['referral_code'] ?? '')));
            $rewardAmount = (float) app_setting('referral_reward_amount', '500');

            if ($userId > 0) {
                $existing = $db->prepare('SELECT id, referral_code FROM referrals WHERE user_id = :user LIMIT 1');
                $existing->execute(['user' => $userId]);
                $current = $existing->fetch();

                $ownCode = $current['referral_code'] ?? self::issueReferralCode($user);
                if ($current) {
                    $db->prepare('UPDATE referrals SET referral_code = :code, updated_at = NOW() WHERE id = :id')->execute([
                        'code' => $ownCode,
                        'id' => (int) $current['id'],
                    ]);
                } else {
                    $db->prepare('
                        INSERT INTO referrals (user_id, referral_code, payout_status, created_at, updated_at)
                        VALUES (:user_id, :referral_code, :payout_status, NOW(), NOW())
                    ')->execute([
                        'user_id' => $userId,
                        'referral_code' => $ownCode,
                        'payout_status' => 'unpaid',
                    ]);
                }

                if ($referralCode !== '' && $rewardAmount > 0) {
                    $sponsor = $db->prepare('SELECT id, user_id, referral_code FROM referrals WHERE referral_code = :code LIMIT 1');
                    $sponsor->execute(['code' => $referralCode]);
                    $sponsorRecord = $sponsor->fetch();

                    if ($sponsorRecord && (int) ($sponsorRecord['user_id'] ?? 0) !== $userId) {
                        $db->prepare('
                            UPDATE referrals
                            SET referred_by_user_id = :referred_by_user_id,
                                referred_by_code = :referred_by_code,
                                updated_at = NOW()
                            WHERE user_id = :user_id
                        ')->execute([
                            'user_id' => $userId,
                            'referred_by_user_id' => (int) ($sponsorRecord['user_id'] ?? 0),
                            'referred_by_code' => $referralCode,
                        ]);

                        $db->prepare('
                            UPDATE referrals
                            SET total_signups = total_signups + 1,
                                total_earned = total_earned + :amount,
                                reward_balance = reward_balance + :amount,
                                payout_status = CASE WHEN payout_status = "paid" THEN "partially_paid" ELSE payout_status END,
                                last_referred_at = NOW(),
                                updated_at = NOW()
                            WHERE id = :id
                        ')->execute([
                            'amount' => $rewardAmount,
                            'id' => (int) ($sponsorRecord['id'] ?? 0),
                        ]);
                    }
                }
            }

            ActivityLog::record(
                'user.registered',
                'New user registered: ' . trim((string) ($user['first_name'] ?? '') . ' ' . (string) ($user['last_name'] ?? '')),
                $payload,
                'user',
                $userId > 0 ? $userId : null,
                $userId > 0 ? $userId : null,
                (string) ($user['email'] ?? '')
            );
        });

        add_action('onUserLogin', static function (array $payload): void {
            $user = $payload['user'] ?? [];
            ActivityLog::record(
                'user.login',
                'User logged in: ' . (string) ($user['email'] ?? 'Unknown account'),
                $payload,
                'user',
                isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                (string) ($user['email'] ?? '')
            );
        });

        add_action('onOrderCreated', static function (array $payload): void {
            ActivityLog::record(
                'order.created',
                'Order created: ' . (string) ($payload['order_number'] ?? 'Order'),
                $payload,
                'order',
                isset($payload['order_id']) ? (int) $payload['order_id'] : null,
                isset($payload['user']['id']) ? (int) $payload['user']['id'] : null,
                (string) ($payload['user']['email'] ?? '')
            );
        });

        add_action('onPaymentSuccess', static function (array $payload): void {
            ActivityLog::record(
                'payment.success',
                'Payment approved for invoice ' . (string) ($payload['payment']['invoice_id'] ?? $payload['invoice_id'] ?? 'manual'),
                $payload,
                'payment',
                isset($payload['payment_id']) ? (int) $payload['payment_id'] : null,
                isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                (string) ($payload['payment']['transaction_id'] ?? '')
            );
        });

        add_action('onTicketCreated', static function (array $payload): void {
            ActivityLog::record(
                'ticket.created',
                'Support ticket created: ' . (string) ($payload['subject'] ?? 'Untitled ticket'),
                $payload,
                'ticket',
                isset($payload['ticket_id']) ? (int) $payload['ticket_id'] : null,
                isset($payload['user_id']) ? (int) $payload['user_id'] : null,
                (string) ($payload['user']['email'] ?? '')
            );
        });

        add_action('onModuleInstall', static function (array $payload): void {
            ActivityLog::record(
                'module.installed',
                'Module installed: ' . (string) ($payload['name'] ?? $payload['slug'] ?? 'module'),
                $payload,
                'module',
                null,
                null,
                (string) ($payload['slug'] ?? '')
            );
        });
    }
}
