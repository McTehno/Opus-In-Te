# Opus in te — Psychological Counseling Platform

<div align="center">

![Opus in te Logo](img/logo/logo_header.png)

**A role-based platform for psychological counseling, bookings, client communications, and content.**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)
[![Status](https://img.shields.io/badge/Status-Active-success.svg)](#)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](#license)

</div>

---

## 📌 Table of Contents

- [Overview](#overview)
- [Highlights](#highlights)
- [Tech Stack](#tech-stack)
- [Quickstart](#quickstart)
  - [Local](#local)
  - [Docker Compose](#docker-compose)
- [Configuration](#configuration)
- [Database & Seed Data](#database--seed-data)
- [Project Structure](#project-structure)
- [Routes & APIs](#routes--apis)
- [Development](#development)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Contact](#contact)

---

## Overview

**Opus in te** (“Work within you”) powers the public site, booking engine, and operational back-office for a psychological counseling practice led by **Vanja Dejanović** (Banja Luka, Bosnia and Herzegovina). It ships with polished public pages, a multi-step scheduling flow, and dedicated dashboards for clients, workers, and administrators.

---

## Highlights

- **Public experience:** Home, Services, About, Blog, and Contact pages with localized (Bosnian) content and subtle animations.
- **Booking wizard:** Location → service → slot → details → confirmation with live worker availability, duration-aware slots, and QR-coded email confirmations plus PDF receipts.
- **Role-based dashboards:** Client, worker, and admin panels for viewing, editing, or exporting appointments and content.
- **Content & exports:** Blog CMS with categories and view tracking; appointment exports to PDF (Dompdf) and Excel (PHPSpreadsheet).
- **Security-first defaults:** Prepared statements everywhere, Argon2id/bcrypt hashing, HMAC-signed confirmation links, and `.htaccess` routing through Bramus Router.

---

## Feature Map (by screen/file)

### Public site
- **Home/Services/About** (`Home.php`, `Services.php`, `About.php`) – hero animations (`js/hero_animation.js`, `js/usluge.js`), service cards, therapist bio.
- **Blog** (`Blog.php`) – category filter, popular list, and post grid powered by `backend/fetch_posts.php` and `backend/increment_view.php`; front-end helpers in `js/blog.js`.
- **Contact** (`Contact.php`, `js/contact_form.js`) – PHPMailer-backed form via `backend/send_contact.php`; includes scroll/hover animations (`js/kontakt_animations.js`).

### Booking flow
- **Wizard UI** (`Booking.php`, `css/booking.css`, `js/booking.js`) – 5-step flow with progress bar, calendar/time slot picker, and confetti on completion.
- **Availability** – slots resolved server-side through `backend/get_slots.php` (per worker, duration-aware). Locations pre-mapped (Banja Luka/Prijedor/Online) to Address IDs.
- **Booking creation** – `backend/book_appointment.php` links client+worker to `Appointment`, updates logged-in user contact info, and returns HMAC tokenized URLs.
- **Email & QR** – PHPMailer template in `backend/book_appointment.php` includes QR pointing to `backend/confirm_appointment.php` and PDF link `backend/generate_appointment_pdf.php`.

### Client & worker
- **Client dashboard** (`UserDashboard.php`, `js/dashboard_calendar.js`) – upcoming/past visits, inline status badges, calendar view, and quick links to EditProfile/booking.
- **Profile editing** (`EditProfile.php`, `js/edit_profile.js`) – name/email/phone/avatar updates.
- **Worker dashboard** (`WorkerDashboard.php`, `js/worker_dashboard.js`) – calendar/list toggle, inline status/time/type edits through `backend/worker_update_appointment.php`.
- **Worker-side booking** (`WorkerBooking.php`, `js/worker_booking.js`) – staff can reserve on behalf of clients; search existing clients via `backend/worker_search_clients.php`.

### Admin back-office
- **Dashboard KPIs** (`AdminDashboard.php`) – daily/weekly stats sourced from DB queries in file; cards styled via `css/admin.css`.
- **Appointments module** (`AdminAppointments.php`, `js/admin_appointments.js`) – filter/search, edit/delete modals, status/price/duration display, backed by `backend/admin_fetch_appointments.php`, `admin_update_appointment.php`, `admin_delete_appointment.php`.
- **Services** (`AdminServices.php`, `js/admin_services.js`) – CRUD through `backend/admin_fetch_services.php`, `admin_add_service.php`, `admin_update_service.php`, `admin_delete_service.php`.
- **Blog CMS** (`AdminBlog.php`, `js/admin_blog.js`) – author picker, publish status, view count, images; uses `backend/admin_fetch_blogs.php`, `admin_get_blog.php`, `admin_update_blog.php`, `admin_delete_blog.php`, `admin_fetch_blog_authors.php`.
- **Users** (`AdminUsers.php`, `js/admin_users.js`) – create/update/delete users and workers; file upload for avatars; endpoints `backend/admin_create_worker.php`, `admin_fetch_users.php`, `admin_update_user.php`, `admin_delete_user.php`.
- **Exports** – `backend/admin_export_excel.php` (PHPSpreadsheet) and `backend/admin_export_pdf.php` (Dompdf) exposed at `/admin/izvoz/{excel|pdf}`.

### Styling & UX
- **CSS** lives in `css/` (public, admin, worker themes) with dedicated animation files.
- **Icons/Fonts**: Font Awesome 6.5.2, Google Fonts (Montserrat, Playfair Display).
- **Notifications**: `js/notifications.js` provides toast helpers across dashboards/admin.

---

## Tech Stack

- **Backend:** PHP 8.2+, Bramus Router, PDO (MySQL), PHPMailer, Dompdf, PHPSpreadsheet.
- **Frontend:** HTML5/CSS3/Vanilla JS, Font Awesome 6.5.2, Google Fonts (Montserrat, Playfair Display).
- **Data:** MySQL 8.x with normalized schema for roles, users, appointments, services, locations, and blog content.
- **Tooling:** Composer for dependencies; Docker Compose for local Apache+PHP, MySQL, phpMyAdmin, and MailHog.

---

## Quickstart

### Local
1. **Install prerequisites:** PHP 8.2+ (`pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `intl`), Composer 2.x, MySQL 8.x, Apache/Nginx with `mod_rewrite`.
2. **Clone & install**
   ```bash
   git clone https://github.com/McTehno/Opus-In-Te.git
   cd Opus-In-Te
   composer install
   ```
3. **Configure**
   - `backend/connect.php` → database credentials.
   - `backend/app_config.php` → `APP_SECRET`, `BASE_URL`.
   - Create `backend/admin_config.php`:
     ```php
     <?php
     require_once __DIR__ . '/connect.php';
     if (session_status() === PHP_SESSION_NONE) { session_start(); }
     const ADMIN_EMAIL = 'admin@opusinte.com';
     const ADMIN_PASSWORD_HASH = '[YOUR_ADMIN_PASSWORD_HASH]'; // password_hash(...)
     ```
   - Create `backend/mail_config.php` (SMTP or MailHog defaults):
     ```php
     <?php
     const SMTP_HOST = 'mailhog';
     const SMTP_PORT = 1025;
     const SMTP_USERNAME = '';
     const SMTP_PASSWORD = '';
     const SMTP_SECURE = '';
     const SMTP_FROM_EMAIL = 'info@opusinte.ba';
     const SMTP_FROM_NAME = 'Opus in te';
     ```
4. **Import database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE opus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p opus < db_properties/opus.sql
   ```
5. **Run**
   - Apache: point VirtualHost to the repo root with `AllowOverride All`.
   - PHP built-in (dev only):
     ```bash
     php -S localhost:8000 index.php
     ```

### Docker Compose
`db_properties/docker-compose.yml` provisions Apache+PHP, MySQL, phpMyAdmin, and MailHog.

1. Prepare mounts
   ```bash
   cd db_properties
   mkdir -p data/www data/mysql
   rsync -a --exclude=data --exclude=db_properties ../ data/www/
   ```
2. Start services
   ```bash
   docker-compose up -d
   docker-compose ps
   ```
3. Install dependencies inside the web container
   ```bash
   docker-compose exec spletni-streznik bash -lc "cd /var/www/html && composer install"
   ```
4. Import seed data
   ```bash
   docker-compose exec -i mysql mysql -uroot -psuperVarnoGeslo opus < opus.sql
   ```
5. Access: app http://localhost:8000 · phpMyAdmin http://localhost:8001 · MailHog http://localhost:8025

---

## Configuration

| File | Purpose | Notes |
| --- | --- | --- |
| `backend/connect.php` | DB connection | Use least-privilege credentials; prefer environment overrides in production. |
| `backend/app_config.php` | App secret & base URL | `APP_SECRET` signs confirmation/PDF URLs. |
| `backend/admin_config.php` (untracked) | Admin login | Sets `ADMIN_EMAIL` and `ADMIN_PASSWORD_HASH`; sessions flag admins via `$_SESSION['is_admin']`. |
| `backend/mail_config.php` (untracked) | SMTP settings | Defaults target MailHog when absent. |
| Web server | Rewrite rules | Enable `mod_rewrite` so `.htaccess` routes everything through `index.php`. |

---

## Database & Seed Data

Schema highlights (`db_properties/opus.sql`):
- Roles (`admin`, `radnik`, `korisnik`), users with optional avatars.
- Locations (state/city/location type), addresses, appointment types & statuses.
- Appointments linked to both workers and clients; receipts placeholder table.
- Blog posts with categories, statuses, and view tracking.

Seeded accounts (replace in production):
- Admin: `admin@opusinte.com` / `secret_pass` (override via `admin_config.php`).
- Worker: `doctor@opusinte.com` / `worker_pass`; additional workers `mihajlo@opusinte.com`, `elena@opusinte.com` / `pass_123`.
- Client: `client@gmail.com` / `client_pass`.

---

## Project Structure

```
Opus-In-Te/
├── index.php                 # Bramus Router entrypoint and route map
├── .htaccess                 # Rewrite to index.php
├── Home.php, Services.php, About.php, Blog.php, Contact.php
├── Booking.php               # Public booking wizard
├── Login.php / Register.php  # Auth pages
├── UserDashboard.php / EditProfile.php
├── WorkerDashboard.php / WorkerBooking.php
├── AdminDashboard.php, AdminAppointments.php, AdminBlog.php, AdminServices.php, AdminUsers.php
├── backend/                  # Connect, auth, mailer, booking, admin/worker CRUD, exports, PHPMailer
├── css/ | js/ | img/
├── db_properties/            # docker-compose + SQL seed
├── composer.json / composer.lock
└── vendor/                   # created by composer install
```

---

## Routes & APIs

Key public routes: `/` (`/pocetna`), `/usluge`, `/o-meni`, `/blog`, `/kontakt`, `/zakazivanje`, `/prijava`, `/registracija`.  
Dashboards: `/korisnicki-panel`, `/radni-panel`, `/admin-panel`, `/admin/termini`, `/admin/blog`, `/admin/usluge`, `/admin/korisnici`, `/admin/izvoz/{excel|pdf}`, `/uredi-profil`, `/radnik-zakazivanje`, `/odjava`.

Selected backend endpoints:
- `/backend/fetch_posts.php`, `/backend/get_blog_post.php`, `/backend/increment_view.php`
- `/backend/send_contact.php`
- `/backend/get_slots.php`, `/backend/book_appointment.php`, `/backend/confirm_appointment.php`, `/backend/generate_appointment_pdf.php`
- Worker: `worker_update_appointment.php`, `worker_get_slots.php`, `worker_book_appointment.php`, `worker_search_clients.php`
- Admin: `admin_fetch_*`, `admin_create_*`, `admin_update_*`, `admin_delete_*`, `admin_export_excel.php`, `admin_export_pdf.php`

All APIs enforce role/session checks (admin flag, worker role `radnik`, or logged-in client).

---

## Development

- No automated test suite; validate changes manually across booking, dashboards, exports, and blog flows.
- Plain CSS/JS—no build pipeline required.
- Use conventional commit prefixes (Add/Fix/Update/Docs/Refactor/Style/Test/Chore).
- Data exports available after admin login via `admin_export_excel.php` and `admin_export_pdf.php`.

---

## Security

Built-in:
- PDO prepared statements throughout.
- Argon2id hashing for worker creation; bcrypt hashes in seed data; `password_verify` on login.
- Session-based auth with explicit role gates and admin flagging.
- HMAC tokens (`APP_SECRET`) on confirmation/PDF links.
- Avatar upload validation (extension whitelist, dedicated folder).
- `.gitignore` excludes secrets (`admin_config.php`, `mail_config.php`, `.env`, `vendor/`).

Hardening checklist:
- [ ] Rotate DB credentials; use env vars and least-privilege DB user.
- [ ] Enforce HTTPS and `session.cookie_secure=1`.
- [ ] Add security headers (e.g., `X-Frame-Options`, `X-Content-Type-Options`, `Strict-Transport-Security`).
- [ ] Tighten file permissions (`640` configs, `750` backend dirs).
- [ ] Enable error logging and disable display in production.
- [ ] Rotate `APP_SECRET` and SMTP credentials regularly.

---

## Troubleshooting

1) **`Class 'Bramus\\Router\\Router' not found`** → Run `composer install` and ensure `vendor/autoload.php` is readable.  
2) **Admin access loop** → Create `backend/admin_config.php` with `ADMIN_EMAIL` and `ADMIN_PASSWORD_HASH`.  
3) **Pretty routes 404** → Enable `mod_rewrite`; serve project root as document root.  
4) **DB connection errors** → Check `backend/connect.php` credentials; Docker host is `podatkovna-baza`.  
5) **No emails in dev** → Open MailHog at `http://localhost:8025`; set SMTP values for production.  
6) **Empty slot list** → Ensure workers exist and `Appointment_Type.duration` is set; verify `get_slots.php` params.  
7) **Export failures** → Confirm PHP extensions `zip`, `gd`, `mbstring`; ensure Dompdf temp directory is writable.

---

## License

Proprietary software developed for Opus in te psychological counseling practice.  
**Copyright © 2026 Opus in te. All rights reserved.**

Usage terms:
- Intended solely for Opus in te.
- No unauthorized copying, modification, or distribution.
- Source code is confidential and provided without warranty.

For licensing inquiries: info@opusinte.ba

---

## Contact

**Opus in te Practice**  
Vanja Dejanović — Licensed Psychotherapist  
Jevrejska 56, Banja Luka, Bosnia and Herzegovina  
Email: info@opusinte.ba  
Phone: [REPLACE BEFORE PRODUCTION] +387 65 123 456  
Website: [www.opusinte.ba](http://www.opusinte.ba)  
Socials: [Facebook](https://facebook.com/opusinte) · [Instagram](https://instagram.com/opus.in.te) · [TikTok](https://tiktok.com/@opusinte) · [Twitter/X](https://twitter.com/opusinte)

Office hours: Mon–Fri 09:00–18:00, Saturday by appointment, Sunday closed.

---

<div align="center">

**Made with ❤️ for mental health awareness**  
*“Opus in te - The work within you”*

Last Updated: January 2026 · Version: 1.1.0

</div>
