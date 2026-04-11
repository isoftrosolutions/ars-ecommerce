<?php
/**
 * Customers Management Backend Logic
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
            case 'get_customers':
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $search = trim($_POST['search'] ?? ''); // Raw — PDO params handle injection
                $offset = ($page - 1) * $limit;

                $where  = ["role = 'customer'"];
                $params = [];

                if ($search !== '') {
                    $where[]  = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                $wc = "WHERE " . implode(" AND ", $where);

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users $wc");
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("
                    SELECT u.*,
                           COALESCE(s.total_orders, 0) as total_orders,
                           COALESCE(s.total_spent, 0) as total_spent,
                           s.last_order_date
                    FROM users u
                    LEFT JOIN (
                        SELECT user_id,
                               COUNT(*) as total_orders,
                               SUM(total_amount) as total_spent,
                               MAX(created_at) as last_order_date
                        FROM orders
                        WHERE payment_status = 'Paid'
                        GROUP BY user_id
                    ) s ON u.id = s.user_id
                    $wc
                    ORDER BY u.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute($params);
                $customers = $stmt->fetchAll();

                // Don't expose password hashes
                foreach ($customers as &$c) unset($c['password']);

                echo json_encode([
                    'success'    => true,
                    'data'       => $customers,
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_customer_details':
                $customer_id = (int)$_POST['customer_id'];
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
                $stmt->execute([$customer_id]);
                $customer = $stmt->fetch();

                if (!$customer) {
                    echo json_encode(['success' => false, 'message' => 'Customer not found']);
                    exit();
                }
                unset($customer['password']);

                $stmt = $pdo->prepare("
                    SELECT o.*, COUNT(oi.id) as item_count
                    FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC LIMIT 10
                ");
                $stmt->execute([$customer_id]);
                $customer['recent_orders'] = $stmt->fetchAll();

                $stmt = $pdo->prepare("
                    SELECT w.created_at, p.id, p.name, p.price, p.image
                    FROM wishlist w JOIN products p ON w.product_id = p.id
                    WHERE w.user_id = ? ORDER BY w.created_at DESC LIMIT 5
                ");
                $stmt->execute([$customer_id]);
                $customer['wishlist'] = $stmt->fetchAll();

                $stmt = $pdo->prepare("
                    SELECT pr.*, p.name as product_name
                    FROM product_reviews pr JOIN products p ON pr.product_id = p.id
                    WHERE pr.user_id = ? ORDER BY pr.created_at DESC LIMIT 5
                ");
                $stmt->execute([$customer_id]);
                $customer['reviews'] = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $customer]);
                break;

            case 'update_customer':
                $customer_id = (int)$_POST['customer_id'];
                $data        = $_POST['customer'];
                $pdo->prepare("
                    UPDATE users SET full_name = ?, email = ?, mobile = ?, address = ?
                    WHERE id = ? AND role = 'customer'
                ")->execute([
                    trim($data['full_name'] ?? ''),
                    trim($data['email']     ?? ''),
                    trim($data['mobile']    ?? ''),
                    trim($data['address']   ?? ''),
                    $customer_id
                ]);
                echo json_encode(['success' => true]);
                break;

            case 'get_customer_orders':
                $customer_id = (int)$_POST['customer_id'];
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
                $stmt->execute([$customer_id]);
                $total = (int)$stmt->fetch()['total'];

                $stmt = $pdo->prepare("
                    SELECT o.*, COUNT(oi.id) as item_count FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?
                ");
                $stmt->execute([$customer_id, $limit, $offset]);

                echo json_encode([
                    'success'    => true,
                    'data'       => $stmt->fetchAll(),
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_customer_stats':
                $stats = [];
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
                $stats['total_customers'] = $stmt->fetch()['total'];

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role='customer' AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')");
                $stmt->execute();
                $stats['new_this_month'] = $stmt->fetch()['total'];

                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $stmt->execute();
                $stats['active_customers'] = $stmt->fetch()['total'];

                $stmt = $pdo->prepare("
                    SELECT u.full_name, u.mobile, SUM(o.total_amount) as total_spent
                    FROM users u JOIN orders o ON u.id = o.user_id
                    WHERE u.role='customer' AND o.payment_status='Paid'
                    GROUP BY u.id, u.full_name, u.mobile ORDER BY total_spent DESC LIMIT 5
                ");
                $stmt->execute();
                $stats['top_customers'] = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'delete_customer':
                $customer_id = (int)$_POST['customer_id'];

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                $stmt->execute([$customer_id]);
                if ($stmt->fetch()['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot delete customer with existing orders']);
                    exit();
                }

                $pdo->beginTransaction();
                $pdo->prepare("DELETE FROM wishlist         WHERE user_id = ?")->execute([$customer_id]);
                $pdo->prepare("DELETE FROM product_reviews  WHERE user_id = ?")->execute([$customer_id]);
                $pdo->prepare("DELETE FROM users            WHERE id = ? AND role = 'customer'")->execute([$customer_id]);
                $pdo->commit();

                echo json_encode(['success' => true]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[ARS] backend/customers.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit();
}

header('Location: ../admin/customers.php');
exit();
?>