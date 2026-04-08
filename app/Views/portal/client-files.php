<div class="dash-card">
    <div class="card-title-row"><h4>Files</h4><span>Downloads and uploads</span></div>
    <?php foreach ($files as $file): ?>
        <div class="list-row"><strong><?= e($file['title']) ?></strong><span><?= e($file['file_type'] ?: 'file') ?></span><a class="badge-soft" href="<?= route_url('/' . ltrim($file['file_path'], '/')) ?>" target="_blank">download</a></div>
    <?php endforeach; ?>
</div>
