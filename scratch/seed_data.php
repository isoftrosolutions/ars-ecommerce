<?php
require_once __DIR__ . '/../includes/db.php';

echo "Seeding sample data...\n";

try {
    // 1. Seed Categories
    $categories = [
        ['Electronics', 'electronics'],
        ['Fashion', 'fashion'],
        ['Home & Garden', 'home-garden'],
        ['Beauty', 'beauty'],
        ['Sports', 'sports'],
        ['Accessories', 'accessories']
    ];

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE categories;");
    $pdo->exec("TRUNCATE TABLE products;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "Categories seeded.\n";

    // Get Category IDs
    $cat_ids = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);

    // 2. Seed Products
    $products = [
        ['Premium Wireless Headphones', 'premium-wireless-headphones', 'Immersive sound with noise cancellation.', 15000.00, 12500.00, 'Electronics', 50, 'headphones.jpg', 'ELEC-001', 1],
        ['Designer Leather Bag', 'designer-leather-bag', 'Handcrafted genuine leather bag.', 8500.00, NULL, 'Fashion', 20, 'leather-bag.jpg', 'FASH-001', 1],
        ['Smart Watch Series 7', 'smart-watch-7', 'Stay connected on the go.', 4500.00, 3999.00, 'Electronics', 30, 'smartwatch.jpg', 'ELEC-002', 1],
        ['Cotton Summer Dress', 'cotton-summer-dress', 'Light and breathable cotton dress.', 2500.00, NULL, 'Fashion', 40, 'dress.jpg', 'FASH-002', 0],
        ['Modern Desk Lamp', 'modern-desk-lamp', 'Sleek design for your workspace.', 1200.00, 999.00, 'Home & Garden', 100, 'lamp.jpg', 'HOME-001', 0],
        ['Mechanical Gaming Keyboard', 'mechanical-keyboard', 'RGB backlit mechanical keys.', 5500.00, 4800.00, 'Electronics', 15, 'keyboard.jpg', 'ELEC-003', 1],
        ['Natural Face Serum', 'face-serum', 'Glow with organic ingredients.', 1800.00, NULL, 'Beauty', 60, 'serum.jpg', 'BEAU-001', 0],
        ['Yoga Mat Pro', 'yoga-mat-pro', 'Non-slip high density foam.', 2200.00, 1950.00, 'Sports', 25, 'yoga-mat.jpg', 'SPOR-001', 0]
    ];

    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, image, sku, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($products as $p) {
        $cat_name = $p[5];
        $cat_id = array_search($cat_name, $cat_ids);
        $p[5] = $cat_id;
        $stmt->execute($p);
    }
    echo "Products seeded.\n";

    echo "Seeding complete! Visit http://localhost/ARS/ to see the results.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
