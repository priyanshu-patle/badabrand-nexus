<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Pricing Plan</h4><span>Dedicated plan workspace</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/plans') ?>">
                <input class="form-control" name="name" placeholder="Plan name" required>
                <input class="form-control" name="price" placeholder="Price">
                <textarea class="form-control" name="description" rows="4" placeholder="Description"></textarea>
                <label class="small-text"><input type="checkbox" name="is_featured" value="1"> Featured plan</label>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary" type="submit">Create Plan</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Pricing Plans</h4><span>Commercial packages</span></div>
            <?php foreach ($plans as $plan): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/plans/update') ?>">
                    <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="name" value="<?= e($plan['name']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="price" value="<?= e($plan['price']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $plan['sort_order']) ?>"></div>
                        <div class="col-md-2 d-flex align-items-center"><label class="small-text mb-0"><input type="checkbox" name="is_featured" value="1" <?= (int) $plan['is_featured'] === 1 ? 'checked' : '' ?>> Featured</label></div>
                        <div class="col-12"><textarea class="form-control" name="description" rows="3"><?= e($plan['description']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/plans/delete') ?>">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
