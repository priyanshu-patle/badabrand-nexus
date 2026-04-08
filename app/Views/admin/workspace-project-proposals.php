<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Proposal</h4><span>Quote workflow</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/projects/proposal') ?>">
                <input type="hidden" name="_redirect" value="/admin/projects/proposals">
                <select class="form-select" name="user_id" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']) ?>"><?= e(trim($client['first_name'] . ' ' . $client['last_name'])) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" name="title" placeholder="Proposal title" required>
                <textarea class="form-control" name="description" rows="4" placeholder="Scope"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="amount" placeholder="Amount"></div>
                    <div class="col-md-6"><input class="form-control" type="date" name="valid_until"></div>
                </div>
                <button class="btn btn-primary" type="submit">Generate Proposal</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Proposal Records</h4><span>Quote history</span></div>
            <?php foreach ($proposals as $proposal): ?>
                <div class="dash-form-block">
                    <div class="list-row">
                        <div>
                            <strong><?= e($proposal['title']) ?></strong>
                            <span><?= e(trim($proposal['first_name'] . ' ' . $proposal['last_name'])) ?></span>
                        </div>
                        <span class="badge-soft"><?= e(money_format_inr($proposal['amount'])) ?></span>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <span class="badge-soft"><?= e($proposal['status']) ?></span>
                        <a class="btn btn-outline-light" href="<?= route_url('/admin/proposal/edit?id=' . $proposal['id']) ?>">Edit</a>
                        <a class="btn btn-outline-light" href="<?= route_url('/client/proposal?id=' . $proposal['id']) ?>">Document</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
