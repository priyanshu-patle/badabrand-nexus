<div class="row g-4">
    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Menu Manager</h4>
                    <p class="mb-0 text-muted">Review built-in navigation groups, add custom menu items, and prepare menu ordering for future theme builders.</p>
                </div>
                <span class="badge-soft">Public + Admin navigation</span>
            </div>
            <div class="menu-builder-grid mt-4">
                <div class="mini-panel">
                    <strong><?= count($publicMenuGroups ?? []) ?></strong>
                    <span>Built-in public menu groups</span>
                </div>
                <div class="mini-panel">
                    <strong><?= count($adminMenuGroups[0]['items'] ?? []) ?></strong>
                    <span>Admin navigation sections</span>
                </div>
                <div class="mini-panel">
                    <strong><?= count($customMenuLinks ?? []) ?></strong>
                    <span>Custom links stored in settings</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Add Menu Item</h4>
                    <p class="mb-0 text-muted">Create custom links for public navigation planning or admin shortcut groups.</p>
                </div>
                <span class="badge-soft">Sort-order ready</span>
            </div>
            <form class="stack-form mt-4" method="post" action="<?= route_url('/admin/appearance/menus') ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Scope</label>
                        <select class="form-select" name="scope">
                            <option value="public">Public Menu</option>
                            <option value="admin">Admin Menu</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Visibility</label>
                        <select class="form-select" name="visibility">
                            <option value="all">Everyone</option>
                            <option value="admin">Admins only</option>
                            <option value="customer">Customers only</option>
                            <option value="vendor">Vendors only</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Menu Group</label>
                        <input class="form-control" type="text" name="group" placeholder="Header, Footer, Utility, Shortcuts">
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Sort Order</label>
                        <input class="form-control" type="number" name="sort_order" value="100" min="0" step="1">
                    </div>
                    <div class="col-12">
                        <label class="small-text d-block mb-2">Label</label>
                        <input class="form-control" type="text" name="label" placeholder="Menu item label" required>
                    </div>
                    <div class="col-12">
                        <label class="small-text d-block mb-2">Target URL</label>
                        <input class="form-control" type="text" name="url" placeholder="/pricing or https://example.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Target</label>
                        <select class="form-select" name="target">
                            <option value="_self">Open in same tab</option>
                            <option value="_blank">Open in new tab</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small-text d-block mb-2">Notes</label>
                        <input class="form-control" type="text" name="notes" placeholder="Optional admin note">
                    </div>
                </div>
                <div class="toolbar-actions mt-4">
                    <button class="btn btn-primary" type="submit">Save Menu Item</button>
                    <span class="small-text">Built-in routes remain untouched; this stores extendable menu links safely in settings.</span>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Built-in Menu Groups</h4>
                    <p class="mb-0 text-muted">Current public and admin navigation structure in production.</p>
                </div>
                <span class="badge-soft">System navigation</span>
            </div>
            <div class="menu-groups-grid mt-4">
                <div class="menu-group-panel">
                    <div class="card-title-row">
                        <div>
                            <h5>Public Navigation</h5>
                            <p class="mb-0 text-muted">Frontend menus defined by the shared website navigation and footer links.</p>
                        </div>
                    </div>
                    <?php foreach (($publicMenuGroups ?? []) as $group): ?>
                        <div class="menu-group-block">
                            <strong><?= e($group['label'] ?? 'Group') ?></strong>
                            <div class="menu-chip-list">
                                <?php foreach (($group['items'] ?? []) as $item): ?>
                                    <span class="menu-chip">
                                        <span><?= e($item['label'] ?? '') ?></span>
                                        <small><?= e($item['href'] ?? '') ?></small>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="menu-group-panel">
                    <div class="card-title-row">
                        <div>
                            <h5>Admin Navigation</h5>
                            <p class="mb-0 text-muted">Sidebar sections, children, and module-aware menu registration.</p>
                        </div>
                    </div>
                    <?php foreach (($adminMenuGroups[0]['items'] ?? []) as $item): ?>
                        <div class="menu-group-block">
                            <strong><?= e($item['label'] ?? 'Section') ?></strong>
                            <div class="menu-chip-list">
                                <?php if (! empty($item['children'])): ?>
                                    <?php foreach ($item['children'] as $child): ?>
                                        <span class="menu-chip">
                                            <span><?= e($child['label'] ?? '') ?></span>
                                            <small><?= e($child['href'] ?? '') ?></small>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="menu-chip">
                                        <span><?= e($item['label'] ?? '') ?></span>
                                        <small><?= e($item['href'] ?? '') ?></small>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="dash-card">
            <div class="card-title-row">
                <div>
                    <h4>Custom Menu Links</h4>
                    <p class="mb-0 text-muted">Saved extendable links for future header, footer, account, or theme-specific menu placements.</p>
                </div>
                <span class="badge-soft"><?= count($customMenuLinks ?? []) ?> stored</span>
            </div>
            <?php if (($customMenuLinks ?? []) === []): ?>
                <div class="dash-empty-state mt-4">
                    <div class="dash-empty-state-icon"><i class="bi bi-list-stars"></i></div>
                    <div class="dash-empty-state-copy">
                        <strong>No custom menu links yet</strong>
                        <span>Create your first custom menu item for a header shortcut, footer link, or admin utility route.</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive mt-4">
                    <table class="table align-middle menu-link-table">
                        <thead>
                            <tr>
                                <th>Scope</th>
                                <th>Group</th>
                                <th>Label</th>
                                <th>URL</th>
                                <th>Visibility</th>
                                <th>Sort</th>
                                <th>Target</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($customMenuLinks ?? []) as $item): ?>
                                <tr>
                                    <td><span class="badge-soft"><?= e(ucfirst((string) ($item['scope'] ?? 'public'))) ?></span></td>
                                    <td><?= e($item['group'] ?? 'Custom Links') ?></td>
                                    <td><?= e($item['label'] ?? '') ?></td>
                                    <td><code><?= e($item['url'] ?? '') ?></code></td>
                                    <td><?= e(ucfirst((string) ($item['visibility'] ?? 'all'))) ?></td>
                                    <td><?= e((string) ($item['sort_order'] ?? 100)) ?></td>
                                    <td><?= e(($item['target'] ?? '_self') === '_blank' ? 'New tab' : 'Same tab') ?></td>
                                    <td class="text-end">
                                        <form method="post" action="<?= route_url('/admin/appearance/menus/delete') ?>">
                                            <input type="hidden" name="menu_id" value="<?= e($item['id'] ?? '') ?>">
                                            <button class="btn btn-sm btn-outline-light rounded-pill" type="submit">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
