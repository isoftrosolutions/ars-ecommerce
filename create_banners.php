<?php
/**
 * Create banners table and seed sample data.
 *
 * Usage (from project root):
 *   php create_banners.php
 *
 * Safe to re-run — skips if banners already exist.
 * Delete this file after successful execution.
 */

$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    die("✗ .env not found at $envPath\n");
}

$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
if (!$env) {
    die("✗ Failed to parse .env file\n");
}

$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';

if (empty($dbname)) {
    die("✗ DB_NAME not set in .env\n");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✓ Connected to database: $dbname\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// 1. Create banners table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `banners` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) DEFAULT NULL,
        `subtitle` VARCHAR(255) DEFAULT NULL,
        `image` VARCHAR(255) NOT NULL,
        `link_type` ENUM('product','category','url','none') DEFAULT 'none',
        `link_value` VARCHAR(500) DEFAULT NULL,
        `sort_order` INT(11) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
        `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_banners_active` (`is_active`),
        KEY `idx_banners_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci
");
echo "✓ banners table ready\n";

// 2. Check if data already exists
$count = $pdo->query("SELECT COUNT(*) FROM banners")->fetchColumn();
if ($count > 0) {
    echo "✓ Banners already exist ($count records) — skipping seed\n";
    exit(0);
}

// 3. Ensure uploads/banners directory
$bannerDir = __DIR__ . '/public/uploads/banners';
if (!is_dir($bannerDir)) {
    mkdir($bannerDir, 0755, true);
    echo "✓ Created directory: public/uploads/banners\n";
}

// 4. Create sample banner images (1200x600 PNGs via GD)
function createBannerImage($filepath, $bgColor, $accentColor, $title, $subtitle) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }
    $width = 1200;
    $height = 600;
    $img = imagecreatetruecolor($width, $height);

    $bg = imagecolorallocate($img, $bgColor[0], $bgColor[1], $bgColor[2]);
    $accent = imagecolorallocate($img, $accentColor[0], $accentColor[1], $accentColor[2]);
    $white = imagecolorallocate($img, 255, 255, 255);

    imagefill($img, 0, 0, $bg);
    imagefilledrectangle($img, 0, 0, 20, $height, $accent);

    for ($i = 0; $i < 5; $i++) {
        $cy = 80 + $i * 100;
        $r = 50 + rand(-15, 15);
        $col = imagecolorallocatealpha($img, $accentColor[0], $accentColor[1], $accentColor[2], 70 + rand(-20, 20));
        imagefilledellipse($img, 900 + rand(-80, 80), $cy, $r * 2, $r * 2, $col);
    }

    imagestring($img, 5, 60, 220, $title, $white);
    imagestring($img, 3, 60, 260, $subtitle, $white);

    imagepng($img, $filepath);
    imagedestroy($img);
    return true;
}

$banners = [
    [
        'title'    => 'Summer Sale — Up to 50% Off',
        'subtitle' => 'Shop the season\'s hottest deals on electronics, fashion & more.',
        'bg'       => [30, 30, 50],
        'accent'   => [255, 107, 53],
    ],
    [
        'title'    => 'New Arrivals — Fresh Drops Weekly',
        'subtitle' => 'Be the first to discover trending products curated just for you.',
        'bg'       => [20, 40, 60],
        'accent'   => [0, 200, 200],
    ],
    [
        'title'    => 'Free Shipping on Orders Over ₹499',
        'subtitle' => 'Limited time offer. No coupon needed — applied automatically.',
        'bg'       => [50, 20, 20],
        'accent'   => [255, 215, 0],
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO banners (title, subtitle, image, link_type, link_value, sort_order, is_active)
    VALUES (?, ?, ?, 'none', NULL, ?, 1)
");

foreach ($banners as $i => $banner) {
    $filename = 'banner-' . ($i + 1) . '.png';
    $filepath = $bannerDir . '/' . $filename;

    $gdAvailable = createBannerImage($filepath, $banner['bg'], $banner['accent'], $banner['title'], $banner['subtitle']);
    if (!$gdAvailable) {
        echo "⚠ GD not available — skipping image creation (DB records still inserted)\n";
    }

    $stmt->execute([$banner['title'], $banner['subtitle'], $filename, $i + 1]);
    echo "✓ Banner {$i}: {$banner['title']}\n";
}

echo "\n✅ All " . count($banners) . " banners created successfully!\n";
