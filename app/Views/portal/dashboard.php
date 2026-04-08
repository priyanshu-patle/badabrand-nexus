<?php $portal = $demo['portal']; ?>
<div class="row g-4">
    <?php foreach ($portal['stats'] as $stat): ?>
        <div class="col-sm-6 col-xl-3"><div class="metric-card"><span><?= e($stat['label']) ?></span><strong><?= e($stat['value']) ?></strong></div></div>
    <?php endforeach; ?>
</div>
<div class="row g-4 mt-1">
    <div class="col-xl-8">
        <div class="dash-card"><div class="card-title-row"><h4>Earnings Overview</h4><span>Realtime billing and renewals</span></div><div class="chart-mock"></div></div>
        <div class="dash-card mt-4"><div class="card-title-row"><h4>Recent Activities</h4><span>Notifications and admin messages</span></div><div class="activity-list"><?php foreach ($portal['activities'] as $activity): ?><div class="activity-item"><?= e($activity) ?></div><?php endforeach; ?></div></div>
        <div class="dash-card mt-4"><div class="card-title-row"><h4>My Services</h4><span>Status, expiry, and renewal</span></div><div class="row g-3"><?php foreach ($portal['services'] as $service): ?><div class="col-md-4"><div class="service-pill"><strong><?= e($service['name']) ?></strong><span><?= e($service['status']) ?></span><small><?= e($service['renewal']) ?></small></div></div><?php endforeach; ?></div></div>
    </div>
    <div class="col-xl-4">
        <div class="dash-card"><div class="card-title-row"><h4>Project Tracker</h4><span>Live progress</span></div><?php foreach ($portal['projects'] as $project): ?><div class="progress-row"><div class="d-flex justify-content-between"><strong><?= e($project['name']) ?></strong><span><?= e($project['progress']) ?>%</span></div><div class="progress my-2"><div class="progress-bar" style="width: <?= e((string) $project['progress']) ?>%"></div></div><small><?= e($project['stage']) ?></small></div><?php endforeach; ?></div>
        <div class="dash-card mt-4"><div class="card-title-row"><h4>Invoices</h4><span>PDF download ready</span></div><?php foreach ($portal['invoices'] as $invoice): ?><div class="list-row"><strong><?= e($invoice['number']) ?></strong><span><?= e($invoice['amount']) ?></span><span class="badge-soft"><?= e($invoice['status']) ?></span></div><?php endforeach; ?></div>
        <div class="dash-card mt-4"><div class="card-title-row"><h4>Manual Payment</h4><span>QR / bank transfer</span></div><div class="stack-form"><input class="form-control" placeholder="Transaction Reference"><select class="form-select"><option>QR Payment</option><option>Bank Transfer</option></select><input class="form-control" placeholder="Upload Payment Screenshot"><button class="btn btn-primary rounded-pill" type="button">Submit for Approval</button></div></div>
        <div class="dash-card mt-4"><div class="card-title-row"><h4>Support Tickets</h4><span>Status tracking</span></div><?php foreach ($portal['tickets'] as $ticket): ?><div class="list-row"><strong><?= e($ticket['id']) ?></strong><span><?= e($ticket['subject']) ?></span><span class="badge-soft"><?= e($ticket['status']) ?></span></div><?php endforeach; ?></div>
    </div>
</div>
