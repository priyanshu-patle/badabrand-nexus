<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Career Role</h4><span>Hiring board</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/careers') ?>">
                <input class="form-control" name="title" placeholder="Role title" required>
                <textarea class="form-control" name="summary" rows="3" placeholder="Summary"></textarea>
                <input class="form-control" name="location" placeholder="Location">
                <input class="form-control" name="employment_type" placeholder="Employment type">
                <div class="row g-3">
                    <div class="col-md-6"><select class="form-select" name="status"><option value="open">open</option><option value="closed">closed</option></select></div>
                    <div class="col-md-6"><input class="form-control" name="sort_order" value="0" placeholder="Sort order"></div>
                </div>
                <button class="btn btn-primary" type="submit">Add Role</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Career Roles</h4><span>Published opportunities</span></div>
            <?php foreach ($jobs as $job): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/careers/update') ?>">
                    <input type="hidden" name="career_id" value="<?= e((string) $job['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="title" value="<?= e($job['title']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="location" value="<?= e($job['location']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="employment_type" value="<?= e($job['employment_type']) ?>"></div>
                        <div class="col-md-2"><select class="form-select" name="status"><option value="open" <?= ($job['status'] ?? '') === 'open' ? 'selected' : '' ?>>open</option><option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>closed</option></select></div>
                        <div class="col-12"><textarea class="form-control" name="summary" rows="3"><?= e($job['summary']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/careers/delete') ?>">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
