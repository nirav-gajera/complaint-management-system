# Complaint Management System

A production-oriented Complaint Management System built with Laravel 12, Sanctum, and Vue 3 SPA architecture.

This project provides:
- 👤 A user portal for submitting and tracking complaints
- 🛠️ An admin portal for managing complaints, categories, users, settings, and audit history
- 🔌 A JSON API backend for current SPA usage and future mobile integration
- 📱 Responsive Vue-based screens with minimal Blade usage only for the SPA mount point

## 🧰 Tech Stack

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- Laravel Boost
- Vue 3
- Vue Router
- Pinia
- Tailwind CSS 4
- MySQL
- Vite
- PHPUnit 11

## 🎯 Purpose

The system is designed to manage the full lifecycle of complaints raised by end users.

Users can:
- 📝 Register and log in
- 📨 Submit complaints with category, description, and attachments
- 👀 View complaint status and public responses
- ✏️ Edit or withdraw complaints before admin action
- 🙍 Manage their profile and password
- 🔎 Search and filter their complaints

Administrators can:
- 🔐 Access a separate admin portal
- 📊 View dashboard metrics and complaint trends
- 🧭 Search, filter, assign, and update complaints
- 💬 Add internal notes and public responses
- 🗂️ Manage complaint categories
- 👥 Manage users and trigger password reset links
- ⚙️ Manage system settings
- 📜 Review audit logs

## ✨ Main Features

### 👤 User Portal
- Registration and login
- Email verification flow
- Complaint dashboard with recent complaints and status counts
- Complaint submission form with attachment upload
- Complaint detail page with public response history
- Complaint edit and withdraw actions
- Profile update and password change
- Search and filter by status, category, and date

### 🛠️ Admin Portal
- Staff-only routing and access control
- Dashboard with complaint statistics
- Complaint management with assignment and status transitions
- Public response and internal note support
- Category CRUD with linked-complaint protection
- User listing, filtering, role/status updates, and reset-password action
- Settings management
- Audit logging

### 🚦 Complaint Statuses
- `pending`
- `in_progress`
- `resolved`
- `rejected`

### 🏷️ Default Categories
- Service
- Product
- Feedback
- Other

## 🏗️ Architecture

### 🔙 Backend
Laravel serves JSON API endpoints only. Authentication is handled through Sanctum session-based SPA auth.

Important API areas:
- `api/auth/*`
- `api/complaints/*`
- `api/profile/*`
- `api/admin/*`

### 🖥️ Frontend
Vue SPA is mounted from a minimal Blade view.

Main frontend areas:
- `resources/js/pages/auth`
- `resources/js/pages/user`
- `resources/js/pages/admin`
- `resources/js/router`
- `resources/js/stores`

## 🔑 Seeded Test Accounts

After running migrations and seeders, these accounts are available:

### 👑 Admin Account
- Email: `admin@example.com`
- Password: `password`
- Role: `admin`

### 🙋 User Account
- Email: `test@example.com`
- Password: `password`
- Role: `user`

## 🚀 Installation

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Configure environment

Copy `.env.example` to `.env` if needed, then configure:
- `APP_URL`
- `DB_*`
- `MAIL_*`
- `QUEUE_CONNECTION`

### 3. Generate app key

```bash
php artisan key:generate
```

### 4. Run migrations and seeders

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. Build frontend assets

```bash
npm run build
```

### 6. Start the application

For local development:

```bash
composer run dev
```

This starts:
- Laravel server
- Queue listener
- Vite dev server

If you prefer manual startup:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

## 🧪 Useful Commands

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan route:list
php artisan config:show mail
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

## 🔌 API Overview

### 🔐 Auth
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `GET /api/auth/user`
- `GET /api/auth/email/status`
- `POST /api/auth/email/verification-notification`

### 👤 User APIs
- `GET /api/dashboard`
- `GET /api/categories`
- `GET /api/complaints`
- `POST /api/complaints`
- `GET /api/complaints/{complaint}`
- `PUT /api/complaints/{complaint}`
- `DELETE /api/complaints/{complaint}`
- `GET /api/profile`
- `PATCH /api/profile`
- `PUT /api/profile/password`

### 🛠️ Admin APIs
- `GET /api/admin/dashboard`
- `GET /api/admin/complaints`
- `GET /api/admin/complaints/{complaint}`
- `PATCH /api/admin/complaints/{complaint}`
- `GET /api/admin/categories`
- `POST /api/admin/categories`
- `PUT /api/admin/categories/{category}`
- `DELETE /api/admin/categories/{category}`
- `GET /api/admin/settings`
- `PUT /api/admin/settings`
- `GET /api/admin/users`
- `PATCH /api/admin/users/{user}`
- `POST /api/admin/users/{user}/reset-password`

## 📂 File Storage

Complaint attachments are stored on the local disk under the Laravel storage path.

For public storage linking if needed:

```bash
php artisan storage:link
```

## 📧 Email and Notifications

The application supports:
- Verification emails
- Password reset emails
- Complaint status update emails

### ⚠️ Important SMTP Note
If email is not being received, check SMTP connectivity from the server machine, not just `.env` values.

This project already surfaces mail errors in API responses and logs them to:
- `storage/logs/laravel.log`

A common failure case is outbound SMTP being blocked by:
- Windows firewall
- antivirus/security software
- hosting/network restrictions
- blocked Mailtrap SMTP ports

If Mailtrap credentials are correct but mail still does not send, verify that the server can open outbound connections to Mailtrap SMTP.

## 🔒 Password Reset Flow

The SPA reset-password flow is supported through:
- named route `password.reset`
- SPA page `/reset-password/{token}`
- API endpoint `POST /api/auth/reset-password`

## ✅ Verification and Testing

Focused test coverage exists for:
- auth flows
- complaint flows
- admin complaint actions
- admin category actions
- admin user actions

Run all tests:

```bash
php artisan test --compact
```

## 📌 Current Operational Notes

- The app uses Sanctum session authentication for the SPA.
- The verify-email screen includes a sign-out option to avoid being trapped in the verification flow.
- Admin user listing, complaint listing, category management, and complaint response flows have been aligned to a consistent API response shape.
- Password reset emails require the named `password.reset` route, which is already configured.

## 🗃️ Project Structure

- `app/Http/Controllers` - API and auth controllers
- `app/Http/Requests` - validation request classes
- `app/Http/Resources` - JSON API resources
- `app/Models` - Eloquent models
- `database/migrations` - schema definitions
- `database/seeders` - seeded accounts, categories, settings
- `resources/js` - Vue SPA source
- `resources/views/app.blade.php` - minimal SPA mount
- `routes/api.php` - protected API routes
- `routes/web.php` - auth JSON routes, SPA mount, signed verification/reset routes

## 📄 License

This project is provided for application use and development purposes within this workspace.