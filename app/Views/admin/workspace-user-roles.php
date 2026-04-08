<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Role Totals</h4><span>Current workspace mix</span></div>
            <?php foreach ($roleStats as $row): ?>
                <div class="list-row">
                    <strong><?= e($row['role']) ?></strong>
                    <span class="badge-soft"><?= e((string) $row['total']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Permissions Matrix</h4><span>Current role boundaries</span></div>
            <?php foreach ($roleMatrix as $roleName => $capabilities): ?>
                <div class="dash-form-block">
                    <strong><?= e(ucfirst($roleName)) ?></strong>
                    <div class="stack-list compact mt-2">
                        <?php foreach ($capabilities as $capability): ?>
                            <li><?= e($capability) ?></li>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
