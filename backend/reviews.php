<?php
/**
 * Product Reviews Management Backend Logic
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
            case 'get_reviews':
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $status = trim($_POST['status'] ?? '');  // Raw — PDO handles it
                $rating = (int)($_POST['rating'] ?? 0);
                $search = trim($_POST['search'] ?? '');
                $offset = ($page - 1) * $limit;

                $where = [];
                $params = [];

                if ($status !== '') {
                    $where[]  = "pr.status = ?";
                    $params[] = $status;
                }
                if ($rating > 0) {
                    $where[]  = "pr.rating = ?";
                    $params[] = $rating;
                }
                if ($search !== '') {
                    $where[]  = "(p.name LIKE ? OR pr.comment LIKE ? OR u.full_name LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                $wc = $where ? "WHERE " . implode(" AND ", $where) : "";

                $count_sql = "
                    SELECT COUNT(*) as total FROM product_reviews pr
                    JOIN products p ON pr.product_id = p.id
                    LEFT JOIN users u ON pr.user_id = u.id $wc";
                $stmt = $pdo->prepare($count_sql);
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("
                    SELECT pr.*, p.name as product_name, p.image as product_image, u.full_name, u.mobile
                    FROM product_reviews pr
                    JOIN products p ON pr.product_id = p.id
                    LEFT JOIN users u ON pr.user_id = u.id
                    $wc ORDER BY pr.created_at DESC LIMIT ? OFFSET ?
                ");
                $stmt->execute($params);

                echo json_encode([
                    'success'    => true,
                    'data'       => $stmt->fetchAll(),
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_review':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("
                    SELECT pr.*, p.name as product_name, p.image as product_image, u.full_name, u.mobile, u.email
                    FROM product_reviews pr
                    JOIN products p ON pr.product_id = p.id
                    LEFT JOIN users u ON pr.user_id = u.id
                    WHERE pr.id = ?
                ");
                $stmt->execute([$id]);
                $review = $stmt->fetch();
                if ($review) echo json_encode(['success' => true, 'data' => $review]);
                else         echo json_encode(['success' => false, 'message' => 'Review not found']);
                break;

            case 'update_status':
                $id     = (int)$_POST['id'];
                $status = trim($_POST['status'] ?? '');
                $valid  = ['pending', 'approved', 'rejected'];
                if (!in_array($status, $valid)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                $pdo->prepare("UPDATE product_reviews SET status = ? WHERE id = ?")->execute([$status, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'delete_review':
                $id = (int)$_POST['id'];
                $pdo->prepare("DELETE FROM product_reviews WHERE id = ?")->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            case 'bulk_update_status':
                $review_ids = array_map('intval', (array)($_POST['review_ids'] ?? []));
                $status     = trim($_POST['status'] ?? '');
                $valid      = ['pending', 'approved', 'rejected'];

                if (!in_array($status, $valid)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                if (empty($review_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No reviews selected']);
                    exit();
                }

                $ph = implode(',', array_fill(0, count($review_ids), '?'));
                $pdo->prepare("UPDATE product_reviews SET status = ? WHERE id IN ($ph)")
                    ->execute(array_merge([$status], $review_ids));

                echo json_encode(['success' => true, 'updated' => count($review_ids)]);
                break;

            case 'bulk_delete':
                $review_ids = array_map('intval', (array)($_POST['review_ids'] ?? []));
                if (empty($review_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No reviews selected']);
                    exit();
                }
                $ph = implode(',', array_fill(0, count($review_ids), '?'));
                $pdo->prepare("DELETE FROM product_reviews WHERE id IN ($ph)")->execute($review_ids);
                echo json_encode(['success' => true, 'deleted' => count($review_ids)]);
                break;

            case 'get_stats':
                $stats = [];
                $stmt  = $pdo->query("SELECT COUNT(*) as total FROM product_reviews");
                $stats['total_reviews'] = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM product_reviews GROUP BY status");
                $stats['status_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $stmt = $pdo->query("SELECT rating, COUNT(*) as count FROM product_reviews WHERE status='approved' GROUP BY rating ORDER BY rating DESC");
                $stats['rating_distribution'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $stmt = $pdo->query("SELECT AVG(rating) as average FROM product_reviews WHERE status='approved'");
                $avg  = $stmt->fetch()['average'];
                $stats['average_rating'] = $avg ? round($avg, 1) : 0;

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_reviews WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $stmt->execute();
                $stats['recent_reviews'] = $stmt->fetch()['count'];

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'get_product_reviews':
                $product_id = (int)$_POST['product_id'];
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM product_reviews WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $total = (int)$stmt->fetch()['total'];

                $stmt = $pdo->prepare("
                    SELECT pr.*, u.full_name, u.mobile FROM product_reviews pr
                    LEFT JOIN users u ON pr.user_id = u.id
                    WHERE pr.product_id = ? ORDER BY pr.created_at DESC LIMIT ? OFFSET ?
                ");
                $stmt->execute([$product_id, $limit, $offset]);

                echo json_encode([
                    'success'    => true,
                    'data'       => $stmt->fetchAll(),
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log('[ARS] backend/reviews.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit();
}

header('Location: ../admin/reviews.php');
exit();
?>