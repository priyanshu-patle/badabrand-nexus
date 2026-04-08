<div class="row g-4">
    <?php foreach ($contracts as $contract): ?>
        <div class="col-xl-6">
            <div class="dash-card">
                <div class="card-title-row"><h4><?= e($contract['title']) ?></h4><span><?= e($contract['status']) ?></span></div>
                <p class="text-muted"><?= e(substr((string) ($contract['contract_body'] ?? ''), 0, 180)) ?></p>
                <?php if ($contract['status'] !== 'signed'): ?>
                    <form class="stack-form mt-3" method="post" action="<?= route_url('/client/contracts/sign') ?>">
                        <input type="hidden" name="contract_id" value="<?= e((string) $contract['id']) ?>">
                        <input class="form-control" name="signature_name" value="<?= e(trim($user['first_name'] . ' ' . $user['last_name'])) ?>" placeholder="Digital signature name">
                        <button class="btn btn-primary rounded-pill" type="submit">Sign Contract</button>
                    </form>
                <?php else: ?>
                    <div class="badge-soft">Signed by <?= e($contract['signature_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
