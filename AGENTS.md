# ARS Easy Shopping — Agent Guide

## Stack
- PHP 7.4+, MySQL/MariaDB, Bootstrap 5, vanilla JS, PWA
- Composer dependency: only `firebase/php-jwt` ^6.10
- No JS framework, no build step, no npm, no PHPUnit, no CI

## Two API layers
- `api/index.php` — Admin REST API (session auth, CSRF-protected, JWT not used here)
- `api/v1/index.php` — Mobile customer-facing REST API v1 (JWT auth via `firebase/php-jwt`)
- Both APIs set `Content-Type: application/json` early, register shutdown/error handlers to catch fatals before output

## Routing
- **Frontend**: `index.php` loads `includes/router.php` which maps clean URLs → files. Dynamic routes: `product/{slug}`, `order/{id}`, `cart-action`
- **Admin API rewrite**: `.htaccess` routes `api/*` → `api/index.php`
- **Mobile API rewrite**: `api/v1/.htaccess` routes `api/v1/*` → `api/v1/index.php`

## Frontend pages (PHP + Bootstrap 5, rendered server-side)
- Templates use `includes/header-bootstrap.php` and `includes/footer-bootstrap.php`
- Pages set `$page_title`, `$page_meta_desc` before `include header-bootstrap.php`
- Admin pages use `admin/includes/header.php` which calls `protect_admin_page()` immediately

## Database
- Schema in `db.sql` (MariaDB dump), charset `utf8mb4`
- `includes/db.php` connects via PDO, sets `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, disables emulated prepares
- `includes/db.php` also configures sessions (httponly, samesite=Lax) and security headers
- Migrations go in `migrations/` as `.sql` files (run manually against MySQL CLI)
- `install.php` is a CLI wizard for initial setup and DB creation

## Auth
- **Website**: Session-based. Login via `backend/login.php`. CSRF tokens via `generate_csrf_token()` / `validate_csrf_token()`
- **Admin**: `protect_admin_page()` checks `$_SESSION['user']['role'] === 'admin'`
- **Mobile API v1**: JWT tokens (HS256, 7-day expiry). Config in `api/v1/config/jwt.php`
- OTP flow: `send_otp()` generates 6-digit code, stores bcrypt hash in DB, rate-limited (5 attempts/hour), 10-minute expiry
- Guest cart transfers to user on login via `transfer_guest_cart_to_user()`

## Security patterns
- `.htaccess` blocks `/logs/`, `/config/`, `/includes/` via HTTP. PHP includes still work (filesystem path)
- `maintenance.php` checks `site_settings` DB + `maintenance.flag` file. Admins bypass. Uses `$_GET['bypass_maintenance']` with APP_KEY
- Audit logging in `includes/audit-logger.php` inserts into `audit_log` table

## PWA
- Service worker: `sw.js` (v2.0.1), caches static + dynamic + images
- `manifest.json` with scope `/ars/`, shortcuts to shop/cart/orders
- Offline page: `offline.php`

## Dev commands
```bash
# Install dependencies
composer install

# Run CLI installer (interactive, 9 steps)
php install.php

# Insert/update admin user
php insert_admin.php

# Daily DB backup (add to crontab)
php scripts/backup-db.php

# Setup cron automatically
php scripts/setup-cron.php

# Smoke test mobile API v1 (requires running server)
bash api/v1/test_smoke.sh

# Enable maintenance mode
touch maintenance.flag

# Disable maintenance mode
rm maintenance.flag
```

## Important gotchas
- `includes/db.php` is **gitignored** (contains credentials). The committed version has fallback credentials — never rely on them
- `.env` is gitignored. Copy `.env.example` → `.env` and fill values
- `insert_admin.php` has hardcoded admin credentials — run it then delete or secure it
- APCu cache functions (`apcu_fetch` / `apcu_store`) are called in helpers but may not be installed — code handles missing APCu gracefully
- `logs/api.log` and `logs/emails.log` are the primary log destinations
- SMTP password is stored in `site_settings` DB table, not in `.env`
- Router lives at `includes/router.php` but the actual route definitions are there — if adding a new page, add it there
- `includes/env.php` auto-loads `.env` on include. The `env()` helper reads from `getenv()` then `$_ENV` then `$_SERVER`
