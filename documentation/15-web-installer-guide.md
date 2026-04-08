# Web Installer Guide

Badabrand Technologies includes a first-run web installer for XAMPP, shared hosting, and cPanel deployments.

## How it works

- Upload or extract the buyer package
- Create an empty MySQL database
- Open the domain or localhost URL
- The installer launches automatically if the platform is not yet installed
- Complete the guided setup steps
- After success, the installer writes configuration, imports the database, creates the admin account, and locks itself

## Installer steps

1. Welcome
2. Requirement check
3. Site setup
4. Database setup
5. Admin account setup
6. Business and branding setup
7. Modules and demo options
8. Finalize installation
9. Success screen

## What the installer writes

- `.env` configuration file
- database schema and seeded platform data
- admin account credentials
- default theme, support, and branding settings
- install lock at `storage/install/installed.json`

## Reinstall protection

- once installation completes, the installer is locked
- visiting `/install` after completion redirects to `/login`
- to reinstall manually, remove `.env` and `storage/install/installed.json`

## Hosting notes

- preferred document root: `public/`
- fallback root install is supported by the project `.htaccess`
- browser-facing assets are standardized under `public/assets`

## Demo content option

- if enabled, sample business content and operational demo records remain available after install
- if disabled, demo users and live operational sample records are removed after import
