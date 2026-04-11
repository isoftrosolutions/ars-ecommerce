-- =============================================================
-- ARS eCommerce — Admin Audit Fix Migration
-- Run this on your LIVE database (ars_ecommerce)
-- Date: 2026-04-11
-- =============================================================

-- 1. Add 'Out for Delivery' to orders.delivery_status enum
--    (was missing, causing silent empty-string inserts from admin UI)
ALTER TABLE `orders`
    MODIFY COLUMN `delivery_status`
    ENUM('Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled')
    DEFAULT 'Pending';

-- 2. Add admin_reply column to contact_submissions
--    (used by the new reply feature in admin/contact.php)
ALTER TABLE `contact_submissions`
    ADD COLUMN IF NOT EXISTS `admin_reply` TEXT DEFAULT NULL
    AFTER `message`;

-- 3. Drop legacy duplicate tables
--    'reviews'   → replaced by 'product_reviews' (all backend code uses product_reviews)
--    'settings'  → replaced by 'site_settings'   (all backend code uses site_settings)
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `settings`;

-- 4. Create uploads/products directory if not already done
--    (Nothing to do in SQL — handled by PHP mkdir() in backend/products.php)

-- 5. Verify site_settings table is correct (no data change needed)
--    site_settings uses VARCHAR(100) key as PRIMARY KEY — correct.

-- Done. Safe to run multiple times.
