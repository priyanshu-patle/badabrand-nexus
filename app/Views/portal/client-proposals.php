<div class="row g-4">
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Submit Your Proposal</h4><span>Custom proposal editor</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/client/proposals') ?>">
                <input class="form-control" name="title" placeholder="Proposal title">
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="amount" placeholder="Expected budget"></div>
                    <div class="col-md-6"><input class="form-control" type="date" name="valid_until"></div>
                </div>
                <textarea class="form-control proposal-editor" id="proposal-editor" name="description" rows="10" placeholder="Describe your idea, features, deliverables, timeline, plugins, themes, software requirements, and expected business outcome" data-rich-editor="full"></textarea>
                <button class="btn btn-primary rounded-pill" type="submit">Submit Proposal</button>
            </form>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="dash-card">
            <div class="card-title-row"><h4>Proposal History</h4><span>Client quotations and requests</span></div>
            <?php foreach ($proposals as $proposal): ?>
                <div class="list-row">
                    <strong><?= e($proposal['title']) ?></strong>
                    <span><?= e(money_format_inr($proposal['amount'])) ?></span>
                    <a class="badge-soft" href="<?= route_url('/client/proposal?id=' . $proposal['id']) ?>"><?= e($proposal['status']) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
