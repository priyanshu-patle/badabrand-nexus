<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="search-toolbar">
                <div>
                    <h4 class="mb-1">Service Catalog Management</h4>
                    <p class="mb-0 text-muted">Search, export, edit, or remove services and pricing plans from one page.</p>
                </div>
                <form class="toolbar-actions" method="get" action="<?= route_url('/admin/services') ?>">
                    <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search service, plan, description, price">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/export?type=services&q=' . urlencode($search ?? '')) ?>">Export Services</a>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/export?type=plans&q=' . urlencode($search ?? '')) ?>">Export Plans</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Service</h4><span>Website service catalog</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/services') ?>">
                <input class="form-control" name="name" placeholder="Service name" required>
                <textarea class="form-control" name="short_description" rows="3" placeholder="Short description"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="price_label" placeholder="Price label"></div>
                    <div class="col-md-6"><input class="form-control" name="icon" value="bi-briefcase" placeholder="Bootstrap icon"></div>
                </div>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary rounded-pill" type="submit">Add Service</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Create Pricing Plan</h4><span>Pricing management</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/plans') ?>">
                <input class="form-control" name="name" placeholder="Plan name" required>
                <input class="form-control" name="price" placeholder="Price">
                <textarea class="form-control" name="description" rows="3" placeholder="Plan description"></textarea>
                <label class="small-text"><input type="checkbox" name="is_featured" value="1"> Mark as featured</label>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-outline-light rounded-pill" type="submit">Create Plan</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Services</h4><span>Edit or delete frontend service items</span></div>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/services/update') ?>">
                        <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><input class="form-control" name="name" value="<?= e($service['name']) ?>" placeholder="Service name"></div>
                            <div class="col-md-3"><input class="form-control" name="price_label" value="<?= e($service['price_label']) ?>" placeholder="Price label"></div>
                            <div class="col-md-3"><input class="form-control" name="icon" value="<?= e($service['icon']) ?>" placeholder="Icon"></div>
                            <div class="col-md-2"><input class="form-control" name="sort_order" value="<?= e((string) $service['sort_order']) ?>" placeholder="Order"></div>
                            <div class="col-12"><textarea class="form-control" name="short_description" rows="2" placeholder="Description"><?= e($service['short_description']) ?></textarea></div>
                        </div>
                        <div class="admin-tools mt-3">
                            <span class="badge-soft"><?= e($service['slug']) ?></span>
                            <div class="toolbar-actions">
                                <button class="btn btn-primary" type="submit">Save</button>
                                <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/services/delete') ?>" onclick="return confirm('Delete this service?')">Delete</button>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No services found for this search.</p>
            <?php endif; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Pricing Plans</h4><span>Edit or delete website pricing cards</span></div>
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/plans/update') ?>">
                        <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><input class="form-control" name="name" value="<?= e($plan['name']) ?>" placeholder="Plan name"></div>
                            <div class="col-md-3"><input class="form-control" name="price" value="<?= e($plan['price']) ?>" placeholder="Price"></div>
                            <div class="col-md-2"><input class="form-control" name="sort_order" value="<?= e((string) $plan['sort_order']) ?>" placeholder="Order"></div>
                            <div class="col-md-3 d-flex align-items-center"><label class="small-text mb-0"><input type="checkbox" name="is_featured" value="1" <?= (int) $plan['is_featured'] === 1 ? 'checked' : '' ?>> Featured plan</label></div>
                            <div class="col-12"><textarea class="form-control" name="description" rows="2" placeholder="Description"><?= e($plan['description']) ?></textarea></div>
                        </div>
                        <div class="admin-tools mt-3">
                            <span class="badge-soft"><?= (int) $plan['is_featured'] === 1 ? 'featured' : 'standard' ?></span>
                            <div class="toolbar-actions">
                                <button class="btn btn-primary" type="submit">Save</button>
                                <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/plans/delete') ?>" onclick="return confirm('Delete this pricing plan?')">Delete</button>
                            </div>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No plans found for this search.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
