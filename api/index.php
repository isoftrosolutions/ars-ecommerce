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
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

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

// Initialize logger
$logger = new Logger();

// Parse request
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path to get API path
$apiBase = '/ars/api';
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
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'products':
            require_once __DIR__ . '/products/ProductController.php';
            $controller = new ProductController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'categories':
            require_once __DIR__ . '/categories/CategoryController.php';
            $controller = new CategoryController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'orders':
            require_once __DIR__ . '/orders/OrderController.php';
            $controller = new OrderController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'customers':
            require_once __DIR__ . '/customers/CustomerController.php';
            $controller = new CustomerController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'reviews':
            require_once __DIR__ . '/reviews/ReviewController.php';
            $controller = new ReviewController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'coupons':
            require_once __DIR__ . '/coupons/CouponController.php';
            $controller = new CouponController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'contact':
            require_once __DIR__ . '/contact/ContactController.php';
            $controller = new ContactController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'settings':
            require_once __DIR__ . '/settings/SettingsController.php';
            $controller = new SettingsController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'dashboard':
            require_once __DIR__ . '/dashboard/DashboardController.php';
            $controller = new DashboardController();
            $response = $controller->handleRequest($requestMethod, $action);
            break;

        case 'uploads':
            require_once __DIR__ . '/uploads/UploadController.php';
            $controller = new UploadController();
            $response = $controller->handleRequest($requestMethod, $action);
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