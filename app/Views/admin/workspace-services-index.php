<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Service Catalog</h4>
            <p class="mb-0 text-muted">List-only service page with dedicated create and edit routes.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/admin/services/create') ?>">Add Service</a>
    </div>

    <?php if ($services === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-grid"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No services published yet</h5>
                <p>Add your first service to populate the admin catalog and customer-facing service pages without leaving this workspace.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-primary" href="<?= route_url('/admin/services/create') ?>">Add Service</a>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/services/categories') ?>">Manage Categories</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="dash-form-block">
                <div class="list-row">
                    <div>
                        <strong><?= e($service['name']) ?></strong>
                        <span><?= e($service['short_description']) ?></span>
                    </div>
                    <span class="badge-soft"><?= e($service['price_label']) ?></span>
                </div>
                <div class="toolbar-actions mt-3">
                    <span class="badge-soft"><?= e($service['slug']) ?></span>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/services/edit/' . $service['id']) ?>">Edit</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
