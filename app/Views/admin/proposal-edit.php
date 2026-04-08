<div class="dash-card">
    <div class="card-title-row"><h4>Edit Proposal</h4><span>PDF-ready quotation data</span></div>
    <form class="stack-form" method="post" action="<?= route_url('/admin/projects/proposal/update') ?>">
        <input type="hidden" name="proposal_id" value="<?= e((string) $proposal['id']) ?>">
        <input class="form-control" name="title" value="<?= e($proposal['title']) ?>" placeholder="Title">
        <textarea class="form-control" name="description" rows="4" placeholder="Description"><?= e($proposal['description'] ?? '') ?></textarea>
        <div class="row g-3">
            <div class="col-md-4"><input class="form-control" name="amount" value="<?= e((string) $proposal['amount']) ?>" placeholder="Amount"></div>
            <div class="col-md-4"><input class="form-control" type="date" name="valid_until" value="<?= e((string) $proposal['valid_until']) ?>"></div>
            <div class="col-md-4"><select class="form-select" name="status"><option value="sent" <?= $proposal['status']==='sent'?'selected':'' ?>>sent</option><option value="accepted" <?= $proposal['status']==='accepted'?'selected':'' ?>>accepted</option><option value="rejected" <?= $proposal['status']==='rejected'?'selected':'' ?>>rejected</option></select></div>
        </div>
        <button class="btn btn-primary rounded-pill" type="submit">Save Proposal</button>
        <a class="btn btn-outline-light rounded-pill" href="<?= route_url('/client/proposal?id=' . $proposal['id']) ?>">Open Document</a>
    </form>
</div>
