-- Migration: 2026-05-22
-- Description: Add 'Returned' delivery status and create order_status_history table
-- Author: Kilo (Production-grade return workflow)
-- Date: 2026-05-22

-- =============================================
-- 1. Extend delivery_status ENUM to include 'Returned'
-- =============================================
ALTER TABLE `orders`
MODIFY COLUMN `delivery_status`
ENUM('Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled','Return Requested','Returned')
DEFAULT 'Pending';

-- =============================================
-- 2. Create order_status_history table for audit trail
--    (Used when delivery or payment status changes)
-- =============================================
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

-- Optional: Add comment for documentation
-- ALTER TABLE `order_status_history` COMMENT = 'Audit log for all order status changes (delivery & payment)';

-- =============================================
-- Rollback (if needed):
-- =============================================
-- ALTER TABLE `orders`
-- MODIFY COLUMN `delivery_status`
-- ENUM('Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled','Return Requested')
-- DEFAULT 'Pending';
--
-- DROP TABLE IF EXISTS `order_status_history`;
