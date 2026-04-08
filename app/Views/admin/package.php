<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Package Center</h4><span>Marketplace-ready product docs</span></div>
            <div class="documentation-nav">
                <?php foreach ($docs as $doc): ?>
                    <a class="documentation-link <?= ($currentDoc['slug'] ?? '') === $doc['slug'] ? 'active' : '' ?>" href="<?= route_url('/admin/package?doc=' . urlencode($doc['slug'])) ?>">
                        <strong><?= e($doc['title']) ?></strong>
                        <span><?= e($doc['description']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4><?= e($currentDoc['title'] ?? 'Package Center') ?></h4>
                    <p class="mb-0 text-muted"><?= e($currentDoc['description'] ?? 'Sale package reference and buyer handoff files.') ?></p>
                </div>
                <span class="badge-soft"><?= e($currentDoc['slug'] ?? 'package') ?></span>
            </div>
            <article class="documentation-prose mt-4">
                <?= $currentDocHtml ?>
            </article>
        </div>
    </div>
</div>
