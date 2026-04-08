<section class="inner-hero"><div class="container"><span class="eyebrow">Pricing</span><h1>Choose a package and let the system create the order, receipt, and bill automatically.</h1></div></section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-4">
                    <div class="pricing-card <?= (int) $plan['is_featured'] === 1 ? 'featured' : '' ?>">
                        <h3><?= e($plan['name']) ?></h3>
                        <div class="price"><?= e($plan['price']) ?></div>
                        <p><?= e($plan['description']) ?></p>
                        <form method="post" action="<?= route_url('/order') ?>">
                            <input type="hidden" name="order_type" value="plan">
                            <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                            <button class="btn btn-primary rounded-pill w-100" type="submit">Buy This Plan</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mt-4">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6">
                    <div class="glass-panel h-100">
                        <div class="card-title-row"><h4><?= e($service['name']) ?></h4><span><?= e($service['price_label']) ?></span></div>
                        <p class="mb-0"><?= e($service['short_description']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
