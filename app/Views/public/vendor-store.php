<section class="inner-hero">
    <div class="container">
        <span class="eyebrow">Vendor Store</span>
        <h1><?= e($vendor['display_name'] ?: $vendor['store_name']) ?></h1>
        <p class="lead"><?= e($vendor['short_bio'] ?: 'Verified marketplace vendor on Badabrand Technologies.') ?></p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="metric-card"><span>Products</span><strong><?= e((string) ($summary['products'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Orders</span><strong><?= e((string) ($summary['orders'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Reviews</span><strong><?= e((string) ($summary['total_reviews'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Average Rating</span><strong><?= e((string) ($summary['average_rating'] ?? 0)) ?>/5</strong></div></div>
        </div>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="feature-card h-100">
                        <div class="product-thumb-wrap">
                            <img class="product-thumb" src="<?= storage_url($product['thumbnail_path'] ?: 'assets/images/products/default-product.svg') ?>" alt="<?= e($product['name']) ?>">
                        </div>
                        <h4><?= e($product['name']) ?></h4>
                        <p><?= e($product['short_description']) ?></p>
                        <div class="card-foot mb-3">
                            <span><?= e($product['price_label'] ?: money_format_inr($product['price'])) ?></span>
                            <span><?= e($product['version_label'] ?: 'Latest') ?></span>
                        </div>
                        <form class="stack-form" method="post" action="<?= route_url('/order') ?>">
                            <input type="hidden" name="order_type" value="product">
                            <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                            <div class="row g-3">
                                <div class="col-4"><input class="form-control" type="number" name="quantity" min="1" value="1"></div>
                                <div class="col-8"><input class="form-control" name="notes" placeholder="License note"></div>
                            </div>
                            <button class="btn btn-primary rounded-pill w-100" type="submit">Buy Product</button>
                        </form>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
