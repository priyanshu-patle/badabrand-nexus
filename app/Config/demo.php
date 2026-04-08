<?php

return [
    'slider' => [
        [
            'eyebrow' => 'Managed IT Services',
            'title' => 'Build Your Digital Future With Badabrand',
            'description' => 'End-to-end web, cloud, automation, and growth systems for ambitious businesses.',
            'primary_cta' => ['label' => 'Get Started', 'url' => '#contact'],
            'secondary_cta' => ['label' => 'View Services', 'url' => '#services'],
        ],
        [
            'eyebrow' => 'Client-first delivery',
            'title' => 'Track Projects, Contracts, Payments, and Support In One Place',
            'description' => 'A premium client experience powered by a modern portal and complete admin control.',
            'primary_cta' => ['label' => 'Open Portal', 'url' => '/portal'],
            'secondary_cta' => ['label' => 'Admin Preview', 'url' => '/admin'],
        ],
    ],
    'services' => [
        ['name' => 'Web Development', 'icon' => 'bi-window', 'category' => 'development', 'price' => 'Starts at Rs 25,000', 'description' => 'Corporate websites, e-commerce stores, and custom Laravel platforms.'],
        ['name' => 'App Development', 'icon' => 'bi-phone', 'category' => 'development', 'price' => 'Starts at Rs 60,000', 'description' => 'Android, iOS, and PWA products with scalable APIs.'],
        ['name' => 'Cloud Hosting', 'icon' => 'bi-cloud-arrow-up', 'category' => 'hosting', 'price' => 'Starts at Rs 4,999/mo', 'description' => 'Fast hosting, SSL, backup automation, and uptime monitoring.'],
        ['name' => 'SEO & Growth', 'icon' => 'bi-graph-up-arrow', 'category' => 'marketing', 'price' => 'Starts at Rs 15,000/mo', 'description' => 'Technical SEO, content systems, local visibility, and reporting.'],
        ['name' => 'UI/UX Design', 'icon' => 'bi-palette2', 'category' => 'design', 'price' => 'Starts at Rs 18,000', 'description' => 'Interface systems, design QA, prototypes, and dashboards.'],
        ['name' => 'Team Augmentation', 'icon' => 'bi-people', 'category' => 'consulting', 'price' => 'Custom', 'description' => 'Dedicated developers, sprint support, and product acceleration.'],
    ],
    'portfolio' => [
        ['title' => 'Fintech Dashboard', 'category' => 'web', 'tag' => 'SaaS Platform', 'description' => 'Admin analytics, KYC workflows, and subscription billing.'],
        ['title' => 'Retail Mobile App', 'category' => 'app', 'tag' => 'Commerce', 'description' => 'Realtime order flow, offers engine, and wallet integrations.'],
        ['title' => 'Managed Hosting Portal', 'category' => 'cloud', 'tag' => 'Infrastructure', 'description' => 'Client area for hosting, renewals, tickets, and invoices.'],
        ['title' => 'SEO Reporting Suite', 'category' => 'marketing', 'tag' => 'Growth', 'description' => 'Automated ranking data, dashboards, and monthly report exports.'],
    ],
    'plans' => [
        ['name' => 'Starter', 'price' => 'Rs 12,999', 'period' => '/project', 'features' => ['5-page corporate site', 'Contact forms', 'Basic SEO setup', '30 days support']],
        ['name' => 'Growth', 'price' => 'Rs 35,999', 'period' => '/project', 'featured' => true, 'features' => ['Custom CMS', 'Blog + portfolio', 'Lead capture funnels', '90 days support']],
        ['name' => 'Scale', 'price' => 'Rs 79,999', 'period' => '/project', 'features' => ['Client portal', 'Invoice + ticketing', 'Analytics dashboard', 'Priority support']],
    ],
    'testimonials' => [
        ['name' => 'Aarav Mehta', 'role' => 'Founder, NovaEdge', 'quote' => 'Badabrand gave us a polished enterprise website and a portal our clients actually enjoy using.'],
        ['name' => 'Sneha Roy', 'role' => 'COO, PixelCraft', 'quote' => 'Project visibility, billing, and support improved immediately after launch.'],
        ['name' => 'Daniel Joseph', 'role' => 'Director, Alpine Tech', 'quote' => 'The team balanced premium design with solid backend systems and great communication.'],
    ],
    'blog' => [
        ['title' => 'How IT Service Companies Can Improve Client Retention', 'slug' => 'improve-client-retention', 'category' => 'Strategy', 'date' => '2026-03-10'],
        ['title' => 'Why Shared Hosting Projects Still Need Modular Architecture', 'slug' => 'shared-hosting-modular-architecture', 'category' => 'Engineering', 'date' => '2026-03-18'],
        ['title' => 'SEO Foundations Every Service Business Needs', 'slug' => 'seo-foundations-service-business', 'category' => 'Marketing', 'date' => '2026-03-24'],
    ],
    'jobs' => [
        ['title' => 'Laravel Developer', 'location' => 'Remote', 'type' => 'Full-time'],
        ['title' => 'UI/UX Designer', 'location' => 'Kolkata / Hybrid', 'type' => 'Contract'],
        ['title' => 'SEO Specialist', 'location' => 'Remote', 'type' => 'Part-time'],
    ],
    'faq' => [
        ['question' => 'Can the admin team manage all website content without code changes?', 'answer' => 'Yes. Services, pages, blog posts, portfolio items, homepage sliders, footer content, popups, and SEO metadata are all designed to be managed from the admin panel.'],
        ['question' => 'Does the customer portal support manual payment verification?', 'answer' => 'Yes. Clients can upload transaction screenshots, choose QR or bank transfer, and admins can approve or reject submissions with notes.'],
        ['question' => 'Is it compatible with cPanel hosting?', 'answer' => 'Yes. The structure is designed for shared hosting deployment with a public document root and a MySQL database import.'],
    ],
    'portal' => [
        'stats' => [
            ['label' => 'Active Services', 'value' => '05'],
            ['label' => 'Pending Orders', 'value' => '02'],
            ['label' => 'Outstanding Due', 'value' => 'Rs 7,500'],
            ['label' => 'Open Tickets', 'value' => '01'],
        ],
        'services' => [
            ['name' => 'Web Hosting', 'status' => 'Active', 'renewal' => '2026-08-15'],
            ['name' => 'Mobile App Development', 'status' => 'In Progress', 'renewal' => 'Milestone Billing'],
            ['name' => 'SEO Optimization', 'status' => 'Active', 'renewal' => '2026-09-20'],
        ],
        'activities' => [
            'New order: Enterprise Website Revamp',
            'Invoice INV-1256 awaiting payment review',
            'Support ticket updated for cloud migration',
        ],
        'projects' => [
            ['name' => 'Enterprise Website', 'progress' => 78, 'stage' => 'QA & content loading'],
            ['name' => 'Client Portal Upgrade', 'progress' => 42, 'stage' => 'API integration'],
        ],
        'invoices' => [
            ['number' => 'INV-1256', 'amount' => 'Rs 3,500', 'status' => 'Pending'],
            ['number' => 'INV-1234', 'amount' => 'Rs 2,000', 'status' => 'Paid'],
            ['number' => 'INV-1239', 'amount' => 'Rs 1,200', 'status' => 'Paid'],
        ],
        'tickets' => [
            ['id' => 'TCK-102', 'subject' => 'Email configuration issue', 'status' => 'Open'],
            ['id' => 'TCK-099', 'subject' => 'Renewal confirmation needed', 'status' => 'Answered'],
        ],
    ],
    'admin' => [
        'stats' => [
            ['label' => 'Total Users', 'value' => '1,284'],
            ['label' => 'Monthly Revenue', 'value' => 'Rs 6.4L'],
            ['label' => 'Active Orders', 'value' => '138'],
            ['label' => 'Open Tickets', 'value' => '24'],
        ],
        'cards' => [
            'Users & roles',
            'Services & pricing',
            'Portfolio & blog CMS',
            'Invoices, GST billing, and approvals',
            'Marketing tools and coupon engine',
            'Theme, logo, popup, and footer controls',
        ],
    ],
];
