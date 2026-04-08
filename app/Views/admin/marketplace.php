<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="search-toolbar">
                <div>
                    <h4 class="mb-1">Digital Product Marketplace</h4>
                    <p class="mb-0 text-muted">Create and manage themes, plugins, and software products that customers can buy from the frontend and dashboard.</p>
                </div>
                <form class="toolbar-actions" method="get" action="<?= route_url('/admin/marketplace') ?>">
                    <input class="form-control toolbar-input" name="q" value="<?= e($search ?? '') ?>" placeholder="Search product, category, type, version">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                    <a class="btn btn-outline-light" href="<?= route_url('/admin/export?type=products&q=' . urlencode($search ?? '')) ?>">Export CSV</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row"><h4>Add Product</h4><span>Marketplace publishing</span></div>
            <form class="stack-form" method="post" action="<?= route_url('/admin/products') ?>" enctype="multipart/form-data">
                <input class="form-control" name="name" placeholder="Product name" required>
                <select class="form-select" name="product_type">
                    <option value="theme">Theme</option>
                    <option value="plugin">Plugin</option>
                    <option value="software">Software</option>
                    <option value="template">Template</option>
                </select>
                <input class="form-control" name="price" placeholder="Price amount">
                <input class="form-control" name="price_label" placeholder="Price label">
                <input class="form-control" name="version_label" placeholder="Version label">
                <textarea class="form-control" name="short_description" rows="2" placeholder="Short description"></textarea>
                <textarea class="form-control" name="description" rows="3" placeholder="Full description"></textarea>
                <textarea class="form-control" name="features_text" rows="4" placeholder="Feature list, one per line"></textarea>
                <input class="form-control" name="download_link" placeholder="Download link or delivery note">
                <input class="form-control" type="file" name="thumbnail_file">
                <div class="row g-3">
                    <div class="col-md-6"><select class="form-select" name="status"><option value="active">active</option><option value="draft">draft</option><option value="inactive">inactive</option></select></div>
                    <div class="col-md-6"><input class="form-control" name="sort_order" value="0" placeholder="Sort order"></div>
                </div>
                <button class="btn btn-primary rounded-pill" type="submit">Add Product</button>
            </form>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row"><h4>Published Products</h4><span>Edit customer-facing marketplace items</span></div>
            <?php foreach ($products as $product): ?>
                <form class="dash-form-block" method="post" action="<?= route_url('/admin/products/update') ?>" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= e((string) $product['id']) ?>">
                    <div class="card-title-row mb-3">
                        <div>
                            <h5 class="mb-1"><?= e($product['name']) ?></h5>
                            <span>Edit details below and use Save or Delete.</span>
                        </div>
                        <span class="badge-soft"><?= e(ucfirst((string) $product['product_type'])) ?></span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4"><input class="form-control" name="name" value="<?= e($product['name']) ?>"></div>
                        <div class="col-md-2"><input class="form-control" name="price" value="<?= e((string) $product['price']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="price_label" value="<?= e($product['price_label']) ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="version_label" value="<?= e($product['version_label']) ?>"></div>
                        <div class="col-md-6">
                            <select class="form-select" name="product_type">
                                <?php foreach (['theme','plugin','software','template'] as $type): ?>
                                    <option value="<?= e($type) ?>" <?= $product['product_type'] === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <?php foreach (['active','draft','inactive'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $product['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><input class="form-control" name="sort_order" value="<?= e((string) $product['sort_order']) ?>"></div>
                        <div class="col-12"><textarea class="form-control" name="short_description" rows="2"><?= e($product['short_description']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="description" rows="3"><?= e($product['description']) ?></textarea></div>
                        <div class="col-12"><textarea class="form-control" name="features_text" rows="3"><?= e($product['features_text']) ?></textarea></div>
                        <div class="col-md-8"><input class="form-control" name="download_link" value="<?= e($product['download_link']) ?>" placeholder="Download link"></div>
                        <div class="col-md-4"><input class="form-control" type="file" name="thumbnail_file"></div>
                    </div>
                    <div class="admin-tools mt-3">
                        <span class="badge-soft"><?= e($product['slug']) ?></span>
                        <div class="toolbar-actions">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="submit" formaction="<?= route_url('/admin/products/delete') ?>" onclick="return confirm('Delete this product?')">Delete</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>
