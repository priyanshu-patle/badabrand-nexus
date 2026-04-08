<?php

namespace App\Core;

use PDO;
use Throwable;

class InstallerService
{
    private const SESSION_KEY = '_installer';

    private const SUCCESS_KEY = '_installer_success';

    public static function steps(): array
    {
        return [
            'welcome' => ['label' => 'Welcome', 'description' => 'Product intro and install start'],
            'requirements' => ['label' => 'Requirements', 'description' => 'Server compatibility and file permissions'],
            'site' => ['label' => 'Site Setup', 'description' => 'Core app identity and environment'],
            'database' => ['label' => 'Database', 'description' => 'Connection details and schema import'],
            'admin' => ['label' => 'Admin Account', 'description' => 'Create your first super admin'],
            'branding' => ['label' => 'Branding', 'description' => 'Company identity and design defaults'],
            'modules' => ['label' => 'Modules & Demo', 'description' => 'Feature toggles and demo content'],
            'finalize' => ['label' => 'Finalize', 'description' => 'Review and run installation'],
            'success' => ['label' => 'Success', 'description' => 'Installation completed'],
        ];
    }

    public static function lockFilePath(): string
    {
        return storage_path('install/installed.json');
    }

    public static function hasLockFile(): bool
    {
        return file_exists(self::lockFilePath());
    }

    public static function isInstallerPath(string $uri): bool
    {
        $uri = normalize_request_path($uri);
        return $uri === '/install' || str_starts_with($uri, '/install/');
    }

    public static function installedMetadata(): array
    {
        if (! self::hasLockFile()) {
            return [];
        }

        $json = file_get_contents(self::lockFilePath());
        if (! is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function canShowSuccessScreen(): bool
    {
        return ! empty($_SESSION[self::SUCCESS_KEY]);
    }

    public static function consumeSuccessPayload(): array
    {
        $payload = $_SESSION[self::SUCCESS_KEY] ?? [];
        unset($_SESSION[self::SUCCESS_KEY]);

        return is_array($payload) ? $payload : [];
    }

    public static function isInstalled(): bool
    {
        if (self::hasLockFile()) {
            return true;
        }

        if (! file_exists(base_path('.env'))) {
            return false;
        }

        try {
            $pdo = self::connectConfiguredDatabase();
            $tables = [];
            foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
                $tables[] = strtolower((string) $table);
            }

            return in_array('users', $tables, true) && in_array('settings', $tables, true);
        } catch (Throwable) {
            return false;
        }
    }

    public static function defaultPayload(): array
    {
        $siteUrl = request_base_url();
        if ($siteUrl === '') {
            $siteUrl = 'http://localhost/badabrand-technologies';
        }

        return [
            'site_name' => 'Badabrand Technologies',
            'site_url' => rtrim($siteUrl, '/'),
            'timezone' => 'Asia/Calcutta',
            'language' => 'en',
            'currency' => 'INR',
            'environment' => 'production',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_name' => 'badabrand_technologies',
            'db_user' => 'root',
            'db_password' => '',
            'table_prefix' => '',
            'admin_name' => 'Priyanshu Patle',
            'admin_email' => 'support@badabrand.in',
            'admin_username' => 'admin',
            'admin_phone' => '+91 9109566312',
            'company_name' => 'Badabrand Technologies',
            'support_email' => 'support@badabrand.in',
            'support_phone' => '+91 9109566312',
            'company_whatsapp' => '+91 9109566312',
            'company_address' => 'Balaghat, Madhya Pradesh, India',
            'accent_color' => '#2d7ff9',
            'default_theme' => 'dark',
            'install_demo_content' => '1',
            'enable_marketplace' => '1',
            'enable_referrals' => '1',
            'enable_vendor_system' => '1',
            'enable_documentation' => '1',
            'install_core_modules' => '1',
            'license_key' => '',
        ];
    }

    public static function payload(): array
    {
        $payload = $_SESSION[self::SESSION_KEY] ?? [];
        return array_replace(self::defaultPayload(), is_array($payload) ? $payload : []);
    }

    public static function store(array $values): void
    {
        $_SESSION[self::SESSION_KEY] = array_replace(self::payload(), $values);
    }

    public static function reset(): void
    {
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SUCCESS_KEY], $_SESSION['_old']);
    }

    public static function requirements(): array
    {
        $envTarget = base_path('.env');
        $envDirectory = dirname($envTarget);
        $storageRoot = storage_path();
        $installDir = dirname(self::lockFilePath());

        if (! is_dir($installDir)) {
            @mkdir($installDir, 0777, true);
        }

        $checks = [];
        $checks[] = [
            'label' => 'PHP 8.1 or higher',
            'status' => version_compare(PHP_VERSION, '8.1.0', '>=') ? 'pass' : 'fail',
            'detail' => 'Current version: ' . PHP_VERSION,
        ];

        foreach (['pdo', 'pdo_mysql', 'json', 'mbstring', 'fileinfo'] as $extension) {
            $checks[] = [
                'label' => strtoupper($extension) . ' extension',
                'status' => extension_loaded($extension) ? 'pass' : 'fail',
                'detail' => extension_loaded($extension) ? 'Loaded' : 'Missing',
            ];
        }

        $rewriteAvailable = function_exists('apache_get_modules')
            ? in_array('mod_rewrite', apache_get_modules(), true)
            : null;
        $checks[] = [
            'label' => 'Apache mod_rewrite support',
            'status' => $rewriteAvailable === false ? 'warn' : 'pass',
            'detail' => $rewriteAvailable === null ? 'Unable to detect automatically on this server; assumed available on shared hosting/cPanel' : ($rewriteAvailable ? 'Enabled' : 'Not detected'),
        ];

        $checks[] = [
            'label' => '.env writable target',
            'status' => (file_exists($envTarget) ? is_writable($envTarget) : is_writable($envDirectory)) ? 'pass' : 'fail',
            'detail' => file_exists($envTarget) ? '.env file can be updated' : 'Project root must allow creating .env',
        ];

        $checks[] = [
            'label' => 'Storage directory writable',
            'status' => is_dir($storageRoot) && is_writable($storageRoot) ? 'pass' : 'fail',
            'detail' => $storageRoot,
        ];

        $checks[] = [
            'label' => 'Install lock directory writable',
            'status' => is_dir($installDir) && is_writable($installDir) ? 'pass' : 'fail',
            'detail' => $installDir,
        ];

        $checks[] = [
            'label' => 'Upload max size',
            'status' => 'pass',
            'detail' => 'upload_max_filesize: ' . ini_get('upload_max_filesize'),
        ];

        $checks[] = [
            'label' => 'Memory limit',
            'status' => 'pass',
            'detail' => 'memory_limit: ' . ini_get('memory_limit'),
        ];

        return $checks;
    }

    public static function allRequirementsPass(): bool
    {
        foreach (self::requirements() as $check) {
            if (($check['status'] ?? '') === 'fail') {
                return false;
            }
        }

        return true;
    }

    public static function validateDatabaseConnection(array $payload): array
    {
        try {
            self::connect(
                (string) ($payload['db_host'] ?? ''),
                (string) ($payload['db_port'] ?? ''),
                (string) ($payload['db_name'] ?? ''),
                (string) ($payload['db_user'] ?? ''),
                (string) ($payload['db_password'] ?? '')
            );

            return ['ok' => true, 'message' => 'Database connection successful.'];
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $exception->getMessage()];
        }
    }

    public static function install(array $payload): array
    {
        $pdo = self::connect(
            (string) $payload['db_host'],
            (string) $payload['db_port'],
            (string) $payload['db_name'],
            (string) $payload['db_user'],
            (string) $payload['db_password']
        );

        self::writeEnvironmentFile($payload);
        self::importSchema($pdo);
        self::applySettings($pdo, $payload);
        self::applyAdminAccount($pdo, $payload);
        self::storeBrandAssets($pdo, $payload);
        self::applyFeatureFlags($pdo, $payload);

        if (($payload['install_demo_content'] ?? '1') !== '1') {
            self::pruneDemoContent($pdo);
        }

        self::createInstallLock($payload);
        $_SESSION[self::SUCCESS_KEY] = [
            'site_url' => (string) $payload['site_url'],
            'login_url' => rtrim((string) $payload['site_url'], '/') . '/login',
            'admin_url' => rtrim((string) $payload['site_url'], '/') . '/admin',
            'admin_email' => (string) $payload['admin_email'],
            'installed_at' => date('c'),
        ];

        return $_SESSION[self::SUCCESS_KEY];
    }

    private static function connectConfiguredDatabase(): PDO
    {
        return self::connect(
            (string) env_value('DB_HOST', '127.0.0.1'),
            (string) env_value('DB_PORT', '3306'),
            (string) env_value('DB_DATABASE', 'badabrand_technologies'),
            (string) env_value('DB_USERNAME', 'root'),
            (string) env_value('DB_PASSWORD', '')
        );
    }

    private static function connect(string $host, string $port, string $database, string $username, string $password): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private static function writeEnvironmentFile(array $payload): void
    {
        $template = file_exists(base_path('.env.example'))
            ? file_get_contents(base_path('.env.example'))
            : '';

        $values = [
            'APP_NAME' => (string) ($payload['site_name'] ?? 'Badabrand Technologies'),
            'APP_ENV' => (string) ($payload['environment'] ?? 'production'),
            'APP_DEBUG' => (($payload['environment'] ?? 'production') === 'production') ? 'false' : 'true',
            'APP_URL' => rtrim((string) ($payload['site_url'] ?? request_base_url()), '/'),
            'APP_TIMEZONE' => (string) ($payload['timezone'] ?? 'Asia/Calcutta'),
            'DB_HOST' => (string) ($payload['db_host'] ?? '127.0.0.1'),
            'DB_PORT' => (string) ($payload['db_port'] ?? '3306'),
            'DB_DATABASE' => (string) ($payload['db_name'] ?? 'badabrand_technologies'),
            'DB_USERNAME' => (string) ($payload['db_user'] ?? 'root'),
            'DB_PASSWORD' => (string) ($payload['db_password'] ?? ''),
            'COMPANY_EMAIL' => (string) ($payload['support_email'] ?? 'support@badabrand.in'),
            'COMPANY_PHONE' => (string) ($payload['support_phone'] ?? '+91 9109566312'),
            'COMPANY_WHATSAPP' => (string) ($payload['company_whatsapp'] ?? '+91 9109566312'),
            'COMPANY_ADDRESS' => (string) ($payload['company_address'] ?? 'Balaghat, Madhya Pradesh, India'),
            'GOOGLE_MAPS_EMBED' => (string) ($payload['google_maps_embed'] ?? 'https://www.google.com/maps?q=Balaghat%20Madhya%20Pradesh%20India&output=embed'),
        ];

        $lines = [];
        if (is_string($template) && trim($template) !== '') {
            foreach (preg_split("/\r\n|\n|\r/", $template) as $line) {
                if (! str_contains($line, '=')) {
                    if (trim($line) !== '') {
                        $lines[] = $line;
                    }
                    continue;
                }

                [$key] = explode('=', $line, 2);
                $key = trim($key);
                if (! array_key_exists($key, $values)) {
                    continue;
                }

                $lines[] = $key . '=' . self::envWrap($values[$key]);
                unset($values[$key]);
            }
        }

        foreach ($values as $key => $value) {
            $lines[] = $key . '=' . self::envWrap($value);
        }

        file_put_contents(base_path('.env'), implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private static function envWrap(string $value): string
    {
        if ($value === '' || preg_match('/\s/', $value)) {
            return '"' . addcslashes($value, '"') . '"';
        }

        return $value;
    }

    private static function importSchema(PDO $pdo): void
    {
        $sql = file_get_contents(base_path('database/sql/badabrand_platform.sql'));
        if (! is_string($sql) || trim($sql) === '') {
            throw new \RuntimeException('The installation SQL file could not be loaded.');
        }

        foreach (self::splitSqlStatements($sql) as $statement) {
            $pdo->exec($statement);
        }
    }

    private static function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inSingle = false;
        $inDouble = false;
        $length = strlen($sql);

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $previous = $index > 0 ? $sql[$index - 1] : '';

            if ($char === "'" && ! $inDouble && $previous !== '\\') {
                $inSingle = ! $inSingle;
            } elseif ($char === '"' && ! $inSingle && $previous !== '\\') {
                $inDouble = ! $inDouble;
            }

            if ($char === ';' && ! $inSingle && ! $inDouble) {
                $trimmed = trim($buffer);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $trimmed = trim($buffer);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private static function applySettings(PDO $pdo, array $payload): void
    {
        $settings = [
            'site_title' => (string) $payload['site_name'],
            'footer_company_name' => (string) $payload['company_name'],
            'support_email' => (string) $payload['support_email'],
            'support_phone' => (string) $payload['support_phone'],
            'company_whatsapp' => (string) $payload['company_whatsapp'],
            'contact_email' => (string) $payload['support_email'],
            'contact_phone' => (string) $payload['support_phone'],
            'contact_address' => (string) $payload['company_address'],
            'theme_default' => (string) $payload['default_theme'],
            'theme_admin' => (string) $payload['default_theme'],
            'theme_public' => (string) $payload['default_theme'],
            'currency' => (string) $payload['currency'],
            'default_language' => (string) $payload['language'],
            'timezone' => (string) $payload['timezone'],
            'support_whatsapp' => (string) $payload['company_whatsapp'],
            'product_version' => '1.0.0',
            'release_channel' => 'stable',
            'accent_color' => (string) ($payload['accent_color'] ?? '#2d7ff9'),
        ];

        foreach ($settings as $key => $value) {
            self::upsertSetting($pdo, $key, $value);
        }
    }

    private static function applyFeatureFlags(PDO $pdo, array $payload): void
    {
        $flags = [
            'marketplace_enabled' => ($payload['enable_marketplace'] ?? '1') === '1' ? '1' : '0',
            'referral_enabled' => ($payload['enable_referrals'] ?? '1') === '1' ? '1' : '0',
            'vendor_enabled' => ($payload['enable_vendor_system'] ?? '1') === '1' ? '1' : '0',
            'documentation_enabled' => ($payload['enable_documentation'] ?? '1') === '1' ? '1' : '0',
            'module_system_enabled' => ($payload['install_core_modules'] ?? '1') === '1' ? '1' : '0',
            'vendor_auto_approve' => '0',
            'vendor_default_commission' => '15',
            'vendor_product_requires_review' => '1',
            'vendor_minimum_payout' => '1000',
            'referral_reward_amount' => '500',
            'referral_percentage' => '5',
            'referral_minimum_payout' => '1000',
        ];

        foreach ($flags as $key => $value) {
            self::upsertSetting($pdo, $key, $value);
        }
    }

    private static function applyAdminAccount(PDO $pdo, array $payload): void
    {
        $fullName = trim((string) ($payload['admin_name'] ?? 'Admin User'));
        $parts = preg_split('/\s+/', $fullName) ?: [];
        $firstName = array_shift($parts) ?: 'Admin';
        $lastName = trim(implode(' ', $parts));
        $email = trim((string) ($payload['admin_email'] ?? 'support@badabrand.in'));
        $phone = trim((string) ($payload['admin_phone'] ?? '+91 9109566312'));
        $passwordHash = password_hash((string) $payload['admin_password'], PASSWORD_DEFAULT);

        $existing = $pdo->prepare('SELECT id FROM users WHERE role = :role ORDER BY id ASC LIMIT 1');
        $existing->execute(['role' => 'admin']);
        $adminId = (int) ($existing->fetchColumn() ?: 0);

        if ($adminId > 0) {
            $pdo->prepare('
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    password = :password,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ')->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $passwordHash,
                'status' => 'active',
                'id' => $adminId,
            ]);
        } else {
            $pdo->prepare("
                INSERT INTO users (role, client_id, first_name, last_name, email, phone, password, status, created_at, updated_at)
                VALUES ('admin', :client_id, :first_name, :last_name, :email, :phone, :password, 'active', NOW(), NOW())
            ")->execute([
                'client_id' => 'BBT-0001',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $passwordHash,
            ]);
            $adminId = (int) $pdo->lastInsertId();
        }

        $referralCode = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $payload['admin_username'] ?? 'ADMIN') ?: 'ADMIN', 0, 8));
        if ($referralCode === '') {
            $referralCode = 'ADMIN001';
        }
        $existingReferral = $pdo->prepare('SELECT id, total_signups, total_earned, reward_balance, payout_status FROM referrals WHERE user_id = :user_id LIMIT 1');
        $existingReferral->execute(['user_id' => $adminId]);
        $referralRow = $existingReferral->fetch() ?: null;

        if ($referralRow) {
            $pdo->prepare('
                UPDATE referrals
                SET referral_code = :referral_code,
                    updated_at = NOW()
                WHERE user_id = :user_id
            ')->execute([
                'user_id' => $adminId,
                'referral_code' => $referralCode,
            ]);
        } else {
            $pdo->prepare('
                INSERT INTO referrals (user_id, referral_code, total_signups, total_earned, reward_balance, payout_status, created_at, updated_at)
                VALUES (:user_id, :referral_code, 0, 0, 0, "unpaid", NOW(), NOW())
            ')->execute([
                'user_id' => $adminId,
                'referral_code' => $referralCode,
            ]);
        }
    }

    private static function storeBrandAssets(PDO $pdo, array &$payload): void
    {
        foreach (['company_logo' => 'logo_file', 'company_favicon' => 'favicon_file'] as $settingKey => $field) {
            $path = self::moveUploadedAsset($field, 'assets/uploads/branding');
            if ($path) {
                self::upsertSetting($pdo, $settingKey, $path);
                $payload[$settingKey] = $path;
            }
        }
    }

    private static function moveUploadedAsset(string $field, string $directory): ?string
    {
        if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = strtolower(pathinfo((string) $_FILES[$field]['name'], PATHINFO_EXTENSION));
        $filename = uniqid('branding_', true) . ($extension !== '' ? '.' . $extension : '');
        $relativeDir = trim($directory, '/');
        $targetDir = base_path('public/' . $relativeDir);

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $target = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (! move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            return null;
        }

        return $relativeDir . '/' . $filename;
    }

    private static function pruneDemoContent(PDO $pdo): void
    {
        $statements = [
            "DELETE FROM users WHERE role <> 'admin'",
            'DELETE FROM orders',
            'DELETE FROM invoices',
            'DELETE FROM payments',
            'DELETE FROM tickets',
            'DELETE FROM ticket_messages',
            'DELETE FROM proposals',
            'DELETE FROM contracts',
            'DELETE FROM notifications',
            'DELETE FROM email_logs',
            'DELETE FROM vendor_commissions',
            'DELETE FROM vendor_payouts',
            'DELETE FROM vendor_documents',
            'DELETE FROM vendor_activity_logs',
            'DELETE FROM vendor_payout_accounts',
            'DELETE FROM vendor_profiles',
            'DELETE FROM vendors',
            'DELETE FROM referrals WHERE user_id NOT IN (SELECT id FROM users WHERE role = "admin")',
            'DELETE FROM product_reviews',
        ];

        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
            } catch (Throwable) {
                continue;
            }
        }
    }

    private static function upsertSetting(PDO $pdo, string $key, string $value): void
    {
        $pdo->prepare('REPLACE INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())')
            ->execute(['key' => $key, 'value' => $value]);
    }

    private static function createInstallLock(array $payload): void
    {
        $lockPath = self::lockFilePath();
        $dir = dirname($lockPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($lockPath, json_encode([
            'installed_at' => date('c'),
            'site_url' => (string) $payload['site_url'],
            'site_name' => (string) $payload['site_name'],
            'admin_email' => (string) $payload['admin_email'],
            'product_version' => '1.0.0',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
