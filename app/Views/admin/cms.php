<div class="row g-4">
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Branding & Homepage CMS</h4><span>Dark-only production branding</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/cms') ?>" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="site_title" value="<?= e($settings['site_title'] ?? '') ?>" placeholder="Company name"></div>
                    <div class="col-md-6"><input class="form-control" name="footer_company_name" value="<?= e($settings['footer_company_name'] ?? '') ?>" placeholder="Footer company name"></div>
                </div>
                <input class="form-control" name="hero_title" value="<?= e($settings['hero_title'] ?? '') ?>" placeholder="Hero title">
                <textarea class="form-control" name="hero_subtitle" rows="4" placeholder="Hero subtitle"><?= e($settings['hero_subtitle'] ?? '') ?></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="hero_cta_primary" value="<?= e($settings['hero_cta_primary'] ?? '') ?>" placeholder="Primary CTA"></div>
                    <div class="col-md-6"><input class="form-control" name="hero_cta_secondary" value="<?= e($settings['hero_cta_secondary'] ?? '') ?>" placeholder="Secondary CTA"></div>
                </div>
                <textarea class="form-control" name="about_summary" rows="4" placeholder="About summary"><?= e($settings['about_summary'] ?? '') ?></textarea>
                <textarea class="form-control" name="footer_text" rows="3" placeholder="Footer text"><?= e($settings['footer_text'] ?? '') ?></textarea>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>" placeholder="Phone"></div>
                    <div class="col-md-4"><input class="form-control" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>" placeholder="Email"></div>
                    <div class="col-md-4"><input class="form-control" name="contact_address" value="<?= e($settings['contact_address'] ?? '') ?>" placeholder="Address"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" type="file" name="logo_file"></div>
                    <div class="col-md-6"><input class="form-control" type="file" name="favicon_file"></div>
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Save CMS Changes</button>
            </form>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Current Branding</h4><span>Live assets</span></div>
            <div class="mini-panel">
                <strong>Logo</strong>
                <span><?= !empty($settings['company_logo']) ? e($settings['company_logo']) : 'Default brand badge' ?></span>
            </div>
            <div class="mini-panel mt-3">
                <strong>Favicon</strong>
                <span><?= !empty($settings['company_favicon']) ? e($settings['company_favicon']) : 'Default favicon' ?></span>
            </div>
            <div class="mini-panel mt-3">
                <strong>Footer Company Name</strong>
                <span><?= e($settings['footer_company_name'] ?? ($settings['site_title'] ?? 'Badabrand Technologies')) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Homepage Highlight</h4><span>Slider/banner cards</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/cms/sliders') ?>">
                <input class="form-control" name="badge" placeholder="Badge text">
                <input class="form-control" name="title" placeholder="Highlight title">
                <textarea class="form-control" name="subtitle" rows="3" placeholder="Highlight subtitle"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="cta_text" placeholder="CTA label"></div>
                    <div class="col-md-6"><input class="form-control" name="cta_link" placeholder="CTA link"></div>
                </div>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary rounded-pill" type="submit">Add Highlight</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Add Homepage Stat</h4><span>Hero counters</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/cms/stats') ?>">
                <input class="form-control" name="label" placeholder="Label">
                <div class="row g-3">
                    <div class="col-md-5"><input class="form-control" name="value" placeholder="Value"></div>
                    <div class="col-md-3"><input class="form-control" name="suffix" placeholder="+"></div>
                    <div class="col-md-4"><input class="form-control" name="sort_order" value="0" placeholder="Sort"></div>
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Add Stat</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Add Testimonial</h4><span>Homepage social proof</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/cms/testimonials') ?>">
                <input class="form-control" name="name" placeholder="Client name">
                <input class="form-control" name="role" placeholder="Client role">
                <textarea class="form-control" name="quote" rows="4" placeholder="Client quote"></textarea>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary rounded-pill" type="submit">Add Testimonial</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Homepage Highlights</h4><span>Edit slider/banner items</span></div>
            <?php foreach ($slider as $item): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/cms/sliders/update') ?>">
                    <input type="hidden" name="slider_id" value="<?= e((string) $item['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-3"><input class="form-control" name="badge" value="<?= e($item['badge']) ?>" placeholder="Badge"></div>
                        <div class="col-md-5"><input class="form-control" name="title" value="<?= e($item['title']) ?>" placeholder="Title"></div>
                        <div class="col-md-2"><input class="form-control" name="cta_text" value="<?= e($item['cta_text'] ?? '') ?>" placeholder="CTA"></div>
                        <div class="col-md-2"><input class="form-control" name="sort_order" value="<?= e((string) $item['sort_order']) ?>" placeholder="Sort"></div>
                        <div class="col-md-12"><textarea class="form-control" name="subtitle" rows="2" placeholder="Subtitle"><?= e($item['subtitle'] ?? '') ?></textarea></div>
                        <div class="col-md-12"><input class="form-control" name="cta_link" value="<?= e($item['cta_link'] ?? '') ?>" placeholder="CTA link"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/cms/sliders/delete') ?>" onclick="return confirm('Delete this highlight?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Homepage Stats</h4><span>Edit hero counters</span></div>
            <?php foreach ($statsItems as $item): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/cms/stats/update') ?>">
                    <input type="hidden" name="stat_id" value="<?= e((string) $item['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-5"><input class="form-control" name="label" value="<?= e($item['label']) ?>" placeholder="Label"></div>
                        <div class="col-md-3"><input class="form-control" name="value" value="<?= e($item['value']) ?>" placeholder="Value"></div>
                        <div class="col-md-2"><input class="form-control" name="suffix" value="<?= e($item['suffix']) ?>" placeholder="+"></div>
                        <div class="col-md-2"><input class="form-control" name="sort_order" value="<?= e((string) $item['sort_order']) ?>" placeholder="Sort"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/cms/stats/delete') ?>" onclick="return confirm('Delete this stat?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Testimonials</h4><span>Edit homepage testimonials</span></div>
            <?php foreach ($testimonials as $item): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/cms/testimonials/update') ?>">
                    <input type="hidden" name="testimonial_id" value="<?= e((string) $item['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="name" value="<?= e($item['name']) ?>" placeholder="Name"></div>
                        <div class="col-md-4"><input class="form-control" name="role" value="<?= e($item['role']) ?>" placeholder="Role"></div>
                        <div class="col-md-4"><input class="form-control" name="sort_order" value="<?= e((string) $item['sort_order']) ?>" placeholder="Sort"></div>
                        <div class="col-md-12"><textarea class="form-control" name="quote" rows="3" placeholder="Quote"><?= e($item['quote']) ?></textarea></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/cms/testimonials/delete') ?>" onclick="return confirm('Delete this testimonial?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
