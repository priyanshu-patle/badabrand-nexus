<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/app/Helpers/functions.php';

if (file_exists(base_path('.env'))) {
    $lines = file(base_path('.env'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value, "\"' \t\n\r\0\x0B");
        $_ENV[trim($key)] = $value;
        $_SERVER[trim($key)] = $value;
    }
}

$timezone = (string) env_value('APP_TIMEZONE', 'Asia/Calcutta');
if ($timezone !== '') {
    @date_default_timezone_set($timezone);
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }
    $path = base_path('app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php');
    if (file_exists($path)) {
        require_once $path;
    }
});

spl_autoload_register(function (string $class): void {
    if (! str_starts_with($class, 'Modules\\')) {
        return;
    }

    if (class_exists(App\Core\ModuleManager::class)) {
        App\Core\ModuleManager::autoload($class);
    }
});

$app = new App\Core\App();
require base_path('routes/web.php');
$app->boot();
return $app;
