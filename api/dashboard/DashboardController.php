<?php
/**
 * Dashboard Controller
 * Handles dashboard statistics and analytics
 */
class DashboardController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'stats':
                        return $this->getStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getStats() {
        try {
            $stats = [];

            // Total Products
            $stmt = $this->executeQuery("SELECT COUNT(*) FROM products");
            $stats['total_products'] = (int)$stmt->fetchColumn();

            // Total Orders
            $stmt = $this->executeQuery("SELECT COUNT(*) FROM orders");
            $stats['total_orders'] = (int)$stmt->fetchColumn();

            // Total Revenue (Paid Orders)
            $stmt = $this->executeQuery("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'");
            $stats['total_revenue'] = (float)$stmt->fetchColumn();

            // Total Customers
            $stmt = $this->executeQuery("SELECT COUNT(*) FROM users WHERE role = 'customer'");
            $stats['total_customers'] = (int)$stmt->fetchColumn();

            // Revenue Trend: this month vs last month
            $stmt = $this->executeQuery("
                SELECT
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN total_amount ELSE 0 END), 0) AS this_month,
                    COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH),'%Y-%m') THEN total_amount ELSE 0 END), 0) AS last_month
                FROM orders WHERE payment_status = 'Paid'
            ");
            $rev = $stmt->fetch();
            $stats['revenue_trend'] = [
                'this_month' => (float)$rev['this_month'],
                'last_month' => (float)$rev['last_month'],
                'change_percent' => $rev['last_month'] > 0
                    ? round(($rev['this_month'] - $rev['last_month']) / $rev['last_month'] * 100, 2)
                    : ($rev['this_month'] > 0 ? 100 : 0)
            ];

            // Orders Trend: this month vs last month
            $stmt = $this->executeQuery("
                SELECT
                    SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN 1 ELSE 0 END) AS this_month,
                    SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH),'%Y-%m') THEN 1 ELSE 0 END) AS last_month
                FROM orders
            ");
            $ord = $stmt->fetch();
            $stats['orders_trend'] = [
                'this_month' => (int)$ord['this_month'],
                'last_month' => (int)$ord['last_month'],
                'change_percent' => $ord['last_month'] > 0
                    ? round(($ord['this_month'] - $ord['last_month']) / $ord['last_month'] * 100, 2)
                    : ($ord['this_month'] > 0 ? 100 : 0)
            ];

            // New customers this week
            $stmt = $this->executeQuery("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['new_customers_week'] = (int)$stmt->fetchColumn();

            // Recent Orders
            $stmt = $this->executeQuery("
                SELECT o.id, o.total_amount, o.delivery_status, o.payment_status, o.created_at,
                       u.full_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC LIMIT 5
            ");
            $stats['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Order status breakdown
            $stmt = $this->executeQuery("
                SELECT delivery_status, COUNT(*) as count
                FROM orders
                GROUP BY delivery_status
            ");
            $stats['order_status_breakdown'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Top products (by order count)
            $stmt = $this->executeQuery("
                SELECT p.name, COUNT(oi.id) as order_count, SUM(oi.quantity) as total_quantity
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                GROUP BY p.id, p.name
                ORDER BY order_count DESC
                LIMIT 5
            ");
            $stats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::success($stats, 'Dashboard statistics retrieved successfully');

        } catch (Exception $e) {
            $this->logger->error('Failed to get dashboard stats: ' . $e->getMessage());
            Response::error('Failed to retrieve dashboard statistics', 500);
        }
    }
}
?>