# WhatStay - WhatsApp AI Assistant for Hotel Booking Automation (SaaS)

> **View-Only Repository** - This codebase is published as a portfolio and code-review showcase. It is intended for hiring managers and technical reviewers to inspect the structure, architecture, and implementation style.
>
> **No reuse permission is granted** unless you receive written approval from the repository owner.

---

## 📌 About This Project

**WhatsBook** is a Laravel-based WhatsApp AI assistant and hotel booking automation SaaS. The repository is organized to make the code structure easy to review while keeping environment-specific secrets, generated files, and local-only artifacts out of version control.

---

## 🛠️ Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP / Laravel 11 |
| Frontend | Blade Templates, Laravel Mix, JavaScript |
| Database | MySQL-compatible database schema |
| Integrations | WhatsApp messaging, AI credits, payments, PDF, QR, sitemap |
| Architecture | Service layer, traits, segmented routing |

---

## 🗂️ Project Structure Highlights

- `app/Services` for business logic, gateways, and reusable service classes
- `app/Traits` for shared application behavior
- `routes/admin.php`, `routes/front.php`, and `routes/tenant.php` for feature-based route separation
- `config/` for payment, installer, and integration settings
- `database/` for schema, seeders, and demo-ready data
- `resources/views/` for admin, frontend, user, and module-specific Blade views

---

## ✨ Authentic Key Features

### 🤖 WhatsApp AI Assistant & Automation
- WhatsApp workflow handling and template-based messaging support
- AI credit and assistant-related business logic
- Template, message, and variable-driven automation flows

### 🏨 Hotel Booking & Service Management
- Booking-oriented service details and availability flows
- Room and booking-related service classes
- Booking UI components and related admin management screens

### 💰 Payments & Operational Tools
- Multi-gateway payment support
- Offline and online payment flow support
- Export, QR code, PDF, and sitemap tooling

### ⚙️ Administrative & Review-Friendly Architecture
- Segmented admin, frontend, and tenant routing
- Centralized service layer for cleaner code review
- Public showcase layout that highlights structure instead of secrets

---

## 🔧 Local Setup

1. Install PHP, Composer, and Node.js dependencies.
2. Copy `.env.example` to `.env` and configure your local database and services.
3. Run migrations and seeders if you need sample data.
4. Build frontend assets with the project’s Laravel Mix commands.

---

## 🔒 Public Showcase Notes

- Keep `.env`, API keys, credentials, and production secrets untracked.
- Avoid committing generated files and local-only build artifacts.
- If you want a stricter public release, move private business rules to a separate private repository and keep this repo demo-safe.

---
