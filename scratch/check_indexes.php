<?php
require_once 'includes/db.php';
try {
    echo "INDEXES on 'orders' table:\n";
    $stmt = $pdo->query("SHOW INDEX FROM orders");
    foreach ($stmt->fetchAll() as $row) {
        echo $row['Key_name'] . " (" . $row['Column_name'] . ")\n";
    }
    
    echo "\nINDEXES on 'users' table:\n";
    $stmt = $pdo->query("SHOW INDEX FROM users");
    foreach ($stmt->fetchAll() as $row) {
        echo $row['Key_name'] . " (" . $row['Column_name'] . ")\n";
    }
    
    echo "\nINDEXES on 'products' table:\n";
    $stmt = $pdo->query("SHOW INDEX FROM products");
    foreach ($stmt->fetchAll() as $row) {
        echo $row['Key_name'] . " (" . $row['Column_name'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
