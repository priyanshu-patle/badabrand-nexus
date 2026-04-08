<div class="row g-4">
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Ticket Management</h4><span>Status tracking</span></div>
            <?php foreach ($tickets as $ticket): ?>
                <div class="list-row">
                    <strong><a href="<?= route_url('/admin/tickets?ticket=' . $ticket['id']) ?>"><?= e($ticket['subject']) ?></a></strong>
                    <span><?= e($ticket['email']) ?></span>
                    <span class="badge-soft"><?= e($ticket['status']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Admin Replies</h4><span>Conversation history</span></div>
            <?php if (!empty($activeTicketId)): ?>
                <div class="activity-list">
                    <?php foreach ($messages as $message): ?>
                        <div class="activity-item"><strong><?= e($message['sender_type']) ?>:</strong> <?= e($message['message']) ?></div>
                    <?php endforeach; ?>
                </div>
                <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/tickets/reply') ?>">
                    <input type="hidden" name="ticket_id" value="<?= e((string) $activeTicketId) ?>">
                    <select class="form-select" name="status"><option value="answered">answered</option><option value="open">open</option><option value="closed">closed</option></select>
                    <textarea class="form-control" name="message" rows="4" placeholder="Reply message"></textarea>
                    <button class="btn btn-primary rounded-pill" type="submit">Send Reply</button>
                </form>
            <?php else: ?>
                <p class="text-muted mb-0">Select a ticket from the left to review messages and reply.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
