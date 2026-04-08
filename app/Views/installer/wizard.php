<?php
$currentStep = $step ?? 'welcome';
$steps = $steps ?? [];
$payload = $installerPayload ?? [];
$requirements = $requirements ?? [];
$themePresets = $themePresets ?? [];
$stepKeys = array_keys($steps);
$currentIndex = array_search($currentStep, $stepKeys, true);
$currentIndex = $currentIndex === false ? 0 : $currentIndex;

$field = static function (string $key, mixed $default = '') use ($payload): mixed {
    return old($key, $payload[$key] ?? $default);
};
?>
<section class="installer-wrap">
    <div class="installer-shell-grid">
        <aside class="installer-sidebar">
            <div class="installer-brand">
                <img src="<?= asset('images/badabrand-logo.svg') ?>" alt="Badabrand Logo">
                <div>
                    <strong>Badabrand Technologies</strong>
                    <small>Web Installer v<?= e($installationVersion ?? '1.0.0') ?></small>
                </div>
            </div>
            <div class="installer-intro">
                <span class="eyebrow">Production Setup</span>
                <h1>Launch your Badabrand workspace</h1>
                <p>Install the platform in a few guided steps for XAMPP, shared hosting, and cPanel deployments.</p>
            </div>
            <ol class="installer-step-list">
                <?php foreach ($steps as $key => $meta): ?>
                    <?php
                    $index = array_search($key, $stepKeys, true);
                    $state = 'upcoming';
                    if ($index < $currentIndex) {
                        $state = 'complete';
                    } elseif ($index === $currentIndex) {
                        $state = 'active';
                    }
                    ?>
                    <li class="installer-step-item is-<?= e($state) ?>">
                        <span class="installer-step-index"><?= e((string) ($index + 1)) ?></span>
                        <div>
                            <strong><?= e($meta['label'] ?? ucfirst($key)) ?></strong>
                            <small><?= e($meta['description'] ?? '') ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </aside>
        <div class="installer-main">
            <div class="installer-card">
                <?php if ($currentStep === 'welcome'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Welcome</span>
                        <h2>Start your installation</h2>
                        <p>We will check your server, write your environment settings, import the database, create your super admin, and finalize branding in one guided flow.</p>
                    </div>
                    <div class="installer-info-grid">
                        <div class="mini-panel">
                            <strong>Install target</strong>
                            <span><?= e($field('site_url', request_base_url() ?: 'http://your-domain.com')) ?></span>
                        </div>
                        <div class="mini-panel">
                            <strong>Package version</strong>
                            <span><?= e($installationVersion ?? '1.0.0') ?></span>
                        </div>
                        <div class="mini-panel">
                            <strong>Installer outcome</strong>
                            <span>Writes `.env`, imports SQL, creates admin, stores branding, and locks installer after success.</span>
                        </div>
                    </div>
                    <form method="post" action="<?= route_url('/install/welcome') ?>" class="installer-actions">
                        <button class="btn btn-primary btn-lg rounded-pill" type="submit">Start Installation</button>
                    </form>
                <?php elseif ($currentStep === 'requirements'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 2</span>
                        <h2>Server requirement check</h2>
                        <p>All critical checks below must pass before the installer can continue.</p>
                    </div>
                    <div class="installer-status-list">
                        <?php foreach ($requirements as $check): ?>
                            <div class="installer-status-item is-<?= e($check['status']) ?>">
                                <div class="installer-status-copy">
                                    <strong><?= e($check['label'] ?? 'Check') ?></strong>
                                    <span><?= e($check['detail'] ?? '') ?></span>
                                </div>
                                <span class="installer-status-badge"><?= e(strtoupper((string) ($check['status'] ?? 'pass'))) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <form method="post" action="<?= route_url('/install/requirements') ?>" class="installer-actions">
                        <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/welcome') ?>">Back</a>
                        <button class="btn btn-primary rounded-pill" type="submit">Continue</button>
                    </form>
                <?php elseif ($currentStep === 'site'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 3</span>
                        <h2>Environment and site setup</h2>
                        <p>Define the core site identity and deployment mode for this installation.</p>
                    </div>
                    <form method="post" action="<?= route_url('/install/site') ?>" class="stack-form">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="small-text d-block mb-2">Site name</label><input class="form-control" name="site_name" value="<?= e((string) $field('site_name')) ?>" placeholder="Badabrand Technologies"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Site URL</label><input class="form-control" name="site_url" value="<?= e((string) $field('site_url')) ?>" placeholder="https://your-domain.com"></div>
                            <div class="col-md-4"><label class="small-text d-block mb-2">Timezone</label><input class="form-control" name="timezone" value="<?= e((string) $field('timezone')) ?>" placeholder="Asia/Calcutta"></div>
                            <div class="col-md-4"><label class="small-text d-block mb-2">Language</label><input class="form-control" name="language" value="<?= e((string) $field('language')) ?>" placeholder="en"></div>
                            <div class="col-md-4"><label class="small-text d-block mb-2">Currency</label><input class="form-control" name="currency" value="<?= e((string) $field('currency')) ?>" placeholder="INR"></div>
                            <div class="col-md-6">
                                <label class="small-text d-block mb-2">Environment mode</label>
                                <select class="form-select" name="environment">
                                    <?php foreach (['production' => 'Production', 'staging' => 'Staging', 'development' => 'Development'] as $key => $label): ?>
                                        <option value="<?= e($key) ?>" <?= $field('environment', 'production') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small-text d-block mb-2">Table prefix</label>
                                <input class="form-control" name="table_prefix" value="<?= e((string) $field('table_prefix')) ?>" placeholder="Leave blank" readonly>
                                <div class="small-text mt-2">Fixed table names are used in this release for compatibility with the current codebase.</div>
                            </div>
                        </div>
                        <div class="installer-actions">
                            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/requirements') ?>">Back</a>
                            <button class="btn btn-primary rounded-pill" type="submit">Save & Continue</button>
                        </div>
                    </form>
                <?php elseif ($currentStep === 'database'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 4</span>
                        <h2>Database setup</h2>
                        <p>Enter the MySQL database you created in cPanel, shared hosting, or XAMPP.</p>
                    </div>
                    <form method="post" action="<?= route_url('/install/database') ?>" class="stack-form">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="small-text d-block mb-2">Database host</label><input class="form-control" name="db_host" value="<?= e((string) $field('db_host')) ?>" placeholder="127.0.0.1"></div>
                            <div class="col-md-2"><label class="small-text d-block mb-2">Port</label><input class="form-control" name="db_port" value="<?= e((string) $field('db_port')) ?>" placeholder="3306"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Database name</label><input class="form-control" name="db_name" value="<?= e((string) $field('db_name')) ?>" placeholder="badabrand_technologies"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Database username</label><input class="form-control" name="db_user" value="<?= e((string) $field('db_user')) ?>" placeholder="root"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Database password</label><input class="form-control" name="db_password" type="password" value="<?= e((string) $field('db_password')) ?>" placeholder="Password"></div>
                            <div class="col-md-12">
                                <div class="mini-panel">
                                    <strong>What happens next?</strong>
                                    <span>The installer will test the connection, import the packaged SQL schema, then continue to admin account creation.</span>
                                </div>
                            </div>
                        </div>
                        <div class="installer-actions">
                            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/site') ?>">Back</a>
                            <button class="btn btn-primary rounded-pill" type="submit">Test & Continue</button>
                        </div>
                    </form>
                <?php elseif ($currentStep === 'admin'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 5</span>
                        <h2>Create your super admin</h2>
                        <p>This account will control the full platform after installation.</p>
                    </div>
                    <form method="post" action="<?= route_url('/install/admin') ?>" class="stack-form">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="small-text d-block mb-2">Admin full name</label><input class="form-control" name="admin_name" value="<?= e((string) $field('admin_name')) ?>" placeholder="Admin User"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Admin email</label><input class="form-control" name="admin_email" value="<?= e((string) $field('admin_email')) ?>" placeholder="admin@example.com"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Admin username</label><input class="form-control" name="admin_username" value="<?= e((string) $field('admin_username')) ?>" placeholder="admin"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Phone number</label><input class="form-control" name="admin_phone" value="<?= e((string) $field('admin_phone')) ?>" placeholder="+91 9109566312"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Password</label><input class="form-control" type="password" name="admin_password" placeholder="Minimum 8 characters"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Confirm password</label><input class="form-control" type="password" name="admin_password_confirmation" placeholder="Repeat password"></div>
                            <div class="col-md-12">
                                <div class="mini-panel">
                                    <strong>Password guidance</strong>
                                    <span>Use at least 8 characters with one uppercase letter and one number.</span>
                                </div>
                            </div>
                        </div>
                        <div class="installer-actions">
                            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/database') ?>">Back</a>
                            <button class="btn btn-primary rounded-pill" type="submit">Save & Continue</button>
                        </div>
                    </form>
                <?php elseif ($currentStep === 'branding'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 6</span>
                        <h2>Business and branding setup</h2>
                        <p>Configure the company identity buyers will see across the admin panel, public website, and communication flow.</p>
                    </div>
                    <form method="post" action="<?= route_url('/install/branding') ?>" class="stack-form" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="small-text d-block mb-2">Company name</label><input class="form-control" name="company_name" value="<?= e((string) $field('company_name')) ?>" placeholder="Badabrand Technologies"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Support email</label><input class="form-control" name="support_email" value="<?= e((string) $field('support_email')) ?>" placeholder="support@badabrand.in"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Support phone</label><input class="form-control" name="support_phone" value="<?= e((string) $field('support_phone')) ?>" placeholder="+91 9109566312"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">WhatsApp number</label><input class="form-control" name="company_whatsapp" value="<?= e((string) $field('company_whatsapp')) ?>" placeholder="+91 9109566312"></div>
                            <div class="col-md-12"><label class="small-text d-block mb-2">Company address</label><textarea class="form-control" name="company_address" rows="3" placeholder="Balaghat, Madhya Pradesh, India"><?= e((string) $field('company_address')) ?></textarea></div>
                            <div class="col-md-4"><label class="small-text d-block mb-2">Accent color</label><input class="form-control form-control-color" name="accent_color" type="color" value="<?= e((string) $field('accent_color', '#2d7ff9')) ?>"></div>
                            <div class="col-md-4">
                                <label class="small-text d-block mb-2">Default theme</label>
                                <select class="form-select" name="default_theme">
                                    <?php foreach ($themePresets as $key => $theme): ?>
                                        <option value="<?= e($key) ?>" <?= $field('default_theme', 'dark') === $key ? 'selected' : '' ?>><?= e($theme['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="small-text d-block mb-2">Logo upload</label><input class="form-control" type="file" name="logo_file" accept=".png,.jpg,.jpeg,.svg,.webp"></div>
                            <div class="col-md-6"><label class="small-text d-block mb-2">Favicon upload</label><input class="form-control" type="file" name="favicon_file" accept=".png,.jpg,.jpeg,.svg,.ico,.webp"></div>
                        </div>
                        <div class="installer-actions">
                            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/admin') ?>">Back</a>
                            <button class="btn btn-primary rounded-pill" type="submit">Save & Continue</button>
                        </div>
                    </form>
                <?php elseif ($currentStep === 'modules'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 7</span>
                        <h2>Modules and demo options</h2>
                        <p>Choose which ready-to-use business features should be enabled immediately after installation.</p>
                    </div>
                    <form method="post" action="<?= route_url('/install/modules') ?>" class="stack-form">
                        <div class="installer-check-grid">
                            <?php
                            $checks = [
                                'install_core_modules' => ['label' => 'Install core required modules', 'note' => 'Recommended for complete buyer experience.'],
                                'install_demo_content' => ['label' => 'Install sample/demo content', 'note' => 'If disabled, demo users and live sample transactions are removed after import.'],
                                'enable_marketplace' => ['label' => 'Enable marketplace', 'note' => 'Digital products, vendor products, and marketplace pages.'],
                                'enable_referrals' => ['label' => 'Enable referral system', 'note' => 'Referral codes, rewards, and admin tracking.'],
                                'enable_vendor_system' => ['label' => 'Enable vendor system', 'note' => 'Vendor onboarding, products, commissions, and payouts.'],
                                'enable_documentation' => ['label' => 'Enable documentation area', 'note' => 'In-admin product and deployment documentation.'],
                            ];
                            ?>
                            <?php foreach ($checks as $key => $meta): ?>
                                <label class="installer-check-card">
                                    <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $field($key, '0') === '1' ? 'checked' : '' ?>>
                                    <span>
                                        <strong><?= e($meta['label']) ?></strong>
                                        <small><?= e($meta['note']) ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-12">
                                <label class="small-text d-block mb-2">License key (optional future field)</label>
                                <input class="form-control" name="license_key" value="<?= e((string) $field('license_key')) ?>" placeholder="Optional, leave blank in this release">
                            </div>
                        </div>
                        <div class="installer-actions">
                            <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/branding') ?>">Back</a>
                            <button class="btn btn-primary rounded-pill" type="submit">Save & Continue</button>
                        </div>
                    </form>
                <?php elseif ($currentStep === 'finalize'): ?>
                    <div class="installer-section-head">
                        <span class="eyebrow">Step 8</span>
                        <h2>Finalize installation</h2>
                        <p>Review the setup summary below and run the installation.</p>
                    </div>
                    <div class="installer-summary-grid">
                        <div class="mini-panel">
                            <strong>Site</strong>
                            <span><?= e((string) ($payload['site_name'] ?? '')) ?></span>
                            <small><?= e((string) ($payload['site_url'] ?? '')) ?></small>
                        </div>
                        <div class="mini-panel">
                            <strong>Database</strong>
                            <span><?= e((string) ($payload['db_host'] ?? '')) ?> : <?= e((string) ($payload['db_port'] ?? '3306')) ?></span>
                            <small><?= e((string) ($payload['db_name'] ?? '')) ?></small>
                        </div>
                        <div class="mini-panel">
                            <strong>Admin</strong>
                            <span><?= e((string) ($payload['admin_name'] ?? '')) ?></span>
                            <small><?= e((string) ($payload['admin_email'] ?? '')) ?></small>
                        </div>
                        <div class="mini-panel">
                            <strong>Theme</strong>
                            <span><?= e(theme_presets()[(string) ($payload['default_theme'] ?? 'dark')]['name'] ?? 'Dark Pro') ?></span>
                            <small><?= e((string) ($payload['company_name'] ?? '')) ?></small>
                        </div>
                    </div>
                    <div class="mini-panel mt-4">
                        <strong>Installation actions</strong>
                        <span>Write `.env`, import SQL, configure settings, create/update the admin account, apply branding, set feature flags, and create the installation lock.</span>
                    </div>
                    <form method="post" action="<?= route_url('/install/finalize') ?>" class="installer-actions">
                        <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/install/modules') ?>">Back</a>
                        <button class="btn btn-primary rounded-pill" type="submit">Run Installation</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
