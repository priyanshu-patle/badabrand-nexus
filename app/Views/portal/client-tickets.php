<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Open Support Ticket</h4><span>Ticket management with custom editor</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/client/tickets') ?>">
                <input class="form-control" name="subject" placeholder="Subject">
                <select class="form-select" name="priority"><option value="low">low</option><option value="medium">medium</option><option value="high">high</option></select>
                <textarea class="form-control" id="ticket-message-editor" name="message" rows="6" placeholder="Describe your issue" data-rich-editor="full"></textarea>
                <button class="btn btn-primary rounded-pill" type="submit">Create Ticket</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>My Tickets</h4><span>Status tracking</span></div>
            <?php foreach ($tickets as $ticket): ?>
                <div class="list-row"><strong><a href="<?= route_url('/client/tickets?ticket=' . $ticket['id']) ?>"><?= e($ticket['subject']) ?></a></strong><span><?= e($ticket['priority']) ?></span><span class="badge-soft"><?= e($ticket['status']) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Conversation</h4><span>Admin replies</span></div>
            <?php if (!empty($activeTicketId)): ?>
                <div class="activity-list">
                    <?php foreach ($messages as $message): ?>
                        <div class="activity-item"><strong><?= e($message['sender_type']) ?>:</strong> <div><?= safe_rich_text($message['message']) ?></div></div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Select a ticket to view status updates and replies.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
