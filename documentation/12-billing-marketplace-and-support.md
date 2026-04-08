# Billing, Marketplace, Projects, And Support Guide

## Billing and payments

The platform supports a review-based billing flow.

Billing workflow:

1. order is created
2. invoice is generated
3. customer uploads payment proof
4. admin reviews payment
5. invoice status updates after approval or rejection

Admin should review:

- invoice totals
- payment gateway or manual details
- transaction reference
- proof upload
- payout impact for vendor-linked orders

## Marketplace

Marketplace supports:

- admin-owned products
- vendor-owned products
- product review counts
- order-linked revenue
- approval status for vendor products

Product management fields include:

- name
- category / product type
- price
- version
- short description
- full description
- feature list
- file / download link
- status

## Vendor payouts

Vendor payouts are tracked manually for now.

Key tables:

- vendors
- vendor_profiles
- vendor_commissions
- vendor_payout_accounts
- vendor_payouts

## Projects and contracts

Projects use order records plus progress tracking.

The admin can:

- update order status
- update progress
- create project updates
- review proposals
- review contracts

## Support workflow

Support tickets are customer-linked and admin-reviewed.

Recommended operating pattern:

- review ticket queue daily
- prioritize high/critical tickets first
- keep pending replies low
- use dashboard activity and support widgets for triage
