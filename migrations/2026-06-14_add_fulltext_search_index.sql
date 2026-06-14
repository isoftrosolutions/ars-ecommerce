-- Migration: 2026-06-14
-- Description: Add FULLTEXT index on products for fast relevancy-based search
-- Author: System
-- Date: 2026-06-14

-- =============================================
-- 1. Add FULLTEXT index on products table
-- =============================================
ALTER TABLE `products`
ADD FULLTEXT INDEX `ft_products_search` (`name`, `description`);

-- =============================================
-- 2. Add FULLTEXT index on categories for search
-- =============================================
ALTER TABLE `categories`
ADD FULLTEXT INDEX `ft_categories_name` (`name`);

-- =============================================
-- Rollback (if needed):
-- =============================================
-- ALTER TABLE `products` DROP INDEX `ft_products_search`;
-- ALTER TABLE `categories` DROP INDEX `ft_categories_name`;
