<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Marketplace Products</h4>
            <p class="mb-0 text-muted">List-first product management with dedicated create and edit screens.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/admin/marketplace/create') ?>">Add Product</a>
    </div>

    <?php if ($products === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-bag"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No marketplace products available</h5>
                <p>Create your first theme, plugin, software, or template listing to start selling through the Badabrand marketplace flow.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="<?= route_url('/admin/marketplace/create') ?>">Add Product</a>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/marketplace/vendors') ?>">Manage Vendors</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($product['name']) ?></strong>
                        <span><?= e($product['short_description']) ?><?= ! empty($product['seller_name']) ? ' | Seller: ' . e($product['seller_name']) : '' ?></span>
                    </div>
                    <span class="badge-soft"><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                </div>
                <div class="toolbar-actions mt-3">
                    <span class="badge-soft"><?= e($product['product_type']) ?> | <?= e($product['status']) ?> | <?= e($product['approval_status'] ?? 'approved') ?></span>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/marketplace/edit/' . $product['id']) ?>">Edit</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
