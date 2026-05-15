<?php
/**
 * Mobile API Router
 * ARS Easy Shopping — Customer-facing REST API v1
 */

require_once __DIR__ . '/bootstrap.php';

// ── Route table ──
// Each entry: [method, path_pattern, controller_file, controller_class, action]
$routes = [
    // Auth (public)
    ['POST', '/auth/register',           'AuthController.php', 'AuthController', 'register'],
    ['POST', '/auth/verify-otp',         'AuthController.php', 'AuthController', 'verifyOtp'],
    ['POST', '/auth/login',              'AuthController.php', 'AuthController', 'login'],
    ['POST', '/auth/resend-otp',         'AuthController.php', 'AuthController', 'resendOtp'],

    // Auth (protected)
    ['POST', '/auth/logout',             'AuthController.php', 'AuthController', 'logout'],
    ['POST', '/auth/change-password',    'AuthController.php', 'AuthController', 'changePassword'],

    // Products (public)
    ['GET',  '/products',                'ProductController.php', 'ProductController', 'index'],
    ['GET',  '/products/featured',       'ProductController.php', 'ProductController', 'featured'],
    ['GET',  '/products/new-arrivals',   'ProductController.php', 'ProductController', 'newArrivals'],
    ['GET',  '/products/{id}',           'ProductController.php', 'ProductController', 'show'],

    // Categories (public)
    ['GET',  '/categories',              'CategoryController.php', 'CategoryController', 'index'],

    // Banners (public)
    ['GET',  '/banners',                 'CategoryController.php', 'CategoryController', 'banners'],

    // Orders (protected)
    ['POST', '/orders',                  'OrderController.php', 'OrderController', 'store'],
    ['GET',  '/orders',                  'OrderController.php', 'OrderController', 'index'],
    ['GET',  '/orders/{id}',             'OrderController.php', 'OrderController', 'show'],

    // User (protected)
    ['GET',  '/user/me',                 'UserController.php', 'UserController', 'me'],
    ['PATCH','/user/me',                 'UserController.php', 'UserController', 'updateMe'],

    // Addresses (protected)
    ['GET',  '/user/addresses',          'AddressController.php', 'AddressController', 'index'],
    ['POST', '/user/addresses',          'AddressController.php', 'AddressController', 'store'],
    ['PATCH','/user/addresses/{id}',     'AddressController.php', 'AddressController', 'update'],
    ['PATCH','/user/addresses/{id}/set-default', 'AddressController.php', 'AddressController', 'setDefault'],
    ['DELETE','/user/addresses/{id}',    'AddressController.php', 'AddressController', 'destroy'],
];

// ── Parse request ──
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Strip base path to get the route
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$routePath = substr($requestUri, strlen($scriptDir));
$routePath = rtrim($routePath, '/') ?: '/';

// ── Match route ──
$matched = false;

foreach ($routes as $route) {
    list($method, $pattern, $file, $class, $action) = $route;

    if ($requestMethod !== $method) {
        continue;
    }

    // Convert route pattern to regex: {id} -> (?P<id>[^/]+)
    $regex = '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern) . '$#';

    if (preg_match($regex, $routePath, $matches)) {
        $matched = true;

        // Extract named params
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        try {
            require_once __DIR__ . '/controllers/' . $file;
            $controller = new $class();
            $controller->$action($params);
        } catch (Throwable $e) {
            $logLine = "[APIv1] Uncaught: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
            error_log($logLine, 3, __DIR__ . '/../../logs/api-v1.log');
            json_error('Internal server error', 500);
        }

        break;
    }
}

if (!$matched) {
    json_error('Endpoint not found', 404);
}
