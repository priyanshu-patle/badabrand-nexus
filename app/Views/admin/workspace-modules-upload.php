<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Upload Module ZIP</h4><span>Install, extract, and register</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/modules/upload') ?>" enctype="multipart/form-data">
                <input class="form-control" type="file" name="module_zip" accept=".zip" required>
                <div class="small-text">Required: <code>module.json</code>, <code>install.sql</code>, <code>routes.php</code>, and isolated MVC files.</div>
                <button class="btn btn-primary" type="submit">Upload & Install</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Recent Module Activity</h4><span>Install and upgrade history</span></div>
            <?php foreach ($activity as $entry): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($entry['module_name']) ?></strong>
                        <span><?= e($entry['action']) ?><?= ! empty($entry['notes']) ? ' | ' . e($entry['notes']) : '' ?></span>
                    </div>
                    <span class="badge-soft"><?= e((string) $entry['created_at']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
