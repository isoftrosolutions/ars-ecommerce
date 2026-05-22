<?php
/**
 * Orders Controller
 * Handles order management operations
 */
class OrderController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getOrders();
                    case 'detail':
                        return $this->getOrderDetails();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'update-status':
                        return $this->updateOrderStatus();
                    case 'update-payment-status':
                        return $this->updatePaymentStatus();
                    case 'update-location':
                        return $this->updateLocation();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get paginated orders list
     */
    private function getOrders() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        // Search filter
        if (!empty($params['search'])) {
            $where[] = "(o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.id = ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $orderId = (int)$params['search'];
            $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $orderId]);
        }

        // Status filter
        if (!empty($params['status'])) {
            $where[] = "o.delivery_status = ?";
            $queryParams[] = $params['status'];
        }

        // Payment status filter
        if (!empty($params['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $queryParams[] = $params['payment_status'];
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
        $stmt = $this->executeQuery("
            SELECT o.*, u.full_name as user_full_name, u.mobile as user_mobile
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            $whereClause
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize names for frontend
        foreach ($orders as &$order) {
            $order['full_name'] = $order['customer_name'] ?: $order['user_full_name'];
            $order['mobile'] = $order['customer_phone'] ?: $order['user_mobile'];
        }

        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($orders, $paginationInfo, 'Orders retrieved successfully');
    }

    /**
     * Get single order details with items
     */
    private function getOrderDetails() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);

        // Get order main info
        $stmt = $this->executeQuery("
            SELECT o.*, u.full_name as user_full_name, u.mobile as user_mobile, u.email as user_email, u.address as user_address
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ", [$params['id']]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Response::error('Order not found', 404);
        }

        // Normalize fields
        $order['full_name'] = $order['customer_name'] ?: $order['user_full_name'];
        $order['mobile'] = $order['customer_phone'] ?: $order['user_mobile'];
        $order['email'] = $order['customer_email'] ?: $order['user_email'];

        // Get order items
        $stmt = $this->executeQuery("
            SELECT oi.*, p.name, p.sku, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$params['id']]);

        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($order, 'Order details retrieved successfully');
    }

    /**
     * Update order delivery status
     */
    private function updateOrderStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['order_id', 'status']);
        
        $orderId = (int)$data['order_id'];
        $newStatus = $data['status'];
        $location = $data['current_location'] ?? null;

        // Get previous status for audit + conditional restock
        $oldStmt = $this->executeQuery("SELECT delivery_status FROM orders WHERE id = ?", [$orderId]);
        $oldStatus = $oldStmt->fetchColumn();

        if ($oldStatus === false) {
            Response::error('Order not found', 404);
        }

        $stmt = $this->executeQuery(
            "UPDATE orders SET delivery_status = ?, current_location = ?, location_updated_at = NOW() WHERE id = ?",
            [$newStatus, $location, $orderId]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Order not found or no changes made', 404);
        }

        // Production-grade: Restock inventory when order is marked Returned (only on transition)
        if ($newStatus === 'Returned' && $oldStatus !== 'Returned') {
            $itemsStmt = $this->executeQuery(
                "SELECT product_id, quantity FROM order_items WHERE order_id = ?",
                [$orderId]
            );
            $items = $itemsStmt->fetchAll();

            foreach ($items as $item) {
                $this->executeQuery(
                    "UPDATE products SET stock = stock + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }

            $this->logAction('restock_on_return', [
                'order_id' => $orderId,
                'items_restocked' => count($items)
            ]);
        }

        // Audit trail - always record status changes
        $note = 'Status changed from ' . ($oldStatus ?? 'unknown') . ' to ' . $newStatus . ' by admin';
        $this->executeQuery(
            "INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())",
            [$orderId, $newStatus, $note]
        );

        $this->logAction('update_order_status', ['order_id' => $orderId, 'status' => $newStatus, 'previous' => $oldStatus]);
        Response::success(null, 'Order status updated successfully');
    }

    /**
     * Update payment status
     */
    private function updatePaymentStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['order_id', 'payment_status']);

        $orderId = (int)$data['order_id'];
        $newPaymentStatus = $data['payment_status'];

        // Get old for audit
        $oldStmt = $this->executeQuery("SELECT payment_status FROM orders WHERE id = ?", [$orderId]);
        $oldPaymentStatus = $oldStmt->fetchColumn() ?: 'unknown';

        $stmt = $this->executeQuery(
            "UPDATE orders SET payment_status = ? WHERE id = ?",
            [$newPaymentStatus, $orderId]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Order not found or no changes made', 404);
        }

        // Audit trail for payment changes too
        $note = 'Payment status changed from ' . $oldPaymentStatus . ' to ' . $newPaymentStatus . ' by admin';
        $this->executeQuery(
            "INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, ?, ?, NOW())",
            [$orderId, 'Payment: ' . $newPaymentStatus, $note]
        );

        $this->logAction('update_payment_status', ['order_id' => $orderId, 'status' => $newPaymentStatus, 'previous' => $oldPaymentStatus]);
        Response::success(null, 'Payment status updated successfully');
    }

    /**
     * Update tracking location
     */
    private function updateLocation() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['order_id', 'location']);

        $stmt = $this->executeQuery(
            "UPDATE orders SET current_location = ?, location_updated_at = NOW() WHERE id = ?",
            [$data['location'], $data['order_id']]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Order not found or no changes made', 404);
        }

        Response::success(null, 'Location updated successfully');
    }
}
?>