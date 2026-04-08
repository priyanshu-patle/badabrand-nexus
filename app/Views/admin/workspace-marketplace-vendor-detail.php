<div class="row g-4">
    <div class="col-xxl-4">
        <div class="dash-card">
            <div class="card-title-row"><h4><?= e($vendor['display_name'] ?: $vendor['store_name']) ?></h4><span>Vendor profile</span></div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/marketplace/vendors/status') ?>">
                <input type="hidden" name="vendor_id" value="<?= e((string) $vendor['id']) ?>">
                <input type="hidden" name="_redirect" value="<?= route_url('/admin/marketplace/vendors/' . $vendor['id']) ?>">
                <select class="form-select" name="status">
                    <?php foreach (['pending', 'approved', 'rejected', 'suspended', 'inactive'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= ($vendor['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="small-text"><input type="checkbox" name="verification_badge" value="1" <?= ! empty($vendor['verification_badge']) ? 'checked' : '' ?>> Verified vendor badge</label>
                <textarea class="form-control" name="admin_notes" rows="3" placeholder="Internal admin notes"><?= e((string) ($vendor['admin_notes'] ?? '')) ?></textarea>
                <button class="btn btn-primary" type="submit">Update Vendor Status</button>
            </form>
        </div>
        <div class="row g-4 mt-1">
            <div class="col-sm-6 col-xxl-12"><div class="metric-card"><span>Products</span><strong><?= e((string) ($vendorSummary['products'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xxl-12"><div class="metric-card"><span>Orders</span><strong><?= e((string) ($vendorSummary['orders'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xxl-12"><div class="metric-card"><span>Available Balance</span><strong><?= e(money_format_inr($vendorSummary['available_balance'] ?? 0)) ?></strong></div></div>
            <div class="col-sm-6 col-xxl-12"><div class="metric-card"><span>Paid Earnings</span><strong><?= e(money_format_inr($vendorSummary['paid_earnings'] ?? 0)) ?></strong></div></div>
        </div>
    </div>
    <div class="col-xxl-8">
        <div class="dash-card">
            <div class="card-title-row"><h4>Edit Vendor Profile</h4><span>Store, support, tax, and payout details</span></div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/marketplace/vendors/profile') ?>" enctype="multipart/form-data">
                <input type="hidden" name="vendor_id" value="<?= e((string) $vendor['id']) ?>">
                <input type="hidden" name="_redirect" value="<?= route_url('/admin/marketplace/vendors/' . $vendor['id']) ?>">
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="store_name" value="<?= e((string) ($vendor['store_name'] ?? '')) ?>" placeholder="Store name"></div>
                    <div class="col-md-6"><input class="form-control" name="display_name" value="<?= e((string) ($vendor['display_name'] ?? '')) ?>" placeholder="Display name"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="email" value="<?= e((string) ($vendor['email'] ?? $vendor['user_email'] ?? '')) ?>" placeholder="Store email"></div>
                    <div class="col-md-6"><input class="form-control" name="phone" value="<?= e((string) ($vendor['phone'] ?? $vendor['user_phone'] ?? '')) ?>" placeholder="Phone number"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="tax_gst" value="<?= e((string) ($vendor['tax_gst'] ?? '')) ?>" placeholder="GST / Tax number"></div>
                    <div class="col-md-6"><input class="form-control" name="commission_percent" value="<?= e((string) ($vendor['commission_percent'] ?? '')) ?>" placeholder="Commission percentage"></div>
                </div>
                <textarea class="form-control" name="short_bio" rows="3" placeholder="Vendor bio"><?= e((string) ($vendor['short_bio'] ?? '')) ?></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="business_name" value="<?= e((string) ($vendor['business_name'] ?? '')) ?>" placeholder="Business name"></div>
                    <div class="col-md-6"><input class="form-control" name="legal_name" value="<?= e((string) ($vendor['legal_name'] ?? '')) ?>" placeholder="Legal name"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="address_line1" value="<?= e((string) ($vendor['address_line1'] ?? '')) ?>" placeholder="Address line 1"></div>
                    <div class="col-md-6"><input class="form-control" name="address_line2" value="<?= e((string) ($vendor['address_line2'] ?? '')) ?>" placeholder="Address line 2"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><input class="form-control" name="city" value="<?= e((string) ($vendor['city'] ?? '')) ?>" placeholder="City"></div>
                    <div class="col-md-3"><input class="form-control" name="state" value="<?= e((string) ($vendor['state'] ?? '')) ?>" placeholder="State"></div>
                    <div class="col-md-3"><input class="form-control" name="country" value="<?= e((string) ($vendor['country'] ?? '')) ?>" placeholder="Country"></div>
                    <div class="col-md-3"><input class="form-control" name="postal_code" value="<?= e((string) ($vendor['postal_code'] ?? '')) ?>" placeholder="Postal code"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" name="website" value="<?= e((string) ($vendor['website'] ?? '')) ?>" placeholder="Website"></div>
                    <div class="col-md-3"><input class="form-control" name="support_email" value="<?= e((string) ($vendor['support_email'] ?? '')) ?>" placeholder="Support email"></div>
                    <div class="col-md-3"><input class="form-control" name="support_phone" value="<?= e((string) ($vendor['support_phone'] ?? '')) ?>" placeholder="Support phone"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" name="account_name" value="<?= e((string) ($vendor['account_name'] ?? '')) ?>" placeholder="Payout account name"></div>
                    <div class="col-md-4"><input class="form-control" name="account_number" value="<?= e((string) ($vendor['account_number'] ?? '')) ?>" placeholder="Account number"></div>
                    <div class="col-md-4"><input class="form-control" name="ifsc_swift" value="<?= e((string) ($vendor['ifsc_swift'] ?? '')) ?>" placeholder="IFSC / Swift"></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" name="upi_id" value="<?= e((string) ($vendor['upi_id'] ?? '')) ?>" placeholder="UPI ID"></div>
                    <div class="col-md-4"><input class="form-control" name="paypal_email" value="<?= e((string) ($vendor['paypal_email'] ?? '')) ?>" placeholder="PayPal email"></div>
                    <div class="col-md-4">
                        <select class="form-select" name="payout_method">
                            <?php foreach (['bank_transfer' => 'Bank Transfer', 'upi' => 'UPI', 'paypal' => 'PayPal'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= ($vendor['payout_method'] ?? 'bank_transfer') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <textarea class="form-control" name="payout_notes" rows="2" placeholder="Payout notes"><?= e((string) ($vendor['payout_notes'] ?? '')) ?></textarea>
                <div class="row g-3">
                    <div class="col-md-6"><input class="form-control" type="file" name="logo_file"></div>
                    <div class="col-md-6"><input class="form-control" type="file" name="banner_file"></div>
                </div>
                <button class="btn btn-primary" type="submit">Save Vendor Profile</button>
            </form>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Vendor Products</h4><span><?= e((string) count($products)) ?> products</span></div>
            <?php foreach ($products as $product): ?>
                <div class="list-row">
                    <strong><?= e($product['name']) ?></strong>
                    <span><?= e(($product['approval_status'] ?? 'pending') . ' | ' . ($product['status'] ?? 'draft')) ?></span>
                    <a class="badge-soft" href="<?= route_url('/admin/marketplace/edit/' . $product['id']) ?>">edit</a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Vendor Documents</h4><span>KYC and tax documents</span></div>
            <?php if ($documents === []): ?>
                <p class="text-muted mb-0">No documents uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($documents as $document): ?>
                    <div class="list-row">
                        <strong><?= e(ucwords(str_replace('_', ' ', (string) $document['document_type']))) ?></strong>
                        <span><?= e((string) ($document['status'] ?? 'pending')) ?></span>
                        <a class="badge-soft" href="<?= storage_url($document['file_path']) ?>" target="_blank">open</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
