# Installation Guide

## Quick install

1. Upload or copy the project files.
2. Create a MySQL database and database user.
3. Preferred: point the web root to `public/`.
4. Fallback root-install: keep the root `.htaccess`.
5. Open the domain or localhost URL in your browser.
6. The Badabrand web installer loads automatically on first run.
7. Complete the installer steps for requirements, environment, database, admin account, branding, and modules.
8. After success, log in to admin and review SMTP, SEO, marketplace, and theme settings.

## Admin login

- Email: `admin@badabrand.in`
- Password: `Admin@123`

## Important notes

- Admin footer branding is protected in code.
- Public assets live in `public/assets`.
- Root installs are supported by rewrite rules that map `/assets/...` to `public/assets/...`.
- The installer writes `.env`, imports the packaged SQL, creates the admin account, and then locks itself.

## Buyer installer steps

1. Welcome
2. Requirement check
3. Site setup
4. Database setup
5. Admin account setup
6. Business and branding setup
7. Modules and demo options
8. Final installation
9. Success screen with login link

## Troubleshooting

- If the installer says `.env` is not writable, adjust file/folder permissions in your host control panel.
- If the database step fails, recheck host, port, database name, username, and password.
- If you want to rerun the installer after completion, remove `.env` and the install lock inside `storage/install/installed.json`.
