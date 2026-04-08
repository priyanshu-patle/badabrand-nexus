# Modules and Hooks

Badabrand Technologies now includes a global hook runtime and a ZIP-installable module system designed to extend the platform without rewriting core files.

## Core hooks

- `onUserRegister`
- `onUserLogin`
- `onOrderCreated`
- `onPaymentSuccess`
- `onTicketCreated`
- `onPageRender`
- `onModuleInstall`

Use the global helpers from anywhere in a module:

```php
add_action('onOrderCreated', function (array $payload): void {
    // Trigger workflows, notifications, analytics, or assignments.
});

add_filter('view.render', function (array $payload): array {
    $payload['data']['moduleBanner'] = 'Injected by module';
    return $payload;
});
```

## Recommended module structure

```text
modules/YourModule/
├── module.json
├── install.sql
├── routes.php
├── bootstrap.php
├── controllers/
├── models/
└── views/
```

`bootstrap.php` is optional but recommended for action and filter registration.

## Example module manifest

```json
{
  "name": "Automation Engine",
  "slug": "automation-engine",
  "version": "1.0.0",
  "description": "Event-driven workflows for orders and payments.",
  "namespace": "Modules\\AutomationEngine\\",
  "routes": "routes.php",
  "install": "install.sql",
  "dependencies": {
    "seo-engine": "^1.0"
  }
}
```

## Route registration

The module runtime loads active module routes after the core routes are registered. Modules can use literal paths or placeholder paths such as `/services/{slug}`.

```php
<?php

use Modules\AutomationEngine\Controllers\AutomationController;

$router->get('/automation/rules', [AutomationController::class, 'index']);
```

## Safety rules

- Keep each module isolated in its own directory.
- Use new tables per module instead of editing existing ones.
- Treat `install.sql` as idempotent so upgrades stay safe.
- Prefer hooks and filters over core edits.
