<?php
/**
 * Backend logic for the Public Landing Page (index.php)
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    // Fetch latest 8 products with their category names
    $stmt = $pdo->query("SELECT p.*, c.name as cat_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.created_at DESC 
                         LIMIT 8");
    $latest_products = $stmt->fetchAll();

    // Fetch top 6 categories
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    // Fallback for empty/uninitialized database
    $latest_products = [];
    $categories = [];
    $logic_error = $e->getMessage();
}
?>
