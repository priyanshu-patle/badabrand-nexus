<div class="row g-4">
    <div class="col-xxl-9">
        <div class="row g-4">
            <div class="col-md-3"><div class="metric-card"><span>Referral Accounts</span><strong><?= e((string) ($referralSummary['accounts'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Attributed Signups</span><strong><?= e((string) ($referralSummary['signups'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Total Earned</span><strong><?= e(money_format_inr($referralSummary['earned'] ?? 0)) ?></strong></div></div>
            <div class="col-md-3"><div class="metric-card"><span>Pending Payouts</span><strong><?= e(money_format_inr($referralSummary['unpaid_balance'] ?? 0)) ?></strong></div></div>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Referral Accounts</h4><span>Codes, balances, sponsor linkage, and payout status</span></div>
            <?php if ($referrals === []): ?>
                <div class="dash-empty-state">
                    <span class="dash-empty-state-icon"><i class="bi bi-megaphone"></i></span>
                    <div class="dash-empty-state-copy">
                        <h5>No referral records yet</h5>
                        <p>Referral accounts will be created automatically when customers register and can then be tracked from this marketing workspace.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($referrals as $referral): ?>
                    <form class="dash-form-block" method="post" action="<?= route_url('/admin/marketing/referrals/update') ?>">
                        <input type="hidden" name="referral_id" value="<?= e((string) $referral['id']) ?>">
                        <div class="list-row">
                            <div>
                                <strong><?= e(trim($referral['first_name'] . ' ' . $referral['last_name'])) ?></strong>
                                <span><?= e($referral['email'] ?? '') ?> | <?= e($referral['referral_code']) ?></span>
                            </div>
                            <span class="badge-soft"><?= e(($referral['total_signups'] ?? 0) . ' signups') ?></span>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-lg-4"><input class="form-control" value="<?= e(money_format_inr($referral['total_earned'] ?? 0)) ?>" readonly></div>
                            <div class="col-lg-4"><input class="form-control" name="reward_balance" value="<?= e((string) ($referral['reward_balance'] ?? 0)) ?>" placeholder="Reward balance"></div>
                            <div class="col-lg-4">
                                <select class="form-select" name="payout_status">
                                    <?php foreach (['unpaid', 'processing', 'paid', 'on_hold'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= ($referral['payout_status'] ?? 'unpaid') === $status ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $status))) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-lg-6"><input class="form-control" value="<?= e(trim(($referral['sponsor_first_name'] ?? '') . ' ' . ($referral['sponsor_last_name'] ?? '')) ?: 'Direct signup') ?>" readonly></div>
                            <div class="col-lg-6"><input class="form-control" value="<?= e((string) ($referral['last_referred_at'] ?? 'No attributed signup yet')) ?>" readonly></div>
                        </div>
                        <textarea class="form-control mt-3" name="notes" rows="2" placeholder="Payout notes or internal referral comments"><?= e((string) ($referral['notes'] ?? '')) ?></textarea>
                        <div class="toolbar-actions mt-3">
                            <span class="badge-soft"><?= e(money_format_inr($referral['reward_balance'] ?? 0)) ?></span>
                            <button class="btn btn-primary" type="submit">Update Referral</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xxl-3">
        <div class="dash-card">
            <div class="card-title-row"><h4>Referral Rules</h4><span>Business settings</span></div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/settings') ?>">
                <input type="hidden" name="_redirect" value="/admin/marketing/referrals">
                <select class="form-select" name="referral_enabled">
                    <option value="1" <?= ($systemSettings['referral_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Referral program enabled</option>
                    <option value="0" <?= ($systemSettings['referral_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Referral program disabled</option>
                </select>
                <input class="form-control" name="referral_reward_amount" value="<?= e((string) ($systemSettings['referral_reward_amount'] ?? '500')) ?>" placeholder="Reward amount per signup">
                <input class="form-control" name="referral_percentage" value="<?= e((string) ($systemSettings['referral_percentage'] ?? '5')) ?>" placeholder="Commission percentage (future orders)">
                <input class="form-control" name="referral_minimum_payout" value="<?= e((string) ($systemSettings['referral_minimum_payout'] ?? '1000')) ?>" placeholder="Minimum payout threshold">
                <button class="btn btn-primary" type="submit">Save Referral Rules</button>
            </form>
        </div>

        <div class="dash-card mt-4">
            <div class="card-title-row"><h4>Lead Inbox</h4><span>Contact and referral opportunities</span></div>
            <?php if ($contacts === []): ?>
                <div class="dash-empty-state">
                    <span class="dash-empty-state-icon"><i class="bi bi-envelope-paper"></i></span>
                    <div class="dash-empty-state-copy">
                        <h5>No recent contact leads</h5>
                        <p>New contact submissions and referral-ready leads will appear here for the marketing team.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($contacts, 0, 10) as $contact): ?>
                    <div class="dash-form-block">
                        <strong><?= e($contact['name']) ?></strong>
                        <div class="small-text"><?= e($contact['email']) ?><?= ! empty($contact['service_interest']) ? ' | ' . e($contact['service_interest']) : '' ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
