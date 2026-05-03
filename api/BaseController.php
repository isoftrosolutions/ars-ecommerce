<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
abstract class BaseController {
    protected $pdo;
    protected $logger;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->logger = new Logger();
    }

    /**
     * Handle incoming request
     */
    abstract public function handleRequest($method, $action);

    /**
     * Get sanitized input data
     */
    protected function getInputData() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        return ValidationMiddleware::sanitizeArray($data);
    }

    /**
     * Get query parameters
     */
    protected function getQueryParams() {
        return ValidationMiddleware::sanitizeArray($_GET);
    }

    /**
     * Validate pagination parameters
     */
    protected function validatePagination($params) {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 10);

        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 10;

        return ['page' => $page, 'limit' => $limit];
    }

    /**
     * Build pagination info
     */
    protected function buildPagination($total, $page, $limit) {
        $totalPages = ceil($total / $limit);

        return [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ];
    }

    /**
     * Execute query with error handling
     */
    protected function executeQuery($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logger->error('Database query failed: ' . $e->getMessage(), [
                'query' => $query,
                'params' => $params
            ]);
            Response::error('Database error occurred', 500);
        }
    }

    /**
     * Begin database transaction
     */
    protected function beginTransaction() {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit database transaction
     */
    protected function commit() {
        $this->pdo->commit();
    }

    /**
     * Rollback database transaction
     */
    protected function rollback() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Log admin action
     */
    protected function logAction($action, $details = []) {
        $user = AuthMiddleware::getCurrentUser();
        $this->logger->info("Admin action: {$action}", array_merge($details, [
            'user_id' => $user['id'] ?? null,
            'user_name' => $user['full_name'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]));
    }
}
?>