<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">FAQ</span>
        <h1>Answers covering hosting compatibility, manual payments, and content control.</h1>
    </div>
</section>
<section class="section-space">
    <div class="container">
        <div class="accordion modern-accordion" id="faqAccordion">
            <?php foreach ($faqItems as $index => $item): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $index ?>">
                            <?= e($item['question']) ?>
                        </button>
                    </h2>
                    <div id="faq<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                        <div class="accordion-body"><?= nl2br(e($item['answer'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php return; ?>
<?php $demo = $demo ?? require base_path('app/Config/demo.php'); ?>
<section class="inner-hero"><div class="container"><span class="eyebrow">FAQ</span><h1>Answers covering hosting compatibility, manual payments, and content control.</h1></div></section>
<section class="section-space"><div class="container"><div class="accordion modern-accordion" id="faqAccordion"><?php foreach ($demo['faq'] as $index => $item): ?><div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $index ?>"><?= e($item['question']) ?></button></h2><div id="faq<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion"><div class="accordion-body"><?= e($item['answer']) ?></div></div></div><?php endforeach; ?></div></div></section>
