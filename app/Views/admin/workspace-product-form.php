<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h4>
            <p class="mb-0 text-muted">Dedicated product publishing form.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/marketplace') ?>">Back to Products</a>
    </div>

    <form class="stack-form mt-4" method="post" action="<?= route_url($isEdit ? '/admin/products/update' : '/admin/products') ?>" enctype="multipart/form-data">
        <?php if ($isEdit): ?>
            <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
        <?php endif; ?>
        <input class="form-control" name="name" value="<?= e($product['name'] ?? '') ?>" placeholder="Product name" required>
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="product_type">
                    <?php foreach (['theme', 'plugin', 'software', 'template'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= ($product['product_type'] ?? 'theme') === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4"><input class="form-control" name="price" value="<?= e((string) ($product['price'] ?? '')) ?>" placeholder="Price amount"></div>
            <div class="col-md-4"><input class="form-control" name="price_label" value="<?= e($product['price_label'] ?? '') ?>" placeholder="Price label"></div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <select class="form-select" name="vendor_id">
                    <option value="0">Admin-owned product</option>
                    <?php foreach (($vendors ?? []) as $vendor): ?>
                        <option value="<?= e((string) $vendor['id']) ?>" <?= (int) ($product['vendor_id'] ?? 0) === (int) $vendor['id'] ? 'selected' : '' ?>><?= e(($vendor['display_name'] ?: $vendor['store_name']) . ' (' . ($vendor['status'] ?? 'pending') . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="approval_status">
                    <?php foreach (['approved', 'pending', 'rejected'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($product['approval_status'] ?? 'approved') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="commission_percent" value="<?= e((string) ($product['commission_percent'] ?? app_setting('vendor_default_commission', '15'))) ?>" placeholder="Commission %"></div>
        </div>
        <input class="form-control" name="version_label" value="<?= e($product['version_label'] ?? '') ?>" placeholder="Version label">
        <textarea class="form-control" name="short_description" rows="3" placeholder="Short description"><?= e($product['short_description'] ?? '') ?></textarea>
        <textarea class="form-control" name="description" rows="5" placeholder="Description"><?= e($product['description'] ?? '') ?></textarea>
        <textarea class="form-control" name="features_text" rows="4" placeholder="Feature list"><?= e($product['features_text'] ?? '') ?></textarea>
        <input class="form-control" name="download_link" value="<?= e($product['download_link'] ?? '') ?>" placeholder="Download link">
        <input class="form-control" type="file" name="thumbnail_file">
        <div class="row g-3">
            <div class="col-md-6">
                <select class="form-select" name="status">
                    <?php foreach (['active', 'draft', 'inactive'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($product['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><input class="form-control" name="sort_order" value="<?= e((string) ($product['sort_order'] ?? 0)) ?>" placeholder="Sort order"></div>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Product' : 'Create Product' ?></button>
    </form>
</div>
