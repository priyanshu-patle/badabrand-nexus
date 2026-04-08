<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Blog Post</h4><span>Publishing</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/blogs') ?>">
                <input class="form-control" name="title" placeholder="Title" required>
                <input class="form-control" name="slug" placeholder="Slug">
                <input class="form-control" name="category" placeholder="Category">
                <textarea class="form-control" name="excerpt" rows="2" placeholder="Excerpt"></textarea>
                <textarea class="form-control" name="content" rows="6" placeholder="Content"></textarea>
                <input class="form-control" name="meta_title" placeholder="Meta title">
                <textarea class="form-control" name="meta_description" rows="3" placeholder="Meta description"></textarea>
                <select class="form-select" name="status"><option value="draft">Draft</option><option value="published">Published</option></select>
                <button class="btn btn-primary" type="submit">Create Post</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Blog Posts</h4><span>Content records</span></div>
            <?php foreach ($blogs as $post): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/blogs/update') ?>">
                    <input type="hidden" name="blog_id" value="<?= e((string) $post['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($post['title']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="slug" value="<?= e($post['slug']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="category" value="<?= e($post['category']) ?>"></div>
                        <div class="col-md-2"><select class="form-select" name="status"><option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option><option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option></select></div>
                        <div class="col-12"><textarea class="form-control" name="excerpt" rows="2"><?= e($post['excerpt']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="content" rows="5"><?= e($post['content']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/blogs/delete') ?>">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
