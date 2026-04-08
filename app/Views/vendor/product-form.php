<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4><?= $isEdit ? 'Edit Vendor Product' : 'Create Vendor Product' ?></h4>
            <p class="mb-0 text-muted">Publish digital products under your approved vendor account.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/vendor/products') ?>">Back to Products</a>
    </div>
    <form class="stack-form mt-4" method="post" action="<?= route_url($isEdit ? '/vendor/products/update' : '/vendor/products') ?>" enctype="multipart/form-data">
        <?php if ($isEdit): ?>
            <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
        <?php endif; ?>
        <input class="form-control" name="name" value="<?= e((string) ($product['name'] ?? '')) ?>" placeholder="Product name" required>
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="product_type">
                    <?php foreach (['theme', 'plugin', 'software', 'template'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= ($product['product_type'] ?? 'software') === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><input class="form-control" name="price" value="<?= e((string) ($product['price'] ?? '')) ?>" placeholder="Price"></div>
            <div class="col-md-4"><input class="form-control" name="price_label" value="<?= e((string) ($product['price_label'] ?? '')) ?>" placeholder="Price label"></div>
        </div>
        <input class="form-control" name="version_label" value="<?= e((string) ($product['version_label'] ?? '')) ?>" placeholder="Version label">
        <textarea class="form-control" name="short_description" rows="3" placeholder="Short description"><?= e((string) ($product['short_description'] ?? '')) ?></textarea>
        <textarea class="form-control" name="description" rows="5" placeholder="Full description"><?= e((string) ($product['description'] ?? '')) ?></textarea>
        <textarea class="form-control" name="features_text" rows="4" placeholder="One feature per line"><?= e((string) ($product['features_text'] ?? '')) ?></textarea>
        <input class="form-control" name="download_link" value="<?= e((string) ($product['download_link'] ?? '')) ?>" placeholder="Download or delivery link">
        <input class="form-control" type="file" name="thumbnail_file">
        <select class="form-select" name="status">
            <?php foreach (['draft', 'active', 'inactive'] as $status): ?>
                <option value="<?= e($status) ?>" <?= ($product['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update Product' : 'Submit Product' ?></button>
    </form>
</div>
