# 🚀 ARS eCommerce — Go-Live Runbook

This document outlines the step-by-step procedure for deploying the ARS eCommerce platform to production.

## 📋 Pre-Deployment Checklist
- [ ] Domain name (easyshoppingars.com) is registered and DNS is pointing to the server.
- [ ] SSL Certificate (Let's Encrypt) is installed and active.
- [ ] Production database (ars_ecommerce) is created.
- [ ] SMTP service (Gmail App Password) is ready.

## 🛠️ Step 1: Environment Setup
1. Clone the repository to the production server.
2. Copy `.env.example` to `.env`.
3. Fill in the production details in `.env`:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://easyshoppingars.com`
   - `DB_USER` and `DB_PASS` (Secure values)
   - `SESSION_SECURE=true`
4. Create the `logs/` directory and ensure it is writable by the web server.

## 🗄️ Step 2: Database Initialization
1. Import the `db.sql` schema into the production database.
2. **IMPORTANT**: Immediately update the `site_settings` table in the database with the real SMTP password:
   ```sql
   UPDATE site_settings SET value = 'your-actual-smtp-password' WHERE `key` = 'smtp_password';
   ```
3. Create the initial admin account using the `/auth/signup` page (temporarily allow) or via direct SQL.

## 🔒 Step 3: Security & Permissions
1. Ensure `uploads/` and its subdirectories have `0755` permissions.
2. Verify that `.htaccess` is correctly enforcing security headers and blocking sensitive directories.
3. Uncomment the HTTPS redirect rule in `.htaccess` once SSL is verified.

## 🧪 Step 4: Verification
1. Visit `https://easyshoppingars.com/api/health` to verify system health.
2. Perform a test order through the entire flow (Add to Cart -> Checkout -> eSewa Upload).
3. Check the `logs/api.log` and `logs/emails.log` for any errors.
4. Verify that the `audit_log` table is capturing events.

## 📢 Step 5: Post-Launch
1. Delete any temporary test orders.
2. Monitor server performance (CPU/Memory) during the first 24 hours.
3. Set up an uptime monitor (e.g., UptimeRobot) pointing to the `/api/health` endpoint.

---
**Maintenance Mode**: To enable maintenance mode during updates, run `touch maintenance.flag` in the root directory. Remove the file to go back online.
