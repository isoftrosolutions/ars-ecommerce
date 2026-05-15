# Deployment Notes — ARS Easy Shopping Mobile API v1

## Prerequisites

- PHP 7.4+ on server (verified AlmaLinux 9 + CyberPanel)
- Composer installed
- MySQL / MariaDB with `ars_ecommerce` database
- OpenLiteSpeed with `.htaccess` support (enabled by default in CyberPanel)

## Step 1: Generate JWT Secret

```bash
openssl rand -hex 32
```

Copy the output and set it as `JWT_SECRET` in `.env`.

## Step 2: Update .env

Add these to your `.env` file:

```env
JWT_SECRET=<generated 64-char hex string>
JWT_EXPIRY_DAYS=7
API_DEBUG=false
APP_BASE_URL=https://easyshoppingars.com
```

## Step 3: Install Composer Dependencies

```bash
cd /home/ektamultp/public_html  # or your project root
composer install --no-interaction
```

This installs `firebase/php-jwt` for JWT token handling.

## Step 4: Run Migration SQL

The migration file is at `api/v1/migrations/001_mobile_api.sql`.

**Review it first**, then apply:

```bash
mysql -u YOUR_DB_USER -p ektamultp_easyshoping < api/v1/migrations/001_mobile_api.sql
```

Or import via phpMyAdmin / CyberPanel Database Manager.

### What the migration adds:
1. `status` column to `users` table (active/pending/suspended)
2. `order_number` column + unique index on `orders` table
3. `otps` table — OTP storage for phone verification
4. `user_addresses` table — customer shipping addresses
5. `order_status_history` table — order tracking timeline
6. `banners` table — home page banners
7. `rate_limits` table — auth rate limiting

## Step 5: Create Upload Directories

```bash
mkdir -p uploads/banners
chmod 755 uploads/banners
```

## Step 6: OpenLiteSpeed / CyberPanel Notes

### .htaccess Support
OpenLiteSpeed in CyberPanel supports `.htaccess` natively. No extra configuration needed.

### Virtual Host Path
The API is served from the same document root as the existing site:
```
Document Root: /home/ektamultp/public_html
API URL: https://easyshoppingars.com/api/v1/
```

### PHP Version
Ensure your CyberPanel PHP config uses PHP 7.4+:
- Login to CyberPanel
- Go to **PHP** → **PHP Settings**
- Select your domain
- Ensure PHP 8.0+ is selected (recommended: PHP 8.1 or 8.2)

## Step 7: Test a Single Endpoint

```bash
# Test products endpoint (no auth needed)
curl https://easyshoppingars.com/api/v1/products

# Expected output:
# {"success":true,"data":[...],"pagination":{...}}
```

```bash
# Test OPTIONS preflight
curl -X OPTIONS https://easyshoppingars.com/api/v1/products -v

# Expected: HTTP 200 with CORS headers
```

### Quick Smoke Test
```bash
# Run the smoke test script
bash api/v1/test_smoke.sh
```

## Troubleshooting

### 404 on all /api/v1 routes
- Ensure `.htaccess` is enabled in OpenLiteSpeed
- Check `AllowOverride` is not `None` in the vhost config
- Verify the file `api/v1/index.php` exists and is readable

### "Class not found" errors for JWT
- Run `composer dump-autoload` to regenerate autoloader
- Ensure `vendor/autoload.php` exists

### Database errors
- Verify migration SQL was applied
- Check DB credentials in `.env`

### CORS errors in mobile app
- The API returns `Access-Control-Allow-Origin: *` on all responses
- For production, restrict to your app's domain if needed

## Rollback

If needed, the migration includes rollback SQL at the bottom of `001_mobile_api.sql`.
