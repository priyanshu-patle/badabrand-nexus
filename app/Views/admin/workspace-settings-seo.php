<div class="dash-card">
    <div class="card-title-row"><h4>SEO Settings</h4><span>Default metadata and social visibility</span></div>
    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
        <input type="hidden" name="_redirect" value="/admin/settings/seo">
        <input class="form-control" name="seo_default_title" value="<?= e($systemSettings['seo_default_title'] ?? '') ?>" placeholder="Default SEO title">
        <textarea class="form-control" name="seo_default_description" rows="4" placeholder="Default SEO description"><?= e($systemSettings['seo_default_description'] ?? '') ?></textarea>
        <input class="form-control" name="seo_keywords" value="<?= e($systemSettings['seo_keywords'] ?? '') ?>" placeholder="SEO keywords">
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="social_facebook" value="<?= e($systemSettings['social_facebook'] ?? '') ?>" placeholder="Facebook URL"></div>
            <div class="col-md-6"><input class="form-control" name="social_twitter" value="<?= e($systemSettings['social_twitter'] ?? '') ?>" placeholder="Twitter/X URL"></div>
            <div class="col-md-6"><input class="form-control" name="social_linkedin" value="<?= e($systemSettings['social_linkedin'] ?? '') ?>" placeholder="LinkedIn URL"></div>
            <div class="col-md-6"><input class="form-control" name="social_instagram" value="<?= e($systemSettings['social_instagram'] ?? '') ?>" placeholder="Instagram URL"></div>
            <div class="col-md-12"><input class="form-control" name="social_youtube" value="<?= e($systemSettings['social_youtube'] ?? '') ?>" placeholder="YouTube URL"></div>
        </div>
        <button class="btn btn-primary" type="submit">Save SEO Settings</button>
    </form>
</div>
