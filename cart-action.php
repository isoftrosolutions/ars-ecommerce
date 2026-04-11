<?php
/**
 * Cart Action Handler
 * Handles add, update, remove cart operations
 * Easy Shopping A.R.S
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$product_id = (int)($_GET['id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'add':
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product ID'];
                break;
            }

            $add_quantity = (int)($_GET['quantity'] ?? 1);
            if ($add_quantity <= 0) $add_quantity = 1;

            $result = add_to_cart($product_id, $add_quantity);
            $response = $result;
            break;

        case 'update':
            if ($product_id <= 0 || $quantity <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product ID or quantity'];
                break;
            }

            $result = update_cart_quantity($product_id, $quantity);
            $response = $result;
            break;

        case 'remove':
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid product ID'];
                break;
            }

            $result = remove_from_cart($product_id);
            $response = $result;
            break;

        case 'clear':
            $result = clear_cart();
            $response = $result;
            break;

        case 'count':
            $response = ['success' => true];
            break;

        default:
            $response = ['success' => false, 'message' => 'Unknown action'];
    }

    if ($response['success']) {
        $response['cart_count'] = get_cart_count();
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'An error occurred'];
}

echo json_encode($response);
?>