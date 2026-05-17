<?php
/**
 * Cancel Order API
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$orderId = isset($data['order_id']) ? (int)$data['order_id'] : 0;
$user = $_SESSION['user'];

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    // Verify ownership and cancellable status
    $stmt = $pdo->prepare("SELECT delivery_status FROM orders WHERE id = ? AND (user_id = ? OR customer_email = ?)");
    $stmt->execute([$orderId, $user['id'], $user['email']]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $ds = strtolower($order['delivery_status']);
    if (!in_array($ds, ['pending', 'confirmed', 'shipped'])) {
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit;
    }

    // Cancel the order
    $stmt = $pdo->prepare("UPDATE orders SET delivery_status = 'Cancelled', location_updated_at = NOW() WHERE id = ?");
    $stmt->execute([$orderId]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
