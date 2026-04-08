<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="search-toolbar">
                <div>
                    <h4 class="mb-1">Projects, Proposals, and Contracts</h4>
                    <p class="mb-0 text-muted">Track progress, update project status, and export delivery records.</p>
                </div>
                <form class="toolbar-actions" method="get" action="<?= route_url('/admin/projects') ?>">
                    <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search order, client, service, proposal, contract">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/export?type=projects&q=' . urlencode($search ?? '')) ?>">Export Projects</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Client Project Tracker</h4><span>Live progress and updates</span></div>
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/projects/order') ?>">
                        <input type="hidden" name="order_id" value="<?= e((string) $order['id']) ?>">
                        <div class="d-flex justify-content-between gap-3 flex-wrap"><strong><?= e($order['order_number']) ?> - <?= e(trim($order['first_name'] . ' ' . $order['last_name'])) ?></strong><span><?= e($order['progress_percent']) ?>%</span></div>
                        <div class="small-text mb-2"><?= e($order['display_name'] ?? $order['service_name'] ?? 'General Project') ?></div>
                        <div class="progress my-2"><div class="progress-bar" style="width: <?= e((string) $order['progress_percent']) ?>%"></div></div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <?php foreach (['pending_approval','approved','in_progress','active','completed','rejected','suspended'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><input class="form-control" name="progress_percent" value="<?= e((string) $order['progress_percent']) ?>" placeholder="Progress"></div>
                            <div class="col-md-3"><input class="form-control" name="total" value="<?= e((string) $order['total']) ?>" placeholder="Total"></div>
                            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit">Save</button><button class="btn btn-danger flex-fill" formaction="<?= route_url('/admin/projects/delete') ?>" type="submit" onclick="return confirm('Delete this project?')">Delete</button></div>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0 text-muted">No projects found for this search.</p>
            <?php endif; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Add Project Update</h4><span>Team collaboration panel</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/projects/update') ?>">
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="order_id" placeholder="Order ID"></div>
                    <div class="col-md-6"><input class="form-control" name="user_id" placeholder="Client user ID"></div>
                </div>
                <input class="form-control" name="title" placeholder="Update title">
                <textarea class="form-control" name="details" rows="4" placeholder="Update details"></textarea>
                <button class="btn btn-primary rounded-pill" type="submit">Post Update</button>
            </form>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Proposal Generator</h4><span>Quotation records</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/projects/proposal') ?>">
                <input class="form-control" name="user_id" placeholder="Client user ID">
                <input class="form-control" name="title" placeholder="Proposal title">
                <textarea class="form-control" name="description" rows="3" placeholder="Proposal scope"></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="amount" placeholder="Amount"></div>
                    <div class="col-md-6"><input class="form-control" type="date" name="valid_until"></div>
                </div>
                <button class="btn btn-outline-light rounded-pill" type="submit">Generate Proposal</button>
            </form>
            <div class="mt-4">
                <?php foreach ($proposals as $proposal): ?>
                    <div class="list-row">
                        <strong><?= e($proposal['title']) ?></strong>
                        <span><?= e(money_format_inr($proposal['amount'])) ?></span>
                        <div class="toolbar-actions">
                            <a class="badge-soft" href="<?= route_url('/admin/proposal/edit?id=' . $proposal['id']) ?>">edit</a>
                            <a class="badge-soft" href="<?= route_url('/client/proposal?id=' . $proposal['id']) ?>">document</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Contract Signing</h4><span>Create contracts for client portal</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/projects/contract') ?>">
                <input class="form-control" name="user_id" placeholder="Client user ID">
                <input class="form-control" name="title" placeholder="Contract title">
                <textarea class="form-control" name="contract_body" rows="4" placeholder="Contract text"></textarea>
                <button class="btn btn-primary rounded-pill" type="submit">Create Contract</button>
            </form>
            <div class="mt-4">
                <?php foreach ($contracts as $contract): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/projects/contract/update') ?>">
                        <input type="hidden" name="contract_id" value="<?= e((string) $contract['id']) ?>">
                        <input class="form-control mb-2" name="title" value="<?= e($contract['title']) ?>">
                        <textarea class="form-control mb-2" name="contract_body" rows="3"><?= e($contract['contract_body'] ?? '') ?></textarea>
                        <div class="d-flex gap-2">
                            <select class="form-select" name="status"><option value="sent" <?= $contract['status']==='sent'?'selected':'' ?>>sent</option><option value="signed" <?= $contract['status']==='signed'?'selected':'' ?>>signed</option><option value="draft" <?= $contract['status']==='draft'?'selected':'' ?>>draft</option></select>
                            <button class="btn btn-outline-light" type="submit">Save</button>
                            <a class="btn btn-primary" href="<?= route_url('/client/contract?id=' . $contract['id']) ?>">Document</a>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
