<?php

namespace App\Core;

class App
{
    private static ?self $instance = null;

    public Router $router;

    public ModuleManager $modules;

    public function __construct()
    {
        self::$instance = $this;
        $this->router = new Router();
        $this->modules = new ModuleManager($this);
    }

    public static function instance(): ?self
    {
        return self::$instance;
    }

    public function boot(): void
    {
        do_action('app.booting', ['app' => $this]);
        if (InstallerService::isInstalled()) {
            SystemHooks::boot();
            $this->modules->boot();
        }
        do_action('app.booted', ['app' => $this]);
    }

    public function run(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = $scriptName === '/' ? '' : rtrim($scriptName, '/');

        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        if (! InstallerService::isInstalled() && ! InstallerService::isInstallerPath($uri)) {
            redirect('/install/welcome');
        }

        if (InstallerService::isInstalled() && InstallerService::isInstallerPath($uri) && ! ($uri === '/install/success' && InstallerService::canShowSuccessScreen())) {
            redirect('/login');
        }

        $this->router->dispatch(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $uri
        );
    }
}
