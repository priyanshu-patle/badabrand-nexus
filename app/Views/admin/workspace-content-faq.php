<div class="row g-4">
    <div class="col-xl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4>Create FAQ</h4><span>Support content</span></div>
            <form class="stack-form mt-3" method="post" action="<?= route_url('/admin/faqs') ?>">
                <input class="form-control" name="question" placeholder="Question" required>
                <textarea class="form-control" name="answer" rows="4" placeholder="Answer"></textarea>
                <input class="form-control" name="sort_order" value="0" placeholder="Sort order">
                <button class="btn btn-primary" type="submit">Add FAQ</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>FAQ Items</h4><span>Public help content</span></div>
            <?php foreach ($faqItems as $item): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/faqs/update') ?>">
                    <input type="hidden" name="faq_id" value="<?= e((string) $item['id']) ?>">
                    <div class="row g-3">
                        <div class="col-md-9"><input class="form-control" name="question" value="<?= e($item['question']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $item['sort_order']) ?>"></div>
                        <div class="col-12"><textarea class="form-control" name="answer" rows="3"><?= e($item['answer']) ?></textarea></div>
                    </div>
                    <div class="toolbar-actions mt-3">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/faqs/delete') ?>">Delete</button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
