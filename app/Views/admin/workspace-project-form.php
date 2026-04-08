<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Create Project</h4>
            <p class="mb-0 text-muted">Create a manual delivery record without touching the existing order workflow.</p>
        </div>
        <a class="btn btn-outline-light" href="<?= route_url('/admin/projects') ?>">Back to Projects</a>
    </div>

    <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/projects/create') ?>">
        <select class="form-select" name="user_id" required>
            <option value="">Select client</option>
            <?php foreach ($clients as $client): ?>
                <option value="<?= e((string) $client['id']) ?>"><?= e(trim($client['first_name'] . ' ' . $client['last_name'])) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="form-control" name="title" placeholder="Project title" required>
        <textarea class="form-control" name="notes" rows="4" placeholder="Scope or internal note"></textarea>
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="total" placeholder="Project total"></div>
            <div class="col-md-4"><input class="form-control" name="progress_percent" value="0" placeholder="Progress %"></div>
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <?php foreach (['approved', 'in_progress', 'active', 'completed'] as $status): ?>
                        <option value="<?= e($status) ?>"><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><input class="form-control" type="date" name="expected_delivery" value="<?= e(date('Y-m-d', strtotime('+14 days'))) ?>"></div>
            <div class="col-md-6"><input class="form-control" type="datetime-local" name="due_at" value="<?= e(date('Y-m-d\TH:i', strtotime('+14 days'))) ?>"></div>
        </div>
        <button class="btn btn-primary" type="submit">Create Project</button>
    </form>
</div>
