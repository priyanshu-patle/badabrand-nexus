# Vendor System

## Overview

Badabrand Technologies now includes a marketplace vendor program for approved third-party sellers. Vendors can apply, complete onboarding, manage store details, publish products, review orders, and request manual payouts without accessing admin-only business controls.

## Vendor Role

- Role key: `vendor`
- Vendor users authenticate through the existing login system
- Vendor access routes:
  - `/vendor/dashboard`
  - `/vendor/products`
  - `/vendor/products/create`
  - `/vendor/products/edit/{id}`
  - `/vendor/orders`
  - `/vendor/payouts`
  - `/vendor/profile`
  - `/vendor/settings`

## Onboarding Flow

1. Applicant opens `/vendor/apply`
2. Completes store, business, payout, and document fields
3. System creates:
   - `users` record with role `vendor`
   - `vendors`
   - `vendor_profiles`
   - `vendor_payout_accounts`
   - `vendor_documents`
4. Admin reviews vendor application
5. Admin changes status to `approved`, `rejected`, `suspended`, or `inactive`

## Vendor Status Rules

- `pending`: onboarding submitted, waiting for admin review
- `approved`: vendor may publish and manage products
- `rejected`: vendor profile kept for audit but publishing blocked
- `suspended`: existing records remain visible, but vendor operations should be restricted by policy
- `inactive`: vendor is disabled without deleting historical records

## Product Ownership

- `products.vendor_id` links marketplace products to a vendor
- Admin-owned products still work with `vendor_id = NULL`
- Vendor-created products can require review before going live
- `approval_status` controls admin review outcome

## Commission Logic

- Global fallback setting: `vendor_default_commission`
- Optional vendor override: `vendors.commission_percent`
- Order stores:
  - `commission_percent`
  - `platform_fee_amount`
  - `vendor_net_amount`
  - `payout_status`
- Commission ledger table:
  - `vendor_commissions`

## Payout Process

1. Customer orders a vendor product
2. Order and invoice are created
3. Commission ledger entry is created
4. Admin approves payment
5. Commission becomes `available`
6. Vendor requests payout
7. Admin marks payout `processing` or `paid`

## Admin Management

Marketplace admin now includes:

- Vendors list
- Vendor details page
- Vendor profile and payout controls
- Vendor document review links
- Vendor payout desk
- Marketplace review moderation

## Public Store Route

- Public vendor store URL:
  - `/marketplace/vendor/{slug}`

This page shows:

- store identity
- product listing
- rating summary
- trust indicators

## Key Tables

- `vendors`
- `vendor_profiles`
- `vendor_payout_accounts`
- `vendor_commissions`
- `vendor_payouts`
- `vendor_documents`
- `vendor_activity_logs`

## Notes

- Runtime schema safety is enforced through `SystemHooks::ensureVendorSchema()`
- Older installations upgrade automatically on boot where possible
- Shared-hosting deployments should still import the updated SQL build for clean installs
