<div class="row g-4">
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Core Hook Events</h4><span>Extension attachment points</span></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <?php foreach ($coreEvents as $event): ?>
                    <span class="badge-soft"><?= e($event) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Quick Create Hooks Test</h4><span>Jump into common extension triggers</span></div>
            <?php foreach ($quickCreateLinks as $link): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($link['label']) ?></strong>
                        <span><?= e($link['href']) ?></span>
                    </div>
                    <a class="badge-soft" href="<?= route_url($link['href']) ?>">Open</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
