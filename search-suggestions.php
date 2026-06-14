<?php
/**
 * Search Suggestions API
 * AJAX endpoint for autocomplete search suggestions
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 1) {
    echo json_encode(['suggestions' => []]);
    exit;
}

try {
    // Try FULLTEXT search first for relevancy
    $suggestions = [];

    // Product name suggestions
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.image,
               MATCH(p.name, p.description) AGAINST(? IN BOOLEAN MODE) as relevance
        FROM products p
        WHERE MATCH(p.name, p.description) AGAINST(? IN BOOLEAN MODE)
           OR p.name LIKE ?
        ORDER BY relevance DESC, p.stock DESC
        LIMIT 8
    ");
    $likeTerm = $q . '%';
    $boolTerm = '+' . implode('* +', explode(' ', $q)) . '*';
    $stmt->execute([$boolTerm, $boolTerm, $likeTerm]);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $suggestions[] = [
            'type' => 'product',
            'id' => $p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'price' => (float)($p['discount_price'] ?: $p['price']),
            'image' => getProductImage($p['image']),
            'url' => url('/product/' . $p['slug'])
        ];
    }

    // Category suggestions (if few product results)
    if (count($suggestions) < 4) {
        $stmt = $pdo->prepare("
            SELECT id, name,
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
            FROM categories c
            WHERE name LIKE ? OR MATCH(name) AGAINST(? IN BOOLEAN MODE)
            LIMIT 4
        ");
        $stmt->execute([$q . '%', '+' . $q . '*']);
        $categories = $stmt->fetchAll();

        foreach ($categories as $c) {
            $suggestions[] = [
                'type' => 'category',
                'id' => $c['id'],
                'name' => $c['name'],
                'count' => $c['product_count'],
                'url' => url('/shop?category=' . $c['id'])
            ];
        }
    }

    echo json_encode(['suggestions' => $suggestions]);

} catch (PDOException $e) {
    // Fallback to simple LIKE if FULLTEXT index not available
    try {
        $suggestions = [];
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.image
            FROM products p
            WHERE p.name LIKE ? OR p.description LIKE ?
            GROUP BY p.id
            ORDER BY p.stock DESC
            LIMIT 8
        ");
        $term = '%' . $q . '%';
        $stmt->execute([$term, $term]);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $suggestions[] = [
                'type' => 'product',
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'price' => (float)($p['discount_price'] ?: $p['price']),
                'image' => getProductImage($p['image']),
                'url' => url('/product/' . $p['slug'])
            ];
        }

        echo json_encode(['suggestions' => $suggestions]);
    } catch (PDOException $e2) {
        echo json_encode(['suggestions' => []]);
    }
}
