<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row"><h4>Customer Marketplace</h4><span>Buy products, themes, plugins, software, and service packages</span></div>
            <p class="text-muted mb-0">Each order automatically creates a receipt, invoice, dashboard notification, and email log entry. You can then upload manual or QR payment proof from the billing section.</p>
            <form class="toolbar-actions mt-3" method="get" action="<?= route_url('/client/marketplace') ?>">
                <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search marketplace products">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
        </div>
    </div>
    <?php foreach ($products as $product): ?>
        <div class="col-md-6 col-xl-4">
            <div class="feature-card h-100">
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
                        <a href="<?= route_url('/marketplace/vendor/' . $product['vendor_slug']) ?>" target="_blank"><?= e($product['seller_name']) ?></a>
                    <?php else: ?>
                        <?= e($product['seller_name'] ?? 'Badabrand Technologies') ?>
                    <?php endif; ?>
                </div>
                <div class="small-text mb-3">Version <?= e($product['version_label'] ?: 'latest') ?></div>
                <form class="stack-form" method="post" action="<?= route_url('/order') ?>">
                    <input type="hidden" name="order_type" value="product">
                    <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                    <div class="row g-3">
                        <div class="col-4"><input class="form-control" type="number" name="quantity" min="1" value="1"></div>
                        <div class="col-8"><input class="form-control" name="notes" placeholder="License note or customization request"></div>
                    </div>
                    <div class="card-foot">
                        <span><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                        <button class="btn btn-primary" type="submit">Buy</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row"><h4>Service Packages</h4><span>Buy implementation and support plans</span></div>
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
                                <button class="btn btn-outline-light w-100" type="submit">Buy Plan</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
