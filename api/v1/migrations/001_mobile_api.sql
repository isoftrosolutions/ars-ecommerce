-- ───────────────────────────────────────────────────────────────────
-- Migration: 001_mobile_api.sql
-- ARS Easy Shopping — Mobile API schema additions
-- Review carefully before executing in production.
-- ───────────────────────────────────────────────────────────────────

-- 1. Add 'status' column to users table (if not exists)
ALTER TABLE users
    ADD COLUMN `status` ENUM('active','pending','suspended') NOT NULL DEFAULT 'pending'
    AFTER `role`;

-- Set existing users as active
UPDATE users SET status = 'active' WHERE 1=1;

-- 2. Add 'order_number' column to orders table (if not exists)
ALTER TABLE orders
    ADD COLUMN `order_number` VARCHAR(20) DEFAULT NULL AFTER `id`;

-- Generate order numbers for existing orders
UPDATE orders SET order_number = CONCAT('ARS-', YEAR(created_at), '-', LPAD(id, 6, '0')) WHERE order_number IS NULL;

-- Add unique index
ALTER TABLE orders
    ADD UNIQUE KEY `idx_order_number` (`order_number`);

-- 3. Create otps table
CREATE TABLE IF NOT EXISTS `otps` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `phone` VARCHAR(15) NOT NULL,
    `otp_code` VARCHAR(6) NOT NULL,
    `hashed_otp` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_otps_phone` (`phone`),
    KEY `idx_otps_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- 4. Create user_addresses table
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `province` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `municipality` VARCHAR(100) NOT NULL,
    `ward` VARCHAR(50) NOT NULL,
    `street` VARCHAR(255) DEFAULT NULL,
    `tag` VARCHAR(50) DEFAULT 'Home' COMMENT 'Home, Work, Other',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
    `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_addresses_user` (`user_id`),
    CONSTRAINT `fk_user_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- 5. Create order_status_history table
CREATE TABLE IF NOT EXISTS `order_status_history` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `note` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_osh_order` (`order_id`),
    CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- 6. Create banners table
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) DEFAULT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `image` VARCHAR(255) NOT NULL,
    `link_type` ENUM('product','category','url','none') DEFAULT 'none',
    `link_value` VARCHAR(500) DEFAULT NULL,
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
    `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_banners_active` (`is_active`),
    KEY `idx_banners_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- 7. Create rate_limits table (for auth rate limiting)
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL COMMENT 'IP or phone',
    `action` VARCHAR(50) NOT NULL COMMENT 'login, register, verify_otp',
    `attempts` INT(11) NOT NULL DEFAULT 1,
    `window_start` TIMESTAMP NULL DEFAULT current_timestamp(),
    `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_rl_lookup` (`identifier`, `action`),
    KEY `idx_rl_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- ───────────────────────────────────────────────────────────────────
-- Rollback (if needed):
-- DROP TABLE IF EXISTS `rate_limits`;
-- DROP TABLE IF EXISTS `banners`;
-- DROP TABLE IF EXISTS `order_status_history`;
-- DROP TABLE IF EXISTS `user_addresses`;
-- DROP TABLE IF EXISTS `otps`;
-- ALTER TABLE orders DROP INDEX idx_order_number, DROP COLUMN order_number;
-- ALTER TABLE users DROP COLUMN status;
-- ───────────────────────────────────────────────────────────────────
