<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>User Directory</h4>
            <p class="mb-0 text-muted">List-first user management with dedicated create and edit routes.</p>
        </div>
        <a class="btn btn-primary" href="<?= route_url('/admin/users/create') ?>">Create User</a>
    </div>

    <?php foreach ($users as $account): ?>
        <div class="dash-form-block">
            <div class="list-row">
                <div>
                    <strong><?= e(trim($account['first_name'] . ' ' . $account['last_name'])) ?></strong>
                    <span><?= e($account['email']) ?><?= ! empty($account['client_id']) ? ' | ' . e($account['client_id']) : '' ?></span>
                </div>
                <span class="badge-soft"><?= e($account['role']) ?></span>
            </div>
            <div class="toolbar-actions mt-3">
                <span class="badge-soft"><?= e($account['status']) ?></span>
                <a class="btn btn-outline-light" href="<?= route_url('/admin/users/edit/' . $account['id']) ?>">Edit</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
