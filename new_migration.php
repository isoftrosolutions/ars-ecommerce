<?php
/**
 * Database Migration Runner
 * Usage: php new_migration.php
 */

require_once __DIR__ . '/includes/db.php';

$migrationsDir = __DIR__ . '/api/v1/db/migrations';

if (!is_dir($migrationsDir)) {
    die("Migrations directory not found: $migrationsDir\n");
}

$files = glob($migrationsDir . '/*.sql');
sort($files);

echo "Running migrations...\n";

foreach ($files as $file) {
    $filename = basename($file);
    echo "  - Applying $filename... ";

    $sql = file_get_contents($file);
    if ($sql === false) {
        echo "FAILED (read error)\n";
        continue;
    }

    try {
        $pdo->exec($sql);
        echo "OK\n";
    } catch (PDOException $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "Migrations complete.\n";
