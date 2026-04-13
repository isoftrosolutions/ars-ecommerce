<?php
/**
 * Admin API Entry Point
 * Production-ready REST API for Easy Shopping A.R.S Admin Panel
 */

// Disable HTML error output for API immediately to prevent HTML leaking into JSON
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Set JSON content type header first
header('Content-Type: application/json');

// Handle preflight OPTIONS request (before includes to be fast)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(200);
    exit;
}

// Include required files (must come before any env() calls)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/BaseController.php';

// Now that env() is available, set CORS headers
$allowed_origin = env('APP_URL', ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
header('Access-Control-Allow-Origin: ' . $allowed_origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

// Initialize logger
$logger = new Logger();

// Parse request
$requestMethod = $_SERVER['REQUEST_METHOD'];
// Parse path to get endpoint and action
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME']; 
$apiBase = dirname($scriptName);
$path = parse_url($requestUri, PHP_URL_PATH);

if (strpos($path, $apiBase) === 0) {
    $path = substr($path, strlen($apiBase));
}

// Remove leading slash
$path = ltrim($path, '/');

// Parse path and query parameters
$pathParts = explode('/', $path);
$endpoint = $pathParts[0] ?? '';
$action = $pathParts[1] ?? 'index';

/**
 * Custom Error Handler to convert PHP errors to JSON
 */
set_error_handler(function($severity, $message, $file, $line) use ($logger) {
    if (!(error_reporting() & $severity)) return;
    
    $logger->error("PHP Error [$severity]: $message in $file on line $line");
    
    // For fatal-like errors, return JSON and exit
    if ($severity & (E_USER_ERROR | E_RECOVERABLE_ERROR)) {
        Response::error("Internal Server Error: $message", 500);
    }
});

/**
 * Shutdown handler to catch fatal errors that set_error_handler cannot
 */
register_shutdown_function(function() use ($logger) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logger->error("PHP Fatal: {$error['message']} in {$error['file']} on line {$error['line']}");
        // Clear any buffered HTML output
        if (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error'
        ]);
    }
});

// Route requests to appropriate handlers
try {
    $response = null;

    switch ($endpoint) {
        case 'auth':
            require_once __DIR__ . '/auth/AuthController.php';
            $controller = new AuthController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'products':
            require_once __DIR__ . '/products/ProductController.php';
            $controller = new ProductController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'categories':
            require_once __DIR__ . '/categories/CategoryController.php';
            $controller = new CategoryController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'orders':
            require_once __DIR__ . '/orders/OrderController.php';
            $controller = new OrderController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'customers':
            require_once __DIR__ . '/customers/CustomerController.php';
            $controller = new CustomerController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'reviews':
            require_once __DIR__ . '/reviews/ReviewController.php';
            $controller = new ReviewController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'coupons':
            require_once __DIR__ . '/coupons/CouponController.php';
            $controller = new CouponController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'contact':
            require_once __DIR__ . '/contact/ContactController.php';
            $controller = new ContactController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'settings':
            require_once __DIR__ . '/settings/SettingsController.php';
            $controller = new SettingsController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'dashboard':
            require_once __DIR__ . '/dashboard/DashboardController.php';
            $controller = new DashboardController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'uploads':
            require_once __DIR__ . '/uploads/UploadController.php';
            $controller = new UploadController();
            $controller->handleRequest($requestMethod, $action);
            break;

        case 'health':
            require_once __DIR__ . '/health/HealthController.php';
            $controller = new HealthController();
            $controller->handleRequest($requestMethod, $action);
            break;

        default:
            Response::error('Endpoint not found', 404);
            break;
    }

} catch (Throwable $e) {
    $userId = null;
    if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
        $userId = $_SESSION['user']['id'] ?? null;
    }

    $logger->error('API Uncaught Error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'endpoint' => $endpoint,
        'action' => $action,
        'method' => $requestMethod,
        'user_id' => $userId
    ]);

    Response::error('Internal server error', 500);
}
?>