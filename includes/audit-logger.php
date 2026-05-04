<?php
/**
 * Audit Logger
 * Easy Shopping A.R.S eCommerce Platform
 * 
 * Tracks admin actions for compliance and debugging.
 * Creates the audit_log table automatically if it doesn't exist.
 */

class AuditLogger {

    private static $tableChecked = false;

    /**
     * Ensure the audit_log table exists
     */
    private static function ensureTable() {
        if (self::$tableChecked) return;
        global $pdo;
        
        // DDL statements like CREATE TABLE cause implicit commits in MySQL.
        // If we are in a transaction, we MUST NOT run this, otherwise it breaks the transaction.
        if ($pdo->inTransaction()) {
            return;
        }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `audit_log` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `user_id` INT DEFAULT NULL,
                    `user_name` VARCHAR(255) DEFAULT NULL,
                    `action` VARCHAR(100) NOT NULL,
                    `entity_type` VARCHAR(50) DEFAULT NULL COMMENT 'e.g. product, order, user, category',
                    `entity_id` INT DEFAULT NULL,
                    `description` TEXT DEFAULT NULL,
                    `old_values` JSON DEFAULT NULL,
                    `new_values` JSON DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `user_agent` VARCHAR(500) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_audit_user` (`user_id`),
                    INDEX `idx_audit_action` (`action`),
                    INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
                    INDEX `idx_audit_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$tableChecked = true;
        } catch (PDOException $e) {
            error_log('[ARS] Audit table creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Log an admin/user action
     * 
     * @param string      $action      Action name (e.g. 'product.create', 'order.update_status')
     * @param string|null $entityType  Entity type (product, order, user, category, setting)
     * @param int|null    $entityId    Related entity ID
     * @param string|null $description Human-readable description
     * @param array|null  $oldValues   Previous values (for updates)
     * @param array|null  $newValues   New values (for creates/updates)
     */
    public static function log(
        $action,
        $entityType = null,
        $entityId = null,
        $description = null,
        $oldValues = null,
        $newValues = null
    ) {
        self::ensureTable();

        global $pdo;

        $userId   = $_SESSION['user']['id'] ?? null;
        $userName = $_SESSION['user']['full_name'] ?? 'Guest';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO audit_log 
                    (user_id, user_name, action, entity_type, entity_id, description, old_values, new_values, ip_address, user_agent)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $userName,
                $action,
                $entityType,
                $entityId,
                $description,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ip,
                $ua,
            ]);
        } catch (PDOException $e) {
            // Never let audit logging break the main flow
            error_log('[ARS] Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Quick log helpers for common actions
     */
    public static function logLogin($userId, $userName) {
        self::log('user.login', 'user', $userId, "$userName logged in");
    }

    public static function logLogout($userId, $userName) {
        self::log('user.logout', 'user', $userId, "$userName logged out");
    }

    public static function logOrderCreate($orderId, $total) {
        self::log('order.create', 'order', $orderId, "New order #$orderId placed (Total: $total)");
    }

    public static function logOrderStatusChange($orderId, $oldStatus, $newStatus) {
        self::log('order.update_status', 'order', $orderId, 
            "Order #$orderId status changed: $oldStatus → $newStatus",
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );
    }

    public static function logProductCreate($productId, $productName) {
        self::log('product.create', 'product', $productId, "Product created: $productName");
    }

    public static function logProductUpdate($productId, $productName, $oldValues = null, $newValues = null) {
        self::log('product.update', 'product', $productId, "Product updated: $productName", $oldValues, $newValues);
    }

    public static function logProductDelete($productId, $productName) {
        self::log('product.delete', 'product', $productId, "Product deleted: $productName");
    }

    public static function logSettingsUpdate($key, $oldValue, $newValue) {
        self::log('settings.update', 'setting', null, "Setting '$key' updated",
            ['value' => $oldValue],
            ['value' => $newValue]
        );
    }

    /**
     * Get recent audit logs (for admin dashboard)
     */
    public static function getRecent($limit = 50, $filters = []) {
        self::ensureTable();
        global $pdo;

        try {
            $query = "SELECT * FROM audit_log WHERE 1=1";
            $params = [];

            if (!empty($filters['action'])) {
                $query .= " AND action LIKE ?";
                $params[] = '%' . $filters['action'] . '%';
            }
            if (!empty($filters['entity_type'])) {
                $query .= " AND entity_type = ?";
                $params[] = $filters['entity_type'];
            }
            if (!empty($filters['user_id'])) {
                $query .= " AND user_id = ?";
                $params[] = $filters['user_id'];
            }

            $query .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = (int) $limit;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('[ARS] Audit log fetch failed: ' . $e->getMessage());
            return [];
        }
    }
}
?>
