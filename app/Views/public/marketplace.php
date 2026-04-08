<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Marketplace</span>
        <h1>Digital products, themes, plugins, software, and service packages in one responsive storefront.</h1>
        <p class="lead">Customers can order directly from the frontend, pay manually or with QR flow, and then track approval, billing, and delivery in the portal.</p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="section-heading text-center">
            <span class="eyebrow">Digital Products</span>
            <h2>Ready-to-buy marketplace items</h2>
        </div>
        <div class="marketplace-toolbar mb-4">
            <form class="toolbar-actions marketplace-search-form" method="get" action="<?= route_url('/marketplace') ?>">
                <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search theme, plugin, software, or version">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
        </div>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="feature-card h-100">
                        <div class="marketplace-meta">
                            <span class="badge-soft"><?= e(ucfirst((string) $product['category'])) ?></span>
                        </div>
                        <div class="product-thumb-wrap">
                            <img class="product-thumb" src="<?= storage_url($product['thumbnail_path'] ?: 'assets/images/products/default-product.svg') ?>" alt="<?= e($product['name']) ?>">
                        </div>
                        <h4><?= e($product['name']) ?></h4>
                        <p><?= e($product['short_description']) ?></p>
                        <div class="small-text mb-2">
                            Sold by
                            <?php if (! empty($product['vendor_slug'])): ?>
                                <a href="<?= route_url('/marketplace/vendor/' . $product['vendor_slug']) ?>"><?= e($product['seller_name']) ?></a>
                            <?php else: ?>
                                <?= e($product['seller_name'] ?? 'Badabrand Technologies') ?>
                            <?php endif; ?>
                        </div>
                        <div class="small-text mb-3"><?= e($product['version_label'] ?: 'Latest version') ?></div>
                        <div class="card-foot mb-3">
                            <span><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                            <span><?= e($product['status']) ?></span>
                        </div>
                        <form class="stack-form" method="post" action="<?= route_url('/order') ?>">
                            <input type="hidden" name="order_type" value="product">
                            <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                            <div class="row g-3">
                                <div class="col-4"><input class="form-control" type="number" min="1" name="quantity" value="1"></div>
                                <div class="col-8"><input class="form-control" name="notes" placeholder="License note or business use case"></div>
                            </div>
                            <button class="btn btn-primary rounded-pill w-100" type="submit">Buy Product</button>
                        </form>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space section-alt">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="glass-panel h-100">
                    <span class="eyebrow">Service Ordering</span>
                    <h2>Need implementation too?</h2>
                    <p class="lead">Customers can buy products and then add service execution such as setup, customization, deployment, or support.</p>
                    <div class="stack-list compact">
                        <?php foreach ($services as $service): ?>
                            <li><?= e($service['name']) ?> - <?= e($service['price_label']) ?></li>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-panel h-100">
                    <span class="eyebrow">Packages</span>
                    <h2>Service plans with automatic invoice generation</h2>
                    <div class="row g-3 mt-1">
                        <?php foreach ($plans as $plan): ?>
                            <div class="col-md-6">
                                <div class="mini-panel h-100">
                                    <strong><?= e($plan['name']) ?></strong>
                                    <span><?= e($plan['price']) ?></span>
                                    <form class="mt-3" method="post" action="<?= route_url('/order') ?>">
                                        <input type="hidden" name="order_type" value="plan">
                                        <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                                        <button class="btn btn-outline-light w-100" type="submit">Buy Plan</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
