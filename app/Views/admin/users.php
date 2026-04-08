<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add User</h4><span>Create admin, customer, or developer</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/users/add') ?>">
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="first_name" placeholder="First name"></div>
                    <div class="col-md-6"><input class="form-control" name="last_name" placeholder="Last name"></div>
                </div>
                <input class="form-control" name="email" placeholder="Email">
                <input class="form-control" name="phone" placeholder="Phone">
                <div class="row g-3">
                    <div class="col-md-6"><select class="form-select" name="role"><option value="customer">customer</option><option value="developer">developer</option><option value="admin">admin</option></select></div>
                    <div class="col-md-6"><select class="form-select" name="status"><option value="active">active</option><option value="suspended">suspended</option></select></div>
                </div>
                <input class="form-control" name="password" placeholder="Password">
                <button class="btn btn-primary rounded-pill" type="submit">Add User</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>User Management</h4><span>Add, edit, delete, suspend, approve</span></div>
            <?php foreach ($users as $account): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/users/update') ?>">
                    <input type="hidden" name="user_id" value="<?= e((string) $account['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-3"><input class="form-control" name="first_name" value="<?= e($account['first_name']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="last_name" value="<?= e($account['last_name']) ?>"></div>
                        <div class="col-md-4"><input class="form-control" name="email" value="<?= e($account['email']) ?>"></div>
                        <div class="col-md-2"><input class="form-control" name="phone" value="<?= e($account['phone'] ?? '') ?>"></div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3"><select class="form-select" name="role"><option value="admin" <?= $account['role']==='admin'?'selected':'' ?>>admin</option><option value="customer" <?= $account['role']==='customer'?'selected':'' ?>>customer</option><option value="developer" <?= $account['role']==='developer'?'selected':'' ?>>developer</option></select></div>
                        <div class="col-md-3"><select class="form-select" name="status"><option value="active" <?= $account['status']==='active'?'selected':'' ?>>active</option><option value="suspended" <?= $account['status']==='suspended'?'selected':'' ?>>suspended</option></select></div>
                        <div class="col-md-6 d-flex gap-2">
                            <button class="btn btn-primary flex-fill" type="submit">Save</button>
                            <button class="btn btn-outline-light flex-fill" formaction="<?= route_url('/admin/users/status') ?>" name="status" value="active" type="submit">Approve</button>
                            <button class="btn btn-outline-light flex-fill" formaction="<?= route_url('/admin/users/status') ?>" name="status" value="suspended" type="submit">Suspend</button>
                            <button class="btn btn-danger flex-fill" formaction="<?= route_url('/admin/users/delete') ?>" type="submit">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
