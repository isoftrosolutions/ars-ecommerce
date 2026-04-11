<?php
/**
 * Dynamic XML Sitemap
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

$base = rtrim($base_url, '/');

// Static pages
$static = [
    ['loc' => '',              'priority' => '1.0',  'changefreq' => 'daily'],
    ['loc' => '/shop',         'priority' => '0.9',  'changefreq' => 'daily'],
    ['loc' => '/categories',   'priority' => '0.8',  'changefreq' => 'weekly'],
    ['loc' => '/deals',        'priority' => '0.8',  'changefreq' => 'daily'],
    ['loc' => '/todays-deal',  'priority' => '0.8',  'changefreq' => 'daily'],
    ['loc' => '/new-arrivals', 'priority' => '0.7',  'changefreq' => 'daily'],
    ['loc' => '/contact',      'priority' => '0.6',  'changefreq' => 'monthly'],
    ['loc' => '/about',        'priority' => '0.6',  'changefreq' => 'monthly'],
    ['loc' => '/support',      'priority' => '0.6',  'changefreq' => 'monthly'],
    ['loc' => '/shipping',     'priority' => '0.5',  'changefreq' => 'monthly'],
    ['loc' => '/returns',      'priority' => '0.5',  'changefreq' => 'monthly'],
    ['loc' => '/privacy',      'priority' => '0.4',  'changefreq' => 'yearly'],
    ['loc' => '/terms',        'priority' => '0.4',  'changefreq' => 'yearly'],
];

// Fetch products
$products = [];
try {
    $stmt = $pdo->query("SELECT slug, updated_at FROM products WHERE stock > 0 ORDER BY updated_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {}

echo "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

foreach ($static as $page) {
    echo "\n  <url>";
    echo "\n    <loc>" . htmlspecialchars($base . $page['loc']) . "</loc>";
    echo "\n    <changefreq>" . $page['changefreq'] . "</changefreq>";
    echo "\n    <priority>" . $page['priority'] . "</priority>";
    echo "\n  </url>";
}

foreach ($products as $p) {
    $lastmod = $p['updated_at'] ? date('Y-m-d', strtotime($p['updated_at'])) : date('Y-m-d');
    echo "\n  <url>";
    echo "\n    <loc>" . htmlspecialchars($base . '/product/' . $p['slug']) . "</loc>";
    echo "\n    <lastmod>" . $lastmod . "</lastmod>";
    echo "\n    <changefreq>weekly</changefreq>";
    echo "\n    <priority>0.8</priority>";
    echo "\n  </url>";
}

echo "\n</urlset>\n";
