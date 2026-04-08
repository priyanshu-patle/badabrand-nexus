# Troubleshooting And Maintenance Guide

## Common troubleshooting areas

### Assets not updating

- hard refresh browser with `Ctrl + F5`
- confirm `public/assets/css/app.css` and `public/assets/js/app.js` are deployed
- for root installs, confirm root `.htaccess` is present

### Login or redirect issues

- check `.env` app URL
- confirm sessions are writable on the server
- confirm Apache rewrite rules are enabled

### Missing database fields on older installs

The platform contains runtime schema hardening for some systems such as:

- notifications
- referrals
- vendor system

Still, fresh installs should always use the latest SQL file.

### Broken theme appearance

- verify current theme values in settings
- clear browser cache
- confirm the loaded CSS file is the latest deployed build

## Maintenance checklist

- review pending payments daily
- review support queue daily
- monitor vendor approvals and payouts
- update pricing, pages, and product records as needed
- export billing/project data regularly if required

## Update checklist

Before replacing files on a live server:

1. back up database
2. back up uploads
3. replace project files
4. review SQL differences if schema changed
5. test admin login
6. test customer login
7. test billing and marketplace

## Package cleanup note

Temporary local files, debug notes, and unrelated release artifacts should not be included in the final buyer package.
