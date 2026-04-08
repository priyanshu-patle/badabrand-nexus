DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS sliders;
DROP TABLE IF EXISTS stats;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS portfolio_projects;
DROP TABLE IF EXISTS faqs;
DROP TABLE IF EXISTS careers;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS pricing_plans;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS user_files;
DROP TABLE IF EXISTS contracts;
DROP TABLE IF EXISTS proposals;
DROP TABLE IF EXISTS project_updates;
DROP TABLE IF EXISTS ticket_messages;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS invoice_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS product_reviews;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS referrals;
DROP TABLE IF EXISTS vendor_activity_logs;
DROP TABLE IF EXISTS vendor_documents;
DROP TABLE IF EXISTS vendor_payouts;
DROP TABLE IF EXISTS vendor_commissions;
DROP TABLE IF EXISTS vendor_payout_accounts;
DROP TABLE IF EXISTS vendor_profiles;
DROP TABLE IF EXISTS vendors;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','customer','developer','vendor') NOT NULL DEFAULT 'customer',
    client_id VARCHAR(30) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    terms_accepted_at DATETIME NULL,
    theme_preference VARCHAR(40) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(190) NOT NULL UNIQUE,
    setting_value LONGTEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE referrals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    referral_code VARCHAR(64) NOT NULL UNIQUE,
    referred_by_user_id BIGINT UNSIGNED NULL,
    referred_by_code VARCHAR(64) NULL,
    total_signups INT NOT NULL DEFAULT 0,
    total_earned DECIMAL(12,2) NOT NULL DEFAULT 0,
    reward_balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    payout_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
    notes TEXT NULL,
    last_referred_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    store_name VARCHAR(190) NOT NULL,
    display_name VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    email VARCHAR(190) NULL,
    phone VARCHAR(60) NULL,
    tax_gst VARCHAR(120) NULL,
    status ENUM('pending','approved','rejected','suspended','inactive') NOT NULL DEFAULT 'pending',
    approval_status VARCHAR(40) NOT NULL DEFAULT 'pending',
    commission_percent DECIMAL(5,2) NULL,
    verification_badge TINYINT(1) NOT NULL DEFAULT 0,
    admin_notes TEXT NULL,
    joined_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL UNIQUE,
    business_name VARCHAR(190) NULL,
    legal_name VARCHAR(190) NULL,
    short_bio TEXT NULL,
    address_line1 VARCHAR(190) NULL,
    address_line2 VARCHAR(190) NULL,
    city VARCHAR(120) NULL,
    state VARCHAR(120) NULL,
    country VARCHAR(120) NULL,
    postal_code VARCHAR(30) NULL,
    website VARCHAR(255) NULL,
    support_email VARCHAR(190) NULL,
    support_phone VARCHAR(60) NULL,
    logo_path VARCHAR(255) NULL,
    banner_path VARCHAR(255) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_payout_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL UNIQUE,
    payout_method VARCHAR(40) NOT NULL DEFAULT 'bank_transfer',
    account_name VARCHAR(190) NULL,
    account_number VARCHAR(120) NULL,
    ifsc_swift VARCHAR(120) NULL,
    upi_id VARCHAR(150) NULL,
    paypal_email VARCHAR(190) NULL,
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_commissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    invoice_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NULL,
    payment_id BIGINT UNSIGNED NULL,
    payout_id BIGINT UNSIGNED NULL,
    gross_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    commission_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    vendor_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payout_status VARCHAR(40) NOT NULL DEFAULT 'pending',
    available_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_payouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    request_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    approved_amount DECIMAL(12,2) NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'INR',
    status VARCHAR(40) NOT NULL DEFAULT 'requested',
    reference_number VARCHAR(120) NULL,
    requested_at DATETIME NULL,
    processed_at DATETIME NULL,
    admin_note TEXT NULL,
    payout_note TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(60) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    notes TEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE vendor_activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NOT NULL,
    actor_user_id BIGINT UNSIGNED NULL,
    action VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    context LONGTEXT NULL,
    created_at DATETIME NULL
);

CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    short_description TEXT NULL,
    icon VARCHAR(100) NULL,
    price_label VARCHAR(190) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE pricing_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    price VARCHAR(190) NOT NULL,
    description TEXT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE testimonials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    role VARCHAR(190) NULL,
    quote TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE stats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(120) NOT NULL,
    value VARCHAR(40) NOT NULL,
    suffix VARCHAR(20) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE sliders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    badge VARCHAR(120) NOT NULL,
    title VARCHAR(180) NOT NULL,
    subtitle TEXT NULL,
    cta_text VARCHAR(80) NULL,
    cta_link VARCHAR(200) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NULL,
    meta_title VARCHAR(180) NULL,
    meta_description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE blogs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category VARCHAR(120) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NULL,
    meta_title VARCHAR(180) NULL,
    meta_description VARCHAR(255) NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE careers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    summary TEXT NOT NULL,
    location VARCHAR(120) NOT NULL,
    employment_type VARCHAR(80) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE faqs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE portfolio_projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    category VARCHAR(120) NOT NULL,
    client_name VARCHAR(150) NULL,
    summary TEXT NOT NULL,
    tech_stack VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE team_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    role VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendor_id BIGINT UNSIGNED NULL,
    name VARCHAR(190) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    category VARCHAR(120) NOT NULL DEFAULT 'software',
    product_type VARCHAR(60) NOT NULL DEFAULT 'software',
    short_description TEXT NULL,
    description LONGTEXT NULL,
    features_text LONGTEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    price_label VARCHAR(190) NULL,
    version_label VARCHAR(120) NULL,
    thumbnail_path VARCHAR(255) NULL,
    download_link VARCHAR(255) NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'active',
    approval_status VARCHAR(40) NOT NULL DEFAULT 'approved',
    commission_percent DECIMAL(5,2) NULL,
    requires_review TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NULL,
    vendor_id BIGINT UNSIGNED NULL,
    order_type VARCHAR(60) NOT NULL DEFAULT 'service',
    item_name VARCHAR(190) NULL,
    pricing_plan_name VARCHAR(190) NULL,
    notes TEXT NULL,
    receipt_number VARCHAR(120) NULL,
    expected_delivery DATE NULL,
    order_number VARCHAR(120) NOT NULL UNIQUE,
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    progress_percent INT NOT NULL DEFAULT 0,
    due_at DATETIME NULL,
    commission_percent DECIMAL(5,2) NULL,
    platform_fee_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    vendor_net_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payout_status VARCHAR(40) NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    invoice_number VARCHAR(120) NOT NULL UNIQUE,
    billing_name VARCHAR(190) NULL,
    gst_number VARCHAR(120) NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    gst_percent DECIMAL(5,2) NOT NULL DEFAULT 18,
    gst_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    due_date DATE NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'unpaid',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(190) NOT NULL,
    description TEXT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NULL,
    gateway VARCHAR(60) NOT NULL DEFAULT 'manual_bank',
    transaction_id VARCHAR(190) NULL,
    proof_path VARCHAR(255) NULL,
    notes TEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'INR',
    status VARCHAR(40) NOT NULL DEFAULT 'pending',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(190) NOT NULL,
    priority VARCHAR(40) NOT NULL DEFAULT 'medium',
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    message LONGTEXT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE ticket_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    sender_type VARCHAR(30) NOT NULL DEFAULT 'customer',
    sender_id BIGINT UNSIGNED NULL,
    message LONGTEXT NOT NULL,
    created_at DATETIME NULL
);

CREATE TABLE project_updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    details LONGTEXT NULL,
    created_at DATETIME NULL
);

CREATE TABLE proposals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    description LONGTEXT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    valid_until DATE NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    submitted_by_customer TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    contract_body LONGTEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    signature_name VARCHAR(190) NULL,
    signed_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE user_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    file_name VARCHAR(190) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(80) NULL,
    created_at DATETIME NULL
);

CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(60) NULL,
    service VARCHAR(190) NULL,
    message LONGTEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'new',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    discount_type VARCHAR(40) NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

CREATE TABLE email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    html_body LONGTEXT NULL,
    text_body LONGTEXT NULL,
    related_type VARCHAR(80) NULL,
    related_id BIGINT UNSIGNED NULL,
    delivery_status VARCHAR(40) NOT NULL DEFAULT 'queued',
    created_at DATETIME NULL,
    updated_at DATETIME NULL
);

INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES
('site_title', 'Badabrand Technologies', NOW(), NOW()),
('company_logo', 'assets/images/badabrand-logo.svg', NOW(), NOW()),
('company_favicon', 'assets/images/badabrand-favicon.svg', NOW(), NOW()),
('footer_company_name', 'Badabrand Technologies', NOW(), NOW()),
('hero_title', 'Build your digital business with Badabrand Technologies', NOW(), NOW()),
('hero_subtitle', 'We design websites, apps, hosting, branding, and business systems with a clean admin CMS and secure customer dashboards.', NOW(), NOW()),
('hero_cta_primary', 'Explore Services', NOW(), NOW()),
('hero_cta_secondary', 'Contact Us', NOW(), NOW()),
('about_summary', 'This build keeps the bold corporate tone of your provided homepage design, but now runs with login, registration, CMS editing, role-based dashboards, and MySQL-backed data.', NOW(), NOW()),
('footer_text', 'Production-ready IT services website with admin CMS, billing, support, and client portal workflows.', NOW(), NOW()),
('contact_phone', '+91 9109566312', NOW(), NOW()),
('contact_email', 'support@badabrand.in', NOW(), NOW()),
('contact_address', 'Balaghat, Madhya Pradesh, India', NOW(), NOW()),
('support_email', 'support@badabrand.in', NOW(), NOW()),
('support_phone', '+91 9109566312', NOW(), NOW()),
('company_whatsapp', '+91 9109566312', NOW(), NOW()),
('smtp_host', 'smtp.example.com', NOW(), NOW()),
('smtp_port', '587', NOW(), NOW()),
('smtp_username', 'support@example.com', NOW(), NOW()),
('smtp_password', '', NOW(), NOW()),
('smtp_from_name', 'Badabrand Technologies', NOW(), NOW()),
('smtp_from_email', 'support@badabrand.in', NOW(), NOW()),
('seo_default_title', 'Badabrand Technologies | IT Services Platform', NOW(), NOW()),
('seo_default_description', 'Premium IT services platform with website, client portal, admin dashboard, billing, CMS, and digital product marketplace.', NOW(), NOW()),
('seo_keywords', 'IT services platform, admin dashboard, customer portal, billing system, digital marketplace', NOW(), NOW()),
('social_facebook', '#', NOW(), NOW()),
('social_twitter', '#', NOW(), NOW()),
('social_linkedin', '#', NOW(), NOW()),
('social_instagram', '#', NOW(), NOW()),
('social_youtube', '#', NOW(), NOW()),
('theme_default', 'dark', NOW(), NOW()),
('theme_admin', 'dark', NOW(), NOW()),
('theme_public', 'dark', NOW(), NOW()),
('referral_enabled', '1', NOW(), NOW()),
('referral_reward_amount', '500', NOW(), NOW()),
('referral_percentage', '5', NOW(), NOW()),
('referral_minimum_payout', '1000', NOW(), NOW()),
('vendor_enabled', '1', NOW(), NOW()),
('vendor_auto_approve', '0', NOW(), NOW()),
('vendor_default_commission', '15', NOW(), NOW()),
('vendor_product_requires_review', '1', NOW(), NOW()),
('vendor_minimum_payout', '1000', NOW(), NOW()),
('product_version', '2.0.0', NOW(), NOW()),
('license_type', 'Single end-product commercial license', NOW(), NOW()),
('buyer_support_window', '6 months', NOW(), NOW()),
('release_channel', 'Stable', NOW(), NOW());

INSERT INTO stats (label, value, suffix, sort_order) VALUES
('Clients Supported', '240', '+', 1),
('Projects Delivered', '620', '+', 2),
('Hosting Uptime', '99.95', '%', 3),
('Support Coverage', '18', 'x7', 4);

INSERT INTO sliders (badge, title, subtitle, cta_text, cta_link, sort_order, created_at, updated_at) VALUES
('Premium IT Delivery', 'Sell, support, and scale from one platform.', 'Website, portal, admin, billing, and automation under one brand system.', 'Start Now', '/contact', 1, NOW(), NOW()),
('Client Experience', 'Give customers real visibility into work in progress.', 'Track projects, invoices, files, tickets, and renewals securely.', 'Client Portal', '/client', 2, NOW(), NOW()),
('Admin Command Center', 'Control every page, service, user, and system setting.', 'Manage content, users, marketing, billing, and operations centrally.', 'Admin Panel', '/admin', 3, NOW(), NOW());

INSERT INTO testimonials (name, role, quote, sort_order, created_at, updated_at) VALUES
('Rahul S.', 'Retail Owner', 'Badabrand helped us take our offline store online with a clean and affordable website.', 1, NOW(), NOW()),
('Amit M.', 'Real Estate Consultant', 'Their team handled website delivery, hosting, and support smoothly from day one.', 2, NOW(), NOW()),
('Neha P.', 'Startup Founder', 'The admin panel and client dashboard make the experience feel much more professional.', 3, NOW(), NOW());

INSERT INTO pages (title, slug, excerpt, content, meta_title, meta_description, created_at, updated_at) VALUES
('Privacy Policy', 'privacy-policy', 'How customer, billing, support, and uploaded file data is handled across the platform.', '<p>Badabrand Technologies collects only the information required to deliver services, process invoices, manage support, and maintain secure customer accounts.</p><p>Uploaded files, contracts, and payment screenshots should be stored securely with access restricted by user role and account ownership.</p>', 'Privacy Policy | Badabrand Technologies', 'Privacy policy for customer accounts, files, billing, and support data.', NOW(), NOW()),
('Terms of Service', 'terms', 'Terms acceptance is built into first-time client login and managed project delivery.', '<p>Customers must accept the service terms during their first login before accessing project files, invoices, or support modules.</p><p>Projects, retainers, and managed services may carry separate commercial scopes through proposals and digital contracts generated within the platform.</p>', 'Terms of Service | Badabrand Technologies', 'Terms and service conditions for services, products, and portal access.', NOW(), NOW());

INSERT INTO blogs (title, slug, category, excerpt, content, meta_title, meta_description, status, published_at, created_at, updated_at) VALUES
('How IT Companies Can Productize Services in 2026', 'productize-services-2026', 'Strategy', 'A practical look at turning custom service delivery into repeatable offers and predictable billing.', '<p>Productized services make pricing clearer, sales faster, and delivery easier to scale.</p><p>This platform supports that model with service packages, automated orders, invoices, contracts, and customer dashboards.</p>', 'How IT Companies Can Productize Services in 2026', 'Strategy article about productized IT services and customer operations.', 'published', NOW(), NOW(), NOW()),
('Hosting Automation Checklist for Indian Agencies', 'hosting-automation-checklist', 'Operations', 'The workflow checklist agencies should use for renewals, invoices, support, and uptime management.', '<p>Hosting work becomes easier to manage when support, billing, renewal reminders, and delivery records live in one place.</p>', 'Hosting Automation Checklist for Indian Agencies', 'Operations guide for hosting, billing, and support automation.', 'published', NOW(), NOW(), NOW());

INSERT INTO careers (title, summary, location, employment_type, status, sort_order, created_at, updated_at) VALUES
('Frontend Engineer', 'Build polished customer dashboards, admin workflows, and responsive public experiences.', 'Remote / Kolkata', 'Full Time', 'open', 1, NOW(), NOW()),
('PHP Platform Developer', 'Work on billing, support, contracts, marketplace modules, and reusable backend systems.', 'Remote / Kolkata', 'Full Time', 'open', 2, NOW(), NOW());

INSERT INTO faqs (question, answer, sort_order, created_at, updated_at) VALUES
('Can this platform scale into a larger product?', 'Yes. The structure is modular and ready to grow into a larger SaaS or productized service workflow.', 1, NOW(), NOW()),
('Do you support manual payments and GST invoicing?', 'Yes. Manual bank and QR submissions, GST-ready invoices, approval states, and payment records are included.', 2, NOW(), NOW()),
('Can clients track projects live?', 'Yes. Customers can follow order status, project progress, invoices, files, proposals, contracts, and support updates from their dashboard.', 3, NOW(), NOW());

INSERT INTO portfolio_projects (title, slug, category, client_name, summary, tech_stack, is_featured, sort_order, created_at, updated_at) VALUES
('ZenHost Cloud Suite', 'zenhost-cloud-suite', 'Hosting SaaS', 'ZenHost', 'Customer portal and billing suite for hosting sales, renewals, and support.', 'PHP, MySQL, Bootstrap, Billing Workflows', 1, 1, NOW(), NOW()),
('MediFlow Connect', 'mediflow-connect', 'Healthcare Platform', 'MediFlow', 'Patient booking and operations dashboard with secure workflows and admin control.', 'Portal UX, Admin Dashboard, Secure Forms', 1, 2, NOW(), NOW()),
('RetailPulse AI', 'retailpulse-ai', 'Commerce Dashboard', 'RetailPulse', 'Retail reporting interface for order insights, sales visibility, and managed operations.', 'Reporting UI, Commerce Ops, Analytics', 0, 3, NOW(), NOW());

INSERT INTO team_members (user_id, name, role, email, created_at, updated_at) VALUES
(1, 'Ritika Sharma', 'Account Manager', 'ritika@badabrand.com', NOW(), NOW()),
(1, 'Dev Bose', 'Technical Lead', 'dev@badabrand.com', NOW(), NOW());
