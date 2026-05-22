<?php
/**
 * Database Backup Script
 * Usage: php scripts/backup-db.php
 * Saves db-back.sql to the backup directory every run (overwrites).
 *
 * Environment variables (from .env or actual env):
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS
 *   BACKUP_DIR (optional, default: <project>/backups)
 */

$backupDir = getenv('BACKUP_DIR') ?: __DIR__ . '/../backups';

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

// Try loading from .env if not set via environment
if (empty($db) || empty($user)) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value);
            if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
            elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
    }
}

if (empty($db) || empty($user)) {
    fwrite(STDERR, "ERROR: Database credentials not found.\n");
    exit(1);
}

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filepath = rtrim($backupDir, '/') . '/db-back.sql';

$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers --events %s > %s',
    escapeshellarg($host),
    escapeshellarg($user),
    escapeshellarg($pass),
    escapeshellarg($db),
    escapeshellarg($filepath)
);

$output = [];
$exitCode = 0;
exec($command, $output, $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "ERROR: Backup failed (exit code $exitCode)\n");
    if (file_exists($filepath)) unlink($filepath);
    exit(1);
}

$size = round(filesize($filepath) / 1024, 2);
echo "OK: db-back.sql ({$size}KB)\n";
