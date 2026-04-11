<?php
/**
 * Simple Router for ARS-e-commerce
 * Handles Clean URLs and dynamic routes
 */

function route($url) {
    // 1. Sanitize and parse URL
    global $app_base_path;
    $base_dir = $app_base_path ?? '/';
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Remove query string if any
    $parsed_url = parse_url($request_uri, PHP_URL_PATH);
    
    // Remove base directory from the URL if applicable
    if ($base_dir !== '/' && strpos($parsed_url, $base_dir) === 0) {
        $parsed_url = substr($parsed_url, strlen($base_dir));
    }
    
    // Trim slashes
    $path = trim($parsed_url, '/');

    // 2. Define Routes
    $routes = [
        '' => 'index.php',
        'shop' => 'shop.php',
        'categories' => 'categories.php',
        'about' => 'about.php',
        'privacy' => 'privacy.php',
        'terms' => 'terms.php',
        'cart' => 'cart.php',
        'checkout' => 'checkout.php',
        'order-success' => 'order-success.php',
        'profile' => 'profile.php',
        'orders' => 'orders.php',
        'wishlist' => 'wishlist.php',
        'auth/login' => 'auth/login.php',
        'auth/signup' => 'auth/signup.php',
        'admin/dashboard' => 'admin/dashboard.php',
        'admin/products' => 'admin/products.php',
        'admin/orders' => 'admin/orders.php',
        'admin/categories' => 'admin/categories.php',
        'admin/customers' => 'admin/customers.php',
        'admin/reviews' => 'admin/reviews.php',
        'admin/coupons' => 'admin/coupons.php',
        'admin/contact' => 'admin/contact.php',
        'admin/settings' => 'admin/settings.php',
        'backend/logout' => 'backend/logout.php',
    ];

    // 3. Match Static Routes
    if (isset($routes[$path])) {
        return $routes[$path];
    }

    // 4. Handle Dynamic Routes (e.g., product/leather-bag, order/123, cart-action)
    if (preg_match('/^product\/([a-zA-Z0-9-]+)$/', $path, $matches)) {
        $_GET['slug'] = $matches[1];
        return 'product.php';
    }

    if (preg_match('/^order\/(\d+)$/', $path, $matches)) {
        $_GET['order_id'] = $matches[1];
        return 'order.php';
    }

    if (preg_match('/^cart-action$/', $path)) {
        return 'cart-action.php';
    }

    // 5. Default: Not Found or fallback to index
    if ($path === 'index.php') return 'index.php'; // Prevent infinite loops
    
    return null; // Let the caller decide how to handle 404
}
?>
