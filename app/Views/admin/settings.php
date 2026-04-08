<div class="row g-4">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>System Settings</h4><span>SMTP, SEO, social links, and commercial release settings</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/settings') ?>">
                <div class="dash-form-block">
                    <div class="card-title-row mb-3"><h5>Support & Business</h5><span>Live contact details used across the product</span></div>
                    <div class="row g-3">
                        <div class="col-md-6"><input class="form-control" name="support_email" value="<?= e($systemSettings['support_email'] ?? '') ?>" placeholder="Support email"></div>
                        <div class="col-md-6"><input class="form-control" name="support_phone" value="<?= e($systemSettings['support_phone'] ?? '') ?>" placeholder="Support phone"></div>
                        <div class="col-md-6"><input class="form-control" name="company_whatsapp" value="<?= e($systemSettings['company_whatsapp'] ?? '') ?>" placeholder="WhatsApp number without +"></div>
                    </div>
                </div>

                <div class="dash-form-block">
                    <div class="card-title-row mb-3"><h5>SMTP Configuration</h5><span>Mail sender identity and basic transport details</span></div>
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="smtp_host" value="<?= e($systemSettings['smtp_host'] ?? '') ?>" placeholder="SMTP host"></div>
                        <div class="col-md-2"><input class="form-control" name="smtp_port" value="<?= e($systemSettings['smtp_port'] ?? '') ?>" placeholder="Port"></div>
                        <div class="col-md-6"><input class="form-control" name="smtp_username" value="<?= e($systemSettings['smtp_username'] ?? '') ?>" placeholder="SMTP username"></div>
                        <div class="col-md-6"><input class="form-control" name="smtp_password" value="<?= e($systemSettings['smtp_password'] ?? '') ?>" placeholder="SMTP password"></div>
                        <div class="col-md-3"><input class="form-control" name="smtp_from_name" value="<?= e($systemSettings['smtp_from_name'] ?? '') ?>" placeholder="From name"></div>
                        <div class="col-md-3"><input class="form-control" name="smtp_from_email" value="<?= e($systemSettings['smtp_from_email'] ?? '') ?>" placeholder="From email"></div>
                    </div>
                </div>

                <div class="dash-form-block">
                    <div class="card-title-row mb-3"><h5>SEO Defaults</h5><span>Shared defaults for titles, descriptions, and search indexing</span></div>
                    <div class="row g-3">
                        <div class="col-md-12"><input class="form-control" name="seo_default_title" value="<?= e($systemSettings['seo_default_title'] ?? '') ?>" placeholder="Default SEO title"></div>
                        <div class="col-md-12"><textarea class="form-control" name="seo_default_description" rows="3" placeholder="Default SEO description"><?= e($systemSettings['seo_default_description'] ?? '') ?></textarea></div>
                        <div class="col-md-12"><input class="form-control" name="seo_keywords" value="<?= e($systemSettings['seo_keywords'] ?? '') ?>" placeholder="SEO keywords"></div>
                    </div>
                </div>

                <div class="dash-form-block">
                    <div class="card-title-row mb-3"><h5>Social Links</h5><span>Footer and brand profile URLs</span></div>
                    <div class="row g-3">
                        <div class="col-md-6"><input class="form-control" name="social_facebook" value="<?= e($systemSettings['social_facebook'] ?? '') ?>" placeholder="Facebook URL"></div>
                        <div class="col-md-6"><input class="form-control" name="social_twitter" value="<?= e($systemSettings['social_twitter'] ?? '') ?>" placeholder="Twitter/X URL"></div>
                        <div class="col-md-6"><input class="form-control" name="social_linkedin" value="<?= e($systemSettings['social_linkedin'] ?? '') ?>" placeholder="LinkedIn URL"></div>
                        <div class="col-md-6"><input class="form-control" name="social_instagram" value="<?= e($systemSettings['social_instagram'] ?? '') ?>" placeholder="Instagram URL"></div>
                        <div class="col-md-12"><input class="form-control" name="social_youtube" value="<?= e($systemSettings['social_youtube'] ?? '') ?>" placeholder="YouTube URL"></div>
                    </div>
                </div>

                <div class="dash-form-block">
                    <div class="card-title-row mb-3"><h5>Commercial Release</h5><span>Package metadata used for selling and buyer support</span></div>
                    <div class="row g-3">
                        <div class="col-md-3"><input class="form-control" name="product_version" value="<?= e($systemSettings['product_version'] ?? '') ?>" placeholder="Version"></div>
                        <div class="col-md-3"><input class="form-control" name="license_type" value="<?= e($systemSettings['license_type'] ?? '') ?>" placeholder="License type"></div>
                        <div class="col-md-3"><input class="form-control" name="buyer_support_window" value="<?= e($systemSettings['buyer_support_window'] ?? '') ?>" placeholder="Support window"></div>
                        <div class="col-md-3"><input class="form-control" name="release_channel" value="<?= e($systemSettings['release_channel'] ?? '') ?>" placeholder="Release channel"></div>
                    </div>
                </div>

                <button class="btn btn-primary rounded-pill" type="submit">Save System Settings</button>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Settings Guide</h4><span>First-run checklist</span></div>
            <div class="mini-panel">
                <strong>1. Business identity</strong>
                <span>Set support email, phone, and WhatsApp so contact actions and outgoing mail match the buyer brand.</span>
            </div>
            <div class="mini-panel mt-3">
                <strong>2. SMTP sender</strong>
                <span>Save host, port, and sender details for mail headers and XAMPP/shared hosting configuration.</span>
            </div>
            <div class="mini-panel mt-3">
                <strong>3. SEO defaults</strong>
                <span>Define marketplace-ready meta text once, then override page-specific content where needed.</span>
            </div>
            <div class="mini-panel mt-3">
                <strong>4. Release metadata</strong>
                <span>Track version, license type, support window, and release channel for buyer documentation.</span>
            </div>
        </div>
    </div>
</div>
