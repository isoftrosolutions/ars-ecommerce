<?php
/**
 * Orders Management Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

protect_admin_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!validate_csrf_token()) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit();
    }

    try {
        switch ($_POST['action']) {
            case 'get_orders':
                $page           = max(1, (int)($_POST['page']  ?? 1));
                $limit          = max(1, (int)($_POST['limit'] ?? 10));
                // Raw SQL inputs — PDO params handle injection; h() is for HTML output only
                $status         = trim($_POST['status']         ?? '');
                $payment_status = trim($_POST['payment_status'] ?? '');
                $search         = trim($_POST['search']         ?? '');
                $offset         = ($page - 1) * $limit;

                $where = [];
                $params = [];

                if ($status !== '') {
                    $where[]  = "o.delivery_status = ?";
                    $params[] = $status;
                }
                if ($payment_status !== '') {
                    $where[]  = "o.payment_status = ?";
                    $params[] = $payment_status;
                }
                if ($search !== '') {
                    $where[]  = "(o.id = ? OR u.full_name LIKE ? OR u.mobile LIKE ? OR u.email LIKE ?)";
                    $params[] = $search;
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                $wc = $where ? "WHERE " . implode(" AND ", $where) : "";

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id $wc");
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("
                    SELECT o.*, u.full_name, u.mobile, u.email
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    $wc
                    ORDER BY o.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute($params);
                $orders = $stmt->fetchAll();

                echo json_encode([
                    'success'    => true,
                    'data'       => $orders,
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_order_details':
                $order_id = (int)$_POST['order_id'];

                $stmt = $pdo->prepare("
                    SELECT o.*, u.full_name, u.mobile, u.email, u.address as user_address
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.id = ?
                ");
                $stmt->execute([$order_id]);
                $order = $stmt->fetch();

                if (!$order) {
                    echo json_encode(['success' => false, 'message' => 'Order not found']);
                    exit();
                }

                $stmt = $pdo->prepare("
                    SELECT oi.*, p.name, p.image, p.sku
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order_id]);
                $order['items'] = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $order]);
                break;

            case 'update_order_status':
                $order_id         = (int)$_POST['order_id'];
                $status           = trim($_POST['status']           ?? '');
                $current_location = trim($_POST['current_location'] ?? '');

                $valid = ['Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled'];
                if (!in_array($status, $valid)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid delivery status.']);
                    exit();
                }

                $pdo->beginTransaction();
                $pdo->prepare("
                    UPDATE orders SET delivery_status = ?, current_location = ?, location_updated_at = NOW()
                    WHERE id = ?
                ")->execute([$status, $current_location, $order_id]);
                $pdo->commit();

                echo json_encode(['success' => true]);
                break;

            case 'update_payment_status':
                $order_id       = (int)$_POST['order_id'];
                $payment_status = trim($_POST['payment_status'] ?? '');
                $transaction_id = trim($_POST['transaction_id'] ?? '');

                $valid = ['Pending','Paid','Failed'];
                if (!in_array($payment_status, $valid)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid payment status.']);
                    exit();
                }

                $pdo->prepare("
                    UPDATE orders SET payment_status = ?, transaction_id = ? WHERE id = ?
                ")->execute([$payment_status, $transaction_id, $order_id]);

                echo json_encode(['success' => true]);
                break;

            case 'get_order_stats':
                $stats = [];
                $stmt = $pdo->query("SELECT delivery_status, COUNT(*) as count FROM orders GROUP BY delivery_status");
                $stats['status_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $stmt = $pdo->query("SELECT payment_status, COUNT(*) as count FROM orders GROUP BY payment_status");
                $stats['payment_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stmt->execute();
                $stats['recent_orders'] = $stmt->fetch()['count'];

                $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'Paid'");
                $stats['total_revenue'] = $stmt->fetch()['revenue'] ?: 0;

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'get_delivery_locations':
                echo json_encode(['success' => true, 'data' => [
                    'Preparing for shipment',
                    'Order packed and ready',
                    'Picked up by courier',
                    'In transit to destination',
                    'Arrived at local facility',
                    'Out for delivery',
                    'Delivered successfully'
                ]]);
                break;

            case 'bulk_update_status':
                $order_ids        = (array)($_POST['order_ids']        ?? []);
                $status           = trim($_POST['status']           ?? '');
                $current_location = trim($_POST['current_location'] ?? '');

                if (empty($order_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No orders selected']);
                    exit();
                }
                $valid = ['Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled'];
                if (!in_array($status, $valid)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
                    exit();
                }

                $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
                $params = array_merge([$status, $current_location], array_map('intval', $order_ids));
                $pdo->prepare("
                    UPDATE orders SET delivery_status = ?, current_location = ?, location_updated_at = NOW()
                    WHERE id IN ($placeholders)
                ")->execute($params);

                echo json_encode(['success' => true, 'updated' => count($order_ids)]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[ARS] backend/orders.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit();
}

header('Location: ../admin/orders.php');
exit();
?>