# Codebase And Folder Structure

This document explains the major folders used in the project.

## Main Application Folders

- `app/Controllers`
  - request handlers for public, portal, auth, and admin features
- `app/Core`
  - shared runtime classes such as auth, database, routing, and content loaders
- `app/Helpers`
  - utility functions used across the platform
- `app/Views`
  - all public, portal, auth, admin, and partial templates
- `config`
  - configuration files
- `routes`
  - application routes
- `public`
  - source public assets and upload target roots
- `documentation`
  - admin-only project documentation source files

## Important View Groups

- `app/Views/public`
  - website pages
- `app/Views/auth`
  - login and registration pages
- `app/Views/portal`
  - customer and developer views
- `app/Views/admin`
  - admin management pages
- `app/Views/partials`
  - shared dashboard and public partials
- `app/Views/layouts`
  - shared full-page wrappers

## Key Controller Responsibilities

- `HomeController`
  - public website pages
  - public ordering
  - homepage rendering
- `AuthController`
  - login, register, forgot password
- `PortalController`
  - customer and developer portal features
- `AdminController`
  - admin dashboard
  - CMS and business controls
  - services, plans, marketplace, users, payments, marketing, projects
  - documentation page

## Important Data Tables

- `users`
- `settings`
- `services`
- `pricing_plans`
- `products`
- `orders`
- `invoices`
- `invoice_items`
- `payments`
- `tickets`
- `ticket_messages`
- `notifications`
- `sliders`
- `stats`
- `testimonials`
- `proposals`
- `contracts`
- `project_updates`
- `coupons`
- `referrals`
- `email_logs`

## Content Sources

- Global branding comes from `settings`
- Homepage counters come from `stats`
- Homepage highlight cards come from `sliders`
- Testimonials come from `testimonials`
- Services come from `services`
- Pricing comes from `pricing_plans`
- Marketplace items come from `products`

## Editing Workflow Summary

1. Admin updates content or business data in the dashboard
2. Controller saves the change into the database
3. Public or portal pages read the updated content through `Content` queries
4. The frontend immediately reflects the new values
