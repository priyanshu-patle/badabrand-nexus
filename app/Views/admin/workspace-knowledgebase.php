<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Knowledgebase</h4><span>Support-ready docs</span></div>
            <div class="documentation-nav mt-3">
                <?php foreach ($docs as $doc): ?>
                    <a class="documentation-link <?= ($currentDoc['slug'] ?? '') === $doc['slug'] ? 'active' : '' ?>" href="<?= route_url('/admin/support/knowledgebase?doc=' . urlencode($doc['slug'])) ?>">
                        <strong><?= e($doc['title']) ?></strong>
                        <span><?= e($doc['slug']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4><?= e($currentDoc['title']) ?></h4><span>Markdown source</span></div>
            <div class="documentation-prose mt-3"><?= $currentDocHtml ?></div>
        </div>
    </div>
</div>
