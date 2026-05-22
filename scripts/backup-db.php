<?php
/**
 * Database Backup Script
 * Usage: php scripts/backup-db.php
 * Set up as a cron job on production:
 *   0 2 * * * /usr/bin/php /path/to/scripts/backup-db.php
 *
 * Environment variables (from .env or actual env):
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS
 *   BACKUP_DIR, BACKUP_RETENTION_DAYS (optional)
 */

$backupDir = getenv('BACKUP_DIR') ?: __DIR__ . '/../backups';
$retentionDays = (int)(getenv('BACKUP_RETENTION_DAYS') ?: 30);

$host  = getenv('DB_HOST') ?: 'localhost';
$db    = getenv('DB_NAME');
$user  = getenv('DB_USER');
$pass  = getenv('DB_PASS');

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
    fwrite(STDERR, "ERROR: Database credentials not found. Set DB_HOST, DB_NAME, DB_USER, DB_PASS.\n");
    exit(1);
}

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$filename = "{$db}_{$timestamp}.sql.gz";
$filepath = rtrim($backupDir, '/') . '/' . $filename;

$command = sprintf(
    'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers --events %s | gzip > %s',
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

$size = file_exists($filepath) ? round(filesize($filepath) / 1024 / 1024, 2) : 0;
echo "OK: $filename ({$size}MB)\n";

// Rotate old backups
$files = glob(rtrim($backupDir, '/') . "/{$db}_*.sql.gz");
$cutoff = time() - ($retentionDays * 86400);
$deleted = 0;
foreach ($files as $f) {
    if (filemtime($f) < $cutoff) {
        unlink($f);
        $deleted++;
    }
}
if ($deleted > 0) {
    echo "Cleaned up $deleted old backup(s)\n";
}
