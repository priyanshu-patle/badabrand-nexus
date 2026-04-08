<?php

namespace App\Controllers;

use App\Core\InstallerService;
use App\Core\View;
use Throwable;

class InstallerController
{
    private function steps(): array
    {
        return InstallerService::steps();
    }

    private function currentStep(): string
    {
        $step = trim((string) input('step', 'welcome'));
        return array_key_exists($step, $this->steps()) ? $step : 'welcome';
    }

    private function redirectToStep(string $step): never
    {
        redirect('/install/' . trim($step, '/'));
    }

    private function renderStep(string $step, array $data = []): void
    {
        $payload = InstallerService::payload();
        View::render('installer/wizard', [
            'pageTitle' => 'Installer - ' . ($this->steps()[$step]['label'] ?? 'Badabrand Technologies'),
            'metaDescription' => 'Badabrand Technologies installer',
            'step' => $step,
            'steps' => $this->steps(),
            'installerPayload' => $payload,
            'requirements' => InstallerService::requirements(),
            'themePresets' => theme_presets(),
            'installationVersion' => '1.0.0',
        ] + $data, 'layouts/installer');
    }

    public function index(): void
    {
        $this->redirectToStep('welcome');
    }

    public function show(): void
    {
        $step = $this->currentStep();

        if ($step === 'success') {
            $successPayload = InstallerService::consumeSuccessPayload();
            if ($successPayload === []) {
                flash('error', 'Installer is locked. Please login to continue.');
                redirect('/login');
            }

            View::render('installer/success', [
                'pageTitle' => 'Installation Complete',
                'metaDescription' => 'Badabrand Technologies installer success',
                'successPayload' => $successPayload,
                'installationVersion' => '1.0.0',
            ], 'layouts/installer');
            return;
        }

        if (InstallerService::isInstalled()) {
            flash('error', 'Installer is locked because this platform is already installed.');
            redirect('/login');
        }

        $this->renderStep($step);
    }

    public function submit(): void
    {
        if (InstallerService::isInstalled()) {
            flash('error', 'Installer is locked because this platform is already installed.');
            redirect('/login');
        }

        $step = $this->currentStep();

        try {
            match ($step) {
                'welcome' => $this->handleWelcome(),
                'requirements' => $this->handleRequirements(),
                'site' => $this->handleSiteSetup(),
                'database' => $this->handleDatabaseSetup(),
                'admin' => $this->handleAdminSetup(),
                'branding' => $this->handleBrandingSetup(),
                'modules' => $this->handleModulesSetup(),
                'finalize' => $this->handleFinalize(),
                default => $this->redirectToStep('welcome'),
            };
        } catch (Throwable $exception) {
            set_old_input($_POST);
            flash('error', $exception->getMessage());
            $this->redirectToStep($step);
        }
    }

    private function handleWelcome(): never
    {
        $this->redirectToStep('requirements');
    }

    private function handleRequirements(): never
    {
        if (! InstallerService::allRequirementsPass()) {
            throw new \RuntimeException('Please resolve the failed requirement checks before continuing.');
        }

        $this->redirectToStep('site');
    }

    private function handleSiteSetup(): never
    {
        $siteName = trim((string) input('site_name', ''));
        $siteUrl = rtrim(trim((string) input('site_url', '')), '/');
        $timezone = trim((string) input('timezone', 'Asia/Calcutta'));
        $language = trim((string) input('language', 'en'));
        $currency = strtoupper(trim((string) input('currency', 'INR')));
        $environment = trim((string) input('environment', 'production'));

        if ($siteName === '' || $siteUrl === '') {
            throw new \RuntimeException('Site name and site URL are required.');
        }

        if (! filter_var($siteUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Please provide a valid site URL.');
        }

        InstallerService::store([
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'timezone' => $timezone,
            'language' => $language,
            'currency' => $currency !== '' ? $currency : 'INR',
            'environment' => in_array($environment, ['production', 'staging', 'development'], true) ? $environment : 'production',
        ]);

        $this->redirectToStep('database');
    }

    private function handleDatabaseSetup(): never
    {
        $payload = [
            'db_host' => trim((string) input('db_host', '127.0.0.1')),
            'db_port' => trim((string) input('db_port', '3306')),
            'db_name' => trim((string) input('db_name', '')),
            'db_user' => trim((string) input('db_user', '')),
            'db_password' => (string) input('db_password', ''),
            'table_prefix' => trim((string) input('table_prefix', '')),
        ];

        if ($payload['db_host'] === '' || $payload['db_name'] === '' || $payload['db_user'] === '') {
            throw new \RuntimeException('Database host, name, and username are required.');
        }

        if ($payload['table_prefix'] !== '') {
            throw new \RuntimeException('Table prefixes are not supported in this release. Please keep the prefix field blank.');
        }

        $result = InstallerService::validateDatabaseConnection($payload);
        if (! ($result['ok'] ?? false)) {
            throw new \RuntimeException('Database connection failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        InstallerService::store($payload);
        $this->redirectToStep('admin');
    }

    private function handleAdminSetup(): never
    {
        $fullName = trim((string) input('admin_name', ''));
        $email = trim((string) input('admin_email', ''));
        $username = trim((string) input('admin_username', ''));
        $password = (string) input('admin_password', '');
        $confirm = (string) input('admin_password_confirmation', '');
        $phone = trim((string) input('admin_phone', ''));

        if ($fullName === '' || $email === '' || $password === '' || $confirm === '') {
            throw new \RuntimeException('Please complete all required admin account fields.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Please enter a valid admin email address.');
        }

        if (strlen($password) < 8 || ! preg_match('/[A-Z]/', $password) || ! preg_match('/\d/', $password)) {
            throw new \RuntimeException('Admin password must be at least 8 characters and include an uppercase letter and a number.');
        }

        if ($password !== $confirm) {
            throw new \RuntimeException('Admin password confirmation does not match.');
        }

        InstallerService::store([
            'admin_name' => $fullName,
            'admin_email' => $email,
            'admin_username' => $username !== '' ? $username : 'admin',
            'admin_password' => $password,
            'admin_phone' => $phone,
        ]);

        $this->redirectToStep('branding');
    }

    private function handleBrandingSetup(): never
    {
        $companyName = trim((string) input('company_name', ''));
        $supportEmail = trim((string) input('support_email', ''));
        $supportPhone = trim((string) input('support_phone', ''));
        $whatsApp = preg_replace('/\D+/', '', (string) input('company_whatsapp', ''));
        $companyAddress = trim((string) input('company_address', ''));
        $accentColor = trim((string) input('accent_color', '#2d7ff9'));
        $defaultTheme = trim((string) input('default_theme', 'dark'));

        if ($companyName === '' || $supportEmail === '') {
            throw new \RuntimeException('Company name and support email are required.');
        }

        if (! filter_var($supportEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Please enter a valid support email address.');
        }

        if (! array_key_exists($defaultTheme, theme_presets())) {
            $defaultTheme = 'dark';
        }

        InstallerService::store([
            'company_name' => $companyName,
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone,
            'company_whatsapp' => $whatsApp,
            'company_address' => $companyAddress,
            'accent_color' => $accentColor !== '' ? $accentColor : '#2d7ff9',
            'default_theme' => $defaultTheme,
        ]);

        $this->redirectToStep('modules');
    }

    private function handleModulesSetup(): never
    {
        InstallerService::store([
            'install_core_modules' => input('install_core_modules', '0') === '1' ? '1' : '0',
            'install_demo_content' => input('install_demo_content', '0') === '1' ? '1' : '0',
            'enable_marketplace' => input('enable_marketplace', '0') === '1' ? '1' : '0',
            'enable_referrals' => input('enable_referrals', '0') === '1' ? '1' : '0',
            'enable_vendor_system' => input('enable_vendor_system', '0') === '1' ? '1' : '0',
            'enable_documentation' => input('enable_documentation', '0') === '1' ? '1' : '0',
            'license_key' => trim((string) input('license_key', '')),
        ]);

        $this->redirectToStep('finalize');
    }

    private function handleFinalize(): never
    {
        $payload = InstallerService::payload();
        foreach (['site_name', 'site_url', 'db_host', 'db_name', 'db_user', 'admin_name', 'admin_email', 'admin_password', 'company_name', 'support_email'] as $required) {
            if (trim((string) ($payload[$required] ?? '')) === '') {
                throw new \RuntimeException('Installation session is incomplete. Please complete the earlier steps first.');
            }
        }

        InstallerService::install($payload);
        flash('success', 'Installation completed successfully.');
        redirect('/install/success');
    }
}
