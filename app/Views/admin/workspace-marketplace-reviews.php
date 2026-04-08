<div class="dash-card">
    <div class="card-title-row">
        <div>
            <h4>Marketplace Reviews</h4>
            <p class="mb-0 text-muted">Moderate product reviews, vendor trust signals, and marketplace reputation.</p>
        </div>
    </div>
    <?php if ($reviews === []): ?>
        <div class="dash-empty-state">
            <span class="dash-empty-state-icon"><i class="bi bi-chat-left-text"></i></span>
            <div class="dash-empty-state-copy">
                <h5>No reviews submitted yet</h5>
                <p>Approved customer reviews will appear here once the marketplace starts collecting feedback.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <form class="dash-form-block" method="post" action="<?= route_url('/admin/marketplace/reviews/status') ?>">
                <input type="hidden" name="review_id" value="<?= e((string) $review['id']) ?>">
                <input type="hidden" name="_redirect" value="/admin/marketplace/reviews">
                <div class="list-row">
                    <div>
                        <strong><?= e($review['product_name'] ?? 'Product') ?></strong>
                        <span><?= e(trim(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? '')) ?: 'Customer') ?> | <?= e($review['vendor_display_name'] ?? 'Badabrand') ?></span>
                    </div>
                    <span class="badge-soft"><?= e((string) ($review['rating'] ?? 0)) ?>/5</span>
                </div>
                <p class="text-muted mt-3 mb-3"><?= e((string) ($review['comment'] ?? '')) ?></p>
                <div class="toolbar-actions">
                    <select class="form-select" name="status" style="max-width: 220px;">
                        <?php foreach (['pending', 'approved', 'rejected'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= ($review['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Update Review</button>
                </div>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
