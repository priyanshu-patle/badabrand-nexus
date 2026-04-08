<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Terms & Conditions</span>
        <h1><?= e($page['title'] ?? 'Terms of Service') ?></h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="glass-panel legal-copy">
            <?php if (!empty($page['excerpt'])): ?>
                <p><?= e($page['excerpt']) ?></p>
            <?php endif; ?>
            <?= !empty($page['content']) ? safe_rich_text($page['content']) : '<p>No terms content found yet.</p>' ?>
        </div>
    </div>
</section>
<?php return; ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">Terms & Conditions</span><h1>Terms acceptance is built into first-time client login.</h1></div></section>
<section class="section-space"><div class="container"><div class="glass-panel legal-copy"><p>Customers must accept the service terms during their first login before accessing project files, invoices, or support modules. Admins can update the legal content from the CMS and track acceptance timestamps.</p><p>Projects, retainers, and managed services may carry separate commercial scopes through proposals and digital contracts generated within the platform.</p></div></div></section>
