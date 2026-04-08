<div class="dash-card">
    <div class="card-title-row"><h4>Pages</h4><span>Public legal and content pages</span></div>
    <?php foreach ($pages as $page): ?>
        <form class="dash-form-block" method="post" action="<?= route_url('/admin/pages/update') ?>">
            <input type="hidden" name="page_id" value="<?= e((string) $page['id']) ?>">
            <div class="row g-3">
                <div class="col-md-6"><input class="form-control" name="title" value="<?= e($page['title']) ?>"></div>
                <div class="col-md-6"><input class="form-control" name="slug" value="<?= e($page['slug']) ?>"></div>
                <div class="col-12"><textarea class="form-control" name="excerpt" rows="2"><?= e($page['excerpt']) ?></textarea></div>
                <div class="col-12"><textarea class="form-control" name="content" rows="6"><?= e($page['content']) ?></textarea></div>
                <div class="col-md-6"><input class="form-control" name="meta_title" value="<?= e($page['meta_title']) ?>"></div>
                <div class="col-md-6"><input class="form-control" name="meta_description" value="<?= e($page['meta_description']) ?>"></div>
            </div>
            <div class="toolbar-actions mt-3"><button class="btn btn-primary" type="submit">Save Page</button></div>
        </form>
    <?php endforeach; ?>
</div>
