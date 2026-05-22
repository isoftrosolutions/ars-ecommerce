<?php
/**
 * Cron Job Setup Script
 * Usage: php scripts/setup-cron.php
 *
 * Adds the daily database backup cron job.
 * Safe to run multiple times — won't duplicate.
 */

$cronLine = '0 2 * * * /usr/bin/php ' . __DIR__ . '/backup-db.php';

exec('crontab -l 2>/dev/null', $existing, $code);
$existingStr = implode("\n", $existing);

if (strpos($existingStr, $cronLine) !== false) {
    echo "Cron job already exists. Nothing to do.\n";
    exit(0);
}

$newCron = trim($existingStr . "\n" . $cronLine . "\n");

$tmpFile = tempnam(sys_get_temp_dir(), 'cron_');
file_put_contents($tmpFile, $newCron . "\n");
exec('crontab ' . escapeshellarg($tmpFile), $out, $exitCode);
unlink($tmpFile);

if ($exitCode !== 0) {
    fwrite(STDERR, "ERROR: Failed to install cron job.\n");
    exit(1);
}

echo "Cron job installed:\n  $cronLine\n";
