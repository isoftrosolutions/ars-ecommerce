<?php
require_once 'includes/db.php';
try {
    echo "Adding indexes...\n";
    $pdo->exec("CREATE INDEX idx_orders_created_at ON orders(created_at)");
    $pdo->exec("CREATE INDEX idx_orders_payment_status ON orders(payment_status)");
    $pdo->exec("CREATE INDEX idx_users_role_created ON users(role, created_at)");
    echo "Indexes added successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
