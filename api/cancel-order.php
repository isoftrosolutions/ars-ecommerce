<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit-logger.php';
require_once __DIR__ . '/../includes/email-service.php';

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

    // Verify ownership and cancellable status
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR customer_email = ?) FOR UPDATE");
    $stmt->execute([$orderId, $user['id'], $user['email']]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $ds = strtolower($order['delivery_status']);
    if (!in_array($ds, ['pending', 'confirmed', 'shipped'])) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Order cannot be cancelled']);
        exit;
    }

    // Restore product stock
    $stmt = $pdo->prepare("
        UPDATE products p
        JOIN order_items oi ON p.id = oi.product_id AND oi.order_id = ?
        SET p.stock = p.stock + oi.quantity
    ");
    $stmt->execute([$orderId]);

    // Update payment status to Refunded if it was Paid
    $oldPaymentStatus = $order['payment_status'];
    if (strtolower($oldPaymentStatus) === 'paid') {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Refunded' WHERE id = ?");
        $stmt->execute([$orderId]);
    }

    // Cancel the order
    $oldDeliveryStatus = $order['delivery_status'];
    $stmt = $pdo->prepare("UPDATE orders SET delivery_status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$orderId]);

    $pdo->commit();

    // Audit log (outside transaction — safe failure)
    AuditLogger::logOrderStatusChange($orderId, $oldDeliveryStatus, 'Cancelled');

    // Send cancellation email (outside transaction — safe failure)
    $emailService = EmailService::getInstance();
    $emailService->sendOrderCancellation(
        $order['customer_email'],
        $order['customer_name'],
        $orderId,
        $order['total_amount']
    );

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
