<?php
/**
 * Seed Sample Data Script
 * Run this once to add sample products and categories
 * Access: http://localhost/ars/seed-data.php
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Seeding Sample Data...</h2>";

try {
    // Add categories
    $categories = [
        ['name' => 'Electronics', 'slug' => 'electronics'],
        ['name' => 'Fashion', 'slug' => 'fashion'],
        ['name' => 'Home & Living', 'slug' => 'home-living'],
        ['name' => 'Beauty & Care', 'slug' => 'beauty-care'],
        ['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors']
    ];

    foreach ($categories as $cat) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
        $stmt->execute([$cat['name'], $cat['slug']]);
    }
    echo "<p>✓ Categories seeded</p>";

    // Add products
    $products = [
        ['name' => 'Samsung Galaxy A14', 'slug' => 'samsung-galaxy-a14', 'desc' => '6.6 inch display, 50MP camera, 5000mAh battery', 'price' => 24999, 'discount' => 21999, 'cat' => 1, 'stock' => 25, 'featured' => 1],
        ['name' => 'Sony WH-1000XM5 Headphones', 'slug' => 'sony-wh1000xm5', 'desc' => 'Premium noise cancelling wireless headphones', 'price' => 34999, 'discount' => 29999, 'cat' => 1, 'stock' => 15, 'featured' => 1],
        ['name' => 'JBL Flip 6 Speaker', 'slug' => 'jbl-flip6', 'desc' => 'Portable waterproof bluetooth speaker', 'price' => 12999, 'discount' => 9999, 'cat' => 1, 'stock' => 30, 'featured' => 0],
        ['name' => 'Men Cotton T-Shirt Pack', 'slug' => 'men-cotton-tshirt-pack', 'desc' => 'Premium cotton t-shirts, comfortable', 'price' => 1599, 'discount' => 999, 'cat' => 2, 'stock' => 100, 'featured' => 1],
        ['name' => 'Women Kurti Set', 'slug' => 'women-kurti-set', 'desc' => 'Elegant kurti with dupatta', 'price' => 2499, 'discount' => 1999, 'cat' => 2, 'stock' => 50, 'featured' => 1],
        ['name' => 'Classic Denim Jeans', 'slug' => 'classic-denim-jeans', 'desc' => 'Slim fit denim jeans for men', 'price' => 2999, 'discount' => 2499, 'cat' => 2, 'stock' => 40, 'featured' => 0],
        ['name' => 'Cotton Bed Sheet Set', 'slug' => 'cotton-bed-sheet-set', 'desc' => 'Double bed bedsheet with 2 pillow covers', 'price' => 1999, 'discount' => 1499, 'cat' => 3, 'stock' => 35, 'featured' => 1],
        ['name' => 'LED Desk Lamp', 'slug' => 'led-desk-lamp', 'desc' => 'Adjustable brightness LED lamp with USB', 'price' => 999, 'discount' => 699, 'cat' => 3, 'stock' => 60, 'featured' => 0],
        ['name' => 'Vitamin C Face Serum', 'slug' => 'vitamin-c-face-serum', 'desc' => 'Brightening face serum with hyaluronic acid', 'price' => 899, 'discount' => 649, 'cat' => 4, 'stock' => 80, 'featured' => 1],
        ['name' => 'Yoga Mat Premium', 'slug' => 'yoga-mat-premium', 'desc' => 'Non-slip exercise mat, 6mm thickness', 'price' => 1299, 'discount' => 899, 'cat' => 5, 'stock' => 55, 'featured' => 1],
    ];

    foreach ($products as $p) {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)");
        $stmt->execute([$p['name'], $p['slug'], $p['desc'], $p['price'], $p['discount'], $p['cat'], $p['stock'], $p['featured']]);
    }
    echo "<p>✓ Products seeded</p>";

    // Add shipping settings
    $settings = [
        ['key' => 'shipping_cost', 'value' => '150'],
        ['key' => 'free_shipping_threshold', 'value' => '5000'],
        ['key' => 'estimated_delivery_days', 'value' => '3-5']
    ];
    foreach ($settings as $s) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $stmt->execute([$s['key'], $s['value']]);
    }
    echo "<p>✓ Shipping settings added</p>";

    // Verify
    $prodCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    echo "<h3>Success!</h3>";
    echo "<ul>";
    echo "<li>Categories: $catCount</li>";
    echo "<li>Products: $prodCount</li>";
    echo "</ul>";
    echo "<p><a href='" . url('/') . "'>Go to Homepage</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>