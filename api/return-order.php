<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit-logger.php';

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
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR customer_email = ?) FOR UPDATE");
    $stmt->execute([$orderId, $user['id'], $user['email']]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $ds = strtolower($order['delivery_status']);
    if ($ds !== 'delivered') {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Only delivered orders can be returned']);
        exit;
    }

    // Check 5-day window
    if ($order['location_updated_at']) {
        $deliveredAt = strtotime($order['location_updated_at']);
        $daysSince = floor((time() - $deliveredAt) / 86400);
        if ($daysSince > 5) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Return period has expired (5 days from delivery)']);
            exit;
        }
    }

    $stmt = $pdo->prepare("UPDATE orders SET delivery_status = 'Return Requested' WHERE id = ?");
    $stmt->execute([$orderId]);

    $pdo->commit();

    AuditLogger::logOrderStatusChange($orderId, 'Delivered', 'Return Requested');

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
