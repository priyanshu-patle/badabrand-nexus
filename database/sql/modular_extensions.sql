CREATE TABLE IF NOT EXISTS system_modules (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_module_activity (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
