-- =============================================
-- Sample Data for ARS E-commerce
-- Run this in phpMyAdmin or MySQL CLI
-- =============================================

-- First, add more categories
INSERT INTO categories (id, name, slug) VALUES 
(2, 'Fashion', 'fashion'),
(3, 'Home & Living', 'home-living'),
(4, 'Beauty & Care', 'beauty-care'),
(5, 'Sports & Outdoors', 'sports-outdoors')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Add sample products (adjust prices and stock as needed)
INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, image, sku, is_featured) VALUES 
-- Electronics (Category 1)
('Samsung Galaxy A14 Smartphone', 'samsung-galaxy-a14', '6.6 inch display, 50MP camera, 5000mAh battery', 24999, 21999, 1, 25, 'samsung-a14.jpg', 'SM-A14-001', 1),
('Sony WH-1000XM5 Headphones', 'sony-wh1000xm5', 'Premium noise cancelling wireless headphones', 34999, 29999, 1, 15, 'sony-headphones.jpg', 'SONY-WH5-001', 1),
('JBL Flip 6 Speaker', 'jbl-flip6', 'Portable waterproof bluetooth speaker with rich bass', 12999, 9999, 1, 30, 'jbl-flip6.jpg', 'JBL-FLIP6-001', 0),

-- Fashion (Category 2)
('Men Cotton T-Shirt Pack of 3', 'men-cotton-tshirt-pack', 'Premium cotton t-shirts, comfortable and breathable', 1599, 999, 2, 100, 'tshirt-pack.jpg', 'TSHIRT-MEN-001', 1),
('Women Kurti Set', 'women-kurti-set', 'Elegant kurti with dupatta, perfect for festivals', 2499, 1999, 2, 50, 'women-kurti.jpg', 'KURTI-W-001', 1),
('Classic Denim Jeans', 'classic-denim-jeans', 'Slim fit denim jeans for men', 2999, 2499, 2, 40, 'denim-jeans.jpg', 'JEANS-MEN-001', 0),

-- Home & Living (Category 3)
('Cotton Bed Sheet Set', 'cotton-bed-sheet-set', 'Double bed bedsheet with 2 pillow covers', 1999, 1499, 3, 35, 'bedsheet.jpg', 'BEDsheet-001', 1),
('LED Desk Lamp', 'led-desk-lamp', 'Adjustable brightness LED lamp with USB port', 999, 699, 3, 60, 'desk-lamp.jpg', 'LAMPSD-001', 0),
('Kitchen Organizer Set', 'kitchen-organizer-set', '6 piece plastic organizer for kitchen', 899, 599, 3, 45, 'kitchen-set.jpg', 'KITCHEN-001', 0),

-- Beauty & Care (Category 4)
('Vitamin C Face Serum', 'vitamin-c-face-serum', 'Brightening face serum with hyaluronic acid', 899, 649, 4, 80, 'face-serum.jpg', 'SERUM-VC-001', 1),
('Hair Dryer 2000W', 'hair-dryer-2000w', 'Professional hair dryer with multiple settings', 1999, 1499, 4, 40, 'hair-dryer.jpg', 'DRYER-H-001', 0),

-- Sports (Category 5)
('Yoga Mat Premium', 'yoga-mat-premium', 'Non-slip exercise mat, 6mm thickness', 1299, 899, 5, 55, 'yoga-mat.jpg', 'YOGA-001', 1),
('Adjustable Dumbbell Set', 'adjustable-dumbbell-set', '5-25kg adjustable weight set', 8999, 7499, 5, 20, 'dumbbell.jpg', 'DUMBBELL-001', 0),
('Sports Water Bottle', 'sports-water-bottle', '1.5L insulated steel water bottle', 899, 599, 5, 70, 'water-bottle.jpg', 'BOTTLE-S-001', 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), price = VALUES(price);

-- Set product images (using placeholder URLs)
UPDATE products SET image = COALESCE(image, 'placeholder.jpg') WHERE image IS NULL OR image = '';

-- Insert product images for gallery (optional)
INSERT INTO product_images (product_id, image_path, is_primary) VALUES 
(3, 'jbl-flip6-1.jpg', 0),
(3, 'jbl-flip6-2.jpg', 0),
(5, 'kurti-1.jpg', 0),
(5, 'kurti-2.jpg', 0)
ON DUPLICATE KEY UPDATE image_path = VALUES(image_path);

-- Verify
SELECT 'Products added:' as info, COUNT(*) as total FROM products;
SELECT 'Categories added:' as info, COUNT(*) as total FROM categories;