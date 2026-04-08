<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4><?= $isEdit ? 'Edit User' : 'Create User' ?></h4>
            <p class="mb-0 text-muted">Dedicated user form page for cleaner admin workflows.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/users') ?>">Back to Users</a>
    </div>

    <form class="stack-form mt-4" method="post" action="<?= route_url($isEdit ? '/admin/users/update' : '/admin/users/add') ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="user_id" value="<?= e((string) $userRecord['id']) ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" name="first_name" value="<?= e($userRecord['first_name'] ?? '') ?>" placeholder="First name" required></div>
            <div class="col-md-6"><input class="form-control" name="last_name" value="<?= e($userRecord['last_name'] ?? '') ?>" placeholder="Last name"></div>
            <div class="col-md-6"><input class="form-control" name="email" value="<?= e($userRecord['email'] ?? '') ?>" placeholder="Email" required></div>
            <div class="col-md-6"><input class="form-control" name="phone" value="<?= e($userRecord['phone'] ?? '') ?>" placeholder="Phone"></div>
            <div class="col-md-6">
                <select class="form-select" name="role">
                    <?php foreach (['customer', 'vendor', 'developer', 'admin'] as $roleOption): ?>
                        <option value="<?= e($roleOption) ?>" <?= ($userRecord['role'] ?? 'customer') === $roleOption ? 'selected' : '' ?>><?= e($roleOption) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <select class="form-select" name="status">
                    <?php foreach (['active', 'suspended'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($userRecord['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (! $isEdit): ?>
                <div class="col-12"><input class="form-control" name="password" placeholder="Password" required></div>
            <?php endif; ?>
        </div>
        <div class="toolbar-actions mt-3">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save User' : 'Create User' ?></button>
        </div>
    </form>
</div>
