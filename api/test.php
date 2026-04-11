<?php
/**
 * API Test Script
 * Simple tests to verify API functionality
 */

// Include required files
require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/utils/Logger.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';

echo "API Test Results:\n";
echo "================\n\n";

// Test 1: Check if API index file exists and is readable
echo "1. API Index File: ";
if (file_exists(__DIR__ . '/index.php')) {
    echo "✅ EXISTS\n";
} else {
    echo "❌ MISSING\n";
}

// Test 2: Check if all controller directories exist
$controllers = ['auth', 'products', 'categories', 'orders', 'customers', 'reviews', 'coupons', 'contact', 'settings', 'dashboard', 'uploads'];
echo "\n2. Controller Directories:\n";
foreach ($controllers as $controller) {
    $dir = __DIR__ . '/' . $controller;
    echo "   $controller: " . (is_dir($dir) ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

// Test 3: Check if middleware files exist
$middleware = ['AuthMiddleware.php', 'ValidationMiddleware.php', 'CorsMiddleware.php'];
echo "\n3. Middleware Files:\n";
foreach ($middleware as $file) {
    $path = __DIR__ . '/middleware/' . $file;
    echo "   $file: " . (file_exists($path) ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

// Test 4: Check if utility files exist
$utils = ['Response.php', 'Logger.php'];
echo "\n4. Utility Files:\n";
foreach ($utils as $file) {
    $path = __DIR__ . '/utils/' . $file;
    echo "   $file: " . (file_exists($path) ? "✅ EXISTS" : "❌ MISSING") . "\n";
}

// Test 5: Check if BaseController exists
echo "\n5. Base Controller: ";
echo (file_exists(__DIR__ . '/BaseController.php') ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Test 6: Check database connection
echo "\n6. Database Connection: ";
try {
    require_once __DIR__ . '/../includes/db.php';
    global $pdo;
    $stmt = $pdo->query("SELECT 1");
    echo "✅ CONNECTED\n";
} catch (Exception $e) {
    echo "❌ FAILED (" . $e->getMessage() . ")\n";
}

echo "\n================\n";
echo "API Setup Complete!\n";
echo "================\n";
echo "\nTo test the API:\n";
echo "1. Start your web server\n";
echo "2. Access: http://localhost/ars/api/dashboard/stats\n";
echo "3. Login first via: http://localhost/ars/admin/dashboard.php\n";
?>