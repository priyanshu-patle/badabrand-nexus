<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Content;
use App\Core\Database;
use App\Core\View;

class HomeController
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

    private function createOrderInvoice(array $user, array $payload): array
    {
        $db = Database::connection();
        $db->beginTransaction();

        $orderNumber = generate_reference('ORD');
        $receiptNumber = generate_reference('RCT');
        $invoiceNumber = generate_reference('INV');
        $dueDate = date('Y-m-d', strtotime('+5 days'));
        $subtotal = round((float) $payload['unit_price'] * (int) $payload['quantity'], 2);
        $gst = round($subtotal * 0.18, 2);
        $total = round($subtotal + $gst, 2);
        $commissionPercent = (float) ($payload['commission_percent'] ?? 0);
        $platformFeeAmount = $commissionPercent > 0 ? round($subtotal * ($commissionPercent / 100), 2) : 0.0;
        $vendorNetAmount = round($subtotal - $platformFeeAmount, 2);

        $db->prepare('
            INSERT INTO orders (user_id, service_id, product_id, vendor_id, order_type, item_name, pricing_plan_name, notes, receipt_number, expected_delivery, order_number, status, total, progress_percent, due_at, commission_percent, platform_fee_amount, vendor_net_amount, payout_status, created_at, updated_at)
            VALUES (:user_id, :service_id, :product_id, :vendor_id, :order_type, :item_name, :pricing_plan_name, :notes, :receipt_number, :expected_delivery, :order_number, :status, :total, 0, :due_at, :commission_percent, :platform_fee_amount, :vendor_net_amount, :payout_status, NOW(), NOW())
        ')->execute([
            'user_id' => $user['id'],
            'service_id' => $payload['service_id'],
            'product_id' => $payload['product_id'],
            'vendor_id' => $payload['vendor_id'],
            'order_type' => $payload['order_type'],
            'item_name' => $payload['item_name'],
            'pricing_plan_name' => $payload['pricing_plan_name'],
            'notes' => $payload['notes'],
            'receipt_number' => $receiptNumber,
            'expected_delivery' => date('Y-m-d', strtotime('+7 days')),
            'order_number' => $orderNumber,
            'status' => 'pending_approval',
            'total' => $total,
            'due_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'commission_percent' => $commissionPercent > 0 ? $commissionPercent : null,
            'platform_fee_amount' => $platformFeeAmount,
            'vendor_net_amount' => $vendorNetAmount,
            'payout_status' => $payload['vendor_id'] ? 'pending' : null,
        ]);
        $orderId = (int) $db->lastInsertId();

        $db->prepare('
            INSERT INTO invoices (user_id, order_id, invoice_number, billing_name, gst_number, subtotal, gst_percent, gst_amount, total, due_date, status, created_at, updated_at)
            VALUES (:user_id, :order_id, :invoice_number, :billing_name, :gst_number, :subtotal, 18, :gst_amount, :total, :due_date, :status, NOW(), NOW())
        ')->execute([
            'user_id' => $user['id'],
            'order_id' => $orderId,
            'invoice_number' => $invoiceNumber,
            'billing_name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'gst_number' => '',
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total' => $total,
            'due_date' => $dueDate,
            'status' => 'unpaid',
        ]);
        $invoiceId = (int) $db->lastInsertId();

        $db->prepare('
            INSERT INTO invoice_items (invoice_id, item_name, description, quantity, unit_price, line_total, created_at, updated_at)
            VALUES (:invoice_id, :item_name, :description, :quantity, :unit_price, :line_total, NOW(), NOW())
        ')->execute([
            'invoice_id' => $invoiceId,
            'item_name' => $payload['item_name'],
            'description' => $payload['description'],
            'quantity' => $payload['quantity'],
            'unit_price' => $payload['unit_price'],
            'line_total' => $subtotal,
        ]);

        if (! empty($payload['vendor_id'])) {
            $db->prepare('
                INSERT INTO vendor_commissions (vendor_id, order_id, invoice_id, product_id, gross_amount, commission_percent, commission_amount, platform_fee_amount, vendor_net_amount, payout_status, created_at, updated_at)
                VALUES (:vendor_id, :order_id, :invoice_id, :product_id, :gross_amount, :commission_percent, :commission_amount, :platform_fee_amount, :vendor_net_amount, :payout_status, NOW(), NOW())
            ')->execute([
                'vendor_id' => (int) $payload['vendor_id'],
                'order_id' => $orderId,
                'invoice_id' => $invoiceId,
                'product_id' => $payload['product_id'],
                'gross_amount' => $subtotal,
                'commission_percent' => $commissionPercent,
                'commission_amount' => $platformFeeAmount,
                'platform_fee_amount' => $platformFeeAmount,
                'vendor_net_amount' => $vendorNetAmount,
                'payout_status' => 'pending',
            ]);
        }

        notify_user((int) $user['id'], 'order', 'Order placed successfully', 'Your order ' . $orderNumber . ' is pending admin approval. Invoice ' . $invoiceNumber . ' has been created in your dashboard.', '/client/services');

        $subject = 'Order confirmation for ' . $payload['item_name'];
        $html = '<h2>Order received</h2><p>Thank you for ordering with Badabrand Technologies.</p><p><strong>Order:</strong> ' . e($orderNumber) . '<br><strong>Receipt:</strong> ' . e($receiptNumber) . '<br><strong>Invoice:</strong> ' . e($invoiceNumber) . '<br><strong>Total:</strong> ' . e(money_format_inr($total)) . '</p><p>You can review the full bill and order status from your customer dashboard.</p>';
        $text = "Order received\nOrder: {$orderNumber}\nReceipt: {$receiptNumber}\nInvoice: {$invoiceNumber}\nTotal: " . money_format_inr($total) . "\nOpen your dashboard for details.";
        log_email((int) $user['id'], (string) $user['email'], $subject, $html, $text, 'order', $orderId);

        $db->commit();

        do_action('onOrderCreated', [
            'order_id' => $orderId,
            'invoice_id' => $invoiceId,
            'order_number' => $orderNumber,
            'invoice_number' => $invoiceNumber,
            'user' => $user,
            'payload' => $payload,
            'totals' => [
                'subtotal' => $subtotal,
                'gst' => $gst,
                'total' => $total,
            ],
        ]);

        return ['order_id' => $orderId, 'invoice_id' => $invoiceId, 'order_number' => $orderNumber, 'invoice_number' => $invoiceNumber];
    }

    public function index(): void
    {
        View::render('public/home', [
            'pageTitle' => 'Badabrand Technologies | IT Services Company',
            'metaDescription' => 'Website development, apps, hosting, branding, and marketing with a full customer portal and admin CMS.',
            'settings' => Content::settings(),
            'services' => Content::services(),
            'plans' => Content::plans(),
            'products' => array_slice(Content::products(true), 0, 3),
            'testimonials' => Content::testimonials(),
            'slider' => Content::slider(),
            'stats' => Content::stats(),
            'user' => Auth::user(),
        ]);
    }

    public function about(): void
    {
        View::render('public/about', [
            'pageTitle' => 'About Us',
            'metaDescription' => 'Learn about Badabrand Technologies and how we help businesses grow online.',
            'settings' => Content::settings(),
            'teamMembers' => Content::teamMembers(),
            'stats' => Content::stats(),
            'user' => Auth::user(),
        ]);
    }

    public function services(): void
    {
        View::render('public/services', [
            'pageTitle' => 'Services',
            'metaDescription' => 'Explore website development, app development, hosting, design, and marketing services.',
            'settings' => Content::settings(),
            'services' => Content::services(),
            'plans' => Content::plans(),
            'user' => Auth::user(),
        ]);
    }

    public function pricing(): void
    {
        View::render('public/pricing', [
            'pageTitle' => 'Pricing',
            'metaDescription' => 'Review service packages and pricing plans.',
            'settings' => Content::settings(),
            'plans' => Content::plans(),
            'services' => Content::services(),
            'user' => Auth::user(),
        ]);
    }

    public function marketplace(): void
    {
        $query = trim((string) input('q', ''));
        $products = $this->filterProducts(Content::products(true), $query);
        View::render('public/marketplace', [
            'pageTitle' => 'Marketplace',
            'metaDescription' => 'Buy digital products, themes, plugins, software, and service packages from Badabrand Technologies.',
            'settings' => Content::settings(),
            'search' => $query,
            'products' => $products,
            'services' => Content::services(),
            'plans' => Content::plans(),
            'user' => Auth::user(),
        ]);
    }

    public function portfolio(): void
    {
        View::render('public/portfolio', [
            'pageTitle' => 'Portfolio',
            'metaDescription' => 'Explore featured Badabrand Technologies projects and delivery case studies.',
            'settings' => Content::settings(),
            'projects' => Content::portfolioProjects(),
            'user' => Auth::user(),
        ]);
    }

    public function blog(): void
    {
        View::render('public/blog', [
            'pageTitle' => 'Blog',
            'metaDescription' => 'Read published Badabrand Technologies insights, product notes, and growth articles.',
            'settings' => Content::settings(),
            'posts' => Content::blogs(true),
            'user' => Auth::user(),
        ]);
    }

    public function careers(): void
    {
        View::render('public/careers', [
            'pageTitle' => 'Careers',
            'metaDescription' => 'View open roles and hiring updates from Badabrand Technologies.',
            'settings' => Content::settings(),
            'jobs' => Content::careers(true),
            'user' => Auth::user(),
        ]);
    }

    public function faq(): void
    {
        View::render('public/faq', [
            'pageTitle' => 'FAQ',
            'metaDescription' => 'Get answers about Badabrand Technologies, billing, support, and delivery workflows.',
            'settings' => Content::settings(),
            'faqItems' => Content::faqs(),
            'user' => Auth::user(),
        ]);
    }

    public function privacy(): void
    {
        $page = Content::pageBySlug('privacy-policy');
        View::render('public/privacy', [
            'pageTitle' => $page['meta_title'] ?? 'Privacy Policy',
            'metaDescription' => $page['meta_description'] ?? 'Privacy policy for Badabrand Technologies.',
            'settings' => Content::settings(),
            'page' => $page,
            'user' => Auth::user(),
        ]);
    }

    public function terms(): void
    {
        $page = Content::pageBySlug('terms');
        View::render('public/terms', [
            'pageTitle' => $page['meta_title'] ?? 'Terms of Service',
            'metaDescription' => $page['meta_description'] ?? 'Terms and service conditions for Badabrand Technologies.',
            'settings' => Content::settings(),
            'page' => $page,
            'user' => Auth::user(),
        ]);
    }

    public function contact(): void
    {
        View::render('public/contact', [
            'pageTitle' => 'Contact Us',
            'metaDescription' => 'Get in touch with Badabrand Technologies.',
            'settings' => Content::settings(),
            'user' => Auth::user(),
        ]);
    }

    public function submitContact(): void
    {
        $stmt = Database::connection()->prepare('
            INSERT INTO contacts (name, email, phone, company, service_interest, message, created_at)
            VALUES (:name, :email, :phone, :company, :service_interest, :message, NOW())
        ');
        $stmt->execute([
            'name' => trim((string) input('name', '')),
            'email' => trim((string) input('email', '')),
            'phone' => trim((string) input('phone', '')),
            'company' => trim((string) input('company', '')),
            'service_interest' => trim((string) input('service_interest', '')),
            'message' => trim((string) input('message', '')),
        ]);

        flash('success', 'Inquiry submitted successfully.');
        redirect('/contact');
    }

    public function placeOrder(): void
    {
        $user = Auth::user();
        if (! $user) {
            flash('error', 'Please login as a customer to place an order.');
            redirect('/login');
        }

        if (($user['role'] ?? '') !== 'customer') {
            flash('error', 'Only customer accounts can place orders.');
            redirect('/');
        }

        $orderType = (string) input('order_type', 'service');
        $quantity = max(1, (int) input('quantity', 1));
        $notes = trim((string) input('notes', ''));
        $payload = [
            'service_id' => null,
            'product_id' => null,
            'vendor_id' => null,
            'order_type' => $orderType,
            'item_name' => '',
            'pricing_plan_name' => null,
            'notes' => $notes,
            'description' => trim((string) input('description', '')),
            'quantity' => $quantity,
            'unit_price' => 0.0,
            'commission_percent' => null,
        ];

        if ($orderType === 'product') {
            $product = Content::product((int) input('product_id', 0));
            if (! $product || ($product['status'] ?? 'inactive') !== 'active') {
                flash('error', 'Product not found.');
                redirect('/marketplace');
            }
            $payload['product_id'] = (int) $product['id'];
            $payload['vendor_id'] = ! empty($product['vendor_id']) ? (int) $product['vendor_id'] : null;
            $payload['item_name'] = (string) $product['name'];
            $payload['description'] = (string) ($product['short_description'] ?: $product['description']);
            $payload['unit_price'] = (float) $product['price'];
            $payload['commission_percent'] = ! empty($product['vendor_id'])
                ? (float) (($product['commission_percent'] ?? null) ?: app_setting('vendor_default_commission', '15'))
                : null;
        } elseif ($orderType === 'plan') {
            $stmt = Database::connection()->prepare('SELECT * FROM pricing_plans WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) input('plan_id', 0)]);
            $plan = $stmt->fetch();
            if (! $plan) {
                flash('error', 'Pricing plan not found.');
                redirect('/pricing');
            }
            $payload['item_name'] = (string) $plan['name'];
            $payload['pricing_plan_name'] = (string) $plan['name'];
            $payload['description'] = (string) $plan['description'];
            $payload['unit_price'] = money_to_float($plan['price']);
        } else {
            $stmt = Database::connection()->prepare('SELECT * FROM services WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) input('service_id', 0)]);
            $service = $stmt->fetch();
            if (! $service) {
                flash('error', 'Service not found.');
                redirect('/services');
            }
            $payload['service_id'] = (int) $service['id'];
            $payload['item_name'] = (string) $service['name'];
            $payload['description'] = (string) $service['short_description'];
            $payload['unit_price'] = max(money_to_float($service['price_label']), money_to_float(input('budget_amount', 0)));
        }

        if ($payload['unit_price'] <= 0) {
            flash('error', 'This item needs a valid amount before it can be ordered.');
            redirect('/contact');
        }

        $result = $this->createOrderInvoice($user, $payload);
        flash('success', 'Your order, receipt, and invoice were created successfully. Complete payment below.');
        redirect('/client/payments?invoice_id=' . $result['invoice_id']);
    }

    public function vendorStore(): void
    {
        $vendor = Content::publicVendorBySlug((string) input('slug', ''));
        if (! $vendor) {
            View::render('public/404', [
                'pageTitle' => 'Vendor Not Found',
                'metaDescription' => 'The vendor store could not be found.',
                'settings' => Content::settings(),
                'user' => Auth::user(),
            ]);
            return;
        }

        View::render('public/vendor-store', [
            'pageTitle' => ($vendor['display_name'] ?? $vendor['store_name']) . ' Store',
            'metaDescription' => 'Explore products from ' . ($vendor['display_name'] ?? $vendor['store_name']) . '.',
            'settings' => Content::settings(),
            'user' => Auth::user(),
            'vendor' => $vendor,
            'summary' => Content::vendorSummary((int) $vendor['id']),
            'products' => Content::vendorProducts((int) $vendor['id'], false),
            'reviews' => Content::vendorReviews((int) $vendor['id']),
        ]);
    }
}
