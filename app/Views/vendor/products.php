<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Vendor Products</h4>
            <p class="mb-0 text-muted">Create and manage your themes, plugins, templates, and software listings.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/vendor/products/create') ?>">Add Product</a>
    </div>
    <?php if ($products === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-bag"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No vendor products yet</h5>
                <p>Create your first product and submit it for admin review to start selling in the marketplace.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($product['name']) ?></strong>
                        <span><?= e($product['short_description']) ?></span>
                    </div>
                    <span class="badge-soft"><?= e(($product['approval_status'] ?? 'pending') . ' | ' . ($product['status'] ?? 'draft')) ?></span>
                </div>
                <div class="toolbar-actions mt-3">
                    <span class="badge-soft"><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                    <a class="btn btn-outline-light" href="<?= route_url('/vendor/products/edit/' . $product['id']) ?>">Edit Product</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
