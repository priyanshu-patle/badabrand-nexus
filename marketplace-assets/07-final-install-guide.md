# Final Install Guide

Use this guide for the buyer handoff package.

## Requirements

- PHP 8 or newer
- MySQL or MariaDB
- Apache or shared hosting with `.htaccess` support
- Writable `assets/uploads` or equivalent upload directory

## Installation Steps

1. Upload the project files to the server.
2. Import `database/sql/badabrand_platform.sql`.
3. Update `.env` with database credentials and app URL.
4. Point the document root to the project public entry.
5. Login as admin and update branding, system settings, and content.

## Post Install

- Change default admin credentials
- Update SMTP and support details
- Replace demo products, pages, and team members
- Review legal pages before production launch
