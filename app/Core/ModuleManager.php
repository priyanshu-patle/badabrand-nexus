<?php

namespace App\Core;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

class ModuleManager
{
    private const STATUS_ACTIVE = 'active';

    private const STATUS_INACTIVE = 'inactive';

    private static ?self $instance = null;

    private static array $namespaceMap = [];

    private array $bootedModules = [];

    public function __construct(private readonly App $app)
    {
        self::$instance = $this;
    }

    public static function instance(): ?self
    {
        return self::$instance;
    }

    public static function autoload(string $class): void
    {
        if (! str_starts_with($class, 'Modules\\') || ! self::$instance instanceof self) {
            return;
        }

        self::$instance->ensureNamespaceMap();

        foreach (self::$namespaceMap as $prefix => $basePath) {
            if (! str_starts_with($class, $prefix)) {
                continue;
            }

            $relative = substr($class, strlen($prefix));
            $candidate = $basePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            if (file_exists($candidate)) {
                require_once $candidate;
                return;
            }

            $segments = explode('\\', $relative);
            if (isset($segments[0])) {
                $segments[0] = lcfirst($segments[0]);
            }

            $fallback = $basePath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments) . '.php';
            if (file_exists($fallback)) {
                require_once $fallback;
            }

            return;
        }
    }

    public function boot(): void
    {
        $this->ensureSystemTables();
        $this->syncDiscoveredModules();

        foreach ($this->allModules() as $module) {
            if (($module['status'] ?? self::STATUS_INACTIVE) !== self::STATUS_ACTIVE) {
                continue;
            }

            $errors = $this->dependencyErrors($module, true);
            if ($errors !== []) {
                $this->deactivateSilently((string) $module['slug'], 'Automatically deactivated because dependencies are missing or inactive.');
                continue;
            }

            $this->bootstrapModule($module);
        }
    }

    public function allModules(): array
    {
        $this->syncDiscoveredModules();
        $rows = Database::connection()->query('SELECT * FROM system_modules ORDER BY name ASC, slug ASC')->fetchAll();

        return array_map(function (array $row): array {
            $row['manifest'] = $this->manifestForRow($row);
            $row['dependencies'] = $this->normalizeDependencies($row['manifest']['dependencies'] ?? []);
            $row['dependency_errors'] = $this->dependencyErrors($row);
            $row['can_activate'] = $row['dependency_errors'] === [];
            $row['routes_file_exists'] = file_exists($this->moduleDirectory((string) $row['directory_name']) . DIRECTORY_SEPARATOR . ($row['route_file'] ?: 'routes.php'));
            $row['install_file_exists'] = file_exists($this->moduleDirectory((string) $row['directory_name']) . DIRECTORY_SEPARATOR . ($row['install_file'] ?: 'install.sql'));

            return $row;
        }, $rows);
    }

    public function stats(): array
    {
        $modules = $this->allModules();

        return [
            'total' => count($modules),
            'active' => count(array_filter($modules, fn (array $module): bool => ($module['status'] ?? self::STATUS_INACTIVE) === self::STATUS_ACTIVE)),
            'inactive' => count(array_filter($modules, fn (array $module): bool => ($module['status'] ?? self::STATUS_INACTIVE) !== self::STATUS_ACTIVE)),
            'with_dependencies' => count(array_filter($modules, fn (array $module): bool => ! empty($module['dependencies']))),
        ];
    }

    public function adminNavigationItems(string $role = 'admin'): array
    {
        $items = [];

        foreach ($this->allModules() as $module) {
            if (($module['status'] ?? self::STATUS_INACTIVE) !== self::STATUS_ACTIVE) {
                continue;
            }

            foreach ($this->normalizeAdminNavigationItems($module) as $item) {
                if (! $this->roleAllowed($item['roles'] ?? ['admin'], $role)) {
                    continue;
                }

                $items[] = $item;
            }
        }

        usort($items, static function (array $left, array $right): int {
            $sort = ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999));
            if ($sort !== 0) {
                return $sort;
            }

            return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
        });

        return $items;
    }

    public function quickCreateLinks(string $role = 'admin'): array
    {
        $items = [];

        foreach ($this->allModules() as $module) {
            if (($module['status'] ?? self::STATUS_INACTIVE) !== self::STATUS_ACTIVE) {
                continue;
            }

            foreach ($this->normalizeQuickCreateItems($module) as $item) {
                if (! $this->roleAllowed($item['roles'] ?? ['admin'], $role)) {
                    continue;
                }

                $items[] = $item;
            }
        }

        usort($items, static function (array $left, array $right): int {
            $sort = ((int) ($left['sort_order'] ?? 999)) <=> ((int) ($right['sort_order'] ?? 999));
            if ($sort !== 0) {
                return $sort;
            }

            return strcmp((string) ($left['label'] ?? ''), (string) ($right['label'] ?? ''));
        });

        return $items;
    }

    public function installFromUpload(array $uploadedFile): array
    {
        if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($uploadedFile['tmp_name'])) {
            throw new InvalidArgumentException('Please upload a valid module ZIP file.');
        }

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive is not available on this server.');
        }

        $tempRoot = storage_path('module_uploads');
        if (! is_dir($tempRoot)) {
            mkdir($tempRoot, 0777, true);
        }

        $extractPath = $tempRoot . DIRECTORY_SEPARATOR . 'extract_' . uniqid('', true);
        mkdir($extractPath, 0777, true);

        $zip = new ZipArchive();
        if ($zip->open($uploadedFile['tmp_name']) !== true) {
            $this->removeDirectory($extractPath);
            throw new RuntimeException('Unable to open the uploaded ZIP archive.');
        }

        if (! $zip->extractTo($extractPath)) {
            $zip->close();
            $this->removeDirectory($extractPath);
            throw new RuntimeException('Unable to extract the uploaded ZIP archive.');
        }
        $zip->close();

        $moduleRoot = $this->findModuleRoot($extractPath);
        $manifest = $this->readManifest($moduleRoot . DIRECTORY_SEPARATOR . 'module.json');
        $record = $this->persistInstalledModule($manifest, $moduleRoot);

        do_action('onModuleInstall', [
            'slug' => $record['slug'],
            'name' => $record['name'],
            'version' => $record['version'],
            'directory_name' => $record['directory_name'],
        ]);

        $this->removeDirectory($extractPath);

        return $record;
    }

    public function activate(string $slug): array
    {
        $module = $this->findModuleOrFail($slug);
        $errors = $this->dependencyErrors($module, true);
        if ($errors !== []) {
            throw new RuntimeException(implode(' ', $errors));
        }

        Database::connection()->prepare('UPDATE system_modules SET status = :status, activated_at = NOW(), updated_at = NOW() WHERE slug = :slug')->execute([
            'status' => self::STATUS_ACTIVE,
            'slug' => $slug,
        ]);

        $module = $this->findModuleOrFail($slug);
        $this->bootstrapModule($module);
        $this->logActivity($module, 'activate', $module['version'] ?? null, $module['version'] ?? null, 'Module activated.');

        return $module;
    }

    public function deactivate(string $slug): array
    {
        $module = $this->findModuleOrFail($slug);
        $dependents = $this->activeDependents($slug);
        if ($dependents !== []) {
            $names = implode(', ', array_map(static fn (array $item): string => (string) $item['name'], $dependents));
            throw new RuntimeException('Deactivate dependent modules first: ' . $names . '.');
        }

        $this->deactivateSilently($slug, 'Module deactivated.');

        return $this->findModuleOrFail($slug);
    }

    public function delete(string $slug): void
    {
        $module = $this->findModuleOrFail($slug);
        if (($module['status'] ?? self::STATUS_INACTIVE) === self::STATUS_ACTIVE) {
            throw new RuntimeException('Deactivate the module before deleting it.');
        }

        $dependents = $this->dependents($slug);
        if ($dependents !== []) {
            $names = implode(', ', array_map(static fn (array $item): string => (string) $item['name'], $dependents));
            throw new RuntimeException('Delete or update dependent modules first: ' . $names . '.');
        }

        $modulePath = $this->moduleDirectory((string) $module['directory_name']);
        if (is_dir($modulePath)) {
            $this->removeDirectory($modulePath);
        }

        $this->logActivity($module, 'delete', $module['version'] ?? null, null, 'Module deleted.');
        Database::connection()->prepare('DELETE FROM system_modules WHERE slug = :slug')->execute(['slug' => $slug]);
        unset($this->bootedModules[$slug]);
    }

    public function activity(int $limit = 12): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM system_module_activity ORDER BY created_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function dependencyErrors(array $module, bool $requireActive = false): array
    {
        $dependencies = $this->normalizeDependencies($module['manifest']['dependencies'] ?? $module['dependencies'] ?? []);
        if ($dependencies === []) {
            return [];
        }

        $installed = [];
        foreach (Database::connection()->query('SELECT slug, name, version, status FROM system_modules')->fetchAll() as $row) {
            $installed[$row['slug']] = $row;
        }

        $errors = [];
        foreach ($dependencies as $dependency) {
            $slug = (string) ($dependency['slug'] ?? '');
            $constraint = (string) ($dependency['constraint'] ?? '*');

            if ($slug === '') {
                continue;
            }

            $target = $installed[$slug] ?? null;
            if (! $target) {
                $errors[] = 'Requires module "' . $slug . '" to be installed.';
                continue;
            }

            if ($requireActive && ($target['status'] ?? self::STATUS_INACTIVE) !== self::STATUS_ACTIVE) {
                $errors[] = 'Requires module "' . ($target['name'] ?? $slug) . '" to be active.';
            }

            if (! $this->versionSatisfies((string) ($target['version'] ?? '0.0.0'), $constraint)) {
                $errors[] = 'Requires module "' . ($target['name'] ?? $slug) . '" version ' . $constraint . '.';
            }
        }

        return $errors;
    }

    private function bootstrapModule(array $module): void
    {
        $slug = (string) ($module['slug'] ?? '');
        if ($slug === '' || isset($this->bootedModules[$slug])) {
            return;
        }

        $manifest = $this->manifestForRow($module);
        $modulePath = $this->moduleDirectory((string) $module['directory_name']);
        if (! is_dir($modulePath)) {
            return;
        }

        $namespace = trim((string) ($manifest['namespace'] ?? ''));
        if ($namespace !== '') {
            self::$namespaceMap[rtrim($namespace, '\\') . '\\'] = $modulePath;
        }

        $app = $this->app;
        $router = $this->app->router;

        $bootstrapFile = $modulePath . DIRECTORY_SEPARATOR . 'bootstrap.php';
        if (file_exists($bootstrapFile)) {
            (static function (string $file, App $app, Router $router, array $module, array $manifest): void {
                require $file;
            })($bootstrapFile, $app, $router, $module, $manifest);
        }

        $routesFile = $modulePath . DIRECTORY_SEPARATOR . ($module['route_file'] ?: 'routes.php');
        if (file_exists($routesFile)) {
            (static function (string $file, App $app, Router $router, array $module, array $manifest): void {
                require $file;
            })($routesFile, $app, $router, $module, $manifest);
        }

        $this->bootedModules[$slug] = true;
    }

    private function syncDiscoveredModules(): void
    {
        foreach ($this->discoverModules() as $discovered) {
            $modulePath = $discovered['path'];
            $manifest = $discovered['manifest'];
            $existing = $this->findModule($manifest['slug']);

            $payload = [
                'name' => $manifest['name'],
                'description' => $manifest['description'] ?? '',
                'version' => $manifest['version'],
                'directory_name' => basename($modulePath),
                'namespace_prefix' => $manifest['namespace'] ?? '',
                'route_file' => $manifest['routes'] ?? 'routes.php',
                'install_file' => $manifest['install'] ?? 'install.sql',
                'manifest_json' => json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                'slug' => $manifest['slug'],
            ];

            if ($existing) {
                Database::connection()->prepare('
                    UPDATE system_modules
                    SET name = :name, description = :description, version = :version, directory_name = :directory_name,
                        namespace_prefix = :namespace_prefix, route_file = :route_file, install_file = :install_file,
                        manifest_json = :manifest_json, updated_at = NOW()
                    WHERE slug = :slug
                ')->execute($payload);
                continue;
            }

            Database::connection()->prepare('
                INSERT INTO system_modules (name, slug, description, version, directory_name, namespace_prefix, route_file, install_file, status, manifest_json, installed_at, created_at, updated_at)
                VALUES (:name, :slug, :description, :version, :directory_name, :namespace_prefix, :route_file, :install_file, :status, :manifest_json, NOW(), NOW(), NOW())
            ')->execute($payload + ['status' => self::STATUS_INACTIVE]);
        }

        $this->ensureNamespaceMap();
    }

    private function ensureNamespaceMap(): void
    {
        self::$namespaceMap = [];

        foreach ($this->discoverModules() as $discovered) {
            $namespace = trim((string) ($discovered['manifest']['namespace'] ?? ''));
            if ($namespace === '') {
                continue;
            }

            self::$namespaceMap[rtrim($namespace, '\\') . '\\'] = $discovered['path'];
        }
    }

    private function discoverModules(): array
    {
        $modulesRoot = modules_path();
        if (! is_dir($modulesRoot)) {
            mkdir($modulesRoot, 0777, true);
        }

        $directories = glob($modulesRoot . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        $modules = [];

        foreach ($directories as $directory) {
            $manifestPath = $directory . DIRECTORY_SEPARATOR . 'module.json';
            if (! file_exists($manifestPath)) {
                continue;
            }

            $modules[] = [
                'path' => $directory,
                'manifest' => $this->readManifest($manifestPath),
            ];
        }

        usort($modules, static fn (array $a, array $b): int => strcmp($a['manifest']['name'], $b['manifest']['name']));

        return $modules;
    }

    private function persistInstalledModule(array $manifest, string $moduleRoot): array
    {
        $slug = $manifest['slug'];
        $directoryName = $this->sanitizeDirectoryName((string) ($manifest['directory'] ?? basename($moduleRoot)));
        $destination = $this->moduleDirectory($directoryName);
        $existing = $this->findModule($slug);
        $previousVersion = $existing['version'] ?? null;
        $previousStatus = $existing['status'] ?? self::STATUS_INACTIVE;
        $dependencies = $this->normalizeDependencies($manifest['dependencies'] ?? []);

        if ($existing && ! $this->versionSatisfies($manifest['version'], '>=' . (string) $existing['version'])) {
            throw new RuntimeException('Uploaded module version must be greater than or equal to the installed version.');
        }

        $backupPath = null;
        if (is_dir($destination)) {
            $backupPath = modules_path('.backup_' . $directoryName . '_' . uniqid('', true));
            if (! rename($destination, $backupPath)) {
                throw new RuntimeException('Unable to prepare the installed module directory for upgrade.');
            }
        }

        if (! @rename($moduleRoot, $destination)) {
            if ($backupPath && is_dir($backupPath)) {
                @rename($backupPath, $destination);
            }

            throw new RuntimeException('Unable to move the extracted module into the modules directory.');
        }

        try {
            $this->runModuleInstallScript($destination, $manifest['install'] ?? 'install.sql');
            $status = $previousStatus;
            if ($dependencies !== [] && $this->dependencyErrors(['manifest' => $manifest], true) !== []) {
                $status = self::STATUS_INACTIVE;
            }

            $payload = [
                'name' => $manifest['name'],
                'slug' => $slug,
                'description' => $manifest['description'] ?? '',
                'version' => $manifest['version'],
                'directory_name' => $directoryName,
                'namespace_prefix' => $manifest['namespace'] ?? '',
                'route_file' => $manifest['routes'] ?? 'routes.php',
                'install_file' => $manifest['install'] ?? 'install.sql',
                'status' => $status,
                'manifest_json' => json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];

            if ($existing) {
                Database::connection()->prepare('
                    UPDATE system_modules
                    SET name = :name, description = :description, version = :version, directory_name = :directory_name,
                        namespace_prefix = :namespace_prefix, route_file = :route_file, install_file = :install_file,
                        status = :status, manifest_json = :manifest_json, installed_at = NOW(), updated_at = NOW()
                    WHERE slug = :slug
                ')->execute($payload);
            } else {
                Database::connection()->prepare('
                    INSERT INTO system_modules (name, slug, description, version, directory_name, namespace_prefix, route_file, install_file, status, manifest_json, installed_at, created_at, updated_at)
                    VALUES (:name, :slug, :description, :version, :directory_name, :namespace_prefix, :route_file, :install_file, :status, :manifest_json, NOW(), NOW(), NOW())
                ')->execute($payload);
            }

            $record = $this->findModuleOrFail($slug);
            $action = $previousVersion ? 'upgrade' : 'install';
            $notes = $previousVersion ? 'Module upgraded from ' . $previousVersion . ' to ' . $record['version'] . '.' : 'Module installed.';
            $this->logActivity($record, $action, $previousVersion, $record['version'] ?? null, $notes);

            if ($backupPath && is_dir($backupPath)) {
                $this->removeDirectory($backupPath);
            }

            return $record;
        } catch (Throwable $exception) {
            if (is_dir($destination)) {
                $this->removeDirectory($destination);
            }
            if ($backupPath && is_dir($backupPath)) {
                @rename($backupPath, $destination);
            }

            throw $exception;
        }
    }

    private function runModuleInstallScript(string $modulePath, string $installFile): void
    {
        $path = $modulePath . DIRECTORY_SEPARATOR . $installFile;
        if (! file_exists($path)) {
            return;
        }

        $sql = file_get_contents($path);
        if ($sql === false) {
            throw new RuntimeException('Unable to read the module install SQL file.');
        }

        $statement = '';
        foreach (preg_split("/\r\n|\n|\r/", $sql) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $statement .= $line . "\n";
            if (str_ends_with(rtrim($line), ';')) {
                Database::connection()->exec($statement);
                $statement = '';
            }
        }

        if (trim($statement) !== '') {
            Database::connection()->exec($statement);
        }
    }

    private function findModuleRoot(string $extractPath): string
    {
        $rootManifest = $extractPath . DIRECTORY_SEPARATOR . 'module.json';
        if (file_exists($rootManifest)) {
            return $extractPath;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && $item->getFilename() === 'module.json') {
                $moduleRoot = dirname($item->getPathname());
                if (str_starts_with($moduleRoot, $extractPath)) {
                    return $moduleRoot;
                }
            }
        }

        throw new RuntimeException('The uploaded ZIP does not contain a valid module.json manifest.');
    }

    private function readManifest(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read the module manifest.');
        }

        $manifest = json_decode($contents, true);
        if (! is_array($manifest)) {
            throw new RuntimeException('The module manifest is not valid JSON.');
        }

        foreach (['name', 'slug', 'version'] as $field) {
            if (trim((string) ($manifest[$field] ?? '')) === '') {
                throw new RuntimeException('Module manifest missing required field: ' . $field . '.');
            }
        }

        $manifest['slug'] = strtolower(trim((string) $manifest['slug']));
        $manifest['routes'] = trim((string) ($manifest['routes'] ?? 'routes.php'));
        $manifest['install'] = trim((string) ($manifest['install'] ?? 'install.sql'));
        $manifest['dependencies'] = $this->normalizeDependencies($manifest['dependencies'] ?? []);

        return $manifest;
    }

    private function normalizeDependencies(mixed $dependencies): array
    {
        if (! is_array($dependencies)) {
            return [];
        }

        $normalized = [];

        if (array_is_list($dependencies)) {
            foreach ($dependencies as $item) {
                if (is_string($item)) {
                    $normalized[] = ['slug' => strtolower(trim($item)), 'constraint' => '*'];
                    continue;
                }

                if (is_array($item) && ! empty($item['slug'])) {
                    $normalized[] = [
                        'slug' => strtolower(trim((string) $item['slug'])),
                        'constraint' => trim((string) ($item['constraint'] ?? '*')),
                    ];
                }
            }

            return array_values(array_filter($normalized, static fn (array $item): bool => $item['slug'] !== ''));
        }

        foreach ($dependencies as $slug => $constraint) {
            $normalized[] = [
                'slug' => strtolower(trim((string) $slug)),
                'constraint' => trim((string) $constraint) ?: '*',
            ];
        }

        return array_values(array_filter($normalized, static fn (array $item): bool => $item['slug'] !== ''));
    }

    private function normalizeAdminNavigationItems(array $module): array
    {
        $manifest = $module['manifest'] ?? $this->manifestForRow($module);
        $navigation = $manifest['admin_navigation'] ?? $manifest['admin_menu'] ?? [];

        if (! is_array($navigation)) {
            return [];
        }

        $items = array_is_list($navigation) ? $navigation : [$navigation];
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $href = trim((string) ($item['href'] ?? ''));
            $label = trim((string) ($item['label'] ?? $manifest['name'] ?? ''));
            if ($href === '' || $label === '') {
                continue;
            }

            $children = [];
            foreach ($item['children'] ?? [] as $child) {
                if (! is_array($child)) {
                    continue;
                }

                $childHref = trim((string) ($child['href'] ?? ''));
                $childLabel = trim((string) ($child['label'] ?? ''));
                if ($childHref === '' || $childLabel === '') {
                    continue;
                }

                $children[] = [
                    'key' => trim((string) ($child['key'] ?? (($module['slug'] ?? 'module') . '-' . slugify($childLabel, 'menu-child')))),
                    'label' => $childLabel,
                    'href' => $childHref,
                    'match' => $this->normalizeMatchers($child['match'] ?? [$childHref]),
                    'badge' => $this->normalizeBadge($child['badge'] ?? $child['badge_count'] ?? null),
                    'roles' => $this->normalizeRoles($child['roles'] ?? $item['roles'] ?? ['admin']),
                    'permission' => $this->normalizeRoles($child['roles'] ?? $item['roles'] ?? ['admin']),
                    'sort_order' => (int) ($child['sort_order'] ?? 999),
                    'module_source' => (string) ($module['slug'] ?? ''),
                ];
            }

            $normalized[] = [
                'key' => trim((string) ($item['key'] ?? (($module['slug'] ?? 'module') . '-' . slugify($label, 'menu')))),
                'parent_key' => trim((string) ($item['parent_key'] ?? $item['append_to'] ?? '')),
                'group' => trim((string) ($item['group'] ?? 'Extensions')) ?: 'Extensions',
                'section' => trim((string) ($item['section'] ?? $module['slug'] ?? '')) ?: (string) ($module['slug'] ?? ''),
                'label' => $label,
                'icon' => trim((string) ($item['icon'] ?? 'bi-puzzle')) ?: 'bi-puzzle',
                'href' => $href,
                'match' => $this->normalizeMatchers($item['match'] ?? [$href]),
                'children' => $children,
                'roles' => $this->normalizeRoles($item['roles'] ?? ['admin']),
                'permission' => $this->normalizeRoles($item['roles'] ?? ['admin']),
                'sort_order' => (int) ($item['sort_order'] ?? (500 + $index)),
                'badge' => $this->normalizeBadge($item['badge'] ?? $item['badge_count'] ?? null),
                'module_slug' => (string) ($module['slug'] ?? ''),
                'module_source' => (string) ($module['slug'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function normalizeQuickCreateItems(array $module): array
    {
        $manifest = $module['manifest'] ?? $this->manifestForRow($module);
        $actions = $manifest['quick_create'] ?? $manifest['quick_actions'] ?? [];

        if (! is_array($actions)) {
            return [];
        }

        $items = array_is_list($actions) ? $actions : [$actions];
        $normalized = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $href = trim((string) ($item['href'] ?? ''));
            $label = trim((string) ($item['label'] ?? ''));
            if ($href === '' || $label === '') {
                continue;
            }

            $normalized[] = [
                'label' => $label,
                'href' => $href,
                'icon' => trim((string) ($item['icon'] ?? 'bi-plus-square')) ?: 'bi-plus-square',
                'roles' => $this->normalizeRoles($item['roles'] ?? ['admin']),
                'sort_order' => (int) ($item['sort_order'] ?? (500 + $index)),
                'badge' => $this->normalizeBadge($item['badge'] ?? $item['badge_count'] ?? null),
                'module_slug' => (string) ($module['slug'] ?? ''),
            ];
        }

        return $normalized;
    }

    private function normalizeRoles(mixed $roles): array
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (! is_array($roles) || $roles === []) {
            return ['admin'];
        }

        return array_values(array_filter(array_map(static fn (mixed $role): string => strtolower(trim((string) $role)), $roles)));
    }

    private function roleAllowed(array $roles, string $role): bool
    {
        return $roles === [] || in_array(strtolower($role), $roles, true);
    }

    private function normalizeMatchers(mixed $matchers): array
    {
        if (! is_array($matchers)) {
            $matchers = [$matchers];
        }

        $normalized = [];
        foreach ($matchers as $matcher) {
            if (is_string($matcher)) {
                $matcher = trim($matcher);
                if ($matcher !== '') {
                    $normalized[] = $matcher;
                }
                continue;
            }

            if (! is_array($matcher)) {
                continue;
            }

            $path = trim((string) ($matcher['path'] ?? $matcher['href'] ?? ''));
            if ($path === '') {
                continue;
            }

            $normalized[] = [
                'path' => $path,
                'type' => strtolower(trim((string) ($matcher['type'] ?? 'exact'))) ?: 'exact',
            ];
        }

        return $normalized;
    }

    private function normalizeBadge(mixed $badge): ?string
    {
        if ($badge === null) {
            return null;
        }

        $badge = trim((string) $badge);

        return $badge === '' ? null : $badge;
    }

    private function versionSatisfies(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if ($constraint === '' || $constraint === '*') {
            return true;
        }

        foreach (array_filter(array_map('trim', explode(',', $constraint))) as $part) {
            if (str_starts_with($part, '^')) {
                $target = substr($part, 1);
                $major = (int) (explode('.', $target)[0] ?? 0);
                $upper = ($major + 1) . '.0.0';
                if (! (version_compare($version, $target, '>=') && version_compare($version, $upper, '<'))) {
                    return false;
                }
                continue;
            }

            if (str_starts_with($part, '~')) {
                $target = substr($part, 1);
                $segments = explode('.', $target);
                $major = (int) ($segments[0] ?? 0);
                $minor = (int) ($segments[1] ?? 0);
                $upper = $major . '.' . ($minor + 1) . '.0';
                if (! (version_compare($version, $target, '>=') && version_compare($version, $upper, '<'))) {
                    return false;
                }
                continue;
            }

            if (preg_match('/^(>=|<=|>|<|=|==)\s*(.+)$/', $part, $matches)) {
                $operator = $matches[1] === '=' ? '==' : $matches[1];
                if (! version_compare($version, trim($matches[2]), $operator)) {
                    return false;
                }
                continue;
            }

            if (! version_compare($version, $part, '==')) {
                return false;
            }
        }

        return true;
    }

    private function ensureSystemTables(): void
    {
        foreach ($this->schemaStatements() as $statement) {
            Database::connection()->exec($statement);
        }
    }

    private function schemaStatements(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS system_modules (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                description TEXT NULL,
                version VARCHAR(50) NOT NULL,
                directory_name VARCHAR(150) NOT NULL,
                namespace_prefix VARCHAR(191) NULL,
                route_file VARCHAR(150) NOT NULL DEFAULT 'routes.php',
                install_file VARCHAR(150) NOT NULL DEFAULT 'install.sql',
                status ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive',
                manifest_json LONGTEXT NULL,
                installed_at DATETIME NULL,
                activated_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_system_modules_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS system_module_activity (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                module_slug VARCHAR(150) NOT NULL,
                module_name VARCHAR(150) NOT NULL,
                action VARCHAR(50) NOT NULL,
                from_version VARCHAR(50) NULL,
                to_version VARCHAR(50) NULL,
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_system_module_activity_slug (module_slug),
                INDEX idx_system_module_activity_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
    }

    private function logActivity(array $module, string $action, ?string $fromVersion, ?string $toVersion, string $notes): void
    {
        Database::connection()->prepare('
            INSERT INTO system_module_activity (module_slug, module_name, action, from_version, to_version, notes, created_at)
            VALUES (:module_slug, :module_name, :action, :from_version, :to_version, :notes, NOW())
        ')->execute([
            'module_slug' => (string) ($module['slug'] ?? ''),
            'module_name' => (string) ($module['name'] ?? $module['slug'] ?? ''),
            'action' => $action,
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'notes' => $notes,
        ]);
    }

    private function moduleDirectory(string $directoryName): string
    {
        return modules_path($directoryName);
    }

    private function sanitizeDirectoryName(string $directoryName): string
    {
        $directoryName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $directoryName) ?: 'module';
        return trim($directoryName, '-_') ?: 'module';
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $realModulesRoot = realpath(modules_path()) ?: modules_path();
        $realStorageRoot = realpath(storage_path()) ?: storage_path();
        $realPath = realpath($path) ?: $path;
        if (! str_starts_with($realPath, $realModulesRoot) && ! str_starts_with($realPath, $realStorageRoot)) {
            throw new RuntimeException('Refusing to remove a directory outside the modules or storage path.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($path);
    }

    private function manifestForRow(array $row): array
    {
        $manifest = json_decode((string) ($row['manifest_json'] ?? ''), true);
        return is_array($manifest) ? $manifest : [];
    }

    private function findModule(string $slug): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM system_modules WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => strtolower(trim($slug))]);
        $module = $stmt->fetch();

        if (! $module) {
            return null;
        }

        $module['manifest'] = $this->manifestForRow($module);
        return $module;
    }

    private function findModuleOrFail(string $slug): array
    {
        $module = $this->findModule($slug);
        if (! $module) {
            throw new RuntimeException('Module not found.');
        }

        return $module;
    }

    private function deactivateSilently(string $slug, string $notes): void
    {
        Database::connection()->prepare('UPDATE system_modules SET status = :status, updated_at = NOW() WHERE slug = :slug')->execute([
            'status' => self::STATUS_INACTIVE,
            'slug' => $slug,
        ]);

        if ($module = $this->findModule($slug)) {
            $this->logActivity($module, 'deactivate', $module['version'] ?? null, $module['version'] ?? null, $notes);
        }

        unset($this->bootedModules[$slug]);
    }

    private function dependents(string $slug): array
    {
        return array_values(array_filter($this->allModules(), function (array $module) use ($slug): bool {
            foreach ($module['dependencies'] ?? [] as $dependency) {
                if (($dependency['slug'] ?? '') === $slug) {
                    return true;
                }
            }

            return false;
        }));
    }

    private function activeDependents(string $slug): array
    {
        return array_values(array_filter($this->dependents($slug), static function (array $module): bool {
            return ($module['status'] ?? self::STATUS_INACTIVE) === self::STATUS_ACTIVE;
        }));
    }
}
