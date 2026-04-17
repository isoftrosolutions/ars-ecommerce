<?php
/**
 * Review Controller
 * Handles product reviews management
 */
class ReviewController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getReviews();
                    case 'stats':
                        return $this->getStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'update-status':
                        return $this->updateStatus();
                    case 'bulk-update-status':
                        return $this->bulkUpdateStatus();
                    case 'delete':
                        return $this->deleteReview();
                    case 'bulk-delete':
                        return $this->bulkDelete();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    private function getReviews() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        if (!empty($params['status'])) {
            $where[] = "pr.status = ?";
            $queryParams[] = $params['status'];
        }

        if (!empty($params['rating'])) {
            $where[] = "pr.rating = ?";
            $queryParams[] = (int)$params['rating'];
        }

        if (!empty($params['product_id'])) {
            $where[] = "pr.product_id = ?";
            $queryParams[] = (int)$params['product_id'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $countStmt = $this->executeQuery("SELECT COUNT(*) FROM product_reviews pr $whereClause", $queryParams);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery("
            SELECT pr.*, p.name as product_name, u.full_name as user_name, u.email as user_email
            FROM product_reviews pr
            JOIN products p ON pr.product_id = p.id
            JOIN users u ON pr.user_id = u.id
            $whereClause
            ORDER BY pr.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($reviews, $paginationInfo, 'Reviews retrieved successfully');
    }

    private function updateStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id', 'status']);
        ValidationMiddleware::validateEnum($data['status'], ['pending', 'approved', 'rejected']);

        $stmt = $this->executeQuery("UPDATE product_reviews SET status = ? WHERE id = ?", [$data['status'], $data['id']]);
        
        if ($stmt->rowCount() === 0) {
            Response::error('Review not found or no changes made', 404);
        }

        $this->logAction('update_review_status', ['review_id' => $data['id'], 'status' => $data['status']]);
        Response::success(null, 'Review status updated');
    }

    private function bulkUpdateStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['ids', 'status']);
        ValidationMiddleware::validateEnum($data['status'], ['pending', 'approved', 'rejected']);

        $ids = $data['ids'];
        if (!is_array($ids) || empty($ids)) {
            Response::error('Invalid IDs', 400);
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $params = array_merge([$data['status']], $ids);

        $stmt = $this->executeQuery("UPDATE product_reviews SET status = ? WHERE id IN ($placeholders)", $params);
        Response::success(['updated' => $stmt->rowCount()], 'Bulk status update successful');
    }

    private function deleteReview() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        $this->executeQuery("DELETE FROM product_reviews WHERE id = ?", [$data['id']]);
        Response::success(null, 'Review deleted');
    }

    private function bulkDelete() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['ids']);

        $ids = $data['ids'];
        if (!is_array($ids) || empty($ids)) {
            Response::error('Invalid IDs', 400);
        }

        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $this->executeQuery("DELETE FROM product_reviews WHERE id IN ($placeholders)", $ids);
        Response::success(['deleted' => $stmt->rowCount()], 'Bulk delete successful');
    }

    private function getStats() {
        $stats = [
            'total' => (int)$this->executeQuery("SELECT COUNT(*) FROM product_reviews")->fetchColumn(),
            'pending' => (int)$this->executeQuery("SELECT COUNT(*) FROM product_reviews WHERE status='pending'")->fetchColumn(),
            'average_rating' => round((float)$this->executeQuery("SELECT AVG(rating) FROM product_reviews WHERE status='approved'")->fetchColumn(), 1)
        ];
        Response::success($stats, 'Review statistics retrieved successfully');
    }
}
?>