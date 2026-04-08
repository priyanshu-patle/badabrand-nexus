<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create Campaign</h4><span>Broadcast and email tools</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/marketing/broadcast') ?>">
                <input type="hidden" name="_redirect" value="/admin/marketing/campaigns">
                <input class="form-control" name="title" placeholder="Campaign title" required>
                <select class="form-select" name="type">
                    <option value="broadcast">Broadcast</option>
                    <option value="push">Push</option>
                    <option value="email">Email</option>
                </select>
                <textarea class="form-control" name="body" rows="5" placeholder="Message body"></textarea>
                <input class="form-control" name="action_url" placeholder="Optional action URL">
                <button class="btn btn-primary" type="submit">Send Campaign</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Recent Delivery Log</h4><span>Recent outbound mail activity</span></div>
            <?php foreach ($emailLogs as $log): ?>
                <div class="list-row">
                    <div>
                        <strong><?= e($log['subject']) ?></strong>
                        <span><?= e($log['recipient_email']) ?></span>
                    </div>
                    <span class="badge-soft"><?= e($log['delivery_status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
