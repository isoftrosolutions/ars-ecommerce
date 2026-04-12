<?php
/**
 * Coupon Controller
 * Handles discount coupons management
 */
class CouponController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();
        AuthMiddleware::checkRateLimit('coupons', 50, 3600);

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getCoupons();
                    case 'detail':
                        return $this->getCouponDetails();
                    case 'stats':
                        return $this->getStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'create':
                        return $this->createCoupon();
                    case 'update':
                        return $this->updateCoupon();
                    case 'delete':
                        return $this->deleteCoupon();
                    case 'toggle-status':
                        return $this->toggleStatus();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    private function getCoupons() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        if (!empty($params['status'])) {
            $where[] = "status = ?";
            $queryParams[] = $params['status'];
        }

        if (!empty($params['type'])) {
            $where[] = "type = ?";
            $queryParams[] = $params['type'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->executeQuery("SELECT COUNT(*) FROM coupons $whereClause", $queryParams);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery("
            SELECT * FROM coupons 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($coupons, $paginationInfo, 'Coupons retrieved successfully');
    }

    private function getCouponDetails() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);

        $stmt = $this->executeQuery("SELECT * FROM coupons WHERE id = ?", [$params['id']]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            Response::error('Coupon not found', 404);
        }

        Response::success($coupon, 'Coupon details retrieved successfully');
    }

    private function createCoupon() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['code', 'type', 'value']);

        $stmt = $this->executeQuery("
            INSERT INTO coupons (code, type, value, min_purchase, start_date, end_date, usage_limit, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $data['code'], $data['type'], $data['value'], 
            $data['min_purchase'] ?? 0, $data['start_date'] ?? null, 
            $data['end_date'] ?? null, $data['usage_limit'] ?? null,
            $data['status'] ?? 'active'
        ]);

        $id = $this->pdo->lastInsertId();
        $this->logAction('create_coupon', ['coupon_id' => $id, 'code' => $data['code']]);

        Response::success(['id' => $id], 'Coupon created successfully', 201);
    }

    private function updateCoupon() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id', 'code', 'type', 'value']);

        $stmt = $this->executeQuery("
            UPDATE coupons SET 
                code = ?, type = ?, value = ?, min_purchase = ?, 
                start_date = ?, end_date = ?, usage_limit = ?, status = ?
            WHERE id = ?
        ", [
            $data['code'], $data['type'], $data['value'], 
            $data['min_purchase'] ?? 0, $data['start_date'] ?? null, 
            $data['end_date'] ?? null, $data['usage_limit'] ?? null,
            $data['status'] ?? 'active', $data['id']
        ]);

        $this->logAction('update_coupon', ['coupon_id' => $data['id']]);
        Response::success(null, 'Coupon updated successfully');
    }

    private function deleteCoupon() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        $stmt = $this->executeQuery("DELETE FROM coupons WHERE id = ?", [$data['id']]);
        $this->logAction('delete_coupon', ['coupon_id' => $data['id']]);

        Response::success(null, 'Coupon deleted successfully');
    }

    private function toggleStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        $stmt = $this->executeQuery("UPDATE coupons SET status = IF(status='active', 'inactive', 'active') WHERE id = ?", [$data['id']]);
        Response::success(null, 'Status toggled successfully');
    }

    private function getStats() {
        $stats = [
            'total' => (int)$this->executeQuery("SELECT COUNT(*) FROM coupons")->fetchColumn(),
            'active' => (int)$this->executeQuery("SELECT COUNT(*) FROM coupons WHERE status='active'")->fetchColumn(),
            'total_usage' => (int)$this->executeQuery("SELECT SUM(usage_count) FROM coupons")->fetchColumn()
        ];
        Response::success($stats, 'Coupon statistics retrieved successfully');
    }
}
?>