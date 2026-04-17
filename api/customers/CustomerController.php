<?php
/**
 * Customer Controller
 * Handles customer (user) management for admins
 */
class CustomerController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getCustomers();
                    case 'detail':
                        return $this->getCustomerDetails();
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
     * Get paginated customer list
     */
    private function getCustomers() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = ["role = 'customer'"];
        $queryParams = [];

        // Search filter
        if (!empty($params['search'])) {
            $where[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm]);
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Get total count
        $countStmt = $this->executeQuery(
            "SELECT COUNT(*) FROM users $whereClause",
            $queryParams
        );
        $total = (int)$countStmt->fetchColumn();

        // Get customers
        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery("
            SELECT id, full_name, email, mobile, address, created_at, 
                   (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders,
                   (SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND payment_status = 'Paid') as total_spend
            FROM users 
            $whereClause
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($customers, $paginationInfo, 'Customers retrieved successfully');
    }

    /**
     * Get customer details and their order history
     */
    private function getCustomerDetails() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);

        $stmt = $this->executeQuery("
            SELECT id, full_name, email, mobile, address, created_at 
            FROM users 
            WHERE id = ? AND role = 'customer'
        ", [$params['id']]);

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            Response::error('Customer not found', 404);
        }

        // Get recent orders
        $stmt = $this->executeQuery("
            SELECT id, total_amount, delivery_status, payment_status, created_at,
                   (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) as item_count
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$params['id']]);

        $customer['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($customer, 'Customer details retrieved successfully');
    }

    /**
     * Get customer statistics
     */
    private function getStats() {
        $stats = [];

        // Total Customers
        $stmt = $this->executeQuery("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $stats['total_customers'] = (int)$stmt->fetchColumn();

        // New this Month
        $stmt = $this->executeQuery("
            SELECT COUNT(*) FROM users 
            WHERE role = 'customer' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        ");
        $stats['new_this_month'] = (int)$stmt->fetchColumn();

        // Active Customers (Regular purchasers - defined as 3+ orders)
        $stats['active_purchasers'] = 0;
        try {
             $stmt = $this->executeQuery("
                SELECT COUNT(*) FROM (
                    SELECT user_id FROM orders WHERE user_id IS NOT NULL GROUP BY user_id HAVING COUNT(*) >= 3
                ) as active_subset
            ");
            $stats['active_purchasers'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {}

        Response::success($stats, 'Customer statistics retrieved successfully');
    }
}
?>