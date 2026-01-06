# Opus in te - Psychological Counseling Platform

<div align="center">

![Opus in te Logo](img/logo/logo_header.png)

**Professional Psychological Counseling and Psychotherapy Services**

A comprehensive, role-based web application for managing psychological counseling appointments, client communications, blog content, and therapy services.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

</div>

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Docker Deployment](#docker-deployment)
- [Project Structure](#project-structure)
- [Configuration](#configuration)
- [Usage Guide](#usage-guide)
- [Development](#development)
- [API Endpoints](#api-endpoints)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## 🎯 About

**Opus in te** (Latin for "Work within you") is a production-grade web platform for psychological counseling led by Vanja Dejanović (Banja Luka, Bosnia and Herzegovina). The application delivers:

- A polished public website for services, therapist profile, blog, and contact
- A multi-step online booking flow with real worker availability
- Role-based dashboards for clients, workers, and administrators
- Exportable appointment documents (PDF/Excel) and email confirmations with secure HMAC tokens

---

## ✨ Features

### Public & Client Experience
- **Home, Services, About, Blog, Contact** pages with localized (Bosnian) copy and animations.
- **Multi-step booking wizard** (location → service → slot → details → confirmation) with live slot generation per worker, location presets (Banja Luka, Prijedor, Online), and service duration awareness.
- **Email confirmations** generated through PHPMailer with QR code pointing to a signed confirmation URL (`confirm_appointment.php`) and a PDF download link (`generate_appointment_pdf.php`).
- **Blog** with categories, popular posts, view counter, and detailed post view backed by MySQL.
- **Contact form** sending styled emails (MailHog in dev or SMTP in production).

### Authenticated Users (Clients)
- Registration (`/registracija`) and login (`/prijava`) backed by PDO with prepared statements.
- **User dashboard** (`UserDashboard.php`) with upcoming/past appointments, status visibility, and quick actions.
- **Profile editing** (`EditProfile.php`) for name, contact info, and avatar.
- Automatic profile refresh on booking to keep contact details current.

### Workers (Role `radnik`)
- **Worker dashboard** (`WorkerDashboard.php`) with calendar and list views, inline appointment edits (status/type/time), and client contact details.
- **Worker booking** (`WorkerBooking.php`) to book on behalf of clients, with availability checks and status updates via `worker_update_appointment.php` and `worker_book_appointment.php`.

### Administrators
- **Admin dashboard** (`AdminDashboard.php`) with KPIs (completed, confirmed counts, monthly income) and 10-day workday trends.
- **Appointments module** (`AdminAppointments.php`) for full CRUD, overlap validation, status management, and worker/client linkage.
- **Exports**: Excel (`admin_export_excel.php`, PHPSpreadsheet) and PDF (`admin_export_pdf.php`, Dompdf) of appointment data.
- **Services module** (`AdminServices.php`) with popularity/profitability stats and CRUD.
- **Blog CMS** (`AdminBlog.php`) with author selection, publishing status, view tracking, and image support.
- **User management** (`AdminUsers.php`) including worker creation with avatar upload (Argon2id hashing), updates, and deletes.

### Security & Data Integrity
- Session-based auth with role gates (`role_check.php`) and admin flagging.
- HMAC-signed appointment confirmation tokens (uses `APP_SECRET`).
- Prepared statements everywhere for SQL safety.
- Password hashing via `password_hash` (Argon2id for worker creation).
- .htaccess rewrite to route all requests through `index.php` (Bramus Router).

---

## 🛠️ Technology Stack

### Backend
- **PHP 8.2+** with Apache (mod_rewrite enabled).
- **Bramus Router** for clean routes (`index.php`).
- **PDO (MySQL)** with strict error modes.
- **PHPMailer** (bundled in `backend/PHPMailer`) for transactional emails.
- **Dompdf** for appointment PDFs.
- **PHPSpreadsheet** for Excel exports.

### Frontend
- **HTML5/CSS3/Vanilla JS**, Font Awesome 6.5.2, Google Fonts (Montserrat, Playfair Display).
- Custom components for booking wizard, dashboards, animations, and notifications.

### Data
- **MySQL 8.x** with normalized schema (roles, users, appointments, services, locations, blog categories/posts).

### Tooling
- **Composer** for PHP dependencies.
- **Docker Compose** for optional local stack (Apache+PHP, MySQL, phpMyAdmin, MailHog).

---

## 📦 Prerequisites

Install the following before running locally:

- **PHP 8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `intl` (matches Docker image).
- **Composer** 2.x
- **MySQL 8.x**
- **Apache** (or Nginx + PHP-FPM) with `mod_rewrite` enabled.
- Optional: **Docker** and **Docker Compose** if using containers.

Quick checks:
```bash
php -v
php -m | grep -E "pdo_mysql|mbstring|openssl|gd|zip"
composer --version
mysql --version
```

---

## 🚀 Installation

### Local (Apache or PHP built-in server)

1. **Clone the repository**
   ```bash
   git clone https://github.com/McTehno/Opus-In-Te.git
   cd Opus-In-Te
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```
   > Installs Bramus Router, Dompdf, and PHPSpreadsheet into `vendor/`.

3. **Configure application settings**
   - `backend/connect.php`: database host, name, user, password.
   - `backend/app_config.php`: `APP_SECRET` (HMAC signing) and `BASE_URL`.
   - `backend/admin_config.php` (not tracked): admin login constants.
     ```php
     <?php
     require_once __DIR__ . '/connect.php';
     if (session_status() === PHP_SESSION_NONE) { session_start(); }
     const ADMIN_EMAIL = 'admin@opusinte.com';
     // Generate your own bcrypt/argon hash, e.g. password_hash('your_admin_password', PASSWORD_BCRYPT)
     const ADMIN_PASSWORD_HASH = '[YOUR_ADMIN_PASSWORD_HASH]';
     ```
   - `backend/mail_config.php` (not tracked): SMTP credentials. Defaults target MailHog.
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

4. **Import the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE opus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p opus < db_properties/opus.sql
   ```

5. **Serve the app**
   - Apache: point VirtualHost to the project root and enable `AllowOverride All` for `.htaccess`.
   - PHP built-in (dev only):
     ```bash
     php -S localhost:8000 index.php
     ```

### Notes
- The `.htaccess` rewrites all non-existing paths to `index.php` for router handling.
- `vendor/` is git-ignored; ensure `composer install` is executed wherever the app runs.

---

## 🗄️ Database Setup

Schema highlights (see `db_properties/opus.sql` and ER diagram):

- **Role** (`admin`, `radnik`, `korisnik`)
- **User** (name, last_name, phone, email, hashed password, role, optional avatar)
- **State / City** (geography) and **Location_Type** (In-person vs Online)
- **Address** (links cities, location types, and optionally users)
- **Appointment_Type** (service catalog with price + duration minutes)
- **Appointment_Status** (`nepotvrđeno`, `potvrđeno`, `završeno`, `otkazano`)
- **Appointment** (datetime, address, type, status, optional receipt)
- **Appointment_User** (links appointments to both worker and client)
- **Blog_Post**, **Blog_Post_Status**, **Blog_Post_Category**, **Blog_Post_Blog_Post_Category**
- **Receipt** placeholder table for future billing attachments

Seeded accounts (from `opus.sql`):
- Admin DB record: `admin@opusinte.com` / password `secret_pass` (replace via `admin_config.php` for production).
- Worker: `doctor@opusinte.com` (`worker_pass`)
- Client: `client@gmail.com` (`client_pass`)
- Additional workers: `mihajlo@opusinte.com`, `elena@opusinte.com` (`pass_123`)

---

## 🐳 Docker Deployment

`db_properties/docker-compose.yml` provisions Apache+PHP, MySQL, phpMyAdmin, and MailHog.

1. **Prepare volumes and code mount**
   ```bash
   cd db_properties
   mkdir -p data/www data/mysql
   rsync -a --exclude=data --exclude=db_properties ../ data/www/
   ```
   (Adjust the rsync/excludes as needed; the compose file mounts `./data/www`.)

2. **Start services**
   ```bash
   docker-compose up -d
   docker-compose ps
   ```

3. **Install PHP dependencies inside the web container**
   ```bash
   docker-compose exec spletni-streznik bash -lc "cd /var/www/html && composer install"
   ```

4. **Import database**
   ```bash
   docker-compose exec -i mysql mysql -uroot -psuperVarnoGeslo opus < opus.sql
   ```

5. **Access**
   - App: http://localhost:8000
   - phpMyAdmin: http://localhost:8001 (root / superVarnoGeslo)
   - MailHog: http://localhost:8025

---

## 📁 Project Structure

```
Opus-In-Te/
├── index.php                 # Bramus Router entrypoint and route map
├── .htaccess                 # Rewrite to index.php
├── Home.php                  # Landing page
├── Services.php              # Public services view
├── About.php                 # Therapist bio
├── Blog.php                  # Blog listing (fetches via backend/fetch_posts.php)
├── Contact.php               # Contact page + JS form to backend/send_contact.php
├── Booking.php               # Public booking wizard
├── Login.php / Register.php  # Auth pages (POST back to same path)
├── UserDashboard.php         # Client dashboard
├── EditProfile.php           # Client profile editor
├── WorkerDashboard.php       # Worker calendar/list view
├── WorkerBooking.php         # Worker-side booking wizard
├── AdminDashboard.php        # Admin KPIs + graphs
├── AdminAppointments.php     # Admin appointment CRUD
├── AdminBlog.php             # Admin blog CMS
├── AdminServices.php         # Admin service management
├── AdminUsers.php            # Admin user/worker management
├── backend/
│   ├── connect.php, app_config.php, admin_config.php*, mail_config.php*
│   ├── role_check.php, login.php, register.php, logout.php
│   ├── fetch_posts.php, get_blog_post.php, increment_view.php
│   ├── send_contact.php
│   ├── get_slots.php, book_appointment.php, confirm_appointment.php
│   ├── generate_appointment_pdf.php
│   ├── admin_* (appointments/users/services/blog CRUD + exports)
│   ├── worker_* (booking, slot lookup, client search, updates)
│   └── PHPMailer/ (bundled mailer library)
├── css/ (styles for public, admin, worker, modals, notifications)
├── js/  (booking, dashboards, admin modules, animations)
├── img/ (logos, therapist and worker assets, blog placeholders)
├── db_properties/ (docker-compose, SQL seed, ERD)
├── composer.json / composer.lock
└── vendor/ (created by composer install)
```
`*` not tracked; create locally with the samples above.

---

## ⚙️ Configuration

1. **Database (`backend/connect.php`)**
   - Set `host`, `db`, `user`, `pass`, `charset`.
   - Use environment variables in production and a least-privilege DB user.

2. **App (`backend/app_config.php`)**
   - `APP_SECRET`: used for HMAC tokens in confirmation links/PDFs.
   - `BASE_URL`: absolute base (e.g., `https://yourdomain.com`).

3. **Admin (`backend/admin_config.php`)**
   - Defines `ADMIN_EMAIL` and `ADMIN_PASSWORD_HASH` checked in `backend/login.php`.
   - Sessions flag admins with `$_SESSION['is_admin'] === true`.

4. **Mail (`backend/mail_config.php`)**
   - SMTP host/port/security and sender identity.
   - Defaults target MailHog when file is absent.

5. **Apache/Nginx**
   - Enable `mod_rewrite` or equivalent to honor `.htaccess`.
   - Serve the project root as document root.

---

## 📖 Usage Guide

### Public Visitors
- Browse `/pocetna`, `/usluge`, `/o-meni`, `/blog`, `/kontakt`.
- Submit contact form; messages arrive via PHPMailer (or MailHog in dev).

### Clients
1. **Register/Login** at `/registracija` / `/prijava`.
2. **Book** at `/zakazivanje`:
   - Choose location (Banja Luka, Prijedor, Online).
   - Pick service (duration-driven) and an available slot/worker.
   - Enter contact details; if logged in, data pre-fills and updates.
   - Receive email with confirmation link and PDF download.
3. **Confirm** via emailed link (`confirm_appointment.php?id=...&token=...`).
4. **Manage** in `UserDashboard.php`: view upcoming/past visits and profile.

### Workers (role `radnik`)
- Log in with worker credentials (seeded `doctor@opusinte.com / worker_pass`).
- Use `WorkerDashboard.php` to browse calendar/list and update status/type/time.
- Use `WorkerBooking.php` to reserve slots for clients with overlap validation.

### Administrators
- Log in using `ADMIN_EMAIL` / password defined in `admin_config.php`.
- **Dashboard**: KPIs + charts.
- **Appointments**: create/update/delete, assign workers/clients, status changes, overlap checks, CSV/PDF exports.
- **Services**: manage catalog and view demand/profit stats.
- **Blog**: create/edit/delete posts, set status/author, manage images.
- **Users**: create workers (Argon2id hash + avatar upload), edit/delete users.

---

## 💻 Development

- No automated test suite is present; validate changes manually through the relevant flows (booking, dashboards, exports, blog).
- Use feature branches and conventional commit prefixes (Add/Fix/Update/Docs/Refactor/Style/Test/Chore).
- Rebuild assets are not required (plain CSS/JS).
- To export data during development, use `admin_export_excel.php` and `admin_export_pdf.php` after logging in as admin.

---

## 🔌 API Endpoints

Routes are declared in `index.php` (Bramus Router). Key paths:

### Public/HTML Routes
- `GET /` or `/pocetna` → `Home.php`
- `GET /usluge` → `Services.php`
- `GET /o-meni` → `About.php`
- `GET /blog` → `Blog.php`
- `GET /kontakt` → `Contact.php`
- `GET /zakazivanje` → `Booking.php`
- `GET|POST /prijava` → `Login.php`
- `GET|POST /registracija` → `Register.php`
- `GET /korisnicki-panel` → `UserDashboard.php`
- `GET /radni-panel` → `WorkerDashboard.php`
- `GET /admin-panel` → `AdminDashboard.php`
- Admin subpages: `/admin/termini`, `/admin/blog`, `/admin/usluge`, `/admin/korisnici`, `/admin/izvoz/{excel|pdf}`, `/admin/odjava`
- `GET /uredi-profil` → `EditProfile.php`
- `GET /radnik-zakazivanje` → `WorkerBooking.php`
- `GET /odjava` → `backend/logout.php`

### JSON/Backend APIs (selected)
- `GET|POST /backend/fetch_posts.php` → Blog categories, popular, posts.
- `POST /backend/increment_view.php` → `{ "id": <blog_id> }`.
- `POST /backend/send_contact.php` → `{ name, email, phone?, message }`.
- `GET|POST /backend/get_slots.php?date=YYYY-MM-DD&duration=60&worker_id=&exclude_appointment_id=&include_taken=1` → slot list per worker.
- `POST /backend/book_appointment.php` → JSON `{ location, serviceId, date, time, workerId, name, email, phone }`, creates appointment, sends email with confirm/PDF links.
- `GET|POST /backend/confirm_appointment.php?id=&token=` → marks status as confirmed.
- `GET|POST /backend/generate_appointment_pdf.php?id=&token=` → outputs Dompdf PDF.
- Worker: `worker_update_appointment.php`, `worker_get_slots.php`, `worker_book_appointment.php`, `worker_search_clients.php`.
- Admin: `admin_fetch_*`, `admin_create_*`, `admin_update_*`, `admin_delete_*` for appointments, blogs, services, users; `admin_export_excel.php`, `admin_export_pdf.php`.

All endpoints expect authenticated sessions according to their role checks (admin flag, role_idRole = 2 for workers, logged-in user_id for clients).

---

## 🔒 Security

Implemented:
- PDO prepared statements everywhere.
- Argon2id hashing for worker creation; bcrypt hashes in seed data; `password_verify` on login.
- Session-based auth with role redirects (`role_check.php`), admin flag, and explicit gate checks in every admin/worker endpoint.
- HMAC tokens (`APP_SECRET`) protecting confirmation/PDF URLs.
- File upload validation for worker avatars (extension whitelist + dedicated folder).
- `.gitignore` excludes credentials (`admin_config.php`, `mail_config.php`, `.env`, `vendor/`).

Production hardening checklist:
- [ ] Replace default DB password and use env vars.
- [ ] Serve over HTTPS; set `session.cookie_secure=1`.
- [ ] Add security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Strict-Transport-Security`).
- [ ] Limit DB user privileges to required CRUD.
- [ ] Rotate `APP_SECRET` and SMTP credentials securely.
- [ ] Enforce correct file permissions (`640` for configs, `750` for backend dirs).
- [ ] Enable error logging and disable display in production.

---

## 🔧 Troubleshooting

1) **`Class 'Bramus\Router\Router' not found`**  
- Run `composer install`; ensure `vendor/autoload.php` exists and is readable.

2) **Redirect loop / missing admin access**  
- Create `backend/admin_config.php` with `ADMIN_EMAIL` and `ADMIN_PASSWORD_HASH`. Admin session flag controls `/admin-*` routes.

3) **404 on pretty routes**  
- Enable `mod_rewrite`; ensure `.htaccess` is honored and document root is the project root.

4) **Database connection errors**  
- Verify `backend/connect.php` credentials and that MySQL is reachable. When using Docker, host is `podatkovna-baza`.

5) **Email not delivered**  
- In dev, check MailHog at `http://localhost:8025`. In prod, set SMTP values in `backend/mail_config.php` and open outbound port 587/465.

6) **Slot selection empty**  
- Ensure workers exist (role `radnik`) and `Appointment_Type.duration` is set. Check `backend/get_slots.php` parameters.

7) **Export failures (Excel/PDF)**  
- Confirm PHP extensions `zip`, `gd`, and `mbstring` are installed. Verify writable temp directory for Dompdf.

---

## 🤝 Contributing

Contributions are welcome. Please:
- Open an issue for bugs or feature requests with reproduction steps.
- Use feature branches and conventional commit prefixes.
- Update documentation when functionality changes.
- Verify affected flows manually (no automated tests exist).

---

## 📄 License

Proprietary software developed for Opus in te psychological counseling practice.  
**Copyright © 2026 Opus in te. All rights reserved.**

Usage terms:
- Intended solely for Opus in te.
- No unauthorized copying, modification, or distribution.
- Source code is confidential and provided without warranty.

For licensing inquiries: info@opusinte.ba

---

## 📞 Contact

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
*"Opus in te - The work within you"*

---

Last Updated: January 2026  
Version: 1.1.0

</div>
