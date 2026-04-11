<?php
/**
 * PHPUnit Bootstrap
 * Easy Shopping A.R.S
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include required files
require_once BASE_PATH . '/includes/db.php';
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/email-service.php';

// Set up test environment
if (!file_exists(BASE_PATH . '/logs')) {
    mkdir(BASE_PATH . '/logs', 0755, true);
}

// Mock session for testing (if needed)
// You can add session mocking here if required for specific tests

// Clean up any existing test data before running tests
function cleanupTestData() {
    global $pdo;

    try {
        // Remove test users
        $pdo->prepare("DELETE FROM users WHERE email LIKE ? OR email LIKE ? OR email LIKE ?")
            ->execute(['test%', 'signup-test%', 'login-test%']);

        // Clear any test sessions/tokens
        $pdo->prepare("UPDATE users SET remember_token = NULL, reset_token = NULL WHERE remember_token IS NOT NULL OR reset_token IS NOT NULL")
            ->execute();
    } catch (Exception $e) {
        // Ignore cleanup errors in bootstrap
    }
}

// Register cleanup function
register_shutdown_function('cleanupTestData');
?>