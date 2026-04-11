<?php
require_once 'includes/db.php';
try {
    $stmt = $pdo->query("DESCRIBE orders");
    echo "COLUMNS IN 'orders' table:\n";
    foreach ($stmt->fetchAll() as $row) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    $stmt = $pdo->query("DESCRIBE order_items");
    echo "\nCOLUMNS IN 'order_items' table:\n";
    foreach ($stmt->fetchAll() as $row) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
