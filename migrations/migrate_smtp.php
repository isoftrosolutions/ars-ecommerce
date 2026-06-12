<?php
/**
 * SMTP Migration Script
 * Switch from Gmail SMTP to Custom Domain Email
 * 
 * Run ONCE: php migrations/migrate_smtp.php
 * Or access via browser: https://easyshoppingars.com/migrations/migrate_smtp.php
 * DELETE this file after running!
 */

require_once __DIR__ . '/../includes/db.php';

$updates = [
    'smtp_host'       => 'mail.easyshoppingars.com',
    'smtp_username'   => 'info@easyshoppingars.com',
    'smtp_password'   => 'ts,M^0o,3MnmVF*Q',
    'smtp_port'       => '587',
    'smtp_encryption' => 'tls',
    'support_email'   => 'info@easyshoppingars.com',
    'from_email'      => 'info@easyshoppingars.com',
];

$isCli = (php_sapi_name() === 'cli');
$br = $isCli ? "\n" : "<br>";

echo ($isCli ? '' : '<pre>');
echo "Migrating SMTP from Gmail to Custom Domain...$br";

$stmt = $pdo->prepare(
    "INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
);

foreach ($updates as $key => $value) {
    $stmt->execute([$key, $value]);
    echo "  ✓ $key = $value$br";
}

echo "$br Done! SMTP is now configured for info@easyshoppingars.com$br";
echo " DELETE this file after confirming it works.$br";
echo ($isCli ? '' : '</pre>');
