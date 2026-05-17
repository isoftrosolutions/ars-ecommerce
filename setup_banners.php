<?php
$_SERVER['REQUEST_URI'] = '/api/v1/banners';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
require_once __DIR__ . '/includes/db.php';
global $pdo;

header('Content-Type: text/plain');

if (!$pdo) { die("No PDO connection\n"); }

$pdo->exec("CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL DEFAULT '',
    title VARCHAR(255) DEFAULT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    link_type ENUM('none','category','product','url') DEFAULT 'none',
    link_value VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "Banners table ready\n";

$banners = [
    ['banner1.jpg', 'Summer Sale', 'Up to 50% off on selected items', 'category', '1', 1],
    ['banner2.jpg', 'New Arrivals', 'Check out the latest products', 'none', '', 2],
    ['banner3.jpg', 'Free Delivery', 'On orders over Rs. 1,000', 'none', '', 3],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO banners (image, title, subtitle, link_type, link_value, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
$count = 0;
foreach ($banners as $b) {
    $stmt->execute($b);
    if ($stmt->rowCount()) $count++;
}
echo "$count sample banners inserted\n";
