# WhatStay - WhatsApp AI Assistant for Hotel Booking Automation (SaaS)

> ⚠️ **View-Only Repository** — This code is publicly shared for portfolio and code review purposes only.
> Unauthorized use, copying, or distribution is strictly prohibited. See [LICENSE](./LICENSE) for details.

---

## 📌 About This Project

**WhatStay** (WhatsBook) is a multi-tenant, subscription-based (SaaS) WhatsApp AI assistant and hotel booking automation platform built with **Laravel** (PHP), developed under **KreativDev**. This repository is publicly shared as a portfolio showcase so that recruiters, technical leads, and hiring managers can review the code structure, architecture, and engineering practices.

---

## 🛠️ Tech Stack

| Layer        | Technology                                                                          |
|--------------|-------------------------------------------------------------------------------------|
| Backend      | PHP / Laravel 11+                                                                   |
| Frontend     | Blade Templates, JavaScript, Laravel Mix                                            |
| Database     | MySQL                                                                               |
| Auth         | Laravel Auth / Multi-Tenancy Guards, Sanctum                                        |
| Integrations | WhatsApp Business API, AI Assistant & Knowledge Vault, Payment Gateways             |
| Styling      | Bootstrap / CSS                                                                     |

---

## 🗂️ Project Structure Highlights

```
whatsbook/
├── app/
│   ├── Http/Controllers/   # Admin, Tenant (User), Auth, Front & Webhook controllers
│   ├── Services/           # AI, WhatsApp, Booking, Room & Payment services
│   ├── Models/             # Eloquent models
│   └── Traits/             # Shared application traits
├── resources/
│   ├── views/              # Blade templates (Admin, Tenant, Front)
│   └── js/ / css/          # Frontend assets
├── routes/
│   ├── web.php / admin.php # Admin & Web routes
│   ├── tenant.php / front.php # Tenant & Frontend routes
│   └── api.php             # API routes
├── database/
│   ├── migrations/         # DB migrations
│   └── seeders/            # DB seeders
└── config/                 # App configurations
```

---

## ✨ Authentic Key Features

### 🤖 WhatsApp AI Assistant & Automation Engine
- **24/7 AI-Powered Guest Assistant:** Automated guest inquiries and reservation assistance via WhatsApp using AI context models.
- **AI Knowledge Vault:** Hotel owners can train their custom AI knowledge base with FAQs, policies, room details, and custom prompts.
- **WhatsApp Workflow & Template Messaging:** Automated template messages, custom auto-response rules, and interactive bot chat triggers.
- **AI Credit & Usage Tracking:** System to manage tenant AI credits, token consumption tracking, and usage limits.

### 🏨 Multi-Tenant Hotel Booking & SaaS Marketplace
- **Subscription Package Management:** Super-Admin creates flexible subscription plans (Free, Monthly, Yearly, Lifetime) for hotels with feature and AI token limits.
- **Tenant & Hotel Admin Portal:** Dedicated dashboard for hotel managers to manage room listings, categories, availability, and reservations.
- **Advanced Room & Availability Controls:** Real-time room availability management, seasonal pricing, check-in/out schedules, and custom booking adjustments.
- **Multi-Staff Delegation:** Assign role-based access for front desk agents, operational managers, and hotel staff.

### 💰 Payments, Billing & Operational Tools
- **Built-in Online Payment Gateways:** Integrated payment support including PayPal, Stripe, Mollie, Razorpay, Paystack, Flutterwave, Mercado Pago, MyFatoorah, PhonePe, and more.
- **Offline & Direct Payment Methods:** Support for custom offline payment options and manual approval flows.
- **PDF & QR Code Generator:** Automated PDF booking receipts, invoices, and custom QR codes for quick WhatsApp chat initiation.
- **Sitemap & SEO Management:** Dynamic sitemap generation and SEO metadata tools.

### 🌐 Advanced Frontend & User Experience
- **Modern SaaS & Tenant Storefronts:** Conversion-focused, responsive layouts for SaaS marketing and individual hotel portals.
- **Multilingual & RTL Support:** Complete multi-language translation engine with Right-to-Left (RTL) layout support.
- **Custom Branding & Page Builders:** Visual menu builder, announcement popups, and customizable tenant branding settings.

### ⚙️ Administrative & Platform Governance
- **Support Ticket System:** Built-in support ticketing system for resolving guest and hotel tenant issues.
- **Automated Cron Jobs & Webhooks:** Scheduled tasks for booking status updates, automated WhatsApp follow-ups, and secure webhook processing.
- **Activity & Financial Logs:** Detailed logs for subscription transactions, payment histories, and AI usage metrics.
