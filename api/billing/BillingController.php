<?php
/**
 * Billing Controller
 * Handles billing/invoice operations
 */
class BillingController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getBillingList();
                    case 'stats':
                        return $this->getBillingStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get paginated billing/invoices list
     */
    private function getBillingList() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        // Search filter (order ID, customer name, email)
        if (!empty($params['search'])) {
            $where[] = "(o.id = ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $orderId = (int)$params['search'];
            $queryParams = array_merge($queryParams, [$orderId, $searchTerm, $searchTerm]);
        }

        // Payment status filter
        if (!empty($params['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $queryParams[] = $params['payment_status'];
        }

        // Delivery status filter
        if (!empty($params['delivery_status'])) {
            $where[] = "o.delivery_status = ?";
            $queryParams[] = $params['delivery_status'];
        }

        // Date range filter
        if (!empty($params['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $queryParams[] = $params['date_from'];
        }
        if (!empty($params['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $queryParams[] = $params['date_to'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // Get total count
        $countStmt = $this->executeQuery(
            "SELECT COUNT(*) FROM orders o $whereClause",
            $queryParams
        );
        $total = (int)$countStmt->fetchColumn();

        // Get orders
        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery(
            "SELECT o.id, o.user_id, o.customer_name, o.customer_email, o.customer_phone, o.total_amount, o.shipping_charge, o.payment_method, o.payment_status, o.delivery_status, o.created_at
             FROM orders o $whereClause
             ORDER BY o.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($queryParams, [$pagination['limit'], $offset])
        );
        $orders = $stmt->fetchAll();

        // Format for response
        $data = array_map(function($o) {
            return [
                'id' => (int)$o['id'],
                'user_id' => $o['user_id'],
                'customer_name' => $o['customer_name'],
                'customer_email' => $o['customer_email'],
                'customer_phone' => $o['customer_phone'],
                'total_amount' => $o['total_amount'],
                'shipping_charge' => $o['shipping_charge'],
                'payment_method' => $o['payment_method'],
                'payment_status' => $o['payment_status'],
                'delivery_status' => $o['delivery_status'],
                'created_at' => $o['created_at']
            ];
        }, $orders);

        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($data, $paginationInfo, 'Billing list retrieved successfully');
    }

    /**
     * Get billing statistics
     */
    private function getBillingStats() {
        // Total revenue (all paid orders)
        $stmt = $this->executeQuery(
            "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'Paid'"
        );
        $totalRevenue = $stmt->fetchColumn();

        // Revenue this month (paid orders)
        $stmt = $this->executeQuery(
            "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE payment_status = 'Paid' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        );
        $monthRevenue = $stmt->fetchColumn();

        // Paid invoices count
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) FROM orders WHERE payment_status = 'Paid'"
        );
        $paidCount = $stmt->fetchColumn();

        // Pending payment count
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) FROM orders WHERE payment_status = 'Pending'"
        );
        $pendingCount = $stmt->fetchColumn();

        Response::success([
            'total_revenue' => $totalRevenue,
            'month_revenue' => $monthRevenue,
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount
        ], 'Billing stats retrieved successfully');
    }
}