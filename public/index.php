<?php

if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    http_response_code(500);
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Badabrand Technologies - Server Requirements</title>
        <style>
            body{margin:0;font-family:Arial,sans-serif;background:#071127;color:#eef4ff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
            .card{max-width:760px;width:100%;background:#111c3c;border:1px solid rgba(111,153,255,.28);border-radius:24px;padding:32px;box-shadow:0 24px 50px rgba(0,0,0,.35)}
            .eyebrow{display:inline-block;padding:8px 14px;border-radius:999px;background:rgba(45,127,249,.14);color:#9eb7ff;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
            h1{margin:18px 0 12px;font-size:38px;line-height:1.1}
            p,li{color:#c6d6ff;font-size:16px;line-height:1.7}
            ul{padding-left:22px}
            .callout{margin-top:22px;padding:16px 18px;border-radius:16px;background:#0b1430;border:1px solid rgba(255,255,255,.08)}
            code{background:rgba(255,255,255,.08);padding:2px 6px;border-radius:6px}
        </style>
    </head>
    <body>
        <div class="card">
            <span class="eyebrow">Installer Requirement</span>
            <h1>PHP 8.1 or higher is required</h1>
            <p>The server is currently running <strong><?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?></strong>, so Badabrand Technologies cannot start the installer yet.</p>
            <ul>
                <li>Set your domain/subdomain to <strong>PHP 8.1</strong> or <strong>PHP 8.2</strong> in cPanel MultiPHP Manager.</li>
                <li>Enable these PHP extensions: <code>pdo</code>, <code>pdo_mysql</code>, <code>json</code>, <code>mbstring</code>, <code>fileinfo</code>.</li>
                <li>Reload the website after changing the PHP version.</li>
            </ul>
            <div class="callout">
                After switching PHP, open the website again and the Badabrand web installer will launch automatically on first run.
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

try {
    $app = require dirname(__DIR__) . '/bootstrap/app.php';
    $app->run();
} catch (Throwable $exception) {
    http_response_code(500);
    $message = $exception->getMessage();
    $message = $message !== '' ? $message : 'Unknown startup error';
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Badabrand Technologies - Startup Error</title>
        <style>
            body{margin:0;font-family:Arial,sans-serif;background:#071127;color:#eef4ff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
            .card{max-width:860px;width:100%;background:#111c3c;border:1px solid rgba(111,153,255,.28);border-radius:24px;padding:32px;box-shadow:0 24px 50px rgba(0,0,0,.35)}
            .eyebrow{display:inline-block;padding:8px 14px;border-radius:999px;background:rgba(255,95,122,.14);color:#ff8fa3;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
            h1{margin:18px 0 12px;font-size:38px;line-height:1.1}
            p,li{color:#c6d6ff;font-size:16px;line-height:1.7}
            .error-box{margin-top:20px;padding:16px 18px;border-radius:16px;background:#0b1430;border:1px solid rgba(255,255,255,.08);word-break:break-word}
            code{background:rgba(255,255,255,.08);padding:2px 6px;border-radius:6px}
            ul{padding-left:22px}
        </style>
    </head>
    <body>
        <div class="card">
            <span class="eyebrow">Startup Error</span>
            <h1>Badabrand could not complete startup</h1>
            <p>The installer or application hit a server-side error during startup. Use the message below to identify the exact missing requirement on your hosting.</p>
            <div class="error-box"><strong>Error:</strong> <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <ul>
                <li>Make sure the server uses <strong>PHP 8.1 or PHP 8.2</strong>.</li>
                <li>Enable <code>pdo</code>, <code>pdo_mysql</code>, <code>json</code>, <code>mbstring</code>, and <code>fileinfo</code>.</li>
                <li>Make sure the project root can create <code>.env</code> and write inside <code>storage/</code>.</li>
            </ul>
        </div>
    </body>
    </html>
    <?php
}
