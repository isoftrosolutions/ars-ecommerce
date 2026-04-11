<?php
require_once 'includes/db.php';
try {
    $pdo->beginTransaction();

    // Update orders table
    $alterOrders = "ALTER TABLE orders 
        ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255) AFTER user_id,
        ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255) AFTER customer_name,
        ADD COLUMN IF NOT EXISTS customer_phone VARCHAR(20) AFTER customer_email,
        ADD COLUMN IF NOT EXISTS shipping_address TEXT AFTER customer_phone,
        ADD COLUMN IF NOT EXISTS shipping_city VARCHAR(100) AFTER shipping_address";
    
    // MariaDB/MySQL ALTER TABLE doesn't support IF NOT EXISTS in all versions, 
    // so we wrap in try-catch or check columns first.
    
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('customer_name', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255) AFTER user_id");
    }
    if (!in_array('customer_email', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_email VARCHAR(255) AFTER customer_name");
    }
    if (!in_array('customer_phone', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) AFTER customer_email");
    }
    if (!in_array('shipping_address', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT AFTER customer_phone");
    }
    if (!in_array('shipping_city', $columns)) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_city VARCHAR(100) AFTER shipping_address");
    }

    // Update order_items table
    $stmt = $pdo->query("DESCRIBE order_items");
    $itemColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('discount_price', $itemColumns)) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN discount_price DECIMAL(10,2) AFTER price");
    }

    $pdo->commit();
    echo "Orders schema updated successfully\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>
