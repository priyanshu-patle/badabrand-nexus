<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Contract</h4><span>Signature workflow</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/projects/contract') ?>">
                <input type="hidden" name="_redirect" value="/admin/projects/contracts">
                <select class="form-select" name="user_id" required>
                    <option value="">Select client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= e((string) $client['id']) ?>"><?= e(trim($client['first_name'] . ' ' . $client['last_name'])) ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" name="title" placeholder="Contract title" required>
                <textarea class="form-control" name="contract_body" rows="6" placeholder="Contract text"></textarea>
                <button class="btn btn-primary" type="submit">Create Contract</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Contract Records</h4><span>Client agreements</span></div>
            <?php foreach ($contracts as $contract): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/projects/contract/update') ?>">
                    <input type="hidden" name="_redirect" value="/admin/projects/contracts">
                    <input type="hidden" name="contract_id" value="<?= e((string) $contract['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-8"><input class="form-control" name="title" value="<?= e($contract['title']) ?>"></div>
                        <div class="col-md-4">
                            <select class="form-select" name="status">
                                <?php foreach (['draft', 'sent', 'signed'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= ($contract['status'] ?? 'draft') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12"><textarea class="form-control" name="contract_body" rows="4"><?= e($contract['contract_body']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a class="btn btn-outline-light" href="<?= route_url('/client/contract?id=' . $contract['id']) ?>">Document</a>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
