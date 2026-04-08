# Installation, Configuration, And Deployment Guide

## Requirements

- PHP 8.1 or newer recommended
- MySQL / MariaDB
- Apache with rewrite support
- writable `storage/` and upload paths

## Local XAMPP installation

1. copy the project into `htdocs`
2. create a database
3. import `database/sql/badabrand_platform.sql`
4. copy `.env.example` to `.env`
5. configure:
   - app URL
   - DB host
   - DB name
   - DB user
   - DB password
6. open the app in browser

## Shared hosting / cPanel deployment

Preferred:

- point the domain/subdomain document root to `public/`

Fallback root-install:

- keep the root `.htaccess`
- it rewrites `/assets/*` to `public/assets/*`
- this allows the package to work even when the host uses the project root as the web root

## SMTP configuration

Set these from admin settings:

- SMTP host
- port
- username
- password
- from name
- from email

## Theme configuration

Admin can set:

- admin theme default
- public theme default

Themes currently supported:

- Dark Pro
- Light Classic
- Midnight Glass

## Asset path note

The clean package uses `public/assets` as the source asset directory.

For root-install deployments, the root `.htaccess` safely rewrites `/assets/...` requests to `public/assets/...`.

## Protected admin footer note

The admin footer branding is protected in code and is not editable from normal admin settings.
