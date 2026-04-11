<?php
/**
 * Coupons Management Backend Logic
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
            case 'get_coupons':
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $status = trim($_POST['status'] ?? ''); // Raw — PDO handles it
                $type   = trim($_POST['type']   ?? '');
                $search = trim($_POST['search'] ?? '');
                $offset = ($page - 1) * $limit;

                $where  = [];
                $params = [];

                if ($status !== '') {
                    $where[]  = "status = ?";
                    $params[] = $status;
                }
                if ($type !== '') {
                    $where[]  = "type = ?";
                    $params[] = $type;
                }
                if ($search !== '') {
                    $where[]  = "(code LIKE ? OR description LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                // FIX: Admin should see ALL coupons (including expired) — removed the automatic
                // expiry filter that was previously hiding expired coupons from the admin list.
                // The frontend already shows expiry_date so admins can see which are expired.

                $wc = $where ? "WHERE " . implode(" AND ", $where) : "";

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM coupons $wc");
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("
                    SELECT c.*,
                           COALESCE(u.times_used, 0) as times_used,
                           COALESCE(u.total_discount, 0) as total_discount,
                           (c.expiry_date IS NOT NULL AND c.expiry_date < CURDATE()) as is_expired
                    FROM coupons c
                    LEFT JOIN (
                        SELECT coupon_code, COUNT(*) as times_used, SUM(discount_amount) as total_discount
                        FROM orders WHERE coupon_code IS NOT NULL AND payment_status = 'Paid'
                        GROUP BY coupon_code
                    ) u ON c.code = u.coupon_code
                    $wc
                    ORDER BY c.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute($params);

                echo json_encode([
                    'success'    => true,
                    'data'       => $stmt->fetchAll(),
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_coupon':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
                $stmt->execute([$id]);
                $coupon = $stmt->fetch();

                if (!$coupon) {
                    echo json_encode(['success' => false, 'message' => 'Coupon not found']);
                    exit();
                }

                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as times_used, SUM(total_amount) as total_order_value, SUM(discount_amount) as total_discount
                    FROM orders WHERE coupon_code = ? AND payment_status = 'Paid'
                ");
                $stmt->execute([$coupon['code']]);
                $coupon['usage_stats'] = $stmt->fetch() ?: ['times_used' => 0, 'total_order_value' => 0, 'total_discount' => 0];

                echo json_encode(['success' => true, 'data' => $coupon]);
                break;

            case 'save_coupon':
                $data     = $_POST['coupon'];
                $code     = strtoupper(trim($data['code'] ?? ''));
                $id_check = isset($data['id']) ? (int)$data['id'] : 0;

                if (!$code) {
                    echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
                    exit();
                }

                $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
                $stmt->execute([$code, $id_check]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Coupon code already exists']);
                    exit();
                }

                $expiry_date = null;
                if (!empty($data['expiry_date'])) {
                    $expiry_date = date('Y-m-d', strtotime($data['expiry_date']));
                }

                if ($id_check) {
                    $pdo->prepare("
                        UPDATE coupons SET code=?, type=?, value=?, min_cart_amount=?, expiry_date=?, status=?
                        WHERE id=?
                    ")->execute([
                        $code, $data['type'], (float)$data['value'],
                        $data['min_cart_amount'] ? (float)$data['min_cart_amount'] : 0,
                        $expiry_date, $data['status'], $id_check
                    ]);
                    $coupon_id = $id_check;
                } else {
                    $pdo->prepare("
                        INSERT INTO coupons (code, type, value, min_cart_amount, expiry_date, status)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $code, $data['type'], (float)$data['value'],
                        $data['min_cart_amount'] ? (float)$data['min_cart_amount'] : 0,
                        $expiry_date, $data['status']
                    ]);
                    $coupon_id = $pdo->lastInsertId();
                }
                echo json_encode(['success' => true, 'coupon_id' => $coupon_id]);
                break;

            case 'delete_coupon':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE coupon_code = (SELECT code FROM coupons WHERE id = ?)");
                $stmt->execute([$id]);
                if ($stmt->fetch()['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot delete coupon that has been used in orders']);
                    exit();
                }
                $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            case 'toggle_status':
                $id     = (int)$_POST['id'];
                $status = trim($_POST['status'] ?? '');
                if (!in_array($status, ['active', 'inactive'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?")->execute([$status, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'validate_coupon':
                // Public-facing (used in checkout) — no admin CSRF required, but still routed here
                $code       = strtoupper(trim($_POST['code']       ?? ''));
                $cart_total = (float)($_POST['cart_total'] ?? 0);

                $stmt = $pdo->prepare("
                    SELECT * FROM coupons
                    WHERE code = ? AND status = 'active'
                    AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                ");
                $stmt->execute([$code]);
                $coupon = $stmt->fetch();

                if (!$coupon) {
                    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
                    exit();
                }

                if ($coupon['min_cart_amount'] > 0 && $cart_total < $coupon['min_cart_amount']) {
                    echo json_encode(['success' => false, 'message' => 'Minimum cart amount of Rs. ' . number_format($coupon['min_cart_amount'], 2) . ' required']);
                    exit();
                }

                $discount = $coupon['type'] === 'fixed'
                    ? min($coupon['value'], $cart_total)
                    : ($cart_total * $coupon['value'] / 100);

                echo json_encode(['success' => true, 'coupon' => $coupon, 'discount' => round($discount, 2), 'new_total' => round($cart_total - $discount, 2)]);
                break;

            case 'get_stats':
                $stats = [];
                $stmt  = $pdo->query("SELECT COUNT(*) as total FROM coupons");
                $stats['total_coupons'] = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT COUNT(*) as total FROM coupons WHERE status='active' AND (expiry_date IS NULL OR expiry_date >= CURDATE())");
                $stats['active_coupons'] = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT COUNT(*) as total FROM coupons WHERE expiry_date < CURDATE()");
                $stats['expired_coupons'] = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT SUM(discount_amount) as total FROM orders WHERE coupon_code IS NOT NULL AND payment_status='Paid'");
                $stats['total_discount_given'] = $stmt->fetch()['total'] ?: 0;

                $stmt = $pdo->query("SELECT coupon_code, COUNT(*) as usage_count FROM orders WHERE coupon_code IS NOT NULL AND payment_status='Paid' GROUP BY coupon_code ORDER BY usage_count DESC LIMIT 1");
                $stats['most_used_coupon'] = $stmt->fetch() ?: null;

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'bulk_delete':
                $coupon_ids = array_map('intval', (array)($_POST['coupon_ids'] ?? []));
                if (empty($coupon_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No coupons selected']);
                    exit();
                }

                $ph = implode(',', array_fill(0, count($coupon_ids), '?'));
                $stmt = $pdo->prepare("SELECT DISTINCT o.coupon_code FROM orders o JOIN coupons c ON o.coupon_code = c.code WHERE c.id IN ($ph)");
                $stmt->execute($coupon_ids);
                $used_codes = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($used_codes)) {
                    $ph2 = implode(',', array_fill(0, count($used_codes), '?'));
                    $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code IN ($ph2)");
                    $stmt->execute($used_codes);
                    $used_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } else {
                    $used_ids = [];
                }

                $unused = array_diff($coupon_ids, $used_ids);
                if (empty($unused)) {
                    echo json_encode(['success' => false, 'message' => 'All selected coupons have been used in orders']);
                    exit();
                }

                $ph3 = implode(',', array_fill(0, count($unused), '?'));
                $pdo->prepare("DELETE FROM coupons WHERE id IN ($ph3)")->execute(array_values($unused));

                echo json_encode(['success' => true, 'deleted' => count($unused), 'skipped' => count($coupon_ids) - count($unused)]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log('[ARS] backend/coupons.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit();
}

header('Location: ../admin/coupons.php');
exit();
?>