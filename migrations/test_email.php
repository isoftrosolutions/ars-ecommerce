<?php
/**
 * Test Email Script
 * Run ONCE to verify SMTP is working
 * DELETE after testing!
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

$to   = 'mind59024@gmail.com';
$name = 'Test User';

$sent = getEmailService()->sendWelcome($to, $name);

$isCli = (php_sapi_name() === 'cli');
$br = $isCli ? "\n" : "<br>";

if ($sent) {
    echo "✓ Welcome email sent to $to$br";
} else {
    echo "✗ Failed to send email to $to$br";
    echo "  Check logs/emails.log for details.$br";
}
