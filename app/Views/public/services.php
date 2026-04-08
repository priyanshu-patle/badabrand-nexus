<section class="inner-hero"><div class="container"><span class="eyebrow">Services</span><h1>Order managed IT services with automatic billing, approvals, and dashboard tracking.</h1></div></section>
<section class="section-space">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="feature-card h-100">
                        <div class="icon-wrap"><i class="bi <?= e($service['icon'] ?: 'bi-briefcase') ?>"></i></div>
                        <h4><?= e($service['name']) ?></h4>
                        <p><?= e($service['short_description']) ?></p>
                        <div class="card-foot mb-3"><span><?= e($service['price_label']) ?></span><span class="badge-soft"><?= e($service['slug']) ?></span></div>
                        <form class="stack-form" method="post" action="<?= route_url('/order') ?>">
                            <input type="hidden" name="order_type" value="service">
                            <input type="hidden" name="service_id" value="<?= e((string) $service['id']) ?>">
                            <input class="form-control service-note-input" name="notes" placeholder="Project note or requirement">
                            <button class="btn btn-primary rounded-pill w-100" type="submit">Order Service</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="dash-card mt-5">
            <div class="card-title-row"><h4>Plan-Based Ordering</h4><span>Customers can order pricing packages directly</span></div>
            <div class="row g-4 mt-1">
                <?php foreach ($plans as $plan): ?>
                    <div class="col-md-4">
                        <div class="pricing-card <?= (int) $plan['is_featured'] === 1 ? 'featured' : '' ?>">
                            <h3><?= e($plan['name']) ?></h3>
                            <div class="price"><?= e($plan['price']) ?></div>
                            <p><?= e($plan['description']) ?></p>
                            <form method="post" action="<?= route_url('/order') ?>">
                                <input type="hidden" name="order_type" value="plan">
                                <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                                <button class="btn btn-outline-light rounded-pill w-100" type="submit">Buy Plan</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
