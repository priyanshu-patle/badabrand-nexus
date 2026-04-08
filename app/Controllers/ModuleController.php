<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Content;
use App\Core\View;
use Throwable;

class ModuleController
{
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
        $manager = modules();

        $this->render('admin/modules', 'Modules & Hooks', [
            'viewName' => 'modules',
            'moduleStats' => $manager->stats(),
            'modules' => $manager->allModules(),
            'activity' => $manager->activity(),
            'coreEvents' => [
                'onUserRegister',
                'onUserLogin',
                'onOrderCreated',
                'onPaymentSuccess',
                'onTicketCreated',
                'onPageRender',
                'onModuleInstall',
            ],
        ]);
    }

    public function upload(): void
    {
        Auth::requireRole(['admin']);

        try {
            $record = modules()->installFromUpload($_FILES['module_zip'] ?? []);
            flash('success', 'Module "' . ($record['name'] ?? $record['slug']) . '" installed successfully.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/admin/modules');
    }

    public function activate(): void
    {
        Auth::requireRole(['admin']);

        try {
            $module = modules()->activate((string) input('slug', ''));
            flash('success', 'Module "' . ($module['name'] ?? $module['slug']) . '" activated.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/admin/modules');
    }

    public function deactivate(): void
    {
        Auth::requireRole(['admin']);

        try {
            $module = modules()->deactivate((string) input('slug', ''));
            flash('success', 'Module "' . ($module['name'] ?? $module['slug']) . '" deactivated.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/admin/modules');
    }

    public function delete(): void
    {
        Auth::requireRole(['admin']);

        try {
            modules()->delete((string) input('slug', ''));
            flash('success', 'Module deleted successfully.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/admin/modules');
    }
}
