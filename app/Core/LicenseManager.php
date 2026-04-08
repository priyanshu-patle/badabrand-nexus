<?php

namespace App\Core;

class LicenseManager
{
    public static function ensureSchema(): void
    {
    }

    public static function currentInstallSummary(): array
    {
        return [
            'domain' => strtolower((string) ($_SERVER['HTTP_HOST'] ?? 'localhost')),
            'status' => 'standalone',
            'is_active' => true,
            'requires_activation' => false,
            'expires_at' => null,
            'countdown_label' => 'Standalone',
            'message' => 'Badabrand Technologies is running without license enforcement.',
        ];
    }

    public static function guardAdminAccess(?array $user = null): void
    {
    }

    public static function activateForCurrentInstall(string $licenseKey): array
    {
        return [
            'ok' => true,
            'status' => 'standalone',
            'message' => 'License activation is disabled for this product.',
        ];
    }

    public static function validateLicenseKey(string $licenseKey, string $domain, string $action = 'check'): array
    {
        return [
            'ok' => true,
            'status' => 'standalone',
            'message' => 'License validation is disabled for this product.',
        ];
    }
}
