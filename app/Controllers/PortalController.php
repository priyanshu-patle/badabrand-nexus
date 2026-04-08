<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Content;
use App\Core\Database;
use App\Core\View;

class PortalController
{
    private function filterProducts(array $products, string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return $products;
        }

        return array_values(array_filter($products, function (array $product) use ($query): bool {
            foreach (['name', 'category', 'product_type', 'short_description', 'description', 'version_label'] as $field) {
                if (stripos((string) ($product[$field] ?? ''), $query) !== false) {
                    return true;
                }
            }
            return false;
        }));
    }

    private function requireVendorContext(): array
    {
        Auth::requireRole(['vendor']);
        $user = Auth::user();
        $vendor = Content::vendorByUserId((int) ($user['id'] ?? 0));
        if (! $vendor) {
            flash('error', 'Vendor profile not found. Please contact support.');
            redirect('/logout');
        }

        return [$user, $vendor];
    }

    private function renderVendor(string $view, string $title, array $data = []): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render($view, [
            'pageTitle' => $title,
            'metaDescription' => $title,
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
        ] + $data, 'layouts/dashboard');
    }

    public function customer(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/customer-dashboard', [
            'pageTitle' => 'Client Dashboard',
            'metaDescription' => 'Manage services, invoices, projects, and support tickets.',
            'user' => $user,
            'settings' => Content::settings(),
            'orders' => Content::userOrders((int) $user['id']),
            'invoices' => Content::userInvoices((int) $user['id']),
            'payments' => Content::userPayments((int) $user['id']),
            'tickets' => Content::userTickets((int) $user['id']),
            'notifications' => Content::userNotifications((int) $user['id']),
            'referral' => Content::userReferral((int) $user['id']),
            'products' => array_slice(Content::products(true), 0, 4),
        ], 'layouts/dashboard');
    }

    public function clientServices(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-services', [
            'pageTitle' => 'Orders & Services',
            'metaDescription' => 'View service orders, product purchases, receipts, and approval workflow.',
            'user' => $user,
            'settings' => Content::settings(),
            'orders' => Content::userOrders((int) $user['id']),
        ], 'layouts/dashboard');
    }

    public function clientMarketplace(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $query = trim((string) input('q', ''));
        View::render('portal/client-marketplace', [
            'pageTitle' => 'Marketplace',
            'metaDescription' => 'Buy digital products, themes, plugins, software, and services.',
            'user' => $user,
            'settings' => Content::settings(),
            'search' => $query,
            'products' => $this->filterProducts(Content::products(true), $query),
            'services' => Content::services(),
            'plans' => Content::plans(),
        ], 'layouts/dashboard');
    }

    public function clientInvoices(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-invoices', [
            'pageTitle' => 'Invoices',
            'metaDescription' => 'Download GST invoices and review billing.',
            'user' => $user,
            'settings' => Content::settings(),
            'invoices' => Content::userInvoices((int) $user['id']),
        ], 'layouts/dashboard');
    }

    public function invoiceView(): void
    {
        Auth::requireRole(['customer', 'admin']);
        $user = Auth::user();
        $invoice = Content::invoice((int) input('id', 0), $user['role'] === 'admin' ? null : (int) $user['id']);
        if (! $invoice) {
            flash('error', 'Invoice not found.');
            redirect($user['role'] === 'admin' ? '/admin/payments' : '/client/invoices');
        }
        View::render('portal/invoice-view', [
            'pageTitle' => 'Invoice ' . $invoice['invoice_number'],
            'metaDescription' => 'Invoice document.',
            'user' => $user,
            'settings' => Content::settings(),
            'invoice' => $invoice,
            'items' => Content::invoiceItems((int) $invoice['id']),
        ], 'layouts/dashboard');
    }

    public function invoiceText(): void
    {
        Auth::requireRole(['customer', 'admin']);
        $user = Auth::user();
        $invoice = Content::invoice((int) input('id', 0), $user['role'] === 'admin' ? null : (int) $user['id']);
        if (! $invoice) {
            flash('error', 'Invoice not found.');
            redirect($user['role'] === 'admin' ? '/admin/payments' : '/client/invoices');
        }

        $items = Content::invoiceItems((int) $invoice['id']);
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $invoice['invoice_number'] . '.txt"');
        echo "Badabrand Technologies\n";
        echo "Invoice: {$invoice['invoice_number']}\n";
        echo 'Customer: ' . trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? '')) . "\n";
        echo 'Billing Name: ' . ($invoice['billing_name'] ?: trim(($invoice['first_name'] ?? '') . ' ' . ($invoice['last_name'] ?? ''))) . "\n";
        echo 'Status: ' . ($invoice['status'] ?? 'unpaid') . "\n";
        echo 'Due Date: ' . ($invoice['due_date'] ?: 'On receipt') . "\n\n";
        foreach ($items as $index => $item) {
            echo ($index + 1) . '. ' . $item['item_name'] . ' | Qty: ' . $item['quantity'] . ' | Unit: ' . money_format_inr($item['unit_price']) . ' | Total: ' . money_format_inr($item['line_total']) . "\n";
        }
        echo "\nSubtotal: " . money_format_inr($invoice['subtotal']) . "\n";
        echo 'GST: ' . money_format_inr($invoice['gst_amount']) . "\n";
        echo 'Grand Total: ' . money_format_inr($invoice['total']) . "\n";
        exit;
    }

    public function clientPayments(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-payments', [
            'pageTitle' => 'Payments',
            'metaDescription' => 'Upload payment proof and review payment history.',
            'user' => $user,
            'settings' => Content::settings(),
            'payments' => Content::userPayments((int) $user['id']),
            'invoices' => Content::userInvoices((int) $user['id']),
            'selectedInvoiceId' => (int) input('invoice_id', 0),
        ], 'layouts/dashboard');
    }

    public function submitPayment(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $proof = upload_file('proof_file');
        $invoiceId = input('invoice_id') ?: null;
        $amount = (float) input('amount', 0);
        if ($invoiceId && $amount <= 0) {
            $invoice = Content::invoice((int) $invoiceId, (int) $user['id']);
            $amount = (float) ($invoice['total'] ?? 0);
        }
        $stmt = Database::connection()->prepare('
            INSERT INTO payments (user_id, invoice_id, gateway, transaction_id, proof_path, notes, amount, currency, status, created_at, updated_at)
            VALUES (:user_id, :invoice_id, :gateway, :transaction_id, :proof_path, :notes, :amount, :currency, :status, NOW(), NOW())
        ');
        $stmt->execute([
            'user_id' => $user['id'],
            'invoice_id' => $invoiceId,
            'gateway' => input('gateway', 'manual_bank'),
            'transaction_id' => input('transaction_id', ''),
            'proof_path' => $proof,
            'notes' => input('notes', ''),
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'pending',
        ]);
        if ($invoiceId) {
            Database::connection()->prepare('UPDATE invoices SET status = :status, updated_at = NOW() WHERE id = :id')->execute([
                'status' => 'payment_review',
                'id' => (int) $invoiceId,
            ]);
        }
        flash('success', 'Payment proof uploaded for admin review.');
        redirect('/client/payments');
    }

    public function clientTickets(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $ticketId = (int) input('ticket', 0);
        $messages = $ticketId > 0 ? Content::ticketMessages($ticketId) : [];
        View::render('portal/client-tickets', [
            'pageTitle' => 'Support Tickets',
            'metaDescription' => 'Create and track support tickets.',
            'user' => $user,
            'settings' => Content::settings(),
            'tickets' => Content::userTickets((int) $user['id']),
            'messages' => $messages,
            'activeTicketId' => $ticketId,
        ], 'layouts/dashboard');
    }

    public function createTicket(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $db = Database::connection();
        $stmt = $db->prepare('
            INSERT INTO tickets (user_id, subject, priority, status, message, created_at, updated_at)
            VALUES (:user_id, :subject, :priority, :status, :message, NOW(), NOW())
        ');
        $stmt->execute([
            'user_id' => $user['id'],
            'subject' => input('subject', ''),
            'priority' => input('priority', 'medium'),
            'status' => 'open',
            'message' => input('message', ''),
        ]);
        $ticketId = (int) $db->lastInsertId();
        $messageStmt = $db->prepare('INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message, created_at) VALUES (:ticket_id, :sender_type, :sender_id, :message, NOW())');
        $messageStmt->execute([
            'ticket_id' => $ticketId,
            'sender_type' => 'customer',
            'sender_id' => $user['id'],
            'message' => input('message', ''),
        ]);

        do_action('onTicketCreated', [
            'ticket_id' => $ticketId,
            'user_id' => (int) $user['id'],
            'user' => $user,
            'subject' => (string) input('subject', ''),
            'priority' => (string) input('priority', 'medium'),
            'message' => (string) input('message', ''),
        ]);

        flash('success', 'Support ticket created.');
        redirect('/client/tickets');
    }

    public function clientProjects(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $orders = Content::userOrders((int) $user['id']);
        $updates = [];
        if ($orders) {
            $stmt = Database::connection()->prepare('SELECT project_updates.*, orders.order_number FROM project_updates JOIN orders ON orders.id = project_updates.order_id WHERE project_updates.user_id = :user ORDER BY project_updates.created_at DESC');
            $stmt->execute(['user' => $user['id']]);
            $updates = $stmt->fetchAll();
        }
        View::render('portal/client-projects', [
            'pageTitle' => 'Project Tracker',
            'metaDescription' => 'Track live project progress and updates.',
            'user' => $user,
            'settings' => Content::settings(),
            'orders' => $orders,
            'updates' => $updates,
        ], 'layouts/dashboard');
    }

    public function clientFiles(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-files', [
            'pageTitle' => 'Files',
            'metaDescription' => 'Download and upload project files.',
            'user' => $user,
            'settings' => Content::settings(),
            'files' => Content::userFiles((int) $user['id']),
        ], 'layouts/dashboard');
    }

    public function clientProposals(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-proposals', [
            'pageTitle' => 'Proposals',
            'metaDescription' => 'View quotations or submit your own proposal request.',
            'user' => $user,
            'settings' => Content::settings(),
            'proposals' => Content::userProposals((int) $user['id']),
        ], 'layouts/dashboard');
    }

    public function submitProposal(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        Database::connection()->prepare('
            INSERT INTO proposals (user_id, title, description, amount, valid_until, status, submitted_by_customer, created_at, updated_at)
            VALUES (:user_id, :title, :description, :amount, :valid_until, :status, 1, NOW(), NOW())
        ')->execute([
            'user_id' => (int) $user['id'],
            'title' => input('title', ''),
            'description' => input('description', ''),
            'amount' => (float) input('amount', 0),
            'valid_until' => input('valid_until', null),
            'status' => 'submitted',
        ]);
        notify_user((int) $user['id'], 'proposal', 'Proposal submitted', 'Your proposal request has been submitted and is awaiting admin review.', '/client/proposals');
        flash('success', 'Your proposal has been submitted successfully.');
        redirect('/client/proposals');
    }

    public function proposalView(): void
    {
        Auth::requireRole(['customer', 'admin']);
        $user = Auth::user();
        $query = 'SELECT proposals.*, users.first_name, users.last_name, users.email FROM proposals JOIN users ON users.id = proposals.user_id WHERE proposals.id = :id';
        $params = ['id' => (int) input('id', 0)];
        if ($user['role'] !== 'admin') {
            $query .= ' AND proposals.user_id = :user';
            $params['user'] = (int) $user['id'];
        }
        $stmt = Database::connection()->prepare($query . ' LIMIT 1');
        $stmt->execute($params);
        $proposal = $stmt->fetch();
        if (! $proposal) {
            flash('error', 'Proposal not found.');
            redirect($user['role'] === 'admin' ? '/admin/projects' : '/client/proposals');
        }
        View::render('portal/proposal-view', [
            'pageTitle' => 'Proposal',
            'metaDescription' => 'Proposal document',
            'user' => $user,
            'settings' => Content::settings(),
            'proposal' => $proposal,
        ], 'layouts/dashboard');
    }

    public function clientContracts(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        View::render('portal/client-contracts', [
            'pageTitle' => 'Contracts',
            'metaDescription' => 'Review and sign contracts.',
            'user' => $user,
            'settings' => Content::settings(),
            'contracts' => Content::userContracts((int) $user['id']),
        ], 'layouts/dashboard');
    }

    public function contractView(): void
    {
        Auth::requireRole(['customer', 'admin']);
        $user = Auth::user();
        $query = 'SELECT contracts.*, users.first_name, users.last_name, users.email FROM contracts JOIN users ON users.id = contracts.user_id WHERE contracts.id = :id';
        $params = ['id' => (int) input('id', 0)];
        if ($user['role'] !== 'admin') {
            $query .= ' AND contracts.user_id = :user';
            $params['user'] = (int) $user['id'];
        }
        $stmt = Database::connection()->prepare($query . ' LIMIT 1');
        $stmt->execute($params);
        $contract = $stmt->fetch();
        if (! $contract) {
            flash('error', 'Contract not found.');
            redirect($user['role'] === 'admin' ? '/admin/projects' : '/client/contracts');
        }
        View::render('portal/contract-view', [
            'pageTitle' => 'Contract',
            'metaDescription' => 'Contract document',
            'user' => $user,
            'settings' => Content::settings(),
            'contract' => $contract,
        ], 'layouts/dashboard');
    }

    public function signContract(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        $stmt = Database::connection()->prepare('UPDATE contracts SET status = :status, signature_name = :signature_name, signed_at = NOW(), updated_at = NOW() WHERE id = :id AND user_id = :user');
        $stmt->execute([
            'status' => 'signed',
            'signature_name' => input('signature_name', trim($user['first_name'] . ' ' . $user['last_name'])),
            'id' => (int) input('contract_id', 0),
            'user' => (int) $user['id'],
        ]);
        flash('success', 'Contract signed successfully.');
        redirect('/client/contracts');
    }

    public function acceptTerms(): void
    {
        Auth::requireRole(['customer']);
        $user = Auth::user();
        Database::connection()->prepare('UPDATE users SET terms_accepted_at = NOW(), updated_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);
        flash('success', 'Terms and conditions accepted.');
        redirect('/client');
    }

    public function developer(): void
    {
        Auth::requireRole(['developer']);
        $user = Auth::user();
        View::render('portal/developer-dashboard', [
            'pageTitle' => 'Developer Dashboard',
            'metaDescription' => 'Track assigned projects, tickets, and delivery tasks.',
            'user' => $user,
            'settings' => Content::settings(),
            'orders' => Content::allOrders(),
            'tickets' => Content::allTickets(),
        ], 'layouts/dashboard');
    }

    public function vendorDashboard(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/dashboard', [
            'pageTitle' => 'Vendor Dashboard',
            'metaDescription' => 'Manage vendor products, orders, payouts, and store profile.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'summary' => Content::vendorSummary((int) $vendor['id']),
            'products' => array_slice(Content::vendorProducts((int) $vendor['id']), 0, 5),
            'orders' => array_slice(Content::vendorOrders((int) $vendor['id']), 0, 5),
            'reviews' => array_slice(Content::vendorReviews((int) $vendor['id']), 0, 5),
            'payouts' => array_slice(Content::vendorPayouts((int) $vendor['id']), 0, 5),
        ], 'layouts/dashboard');
    }

    public function vendorProducts(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/products', [
            'pageTitle' => 'Vendor Products',
            'metaDescription' => 'Manage vendor marketplace products.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'products' => Content::vendorProducts((int) $vendor['id']),
        ], 'layouts/dashboard');
    }

    public function vendorProductCreate(): void
    {
        $this->renderVendor('vendor/product-form', 'Create Vendor Product', [
            'mode' => 'create',
            'product' => null,
        ]);
    }

    public function vendorProductEdit(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        $product = Content::product((int) input('id', 0));
        if (! $product || (int) ($product['vendor_id'] ?? 0) !== (int) $vendor['id']) {
            flash('error', 'Product not found.');
            redirect('/vendor/products');
        }

        View::render('vendor/product-form', [
            'pageTitle' => 'Edit Vendor Product',
            'metaDescription' => 'Edit your marketplace product.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'mode' => 'edit',
            'product' => $product,
        ], 'layouts/dashboard');
    }

    public function storeVendorProduct(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        if (($vendor['status'] ?? 'pending') !== 'approved') {
            flash('error', 'Your vendor account must be approved before you can publish products.');
            redirect('/vendor/dashboard');
        }

        $name = trim((string) input('name', ''));
        if ($name === '') {
            flash('error', 'Product name is required.');
            redirect('/vendor/products/create');
        }

        $thumbnail = upload_file('thumbnail_file', 'assets/uploads/vendors/products');
        $productType = strtolower(trim((string) input('product_type', 'software')));
        $requiresReview = app_setting('vendor_product_requires_review', '1') === '1';
        Database::connection()->prepare('
            INSERT INTO products (name, slug, vendor_id, category, product_type, short_description, description, features_text, price, price_label, version_label, thumbnail_path, download_link, status, approval_status, commission_percent, requires_review, sort_order, created_at, updated_at)
            VALUES (:name, :slug, :vendor_id, :category, :product_type, :short_description, :description, :features_text, :price, :price_label, :version_label, :thumbnail_path, :download_link, :status, :approval_status, :commission_percent, :requires_review, :sort_order, NOW(), NOW())
        ')->execute([
            'name' => $name,
            'slug' => slugify($name, 'product'),
            'vendor_id' => (int) $vendor['id'],
            'category' => match ($productType) {
                'theme' => 'themes',
                'plugin' => 'plugins',
                'template' => 'templates',
                default => 'software',
            },
            'product_type' => $productType,
            'short_description' => input('short_description', ''),
            'description' => input('description', ''),
            'features_text' => input('features_text', ''),
            'price' => (float) input('price', 0),
            'price_label' => input('price_label', ''),
            'version_label' => input('version_label', ''),
            'thumbnail_path' => $thumbnail,
            'download_link' => input('download_link', ''),
            'status' => input('status', 'draft'),
            'approval_status' => $requiresReview ? 'pending' : 'approved',
            'commission_percent' => (float) ($vendor['commission_percent'] ?? app_setting('vendor_default_commission', '15')),
            'requires_review' => $requiresReview ? 1 : 0,
            'sort_order' => 0,
        ]);
        $productId = (int) Database::connection()->lastInsertId();
        Database::connection()->prepare('
            INSERT INTO vendor_activity_logs (vendor_id, actor_user_id, action, description, context, created_at)
            VALUES (:vendor_id, :actor_user_id, :action, :description, :context, NOW())
        ')->execute([
            'vendor_id' => (int) $vendor['id'],
            'actor_user_id' => (int) $user['id'],
            'action' => 'vendor.product.created',
            'description' => 'Vendor submitted a product for marketplace review.',
            'context' => json_encode(['product_id' => $productId], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        notify_user((int) $user['id'], 'vendor', 'Product submitted', 'Your product has been saved and submitted into the vendor marketplace workflow.', '/vendor/products');
        flash('success', 'Vendor product saved successfully.');
        redirect('/vendor/products');
    }

    public function updateVendorProduct(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        $productId = (int) input('product_id', 0);
        $current = Content::product($productId);
        if (! $current || (int) ($current['vendor_id'] ?? 0) !== (int) $vendor['id']) {
            flash('error', 'Product not found.');
            redirect('/vendor/products');
        }

        $thumbnail = upload_file('thumbnail_file', 'assets/uploads/vendors/products') ?: ($current['thumbnail_path'] ?? null);
        $productType = strtolower(trim((string) input('product_type', (string) ($current['product_type'] ?? 'software'))));
        $requiresReview = app_setting('vendor_product_requires_review', '1') === '1';
        $approvalStatus = $requiresReview ? 'pending' : (($current['approval_status'] ?? 'approved') ?: 'approved');

        Database::connection()->prepare('
            UPDATE products
            SET name = :name,
                slug = :slug,
                category = :category,
                product_type = :product_type,
                short_description = :short_description,
                description = :description,
                features_text = :features_text,
                price = :price,
                price_label = :price_label,
                version_label = :version_label,
                thumbnail_path = :thumbnail_path,
                download_link = :download_link,
                status = :status,
                approval_status = :approval_status,
                requires_review = :requires_review,
                updated_at = NOW()
            WHERE id = :id AND vendor_id = :vendor_id
        ')->execute([
            'id' => $productId,
            'vendor_id' => (int) $vendor['id'],
            'name' => trim((string) input('name', '')),
            'slug' => slugify((string) input('name', ''), 'product'),
            'category' => match ($productType) {
                'theme' => 'themes',
                'plugin' => 'plugins',
                'template' => 'templates',
                default => 'software',
            },
            'product_type' => $productType,
            'short_description' => input('short_description', ''),
            'description' => input('description', ''),
            'features_text' => input('features_text', ''),
            'price' => (float) input('price', 0),
            'price_label' => input('price_label', ''),
            'version_label' => input('version_label', ''),
            'thumbnail_path' => $thumbnail,
            'download_link' => input('download_link', ''),
            'status' => input('status', 'draft'),
            'approval_status' => $approvalStatus,
            'requires_review' => $requiresReview ? 1 : 0,
        ]);
        notify_user((int) $user['id'], 'vendor', 'Product updated', 'Your marketplace product was updated successfully.', '/vendor/products');
        flash('success', 'Product updated.');
        redirect('/vendor/products');
    }

    public function vendorOrders(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/orders', [
            'pageTitle' => 'Vendor Orders',
            'metaDescription' => 'Review marketplace orders linked to your products.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'orders' => Content::vendorOrders((int) $vendor['id']),
            'commissions' => Content::vendorCommissions((int) $vendor['id']),
        ], 'layouts/dashboard');
    }

    public function vendorPayouts(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/payouts', [
            'pageTitle' => 'Vendor Payouts',
            'metaDescription' => 'Track commissions, balances, and payout history.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'summary' => Content::vendorSummary((int) $vendor['id']),
            'commissions' => Content::vendorCommissions((int) $vendor['id']),
            'payouts' => Content::vendorPayouts((int) $vendor['id']),
            'payoutAccount' => Content::vendorPayoutAccount((int) $vendor['id']),
        ], 'layouts/dashboard');
    }

    public function requestVendorPayout(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        $summary = Content::vendorSummary((int) $vendor['id']);
        $requestAmount = round((float) input('request_amount', 0), 2);
        $minimum = (float) app_setting('vendor_minimum_payout', '1000');
        if ($requestAmount <= 0 || $requestAmount > (float) ($summary['available_balance'] ?? 0)) {
            flash('error', 'Enter a valid payout amount from your available balance.');
            redirect('/vendor/payouts');
        }
        if ($requestAmount < $minimum) {
            flash('error', 'Minimum payout request is ' . money_format_inr($minimum) . '.');
            redirect('/vendor/payouts');
        }

        $db = Database::connection();
        $db->prepare('
            INSERT INTO vendor_payouts (vendor_id, request_amount, currency, status, requested_at, admin_note, created_at, updated_at)
            VALUES (:vendor_id, :request_amount, :currency, :status, NOW(), :admin_note, NOW(), NOW())
        ')->execute([
            'vendor_id' => (int) $vendor['id'],
            'request_amount' => $requestAmount,
            'currency' => 'INR',
            'status' => 'requested',
            'admin_note' => input('vendor_note', ''),
        ]);
        $payoutId = (int) $db->lastInsertId();
        $db->prepare('
            UPDATE vendor_commissions
            SET payout_status = "requested", payout_id = :payout_id, updated_at = NOW()
            WHERE vendor_id = :vendor_id AND payout_status = "available"
        ')->execute([
            'payout_id' => $payoutId,
            'vendor_id' => (int) $vendor['id'],
        ]);
        $db->prepare('
            INSERT INTO vendor_activity_logs (vendor_id, actor_user_id, action, description, context, created_at)
            VALUES (:vendor_id, :actor_user_id, :action, :description, :context, NOW())
        ')->execute([
            'vendor_id' => (int) $vendor['id'],
            'actor_user_id' => (int) $user['id'],
            'action' => 'vendor.payout.requested',
            'description' => 'Vendor requested payout.',
            'context' => json_encode(['amount' => $requestAmount, 'payout_id' => $payoutId], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        flash('success', 'Payout request submitted.');
        redirect('/vendor/payouts');
    }

    public function vendorProfile(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/profile', [
            'pageTitle' => 'Vendor Profile',
            'metaDescription' => 'Manage store identity, support details, and payout information.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'documents' => Content::vendorDocuments((int) $vendor['id']),
            'payoutAccount' => Content::vendorPayoutAccount((int) $vendor['id']),
        ], 'layouts/dashboard');
    }

    public function updateVendorProfile(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        $db = Database::connection();
        $logo = upload_file('logo_file', 'assets/uploads/vendors') ?: ($vendor['logo_path'] ?? null);
        $banner = upload_file('banner_file', 'assets/uploads/vendors') ?: ($vendor['banner_path'] ?? null);
        $identity = upload_file('identity_file', 'assets/uploads/vendors/documents');
        $gstFile = upload_file('gst_file', 'assets/uploads/vendors/documents');

        $db->prepare('
            UPDATE vendors
            SET store_name = :store_name,
                display_name = :display_name,
                phone = :phone,
                tax_gst = :tax_gst,
                updated_at = NOW()
            WHERE id = :id
        ')->execute([
            'id' => (int) $vendor['id'],
            'store_name' => input('store_name', $vendor['store_name'] ?? ''),
            'display_name' => input('display_name', $vendor['display_name'] ?? ''),
            'phone' => input('phone', $vendor['phone'] ?? ''),
            'tax_gst' => input('tax_gst', $vendor['tax_gst'] ?? ''),
        ]);
        $db->prepare('
            UPDATE vendor_profiles
            SET business_name = :business_name,
                legal_name = :legal_name,
                short_bio = :short_bio,
                address_line1 = :address_line1,
                address_line2 = :address_line2,
                city = :city,
                state = :state,
                country = :country,
                postal_code = :postal_code,
                website = :website,
                support_email = :support_email,
                support_phone = :support_phone,
                logo_path = :logo_path,
                banner_path = :banner_path,
                updated_at = NOW()
            WHERE vendor_id = :vendor_id
        ')->execute([
            'vendor_id' => (int) $vendor['id'],
            'business_name' => input('business_name', $vendor['business_name'] ?? ''),
            'legal_name' => input('legal_name', $vendor['legal_name'] ?? ''),
            'short_bio' => input('short_bio', $vendor['short_bio'] ?? ''),
            'address_line1' => input('address_line1', $vendor['address_line1'] ?? ''),
            'address_line2' => input('address_line2', $vendor['address_line2'] ?? ''),
            'city' => input('city', $vendor['city'] ?? ''),
            'state' => input('state', $vendor['state'] ?? ''),
            'country' => input('country', $vendor['country'] ?? ''),
            'postal_code' => input('postal_code', $vendor['postal_code'] ?? ''),
            'website' => input('website', $vendor['website'] ?? ''),
            'support_email' => input('support_email', $vendor['support_email'] ?? ''),
            'support_phone' => input('support_phone', $vendor['support_phone'] ?? ''),
            'logo_path' => $logo,
            'banner_path' => $banner,
        ]);
        $db->prepare('
            UPDATE vendor_payout_accounts
            SET payout_method = :payout_method,
                account_name = :account_name,
                account_number = :account_number,
                ifsc_swift = :ifsc_swift,
                upi_id = :upi_id,
                paypal_email = :paypal_email,
                notes = :notes,
                updated_at = NOW()
            WHERE vendor_id = :vendor_id
        ')->execute([
            'vendor_id' => (int) $vendor['id'],
            'payout_method' => input('payout_method', $vendor['payout_method'] ?? 'bank_transfer'),
            'account_name' => input('account_name', $vendor['account_name'] ?? ''),
            'account_number' => input('account_number', $vendor['account_number'] ?? ''),
            'ifsc_swift' => input('ifsc_swift', $vendor['ifsc_swift'] ?? ''),
            'upi_id' => input('upi_id', $vendor['upi_id'] ?? ''),
            'paypal_email' => input('paypal_email', $vendor['paypal_email'] ?? ''),
            'notes' => input('payout_notes', $vendor['payout_notes'] ?? ''),
        ]);

        foreach (['identity' => $identity, 'gst' => $gstFile] as $documentType => $path) {
            if (! $path) {
                continue;
            }
            $db->prepare('
                INSERT INTO vendor_documents (vendor_id, document_type, file_path, status, notes, created_at, updated_at)
                VALUES (:vendor_id, :document_type, :file_path, "pending", :notes, NOW(), NOW())
            ')->execute([
                'vendor_id' => (int) $vendor['id'],
                'document_type' => $documentType,
                'file_path' => $path,
                'notes' => 'Uploaded from vendor profile settings',
            ]);
        }

        flash('success', 'Vendor profile updated.');
        redirect('/vendor/profile');
    }

    public function vendorSettings(): void
    {
        [$user, $vendor] = $this->requireVendorContext();
        View::render('vendor/settings', [
            'pageTitle' => 'Vendor Settings',
            'metaDescription' => 'View vendor program settings and approval rules.',
            'user' => $user,
            'vendor' => $vendor,
            'settings' => Content::settings(),
            'summary' => Content::vendorSummary((int) $vendor['id']),
        ], 'layouts/dashboard');
    }

    public function developerProjects(): void
    {
        Auth::requireRole(['developer']);
        $user = Auth::user();
        View::render('portal/developer-projects', [
            'pageTitle' => 'Assigned Projects',
            'metaDescription' => 'View project work queue.',
            'user' => $user,
            'settings' => Content::settings(),
            'orders' => Content::allOrders(),
        ], 'layouts/dashboard');
    }

    public function developerTickets(): void
    {
        Auth::requireRole(['developer']);
        $user = Auth::user();
        View::render('portal/developer-tickets', [
            'pageTitle' => 'Support Queue',
            'metaDescription' => 'View ticket queue.',
            'user' => $user,
            'settings' => Content::settings(),
            'tickets' => Content::allTickets(),
        ], 'layouts/dashboard');
    }
}
