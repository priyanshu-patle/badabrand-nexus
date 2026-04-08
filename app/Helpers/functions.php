<?php

use App\Core\Database;
use App\Core\Hooks;
use App\Core\ModuleManager;

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);
    return $path ? $base . DIRECTORY_SEPARATOR . $path : $base;
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
}

function modules_path(string $path = ''): string
{
    return base_path('modules' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
}

function config_path(string $path = ''): string
{
    return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
}

function view_path(string $path = ''): string
{
    return base_path('app/Views' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
}

function env_value(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

function config(string $key, mixed $default = null): mixed
{
    static $config = [];
    [$file, $nested] = array_pad(explode('.', $key, 2), 2, null);
    if (! isset($config[$file])) {
        $path = config_path($file . '.php');
        $config[$file] = file_exists($path) ? require $path : [];
    }
    $value = $config[$file];
    if ($nested === null) {
        return $value ?: $default;
    }
    foreach (explode('.', $nested) as $segment) {
        if (! is_array($value) || ! array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function app_settings_cached(): array
{
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    try {
        $rows = Database::connection()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (\Throwable) {
        $settings = [];
    }

    return $settings;
}

function app_setting(string $key, mixed $default = null): mixed
{
    $settings = app_settings_cached();
    return $settings[$key] ?? $default;
}

function theme_presets(): array
{
    return [
        'dark' => [
            'name' => 'Dark Pro',
            'description' => 'Dark-first admin workspace with strong contrast and the current Badabrand identity.',
            'swatch' => 'linear-gradient(135deg, #0b1437 0%, #17358a 55%, #32b7ff 100%)',
        ],
        'light' => [
            'name' => 'Light Classic',
            'description' => 'Bright enterprise presentation for daytime operations and client-facing sessions.',
            'swatch' => 'linear-gradient(135deg, #ffffff 0%, #ecf3ff 55%, #b8d8ff 100%)',
        ],
        'midnight' => [
            'name' => 'Midnight Glass',
            'description' => 'Deeper glassmorphism variant with cooler contrast and premium presentation.',
            'swatch' => 'linear-gradient(135deg, #040816 0%, #0d1f52 45%, #5536ff 100%)',
        ],
    ];
}

function resolve_theme_name(string $scope = 'admin', ?array $settings = null, ?array $user = null): string
{
    $available = array_keys(theme_presets());
    $settings ??= app_settings_cached();

    $scopeKey = $scope === 'public' ? 'theme_public' : 'theme_admin';
    $fallback = $scope === 'public'
        ? ($settings['theme_public'] ?? $settings['theme_default'] ?? 'dark')
        : ($settings['theme_admin'] ?? $settings['theme_default'] ?? 'dark');

    $userPreference = '';
    if (is_array($user)) {
        $userPreference = trim((string) ($user['theme_preference'] ?? ''));
    }

    $theme = $userPreference !== '' ? $userPreference : trim((string) $fallback);
    if (! in_array($theme, $available, true)) {
        $theme = 'dark';
    }

    return $theme;
}

function request_scheme(): string
{
    $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    $forwarded = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

    if ($https === 'on' || $https === '1' || $forwarded === 'https') {
        return 'https';
    }

    return 'http';
}

function request_base_url(): string
{
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }

    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = $scriptName === '/' ? '' : rtrim($scriptName, '/');

    return request_scheme() . '://' . $host . $basePath;
}

function normalize_request_path(string $path): string
{
    $normalized = '/' . trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
    return $normalized === '/' ? '/' : rtrim($normalized, '/');
}

function current_request_path(): string
{
    $uri = normalize_request_path((string) ($_SERVER['REQUEST_URI'] ?? '/'));
    $basePath = normalize_request_path((string) (parse_url(app_url_base(), PHP_URL_PATH) ?: ''));

    if ($basePath !== '/' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath)) ?: '/';
    }

    return normalize_request_path($uri);
}

function app_url_base(): string
{
    $runtime = request_base_url();
    if ($runtime !== '') {
        return rtrim($runtime, '/');
    }

    return rtrim((string) config('app.url', ''), '/');
}

function asset(string $path): string
{
    return app_url_base() . '/assets/' . ltrim($path, '/');
}

function route_url(string $path = ''): string
{
    return app_url_base() . '/' . ltrim($path, '/');
}

function app(): ?\App\Core\App
{
    return \App\Core\App::instance();
}

function modules(): ModuleManager
{
    $manager = ModuleManager::instance();
    if (! $manager instanceof ModuleManager) {
        throw new RuntimeException('Module manager has not been booted yet.');
    }

    return $manager;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . route_url($path));
    exit;
}

function add_action(string $event, callable $callback, int $priority = 10): void
{
    Hooks::addAction($event, $callback, $priority);
}

function do_action(string $event, mixed $data = null): void
{
    Hooks::doAction($event, $data);
}

function add_filter(string $event, callable $callback, int $priority = 10): void
{
    Hooks::addFilter($event, $callback, $priority);
}

function apply_filters(string $event, mixed $data = null): mixed
{
    return Hooks::applyFilters($event, $data);
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function set_old_input(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function storage_url(?string $path): string
{
    if (! $path) {
        return asset('images/favicon.svg');
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return rtrim(config('app.url', ''), '/') . '/' . ltrim($path, '/');
}

function brand_asset_url(?string $path, string $fallbackAsset = 'images/badabrand-logo.svg'): string
{
    $path = trim((string) $path);

    if ($path !== '') {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $resolved = base_path('public/' . ltrim($path, '/'));
        if (file_exists($resolved)) {
            return storage_url($path);
        }
    }

    return asset($fallbackAsset);
}

function money_format_inr(float|int|string|null $value): string
{
    return 'Rs ' . number_format((float) $value, 2);
}

function money_to_float(float|int|string|null $value): float
{
    if (is_float($value) || is_int($value)) {
        return (float) $value;
    }

    preg_match('/-?\d[\d,]*(?:\.\d+)?/', (string) $value, $matches);
    if (empty($matches[0])) {
        return 0.0;
    }

    return (float) str_replace(',', '', $matches[0]);
}

function generate_reference(string $prefix): string
{
    return strtoupper($prefix) . '-' . date('Ymd') . '-' . random_int(1000, 9999);
}

function slugify(?string $value, string $fallbackPrefix = 'item'): string
{
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value ?? '');
    $value = trim((string) $value, '-');

    if ($value === '') {
        return $fallbackPrefix . '-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
    }

    return $value;
}

function notify_user(?int $userId, string $type, string $title, string $body, string $actionUrl = ''): void
{
    if (! $userId) {
        return;
    }

    Database::connection()->prepare('
        INSERT INTO notifications (user_id, type, title, body, action_url, created_at, updated_at)
        VALUES (:user_id, :type, :title, :body, :action_url, NOW(), NOW())
    ')->execute([
        'user_id' => $userId,
        'type' => $type,
        'title' => $title,
        'body' => $body,
        'action_url' => $actionUrl,
    ]);
}

function log_email(?int $userId, string $recipientEmail, string $subject, string $htmlBody, string $textBody, string $relatedType = '', ?int $relatedId = null): void
{
    $deliveryStatus = 'queued';
    if (function_exists('mail')) {
        $smtpHost = (string) app_setting('smtp_host', env_value('MAIL_HOST', ''));
        $smtpPort = (string) app_setting('smtp_port', env_value('MAIL_PORT', '25'));
        $fromEmail = (string) app_setting('smtp_from_email', env_value('COMPANY_EMAIL', 'support@badabrand.in'));
        $fromName = (string) app_setting('smtp_from_name', app_setting('site_title', 'Badabrand Technologies'));

        if ($smtpHost !== '') {
            @ini_set('SMTP', $smtpHost);
        }
        if ($smtpPort !== '') {
            @ini_set('smtp_port', $smtpPort);
        }
        if ($fromEmail !== '') {
            @ini_set('sendmail_from', $fromEmail);
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= 'From: ' . ($fromName !== '' ? $fromName . ' <' . $fromEmail . '>' : $fromEmail) . "\r\n";
        $deliveryStatus = @mail($recipientEmail, $subject, $htmlBody, $headers) ? 'sent' : 'queued';
    }

    Database::connection()->prepare('
        INSERT INTO email_logs (user_id, recipient_email, subject, html_body, text_body, related_type, related_id, delivery_status, created_at, updated_at)
        VALUES (:user_id, :recipient_email, :subject, :html_body, :text_body, :related_type, :related_id, :delivery_status, NOW(), NOW())
    ')->execute([
        'user_id' => $userId,
        'recipient_email' => $recipientEmail,
        'subject' => $subject,
        'html_body' => $htmlBody,
        'text_body' => $textBody,
        'related_type' => $relatedType,
        'related_id' => $relatedId,
        'delivery_status' => $deliveryStatus,
    ]);
}

function safe_rich_text(?string $html): string
{
    $allowed = '<p><br><ul><ol><li><strong><b><em><i><u><a><blockquote><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><th><td>';
    return strip_tags((string) $html, $allowed);
}

function upload_file(string $field, string $directory = 'assets/uploads'): ?string
{
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    $name = uniqid('upload_', true) . ($extension ? '.' . $extension : '');
    $relativeDir = trim($directory, '/');
    $targetDir = base_path('public/' . $relativeDir);

    if (! is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $target = $targetDir . DIRECTORY_SEPARATOR . $name;
    if (! move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
        return null;
    }

    return $relativeDir . '/' . $name;
}

function markdown_inline(string $text): string
{
    $escaped = e($text);
    return (string) preg_replace_callback('/`([^`]+)`/', static fn (array $matches): string => '<code>' . e($matches[1]) . '</code>', $escaped);
}

function simple_markdown_to_html(string $markdown): string
{
    $lines = preg_split("/\r\n|\n|\r/", trim($markdown));
    $html = [];
    $inCode = false;
    $inUl = false;
    $inOl = false;

    $closeLists = static function () use (&$html, &$inUl, &$inOl): void {
        if ($inUl) {
            $html[] = '</ul>';
            $inUl = false;
        }
        if ($inOl) {
            $html[] = '</ol>';
            $inOl = false;
        }
    };

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (str_starts_with($trimmed, '```')) {
            $closeLists();
            if (! $inCode) {
                $html[] = '<pre><code>';
                $inCode = true;
            } else {
                $html[] = '</code></pre>';
                $inCode = false;
            }
            continue;
        }

        if ($inCode) {
            $html[] = e($line);
            continue;
        }

        if ($trimmed === '') {
            $closeLists();
            continue;
        }

        if (preg_match('/^(#{1,4})\s+(.*)$/', $trimmed, $matches)) {
            $closeLists();
            $level = min(strlen($matches[1]), 4);
            $html[] = '<h' . $level . '>' . markdown_inline($matches[2]) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^- (.*)$/', $trimmed, $matches)) {
            if (! $inUl) {
                $closeLists();
                $html[] = '<ul>';
                $inUl = true;
            }
            $html[] = '<li>' . markdown_inline($matches[1]) . '</li>';
            continue;
        }

        if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $matches)) {
            if (! $inOl) {
                $closeLists();
                $html[] = '<ol>';
                $inOl = true;
            }
            $html[] = '<li>' . markdown_inline($matches[1]) . '</li>';
            continue;
        }

        $closeLists();
        $html[] = '<p>' . markdown_inline($trimmed) . '</p>';
    }

    if ($inCode) {
        $html[] = '</code></pre>';
    }

    if ($inUl) {
        $html[] = '</ul>';
    }

    if ($inOl) {
        $html[] = '</ol>';
    }

    return implode("\n", $html);
}
