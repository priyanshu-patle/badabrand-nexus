<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Privacy Policy</span>
        <h1><?= e($page['title'] ?? 'Privacy Policy') ?></h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="glass-panel legal-copy">
            <?php if (!empty($page['excerpt'])): ?>
                <p><?= e($page['excerpt']) ?></p>
            <?php endif; ?>
            <?= !empty($page['content']) ? safe_rich_text($page['content']) : '<p>No privacy policy content found yet.</p>' ?>
        </div>
    </div>
</section>
<?php return; ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">Privacy Policy</span><h1>Privacy-ready legal page editable from the CMS.</h1></div></section>
<section class="section-space"><div class="container"><div class="glass-panel legal-copy"><p>We collect only the information required to deliver services, process invoices, manage support, and maintain secure customer accounts. Data can be managed, exported, or updated through the admin panel and client portal modules.</p><p>Uploaded files, contracts, and payment screenshots should be stored securely with access restricted by user role and account ownership.</p></div></div></section>
