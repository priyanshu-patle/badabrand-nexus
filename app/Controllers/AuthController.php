<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class AuthController
{
    public function login(): void
    {
        View::render('auth/login', [
            'pageTitle' => 'Login',
            'metaDescription' => 'Login to your Badabrand account.',
        ], 'layouts/auth');
    }

    public function authenticate(): void
    {
        $email = trim((string) input('email', ''));
        $password = (string) input('password', '');

        if (! Auth::attempt($email, $password)) {
            set_old_input($_POST);
            flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        clear_old_input();
        $user = Auth::user();

        if ($user['role'] === 'admin') {
            redirect('/admin');
        }
        if ($user['role'] === 'developer') {
            redirect('/developer');
        }
        if ($user['role'] === 'vendor') {
            redirect('/vendor/dashboard');
        }
        redirect('/client');
    }

    public function register(): void
    {
        View::render('auth/register', [
            'pageTitle' => 'Register',
            'metaDescription' => 'Create a new client account.',
            'referralCode' => trim((string) input('ref', old('referral_code', ''))),
        ], 'layouts/auth');
    }

    public function store(): void
    {
        $firstName = trim((string) input('first_name', ''));
        $lastName = trim((string) input('last_name', ''));
        $email = trim((string) input('email', ''));
        $phone = trim((string) input('phone', ''));
        $password = (string) input('password', '');
        $referralCode = strtoupper(trim((string) input('referral_code', '')));

        if ($firstName === '' || $email === '' || $password === '') {
            set_old_input($_POST);
            flash('error', 'Name, email, and password are required.');
            redirect('/register');
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            set_old_input($_POST);
            flash('error', 'That email is already registered.');
            redirect('/register');
        }

        $clientId = 'BBT-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        $insert = $db->prepare("
            INSERT INTO users (role, client_id, first_name, last_name, email, phone, password, status, created_at, updated_at)
            VALUES ('customer', :client_id, :first_name, :last_name, :email, :phone, :password, 'active', NOW(), NOW())
        ");
        $insert->execute([
            'client_id' => $clientId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) $db->lastInsertId();

        do_action('onUserRegister', [
            'user_id' => $userId,
            'user' => [
                'id' => $userId,
                'role' => 'customer',
                'client_id' => $clientId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'status' => 'active',
            ],
            'referral_code' => $referralCode,
        ]);

        clear_old_input();
        flash('success', 'Registration successful. Please login.');
        redirect('/login');
    }

    public function forgotPassword(): void
    {
        View::render('auth/forgot-password', [
            'pageTitle' => 'Forgot Password',
            'metaDescription' => 'Reset your password.',
        ], 'layouts/auth');
    }

    public function vendorApply(): void
    {
        View::render('auth/vendor-apply', [
            'pageTitle' => 'Vendor Application',
            'metaDescription' => 'Apply to become a Badabrand marketplace vendor.',
        ], 'layouts/auth');
    }

    public function storeVendorApplication(): void
    {
        $firstName = trim((string) input('first_name', ''));
        $lastName = trim((string) input('last_name', ''));
        $email = trim((string) input('email', ''));
        $phone = trim((string) input('phone', ''));
        $password = (string) input('password', '');
        $storeName = trim((string) input('store_name', ''));
        $displayName = trim((string) input('display_name', $storeName));
        $shortBio = trim((string) input('short_bio', ''));
        $taxGst = trim((string) input('tax_gst', ''));
        $website = trim((string) input('website', ''));

        if ($firstName === '' || $email === '' || $password === '' || $storeName === '') {
            set_old_input($_POST);
            flash('error', 'Owner name, email, password, and store name are required.');
            redirect('/vendor/apply');
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            set_old_input($_POST);
            flash('error', 'That email is already registered.');
            redirect('/vendor/apply');
        }

        $storeSlug = slugify((string) input('store_slug', $storeName), 'vendor');
        $existingStore = $db->prepare('SELECT id FROM vendors WHERE slug = :slug LIMIT 1');
        $existingStore->execute(['slug' => $storeSlug]);
        if ($existingStore->fetch()) {
            $storeSlug .= '-' . random_int(100, 999);
        }

        $logoPath = upload_file('logo_file', 'assets/uploads/vendors');
        $bannerPath = upload_file('banner_file', 'assets/uploads/vendors');
        $identityPath = upload_file('identity_file', 'assets/uploads/vendors');
        $gstPath = upload_file('gst_file', 'assets/uploads/vendors');

        $db->beginTransaction();
        try {
            $clientId = 'VND-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $db->prepare("
                INSERT INTO users (role, client_id, first_name, last_name, email, phone, password, status, created_at, updated_at)
                VALUES ('vendor', :client_id, :first_name, :last_name, :email, :phone, :password, 'active', NOW(), NOW())
            ")->execute([
                'client_id' => $clientId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $db->lastInsertId();

            $db->prepare('
                INSERT INTO vendors (user_id, store_name, display_name, slug, email, phone, tax_gst, status, joined_at, created_at, updated_at)
                VALUES (:user_id, :store_name, :display_name, :slug, :email, :phone, :tax_gst, :status, NOW(), NOW(), NOW())
            ')->execute([
                'user_id' => $userId,
                'store_name' => $storeName,
                'display_name' => $displayName !== '' ? $displayName : $storeName,
                'slug' => $storeSlug,
                'email' => $email,
                'phone' => $phone,
                'tax_gst' => $taxGst,
                'status' => app_setting('vendor_auto_approve', '0') === '1' ? 'approved' : 'pending',
            ]);
            $vendorId = (int) $db->lastInsertId();

            $db->prepare('
                INSERT INTO vendor_profiles (vendor_id, business_name, legal_name, short_bio, address_line1, city, state, country, postal_code, website, support_email, support_phone, logo_path, banner_path, created_at, updated_at)
                VALUES (:vendor_id, :business_name, :legal_name, :short_bio, :address_line1, :city, :state, :country, :postal_code, :website, :support_email, :support_phone, :logo_path, :banner_path, NOW(), NOW())
            ')->execute([
                'vendor_id' => $vendorId,
                'business_name' => $storeName,
                'legal_name' => trim((string) input('legal_name', '')),
                'short_bio' => $shortBio,
                'address_line1' => trim((string) input('address_line1', '')),
                'city' => trim((string) input('city', '')),
                'state' => trim((string) input('state', '')),
                'country' => trim((string) input('country', 'India')),
                'postal_code' => trim((string) input('postal_code', '')),
                'website' => $website,
                'support_email' => trim((string) input('support_email', $email)),
                'support_phone' => trim((string) input('support_phone', $phone)),
                'logo_path' => $logoPath,
                'banner_path' => $bannerPath,
            ]);

            $db->prepare('
                INSERT INTO vendor_payout_accounts (vendor_id, payout_method, account_name, account_number, ifsc_swift, upi_id, paypal_email, notes, created_at, updated_at)
                VALUES (:vendor_id, :payout_method, :account_name, :account_number, :ifsc_swift, :upi_id, :paypal_email, :notes, NOW(), NOW())
            ')->execute([
                'vendor_id' => $vendorId,
                'payout_method' => trim((string) input('payout_method', 'bank_transfer')),
                'account_name' => trim((string) input('account_name', '')),
                'account_number' => trim((string) input('account_number', '')),
                'ifsc_swift' => trim((string) input('ifsc_swift', '')),
                'upi_id' => trim((string) input('upi_id', '')),
                'paypal_email' => trim((string) input('paypal_email', '')),
                'notes' => trim((string) input('payout_notes', '')),
            ]);

            $documentInsert = $db->prepare('
                INSERT INTO vendor_documents (vendor_id, document_type, file_path, status, created_at, updated_at)
                VALUES (:vendor_id, :document_type, :file_path, :status, NOW(), NOW())
            ');
            foreach (['identity' => $identityPath, 'tax_gst' => $gstPath] as $type => $path) {
                if (! $path) {
                    continue;
                }
                $documentInsert->execute([
                    'vendor_id' => $vendorId,
                    'document_type' => $type,
                    'file_path' => $path,
                    'status' => 'pending',
                ]);
            }

            $db->prepare('
                INSERT INTO vendor_activity_logs (vendor_id, actor_user_id, action, description, context, created_at)
                VALUES (:vendor_id, :actor_user_id, :action, :description, :context, NOW())
            ')->execute([
                'vendor_id' => $vendorId,
                'actor_user_id' => $userId,
                'action' => 'vendor.applied',
                'description' => 'Vendor application submitted by store ' . $storeName,
                'context' => json_encode(['store_name' => $storeName, 'tax_gst' => $taxGst], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $db->commit();
        } catch (\Throwable $exception) {
            $db->rollBack();
            set_old_input($_POST);
            flash('error', 'Vendor application could not be submitted right now.');
            redirect('/vendor/apply');
        }

        clear_old_input();
        flash('success', 'Vendor application submitted successfully. Admin approval is required before publishing products.');
        redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
