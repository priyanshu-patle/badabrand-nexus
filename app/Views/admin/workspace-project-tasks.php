<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Project Update</h4><span>Task feed</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/projects/update') ?>">
                <input type="hidden" name="_redirect" value="/admin/projects/tasks">
                <input class="form-control" name="order_id" placeholder="Order ID" required>
                <input class="form-control" name="user_id" placeholder="Client User ID" required>
                <input class="form-control" name="title" placeholder="Update title" required>
                <textarea class="form-control" name="details" rows="4" placeholder="Update details"></textarea>
                <button class="btn btn-primary" type="submit">Post Update</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Recent Updates</h4><span>Project activity stream</span></div>
            <?php foreach ($updates as $update): ?>
                <div class="dash-form-block">
                    <div class="list-row">
                        <div>
                            <strong><?= e($update['title']) ?></strong>
                            <span><?= e($update['order_number']) ?> | <?= e(trim($update['first_name'] . ' ' . $update['last_name'])) ?></span>
                        </div>
                        <span class="badge-soft"><?= e((string) $update['created_at']) ?></span>
                    </div>
                    <p class="mt-3 mb-0 text-muted"><?= e($update['details']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
