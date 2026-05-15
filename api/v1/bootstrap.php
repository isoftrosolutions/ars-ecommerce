<?php
/**
 * Mobile API Bootstrap
 * ARS Easy Shopping — Customer-facing REST API v1
 *
 * Loads environment, DB, Composer autoload, CORS, and helpers.
 */

// ── Error reporting ──
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// ── CORS Headers ──
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// ── Preflight ──
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Shutdown handler for fatal errors ──
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
    }
});

// ── Load environment config ──
require_once __DIR__ . '/../../includes/env.php';

// ── Composer autoload ──
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// ── Database connection ──
require_once __DIR__ . '/../../includes/db.php';

// ── Global PDO reference ──
global $pdo;

// ── Load helpers ──
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/validator.php';
require_once __DIR__ . '/helpers/image_url.php';
require_once __DIR__ . '/helpers/jwt_helper.php';
require_once __DIR__ . '/helpers/auth_middleware.php';

// ── Load config ──
require_once __DIR__ . '/config/jwt.php';

// ── Custom error handler ──
set_error_handler(function ($severity, $message, $file, $line) {
    $debug = env('API_DEBUG', 'false') === 'true';
    $logLine = "[APIv1] PHP Error [$severity]: $message in $file on line $line";
    error_log($logLine, 3, __DIR__ . '/../../logs/api-v1.log');

    if ($severity & (E_USER_ERROR | E_RECOVERABLE_ERROR)) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        json_error('Internal server error', 500);
    }

    // In debug mode, let notices/warnings pass through for development
    if ($debug) {
        return false;
    }
    return true;
});
