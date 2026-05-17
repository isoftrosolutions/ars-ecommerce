<?php
/**
 * Database Migration Runner
 * Usage: php new_migration.php [--fresh]
 *   --fresh : drop and recreate migrations tracking table
 */

require_once __DIR__ . '/includes/db.php';

$migrationsDir = __DIR__ . '/api/v1/db/migrations';
$createTracking = true;

if (!is_dir($migrationsDir)) {
    die("Migrations directory not found: $migrationsDir\n");
}

// Create migrations tracking table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `filename` VARCHAR(255) NOT NULL UNIQUE,
            `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
} catch (PDOException $e) {
    die("Failed to create migrations table: " . $e->getMessage() . "\n");
}

if (isset($argv[1]) && $argv[1] === '--fresh') {
    $pdo->exec("DROP TABLE IF EXISTS `migrations`");
    $pdo->exec("
        CREATE TABLE `migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `filename` VARCHAR(255) NOT NULL UNIQUE,
            `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Fresh migrations table created.\n";
}

$files = glob($migrationsDir . '/*.sql');
sort($files);

echo "Checking migrations...\n";

$applied = $pdo->query("SELECT filename FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

foreach ($files as $file) {
    $filename = basename($file);

    if (in_array($filename, $applied)) {
        echo "  - $filename ... SKIPPED (already applied)\n";
        continue;
    }

    echo "  - Applying $filename ... ";

    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "FAILED (read error)\n";
        continue;
    }

    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);
        echo "OK\n";
    } catch (PDOException $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "Migrations complete.\n";
