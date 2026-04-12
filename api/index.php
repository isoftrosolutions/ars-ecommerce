<?php
/**
 * Admin API Entry Point
 * Production-ready REST API for Easy Shopping A.R.S Admin Panel
 */

// Enable error reporting in development
if (getenv('APP_ENV') !== 'production') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Set headers for API responses
header('Content-Type: application/json');
$allowed_origin = env('APP_URL', ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
header('Access-Control-Allow-Origin: ' . $allowed_origin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/BaseController.php';

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

} catch (Exception $e) {
    $logger->error('API Error: ' . $e->getMessage(), [
        'endpoint' => $endpoint,
        'action' => $action,
        'method' => $requestMethod,
        'user_id' => $_SESSION['user']['id'] ?? null
    ]);

    Response::error('Internal server error', 500);
}
?>