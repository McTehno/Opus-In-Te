# Opus in te - Psychological Counseling Platform

<div align="center">

![Opus in te Logo](img/logo/headlogo.png)

**Professional Psychological Counseling and Psychotherapy Services**

A comprehensive web application for managing psychological counseling appointments, client communications, and therapy services.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
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

**Opus in te** (Latin for "Work within you") is a professional web platform designed for psychological counseling and psychotherapy services operated by Vanja Dejanović in Banja Luka, Bosnia and Herzegovina. The platform provides a seamless experience for clients to:

- Browse available psychological services
- Schedule therapy appointments online
- Manage their personal profiles
- Access educational blog content
- Connect with the therapist

The name "Opus in te" reflects the philosophy that meaningful personal growth and healing comes from the work within oneself.

### Mission

To provide accessible, professional, and empathetic psychological support through modern technology, making mental health services more convenient and approachable.

---

## ✨ Features

### Client-Facing Features

#### 🏠 **Home Page**
- Elegant landing page with hero section
- Introduction to services and therapist
- Call-to-action buttons for easy navigation
- Responsive design for all devices

#### 📅 **Appointment Booking System**
- Real-time availability checking
- Multiple service types:
  - Individual Psychotherapy
  - Couples Counseling
  - Group Workshops and Education
  - Psychological Evaluations
  - Report Writing
- Time slot selection with visual calendar
- Automatic email confirmations
- Support for both in-person and online sessions

#### 👤 **User Account Management**
- Secure user registration and authentication
- Profile editing capabilities
- Appointment history tracking
- Personal dashboard with upcoming appointments
- Email and phone verification

#### 📚 **Information Pages**
- **Services**: Detailed description of all offered services
- **About**: Therapist biography, qualifications, and approach
- **Blog**: Educational content on mental health topics
- **Contact**: Multiple ways to reach the practice

#### 🔐 **Security Features**
- Password hashing and secure authentication
- Session management
- CSRF protection
- Input sanitization and validation

### Administrative Features

#### 📊 **User Dashboard**
- View and manage appointments
- Profile information management
- Notification system
- Appointment status tracking

#### 🗄️ **Database Management**
- Comprehensive user database
- Appointment scheduling system
- Service type management
- Location tracking (in-person vs. online)
- Role-based access control

---

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Custom styling with modern features
  - Font Awesome 6.5.2 for icons
  - Google Fonts (Montserrat, Playfair Display)
  - Responsive grid layouts
  - Custom animations
- **JavaScript (Vanilla)**: Client-side interactivity
  - Booking system logic
  - Form validation
  - Dashboard interactions
  - Calendar functionality
  - Animation controls

### Backend
- **PHP 8.x**: Server-side scripting
  - PDO for database interactions
  - Session management
  - Server-side validation
  - RESTful API endpoints
- **MySQL 8.x**: Relational database
  - Normalized schema design
  - Foreign key constraints
  - Transactional support

### Email System
- **PHPMailer**: Professional email delivery
  - SMTP configuration
  - HTML email templates
  - Attachment support
  - Email verification

### Development & Deployment
- **Docker**: Containerized deployment
  - Apache web server
  - MySQL database server
  - phpMyAdmin for database management
  - MailHog for email testing
- **Git**: Version control

### Database Architecture
- **Users**: Client and therapist accounts
- **Appointments**: Booking and scheduling
- **Services**: Type of therapy sessions
- **Locations**: In-person and online sessions
- **Roles**: User permission management
- **Geography**: Cities and states for client information

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

### Required Software

1. **Docker** (Recommended: v20.10 or higher)
   ```bash
   # Check Docker version
   docker --version
   ```

2. **Docker Compose** (Recommended: v1.29 or higher)
   ```bash
   # Check Docker Compose version
   docker-compose --version
   ```

3. **Git** (for version control)
   ```bash
   # Check Git version
   git --version
   ```

### Alternative Setup (Without Docker)

If not using Docker, you'll need:

1. **PHP** (8.0 or higher)
   - Extensions: PDO, pdo_mysql, mbstring, openssl, curl
   ```bash
   php -v
   php -m | grep -E "pdo|mysql|mbstring|openssl|curl"
   ```

2. **MySQL** (8.0 or higher)
   ```bash
   mysql --version
   ```

3. **Apache** or **Nginx** web server

4. **Composer** (optional, for future dependency management)
   ```bash
   composer --version
   ```

### System Requirements

- **OS**: Linux, macOS, or Windows 10/11
- **RAM**: Minimum 2GB (4GB recommended)
- **Storage**: 500MB free space minimum
- **Network**: Internet connection for initial setup

---

## 🚀 Installation

### Option 1: Docker Installation (Recommended)

Docker provides the easiest and most consistent setup experience.

#### Step 1: Clone the Repository

```bash
# Clone the repository
git clone https://github.com/McTehno/Opus-In-Te.git

# Navigate to project directory
cd Opus-In-Te
```

#### Step 2: Prepare Docker Environment

```bash
# Ensure you're in the project root
pwd  # Should show /path/to/Opus-In-Te

# Create data directories (if they don't exist)
mkdir -p data/www data/mysql
```

#### Step 3: Copy Project Files

```bash
# Copy all project files to Docker volume directory
cp -r !(data|db_properties) data/www/
# Or manually copy: About.php, Blog.php, Booking.php, Contact.php, etc.
```

#### Step 4: Start Docker Services

```bash
# Navigate to Docker configuration directory
cd db_properties

# Start all services (detached mode)
docker-compose up -d

# Check if all containers are running
docker-compose ps
```

You should see three containers running:
- `spletni-streznik` (Web Server on port 8000)
- `mysql` (Database Server)
- `phpmyadmin` (Database UI on port 8001)
- `mailhog` (Email testing on port 8025)

#### Step 5: Import Database

```bash
# Option A: Using command line
docker exec -i mysql mysql -uroot -psuperVarnoGeslo opus < opus.sql

# Option B: Using phpMyAdmin
# 1. Open http://localhost:8001 in browser
# 2. Login with username: root, password: superVarnoGeslo
# 3. Select 'opus' database
# 4. Click Import tab
# 5. Choose opus.sql file
# 6. Click Go
```

#### Step 6: Verify Installation

```bash
# Check web server
curl http://localhost:8000

# Check if you can access the site
# Open http://localhost:8000 in your web browser
```

### Option 2: Manual Installation (Without Docker)

If you prefer a traditional LAMP/LEMP stack:

#### Step 1: Clone and Setup

```bash
# Clone repository
git clone https://github.com/McTehno/Opus-In-Te.git
cd Opus-In-Te

# Copy to web server directory (Apache example)
sudo cp -r . /var/www/html/opus-in-te/

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/opus-in-te/
sudo chmod -R 755 /var/www/html/opus-in-te/
```

#### Step 2: Configure Database Connection

Edit `backend/connect.php`:

```php
<?php
$host = 'localhost';  // or your MySQL server address
$db   = 'opus';
$user = 'your_mysql_user';
$pass = 'your_mysql_password';
$charset = 'utf8mb4';
// ... rest of the file
```

#### Step 3: Import Database

```bash
# Create database and import schema
mysql -u root -p
CREATE DATABASE opus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;

# Import SQL file
mysql -u root -p opus < db_properties/opus.sql
```

#### Step 4: Configure Web Server

**Apache Configuration** (`/etc/apache2/sites-available/opus-in-te.conf`):

```apache
<VirtualHost *:80>
    ServerName opus-in-te.local
    DocumentRoot /var/www/html/opus-in-te
    
    <Directory /var/www/html/opus-in-te>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/opus-error.log
    CustomLog ${APACHE_LOG_DIR}/opus-access.log combined
</VirtualHost>
```

```bash
# Enable site and restart Apache
sudo a2ensite opus-in-te.conf
sudo systemctl restart apache2
```

---

## 🗄️ Database Setup

### Database Schema Overview

The application uses a well-structured relational database with the following main tables:

#### Core Tables

1. **User**
   - Stores client and therapist information
   - Fields: idUser, phone, email, pass, name, last_name, picture_path, Role_idRole
   - Passwords are hashed for security

2. **Role**
   - Defines user roles (Admin, Client, Therapist)
   - Fields: idRole, name

3. **Appointment**
   - Manages therapy session bookings
   - Fields: idAppointment, date_time, duration, note, User_idUser, Appointment_Type_idAppointment_Type, Location_idLocation
   - Links users with appointment types and locations

4. **Appointment_Type**
   - Defines types of therapy services
   - Fields: idAppointment_Type, name, description, price, duration
   - Examples: Individual therapy, couples counseling, workshops

5. **Location**
   - Specifies where appointments take place
   - Fields: idLocation, address, Location_Type_idLocation_Type, City_idCity
   - Supports both in-person and online sessions

6. **Location_Type**
   - Categories: In-person, Online (video call)
   - Fields: idLocation_Type, name

7. **City & State**
   - Geographic information for clients
   - Support for multiple cities and states

### Database Initialization

The database is automatically initialized with the SQL script located at `db_properties/opus.sql`.

#### Manual Database Setup

If you need to reset or manually set up the database:

```bash
# Using Docker
docker exec -i mysql mysql -uroot -psuperVarnoGeslo < db_properties/opus.sql

# Using local MySQL
mysql -u root -p < db_properties/opus.sql
```

#### Database Migrations

Currently, schema changes are managed manually. For future updates:

1. Back up existing database:
   ```bash
   docker exec mysql mysqldump -uroot -psuperVarnoGeslo opus > backup_$(date +%Y%m%d).sql
   ```

2. Apply new schema changes
3. Test thoroughly before deploying to production

### Database Credentials

**Docker Environment:**
- Host: `podatkovna-baza` (internal Docker network)
- Database: `opus`
- Username: `root`
- Password: `superVarnoGeslo`
- Port: 3306 (internal)

**⚠️ IMPORTANT SECURITY NOTE:** 
Change the default password in production! Edit `db_properties/docker-compose.yml` and `backend/connect.php`.

---

## 🐳 Docker Deployment

### Understanding the Docker Setup

The application uses a multi-container Docker setup defined in `db_properties/docker-compose.yml`:

#### Container Architecture

```
┌─────────────────────────────────────────┐
│         Docker Network                  │
│                                         │
│  ┌──────────────┐  ┌────────────────┐ │
│  │  Apache+PHP  │  │     MySQL      │ │
│  │   (Web App)  │──│   (Database)   │ │
│  │  Port: 8000  │  │  Port: 3306    │ │
│  └──────────────┘  └────────────────┘ │
│         │                  │           │
│  ┌──────────────┐  ┌────────────────┐ │
│  │  phpMyAdmin  │  │    MailHog     │ │
│  │  Port: 8001  │  │  Port: 8025    │ │
│  └──────────────┘  └────────────────┘ │
│                                         │
└─────────────────────────────────────────┘
```

### Service Details

#### 1. Web Server (spletni-streznik)
- **Base Image**: PHP with Apache
- **Extensions**: PDO, PDO_MySQL
- **Port**: 8000 (host) → 80 (container)
- **Access**: http://localhost:8000
- **Volume**: `./data/www` → `/var/www/html`

#### 2. MySQL Database (mysql)
- **Image**: mysql:latest
- **Port**: 3306 (internal only)
- **Volume**: `./data/mysql` → `/var/lib/mysql`
- **Hostname**: `podatkovna-baza`
- **Persistence**: Data persists between restarts

#### 3. phpMyAdmin (phpmyadmin)
- **Image**: phpmyadmin/phpmyadmin:latest
- **Port**: 8001 (host) → 80 (container)
- **Access**: http://localhost:8001
- **Credentials**: root / superVarnoGeslo

#### 4. MailHog (mailhog)
- **Image**: mailhog/mailhog
- **SMTP Port**: 1025 (for application)
- **Web UI Port**: 8025 (for viewing emails)
- **Access**: http://localhost:8025

### Docker Commands

#### Starting Services

```bash
# Start all services
cd db_properties
docker-compose up -d

# Start and view logs
docker-compose up

# Start specific service
docker-compose up -d spletni-streznik
```

#### Stopping Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (⚠️ WARNING: Deletes all data)
docker-compose down -v
```

#### Viewing Logs

```bash
# View all logs
docker-compose logs

# Follow logs in real-time
docker-compose logs -f

# View logs for specific service
docker-compose logs mysql
docker-compose logs -f spletni-streznik
```

#### Managing Containers

```bash
# List running containers
docker-compose ps

# Restart a service
docker-compose restart spletni-streznik

# Execute command in container
docker-compose exec spletni-streznik bash
docker-compose exec mysql mysql -uroot -psuperVarnoGeslo opus

# View resource usage
docker stats
```

#### Maintenance Commands

```bash
# Rebuild containers (after Dockerfile changes)
docker-compose build
docker-compose up -d --build

# Pull latest images
docker-compose pull

# Remove unused containers and images
docker system prune -a
```

### Docker Troubleshooting

#### Container Won't Start

```bash
# Check logs
docker-compose logs [service-name]

# Check if port is already in use
sudo lsof -i :8000
sudo lsof -i :8001

# Remove old containers
docker-compose down
docker-compose up -d
```

#### Database Connection Issues

```bash
# Verify MySQL is running
docker-compose ps mysql

# Test database connection
docker-compose exec mysql mysql -uroot -psuperVarnoGeslo -e "SHOW DATABASES;"

# Check if opus database exists
docker-compose exec mysql mysql -uroot -psuperVarnoGeslo -e "USE opus; SHOW TABLES;"
```

#### Web Server Issues

```bash
# Check if files are properly mounted
docker-compose exec spletni-streznik ls -la /var/www/html/

# Verify PHP configuration
docker-compose exec spletni-streznik php -i | grep -i pdo

# Restart web server
docker-compose restart spletni-streznik
```

---

## 📁 Project Structure

```
Opus-In-Te/
│
├── 📄 index.php                 # Main landing page / home
├── 📄 About.php                 # Therapist biography and credentials
├── 📄 Blog.php                  # Mental health blog articles
├── 📄 Booking.php               # Appointment booking system
├── 📄 Contact.php               # Contact form and information
├── 📄 Services.php              # Services listing page
├── 📄 Login.php                 # User authentication page
├── 📄 Register.php              # New user registration
├── 📄 UserDashboard.php         # Client dashboard
├── 📄 EditProfile.php           # User profile editing
│
├── 📁 backend/                  # Server-side PHP scripts
│   ├── 📄 connect.php           # Database connection configuration
│   ├── 📄 login.php             # Authentication logic
│   ├── 📄 register.php          # Registration processing
│   ├── 📄 logout.php            # Session termination
│   ├── 📄 book_appointment.php  # Appointment booking handler
│   ├── 📄 get_slots.php         # Available time slots API
│   ├── 📄 edit_profile.php      # Profile update handler
│   └── 📁 PHPMailer/            # Email library
│       ├── 📄 PHPMailer.php
│       ├── 📄 SMTP.php
│       └── 📄 Exception.php
│
├── 📁 css/                      # Stylesheets
│   ├── 📄 styles.css            # Main application styles
│   └── 📄 notifications.css     # Notification system styles
│
├── 📁 js/                       # JavaScript files
│   ├── 📄 navbar.js             # Navigation bar interactions
│   ├── 📄 loading_screen.js     # Loading animation control
│   ├── 📄 booking.js            # Booking system logic
│   ├── 📄 dashboard.js          # Dashboard functionality
│   ├── 📄 dashboard_calendar.js # Calendar widget
│   ├── 📄 edit_profile.js       # Profile editing interactions
│   ├── 📄 notifications.js      # Notification system
│   ├── 📄 login_animations.js   # Login page animations
│   ├── 📄 kontakt_animations.js # Contact page animations
│   ├── 📄 omeni_animations.js   # About page animations
│   └── 📄 usluge.js             # Services page interactions
│
├── 📁 img/                      # Image assets
│   ├── 📁 logo/                 # Brand logos
│   │   ├── 🖼️ headlogo.png
│   │   ├── 🖼️ loading.gif
│   │   └── 🖼️ fulltransparentlogo.png
│   ├── 📁 vanjapic/             # Therapist photos
│   └── 📁 [other image dirs]    # Additional assets
│
├── 📁 db_properties/            # Database configuration
│   ├── 📄 docker-compose.yml    # Docker services configuration
│   ├── 📄 opus.sql              # Database schema and initial data
│   ├── 📄 ER.pdf                # Entity-Relationship diagram
│   └── 📄 ER.mwb                # MySQL Workbench model
│
├── 📄 .gitignore                # Git ignore rules
├── 📄 README.md                 # This file
└── 📄 LICENSE                   # License information

```

### File Descriptions

#### Root PHP Files

| File | Purpose | Main Functionality |
|------|---------|-------------------|
| `index.php` | Landing page | Hero section, service overview, introduction |
| `About.php` | About therapist | Biography, credentials, approach |
| `Services.php` | Services list | Detailed service descriptions |
| `Booking.php` | Appointment system | Calendar, time slots, service selection |
| `Contact.php` | Contact page | Contact form, location, hours |
| `Login.php` | Authentication | User login form |
| `Register.php` | Registration | New user signup |
| `UserDashboard.php` | User panel | Appointments, notifications, profile |
| `EditProfile.php` | Profile management | Edit personal information |

#### Backend PHP Files

| File | Purpose | Key Functions |
|------|---------|---------------|
| `connect.php` | DB connection | PDO setup, connection parameters |
| `login.php` | Login handler | Authentication, session creation |
| `register.php` | Registration handler | User creation, validation |
| `logout.php` | Logout handler | Session destruction |
| `book_appointment.php` | Booking handler | Appointment creation, validation |
| `get_slots.php` | Availability API | Return available time slots |
| `edit_profile.php` | Profile handler | Update user information |

#### Frontend Assets

| Directory | Contents | Purpose |
|-----------|----------|---------|
| `css/` | Stylesheets | Visual design, responsive layouts |
| `js/` | JavaScript | Interactivity, validation, animations |
| `img/` | Images | Logos, photos, icons, backgrounds |

---

## ⚙️ Configuration

### Database Configuration

Located in `backend/connect.php`:

```php
<?php
// Database connection settings
$host = 'podatkovna-baza';  // MySQL host (Docker container name)
$db   = 'opus';              // Database name
$user = 'root';              // Database user
$pass = 'superVarnoGeslo';   // Database password
$charset = 'utf8mb4';        // Character encoding

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
```

**For Production:**
1. Change database password
2. Use environment variables for credentials
3. Restrict database user privileges
4. Enable SSL connections

### Email Configuration

Email functionality requires PHPMailer configuration. Create `backend/mail_config.php` (this file is in .gitignore):

```php
<?php
// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');  // or your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'info@opusinte.ba');
define('SMTP_FROM_NAME', 'Opus in te');

// For development with MailHog
// define('SMTP_HOST', 'mailhog');
// define('SMTP_PORT', 1025);
```

### Session Configuration

PHP sessions are used for authentication. Default settings in PHP files:

```php
session_start();
// Session timeout: 24 minutes of inactivity
ini_set('session.gc_maxlifetime', 1440);
```

### Docker Configuration

Edit `db_properties/docker-compose.yml` for:

- Port mappings
- Volume locations
- MySQL password
- PHP extensions
- Resource limits

### Security Settings

**Recommended PHP Settings** (in `php.ini` or `.htaccess`):

```ini
# Hide PHP version
expose_php = Off

# Increase security
session.cookie_httponly = 1
session.cookie_secure = 1  # Only if using HTTPS
session.use_strict_mode = 1

# File upload limits
upload_max_filesize = 5M
post_max_size = 5M

# Error reporting (production)
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

---

## 📖 Usage Guide

### For Clients

#### 1. Registration

1. Navigate to http://localhost:8000
2. Click the user icon in the header
3. Click "Register" or navigate to `Register.php`
4. Fill in required information:
   - Full name
   - Email address
   - Phone number
   - Password (minimum 8 characters)
5. Submit the form
6. You'll be redirected to login page

#### 2. Booking an Appointment

1. Log in to your account
2. Click "Zakažite termin" (Schedule Appointment)
3. Select service type:
   - Individualna psihoterapija (Individual Therapy)
   - Savjetovanje za parove (Couples Counseling)
   - Grupne radionice (Group Workshops)
   - Other specialized services
4. Choose appointment date and time
5. Select location type:
   - In-person (at office)
   - Online (video call)
6. Add optional notes
7. Confirm booking
8. Receive email confirmation

#### 3. Managing Your Profile

1. Log in and go to User Dashboard
2. Click "Edit Profile"
3. Update information:
   - Contact details
   - Password
   - Profile picture
4. Save changes

#### 4. Viewing Appointments

1. Access User Dashboard
2. View upcoming appointments
3. See appointment history
4. Check appointment status

### For Administrators

#### Managing the Database

**Using phpMyAdmin** (http://localhost:8001):

1. Login with root credentials
2. Select `opus` database
3. View/edit tables:
   - `User` - Manage user accounts
   - `Appointment` - View all bookings
   - `Appointment_Type` - Add/edit services
   - `Location` - Manage locations

#### Adding New Services

```sql
INSERT INTO Appointment_Type (name, description, price, duration) 
VALUES (
    'Service Name',
    'Service Description',
    120.00,  -- Price in BAM
    60       -- Duration in minutes
);
```

#### Viewing User Activity

```sql
-- Recent appointments
SELECT 
    a.date_time,
    CONCAT(u.name, ' ', u.last_name) as client_name,
    at.name as service_type,
    l.address as location
FROM Appointment a
JOIN User u ON a.User_idUser = u.idUser
JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
LEFT JOIN Location l ON a.Location_idLocation = l.idLocation
ORDER BY a.date_time DESC
LIMIT 20;
```

---

## 💻 Development

### Development Environment Setup

#### Using Docker for Development

```bash
# Start services with live logs
docker-compose -f db_properties/docker-compose.yml up

# In another terminal, watch for file changes
# No hot-reload needed, PHP reloads on each request
```

#### Local Development Setup

```bash
# Install PHP dependencies (if using Composer)
composer install

# Start PHP built-in server (alternative to Apache)
php -S localhost:8000

# In another terminal, start MySQL
sudo systemctl start mysql
```

### Development Workflow

1. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**
   - Edit PHP files for backend logic
   - Modify CSS in `css/styles.css`
   - Update JavaScript in respective `js/` files

3. **Test Changes**
   - Access http://localhost:8000
   - Test all affected functionality
   - Check browser console for errors
   - Verify database changes

4. **Commit Changes**
   ```bash
   git add .
   git commit -m "Descriptive commit message"
   ```

5. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```

### Coding Standards

#### PHP Standards
- Follow PSR-12 coding standard
- Use prepared statements for all database queries
- Validate and sanitize all user input
- Use meaningful variable and function names
- Comment complex logic

```php
// Good example
$stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Bad example - SQL injection risk
$user = $pdo->query("SELECT * FROM User WHERE email = '$email'");
```

#### JavaScript Standards
- Use ES6+ features where supported
- Comment complex functions
- Use descriptive variable names
- Handle errors gracefully

```javascript
// Good example
async function fetchAvailableSlots(date) {
    try {
        const response = await fetch(`backend/get_slots.php?date=${date}`);
        const slots = await response.json();
        return slots;
    } catch (error) {
        console.error('Failed to fetch slots:', error);
        return [];
    }
}
```

#### CSS Standards
- Use meaningful class names
- Follow BEM naming convention where possible
- Comment sections
- Keep specificity low
- Mobile-first responsive design

### Database Migrations

For schema changes:

1. **Create backup**
   ```bash
   docker exec mysql mysqldump -uroot -psuperVarnoGeslo opus > backup.sql
   ```

2. **Create migration SQL file**
   ```sql
   -- migration_YYYYMMDD_description.sql
   ALTER TABLE User ADD COLUMN verification_token VARCHAR(255);
   ```

3. **Apply migration**
   ```bash
   docker exec -i mysql mysql -uroot -psuperVarnoGeslo opus < migration.sql
   ```

4. **Update `opus.sql`** with the new schema

### Testing

#### Manual Testing Checklist

- [ ] User registration works
- [ ] Login/logout functions correctly
- [ ] Appointment booking process completes
- [ ] Email notifications are sent
- [ ] Profile editing saves correctly
- [ ] All pages load without errors
- [ ] Mobile responsive design works
- [ ] Forms validate input properly

#### Database Testing

```bash
# Test database connection
docker exec -i mysql mysql -uroot -psuperVarnoGeslo opus -e "SELECT COUNT(*) FROM User;"

# Verify foreign key constraints
docker exec -i mysql mysql -uroot -psuperVarnoGeslo opus -e "SHOW CREATE TABLE Appointment;"
```

#### Browser Testing

Test in multiple browsers:
- Chrome/Chromium
- Firefox
- Safari
- Edge

Test responsive design:
- Desktop (1920x1080, 1366x768)
- Tablet (768x1024)
- Mobile (375x667, 414x896)

---

## 🔌 API Endpoints

### Backend API Routes

All API endpoints are located in the `backend/` directory.

#### Authentication Endpoints

**POST** `/backend/login.php`
```json
Request:
{
    "email": "user@example.com",
    "password": "userpassword"
}

Response:
{
    "success": true,
    "user_id": 123,
    "name": "John Doe",
    "role": "client"
}
```

**POST** `/backend/register.php`
```json
Request:
{
    "name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+387 65 123 456",
    "password": "securepassword"
}

Response:
{
    "success": true,
    "message": "Registration successful",
    "user_id": 123
}
```

**GET** `/backend/logout.php`
```
Destroys session and redirects to home page
```

#### Appointment Endpoints

**GET** `/backend/get_slots.php`
```
Parameters:
- date: YYYY-MM-DD format
- service_id: integer

Response:
{
    "slots": [
        {
            "time": "09:00",
            "available": true
        },
        {
            "time": "10:00",
            "available": false
        }
    ]
}
```

**POST** `/backend/book_appointment.php`
```json
Request:
{
    "user_id": 123,
    "appointment_type_id": 1,
    "date_time": "2025-01-15 10:00:00",
    "location_id": 1,
    "note": "Optional note"
}

Response:
{
    "success": true,
    "appointment_id": 456,
    "message": "Appointment booked successfully"
}
```

#### Profile Endpoints

**POST** `/backend/edit_profile.php`
```json
Request:
{
    "user_id": 123,
    "name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+387 65 123 456"
}

Response:
{
    "success": true,
    "message": "Profile updated successfully"
}
```

### Error Handling

All endpoints return standardized error responses:

```json
{
    "success": false,
    "error": "Error message",
    "code": "ERROR_CODE"
}
```

Common error codes:
- `INVALID_INPUT` - Validation failed
- `AUTH_FAILED` - Authentication failed
- `NOT_FOUND` - Resource not found
- `DB_ERROR` - Database error
- `PERMISSION_DENIED` - Access denied

---

## 🔒 Security

### Implemented Security Measures

1. **Password Security**
   - Passwords hashed using PHP's `password_hash()`
   - BCrypt algorithm with automatic salt
   - Never stored or logged in plain text

2. **SQL Injection Prevention**
   - All database queries use PDO prepared statements
   - Input parameters properly bound
   - No dynamic SQL construction with user input

3. **XSS Prevention**
   - User input sanitized before display
   - `htmlspecialchars()` used for output
   - Content Security Policy headers (to be implemented)

4. **Session Security**
   - Session IDs regenerated on login
   - HTTPOnly cookies enabled
   - Secure flag for HTTPS (recommended for production)

5. **Input Validation**
   - Server-side validation for all forms
   - Email format validation
   - Phone number format checking
   - Length limits enforced

### Security Best Practices

#### For Production Deployment

1. **Change Default Credentials**
   ```php
   // backend/connect.php
   $pass = getenv('DB_PASSWORD');  // Use environment variable
   ```

2. **Enable HTTPS**
   - Obtain SSL certificate (Let's Encrypt)
   - Force HTTPS redirect
   - Update session cookie settings

3. **Set Secure Headers**
   ```php
   // Add to all PHP files
   header("X-Frame-Options: DENY");
   header("X-Content-Type-Options: nosniff");
   header("X-XSS-Protection: 1; mode=block");
   header("Strict-Transport-Security: max-age=31536000");
   ```

4. **Restrict File Permissions**
   ```bash
   # On production server
   chmod 750 backend/
   chmod 640 backend/connect.php
   chown -R www-data:www-data /var/www/html/opus-in-te/
   ```

5. **Regular Updates**
   - Keep PHP updated
   - Update MySQL regularly
   - Monitor security advisories
   - Update PHPMailer library

6. **Database Security**
   ```sql
   -- Create limited privilege user for application
   CREATE USER 'opus_app'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT SELECT, INSERT, UPDATE ON opus.* TO 'opus_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

7. **Enable Error Logging**
   ```php
   // Production settings
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', '/var/log/php/opus-errors.log');
   ```

### Security Checklist

- [ ] Default database password changed
- [ ] HTTPS enabled and enforced
- [ ] File permissions properly set
- [ ] Error reporting disabled in production
- [ ] Security headers configured
- [ ] Database user has minimal privileges
- [ ] Backup strategy implemented
- [ ] Input validation on all forms
- [ ] SQL injection protection verified
- [ ] XSS protection verified
- [ ] Session security configured
- [ ] Email verification implemented (if needed)

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### 1. Database Connection Failed

**Symptoms:**
- "Connection refused" error
- "SQLSTATE[HY000] [2002]" error

**Solutions:**
```bash
# Check if MySQL is running
docker-compose ps mysql
# or
sudo systemctl status mysql

# Verify database credentials in backend/connect.php
# Check if database exists
docker exec -i mysql mysql -uroot -psuperVarnoGeslo -e "SHOW DATABASES;"

# Recreate database if needed
docker exec -i mysql mysql -uroot -psuperVarnoGeslo < db_properties/opus.sql
```

#### 2. Page Shows "404 Not Found"

**Solutions:**
```bash
# Verify file exists
ls -la /path/to/file.php

# Check Apache configuration
docker-compose logs spletni-streznik

# Ensure files are in correct location
docker-compose exec spletni-streznik ls -la /var/www/html/
```

#### 3. Appointment Booking Not Working

**Diagnosis:**
```bash
# Check browser console for JavaScript errors
# Check network tab for failed API requests

# Verify get_slots.php works
curl http://localhost:8000/backend/get_slots.php?date=2025-01-15

# Check database for appointment types
docker exec -i mysql mysql -uroot -psuperVarnoGeslo opus -e "SELECT * FROM Appointment_Type;"
```

#### 4. Email Not Sending

**Solutions:**
```bash
# For development, check MailHog
# Access http://localhost:8025 to see captured emails

# Verify SMTP configuration in mail_config.php
# Check PHPMailer error messages in logs

# Test email sending
docker-compose logs mailhog
```

#### 5. Session Lost / Logout Issues

**Solutions:**
```php
// Check session configuration in php.ini
session.gc_maxlifetime = 1440
session.cookie_lifetime = 0

// Verify session directory is writable
chmod 777 /var/lib/php/sessions
```

#### 6. CSS/JS Not Loading

**Solutions:**
```bash
# Check browser console for 404 errors
# Verify file paths are correct
# Clear browser cache (Ctrl+Shift+R)

# Check if files exist
ls -la css/
ls -la js/

# Verify permissions
chmod 644 css/*.css
chmod 644 js/*.js
```

#### 7. Docker Container Won't Start

**Solutions:**
```bash
# Check logs
docker-compose logs

# Remove old containers and restart
docker-compose down
docker-compose up -d

# Check port conflicts
sudo lsof -i :8000
sudo lsof -i :3306

# Rebuild containers
docker-compose build --no-cache
docker-compose up -d
```

#### 8. Database Import Failed

**Solutions:**
```bash
# Check SQL file syntax
mysql -uroot -p --force opus < db_properties/opus.sql

# Import with verbose errors
docker exec -i mysql mysql -uroot -psuperVarnoGeslo --verbose opus < db_properties/opus.sql

# Check MySQL error log
docker-compose logs mysql | grep -i error
```

### Getting Help

If you encounter issues not covered here:

1. Check browser console for JavaScript errors (F12 → Console)
2. Review Docker logs: `docker-compose logs`
3. Check PHP error logs
4. Verify database connectivity
5. Test with minimal configuration
6. Contact project maintainer (see Contact section)

---

## 🤝 Contributing

We welcome contributions to improve Opus in te! Here's how you can help:

### Ways to Contribute

1. **Report Bugs**
   - Use GitHub Issues
   - Provide detailed description
   - Include steps to reproduce
   - Attach screenshots if applicable

2. **Suggest Features**
   - Open a feature request issue
   - Explain use case
   - Provide examples if possible

3. **Submit Code**
   - Fork the repository
   - Create a feature branch
   - Make your changes
   - Submit a pull request

### Pull Request Process

1. **Fork and Clone**
   ```bash
   git clone https://github.com/yourusername/Opus-In-Te.git
   cd Opus-In-Te
   ```

2. **Create Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Changes**
   - Follow coding standards
   - Test thoroughly
   - Update documentation if needed

4. **Commit Changes**
   ```bash
   git add .
   git commit -m "Add: descriptive commit message"
   ```

5. **Push and Create PR**
   ```bash
   git push origin feature/your-feature-name
   ```
   Then create Pull Request on GitHub

### Commit Message Guidelines

Follow conventional commits:

- `Add:` New feature
- `Fix:` Bug fix
- `Update:` Update existing feature
- `Refactor:` Code refactoring
- `Docs:` Documentation changes
- `Style:` Code style changes
- `Test:` Add or update tests
- `Chore:` Maintenance tasks

Examples:
```
Add: appointment reminder email feature
Fix: booking calendar not showing available slots
Update: user dashboard layout
Docs: add API documentation
```

### Code Review Process

1. Maintainer reviews PR
2. Feedback provided if needed
3. Changes requested and implemented
4. PR approved and merged
5. Changes deployed to production

---

## 📄 License

This project is proprietary software developed for Opus in te psychological counseling practice.

**Copyright © 2025 Opus in te. All rights reserved.**

### Usage Terms

- This software is intended solely for use by Opus in te practice
- Unauthorized copying, modification, or distribution is prohibited
- Source code is confidential and proprietary
- No warranty or guarantee is provided

For licensing inquiries, please contact: info@opusinte.ba

---

## 📞 Contact

### Opus in te Practice

**Vanja Dejanović**  
Licensed Psychotherapist

📍 **Address:**  
Jevrejska 56  
Banja Luka, Bosnia and Herzegovina

📧 **Email:**  
info@opusinte.ba

📱 **Phone:**  
+387 65 123 456

🌐 **Website:**  
[www.opusinte.ba](http://www.opusinte.ba)

### Social Media

- **Facebook:** [@opusinte](https://facebook.com/opusinte)
- **Instagram:** [@opus.in.te](https://instagram.com/opus.in.te)
- **TikTok:** [@opusinte](https://tiktok.com/@opusinte)
- **Twitter/X:** [@opusinte](https://twitter.com/opusinte)

### Office Hours

**Monday - Friday:** 09:00 - 18:00  
**Saturday:** By appointment only  
**Sunday:** Closed

### For Technical Support

For issues with the website or booking system:
- Email: support@opusinte.ba
- Please include:
  - Description of the issue
  - Steps to reproduce
  - Browser and device information
  - Screenshots if applicable

### For Developers

**Project Repository:**  
https://github.com/McTehno/Opus-In-Te

**Maintainer:**  
McTehno (GitHub)

---

## 🙏 Acknowledgments

### Technologies Used

- **PHP** - Server-side scripting
- **MySQL** - Database management
- **Docker** - Containerization
- **PHPMailer** - Email functionality
- **Font Awesome** - Icon library
- **Google Fonts** - Typography

### Special Thanks

- To all contributors who have helped improve this project
- The open-source community for the amazing tools and libraries
- Mental health professionals for their invaluable feedback

---

## 📚 Additional Resources

### Mental Health Resources

- [World Health Organization - Mental Health](https://www.who.int/health-topics/mental-health)
- [National Alliance on Mental Illness](https://www.nami.org/)
- [Mental Health America](https://www.mhanational.org/)

### Technical Documentation

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Docker Documentation](https://docs.docker.com/)
- [PHPMailer Documentation](https://github.com/PHPMailer/PHPMailer)

### Development Tools

- [MySQL Workbench](https://www.mysql.com/products/workbench/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Postman](https://www.postman.com/) - API testing
- [XAMPP](https://www.apachefriends.org/) - Alternative local development

---

<div align="center">

**Made with ❤️ for mental health awareness**

*"Opus in te - The work within you"*

---

Last Updated: December 2025  
Version: 1.0.0

</div>
