-- Migration: 2026-07-13
-- Description: Add product variant system (attributes, values, variants)
-- Run against MySQL CLI after backing up DB

-- =============================================
-- 1. Product attributes (e.g., "Color", "Size")
-- =============================================
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- =============================================
-- 2. Product attribute values (e.g., "Red", "XL")
--    image_path stores per-value image (for colors)
-- =============================================
CREATE TABLE IF NOT EXISTS `product_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `product_attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- =============================================
-- 3. Product variants (concrete SKU-level combos)
--    price/discount_price/stock NULL = inherit from parent product
-- =============================================
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- =============================================
-- 4. Junction: variant → attribute values
-- =============================================
CREATE TABLE IF NOT EXISTS `product_variant_values` (
  `variant_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_id`, `attribute_value_id`),
  KEY `attribute_value_id` (`attribute_value_id`),
  CONSTRAINT `product_variant_values_ibfk_1` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_variant_values_ibfk_2` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- =============================================
-- 5. Cart items: add optional variant_id
-- =============================================
ALTER TABLE `cart_items`
  ADD COLUMN `variant_id` int(11) DEFAULT NULL AFTER `product_id`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

-- Update unique keys to include variant_id
ALTER TABLE `cart_items`
  DROP INDEX `unique_user_product`,
  DROP INDEX `unique_session_product`,
  ADD UNIQUE KEY `unique_user_product_variant` (`user_id`,`product_id`,`variant_id`),
  ADD UNIQUE KEY `unique_session_product_variant` (`session_id`,`product_id`,`variant_id`);

-- =============================================
-- 6. Order items: add optional variant_id
-- =============================================
ALTER TABLE `order_items`
  ADD COLUMN `variant_id` int(11) DEFAULT NULL AFTER `product_id`,
  ADD KEY `order_items_variant_id` (`variant_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

-- =============================================
-- Rollback (if needed):
-- =============================================
-- DROP TABLE IF EXISTS `product_variant_values`;
-- DROP TABLE IF EXISTS `product_variants`;
-- DROP TABLE IF EXISTS `product_attribute_values`;
-- DROP TABLE IF EXISTS `product_attributes`;
-- ALTER TABLE `cart_items` DROP FOREIGN KEY `cart_items_ibfk_3`;
-- ALTER TABLE `cart_items` DROP KEY `variant_id`;
-- ALTER TABLE `cart_items` DROP COLUMN `variant_id`;
-- ALTER TABLE `cart_items` DROP INDEX `unique_user_product_variant`, DROP INDEX `unique_session_product_variant`;
-- ALTER TABLE `cart_items` ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`);
-- ALTER TABLE `cart_items` ADD UNIQUE KEY `unique_session_product` (`session_id`,`product_id`);
