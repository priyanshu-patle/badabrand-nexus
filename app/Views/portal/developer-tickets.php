<div class="dash-card">
    <div class="card-title-row"><h4>Support Queue</h4><span>Engineering follow-up</span></div>
    <?php foreach ($tickets as $ticket): ?>
        <div class="list-row"><strong><?= e($ticket['subject']) ?></strong><span><?= e($ticket['email']) ?></span><span class="badge-soft"><?= e($ticket['status']) ?></span></div>
    <?php endforeach; ?>
</div>
