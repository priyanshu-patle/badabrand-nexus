<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4><?= $isEdit ? 'Edit Service' : 'Create Service' ?></h4>
            <p class="mb-0 text-muted">One route, one purpose: service creation or editing.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/services') ?>">Back to Services</a>
    </div>

    <form class="stack-form mt-4" method="post" action="<?= route_url($isEdit ? '/admin/services/update' : '/admin/services') ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
        <?php endif; ?>
        <input class="form-control" name="name" value="<?= e($service['name'] ?? '') ?>" placeholder="Service name" required>
        <textarea class="form-control" name="short_description" rows="4" placeholder="Short description"><?= e($service['short_description'] ?? '') ?></textarea>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="price_label" value="<?= e($service['price_label'] ?? '') ?>" placeholder="Price label"></div>
            <div class="col-md-6"><input class="form-control" name="icon" value="<?= e($service['icon'] ?? 'bi-briefcase') ?>" placeholder="Bootstrap icon"></div>
        </div>
        <input class="form-control" name="sort_order" value="<?= e((string) ($service['sort_order'] ?? 0)) ?>" placeholder="Sort order">
        <div class="toolbar-actions">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save Service' : 'Create Service' ?></button>
        </div>
    </form>
</div>
