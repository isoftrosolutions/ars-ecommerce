<?php
/**
 * Admin Dashboard Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Protect admin page
protect_admin_page();

// Handle AJAX requests for dashboard data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'get_stats':
                // Get dashboard statistics
                $stats = [];

                // Total products
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
                $stats['total_products'] = $stmt->fetch()['total'];

                // Total categories
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
                $stats['total_categories'] = $stmt->fetch()['total'];

                // Total customers
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
                $stats['total_customers'] = $stmt->fetch()['total'];

                // Total orders
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
                $stats['total_orders'] = $stmt->fetch()['total'];

                // Total revenue
                $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'Paid'");
                $revenue = $stmt->fetch()['revenue'];
                $stats['total_revenue'] = $revenue ? $revenue : 0;

                // Pending orders
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE delivery_status = 'Pending'");
                $stats['pending_orders'] = $stmt->fetch()['total'];

                // Low stock products (< 10)
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
                $stats['low_stock'] = $stmt->fetch()['total'];

                // Recent orders (last 7 days)
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $stmt->execute();
                $stats['recent_orders'] = $stmt->fetch()['total'];

                // Pending reviews
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_reviews WHERE status = 'pending'");
                $stats['pending_reviews'] = $stmt->fetch()['total'];

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'get_recent_orders':
                // Get recent orders for dashboard
                $stmt = $pdo->prepare("
                    SELECT o.id, o.total_amount, o.payment_status, o.delivery_status, o.created_at,
                           u.full_name, u.mobile
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.created_at DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $orders = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $orders]);
                break;

            case 'get_top_products':
                // Get top selling products
                $stmt = $pdo->prepare("
                    SELECT p.name, p.image, SUM(oi.quantity) as total_sold,
                           SUM(oi.price * oi.quantity) as revenue
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.payment_status = 'Paid'
                    GROUP BY p.id, p.name, p.image
                    ORDER BY total_sold DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $products = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $products]);
                break;

            case 'get_monthly_sales':
                // Get monthly sales data for chart
                $stmt = $pdo->prepare("
                    SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                           COUNT(*) as orders_count,
                           SUM(total_amount) as revenue
                    FROM orders
                    WHERE payment_status = 'Paid'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month ASC
                ");
                $stmt->execute();
                $monthly_data = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $monthly_data]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// If not AJAX request, redirect to dashboard page
header('Location: ../admin/dashboard.php');
exit();
?>