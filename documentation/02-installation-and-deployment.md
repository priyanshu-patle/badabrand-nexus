# Installation And Deployment

This document covers local XAMPP setup and the current project structure.

## Requirements

- PHP 8.x
- MySQL / MariaDB
- Apache with mod_rewrite
- XAMPP or compatible local stack

## Local XAMPP Path

The current live local site is deployed at:

`C:\xampp\htdocs\badabrand-technologies`

## Workspace Path

The editable source workspace is:

`D:\BadaBrand`

## Local Database

- Database name: `badabrand_technologies`
- Host: `127.0.0.1`
- User: `root`
- Password: empty by default in local XAMPP

## Installation Steps

1. Start Apache and MySQL from XAMPP.
2. Create or verify the database `badabrand_technologies`.
3. Import the SQL file from:
   `database/sql/badabrand_platform.sql`
4. Configure `.env` or environment values for:
   - app URL
   - database host
   - database name
   - database user
   - database password
5. Copy the source application into XAMPP htdocs.
6. Confirm `assets`, `app`, `routes`, and `storage` are present in the live directory.
7. Open:
   `http://localhost/badabrand-technologies/`

## Main Runtime Routes

- `/` public homepage
- `/marketplace` public product marketplace
- `/login` login page
- `/client` customer dashboard
- `/developer` developer dashboard
- `/admin` admin dashboard

## Production Notes

- Replace demo credentials
- Enforce stronger passwords
- Configure mail transport
- Validate file uploads more strictly
- Add HTTPS
- Add backup and logging strategy
- Review write permissions on uploads and storage
