<?php
/**
 * Health Check Controller
 * Easy Shopping A.R.S — API Health Endpoint
 * 
 * GET /api/health — Returns system health status
 * Checks: Database connectivity, disk space, PHP version, uptime
 */
class HealthController extends BaseController {

    public function handleRequest($method, $action) {
        if ($method !== 'GET') {
            Response::error('Method not allowed', 405);
            return;
        }

        switch ($action) {
            case 'index':
            case 'check':
                $this->healthCheck();
                break;
            case 'detailed':
                AuthMiddleware::authenticate();
                $this->detailedHealthCheck();
                break;
            default:
                Response::error('Action not found', 404);
        }
    }

    /**
     * Basic health check — public, lightweight
     */
    private function healthCheck() {
        $status = 'healthy';
        $checks = [];

        // 1. Database connectivity
        try {
            global $pdo;
            $stmt = $pdo->query("SELECT 1");
            $checks['database'] = ['status' => 'ok', 'latency_ms' => 0];
            
            $start = microtime(true);
            $pdo->query("SELECT COUNT(*) FROM products");
            $checks['database']['latency_ms'] = round((microtime(true) - $start) * 1000, 2);
        } catch (Exception $e) {
            $checks['database'] = ['status' => 'error', 'message' => 'Connection failed'];
            $status = 'unhealthy';
        }

        // 2. Disk space
        $freeSpace = @disk_free_space(__DIR__ . '/../../');
        $totalSpace = @disk_total_space(__DIR__ . '/../../');
        if ($freeSpace !== false && $totalSpace !== false) {
            $freePercent = round(($freeSpace / $totalSpace) * 100, 1);
            $checks['disk'] = [
                'status' => $freePercent > 10 ? 'ok' : 'warning',
                'free_percent' => $freePercent,
                'free_gb' => round($freeSpace / 1073741824, 2),
            ];
            if ($freePercent <= 5) {
                $status = 'unhealthy';
            }
        }

        // 3. Uploads directory writable
        $uploadsDir = __DIR__ . '/../../uploads/';
        $checks['uploads'] = [
            'status' => is_writable($uploadsDir) ? 'ok' : 'error',
        ];
        if (!is_writable($uploadsDir)) {
            $status = 'degraded';
        }

        // 4. Logs directory writable
        $logsDir = __DIR__ . '/../../logs/';
        $checks['logs'] = [
            'status' => (is_dir($logsDir) && is_writable($logsDir)) ? 'ok' : 'warning',
        ];

        $httpCode = $status === 'healthy' ? 200 : ($status === 'degraded' ? 200 : 503);
        
        http_response_code($httpCode);
        echo json_encode([
            'status' => $status,
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'checks' => $checks,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Detailed health check — admin only
     */
    private function detailedHealthCheck() {
        $basic = [];

        // Database info
        try {
            global $pdo;
            $version = $pdo->query("SELECT VERSION()")->fetchColumn();
            $tables = $pdo->query("SHOW TABLE STATUS")->fetchAll();
            $totalRows = array_sum(array_column($tables, 'Rows'));
            $totalSize = array_sum(array_column($tables, 'Data_length'));

            $basic['database'] = [
                'status' => 'ok',
                'version' => $version,
                'total_tables' => count($tables),
                'total_rows' => $totalRows,
                'data_size_mb' => round($totalSize / 1048576, 2),
            ];
        } catch (Exception $e) {
            $basic['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // PHP info
        $basic['php'] = [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'opcache_enabled' => function_exists('opcache_get_status'),
        ];

        // Session info
        $basic['session'] = [
            'handler' => ini_get('session.save_handler'),
            'cookie_httponly' => (bool) ini_get('session.cookie_httponly'),
            'cookie_secure' => (bool) ini_get('session.cookie_secure'),
            'use_strict_mode' => (bool) ini_get('session.use_strict_mode'),
        ];

        // Environment
        $basic['environment'] = [
            'app_env' => env('APP_ENV', 'unknown'),
            'app_debug' => env('APP_DEBUG', 'unknown'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        ];

        // Disk
        $freeSpace = @disk_free_space(__DIR__ . '/../../');
        $totalSpace = @disk_total_space(__DIR__ . '/../../');
        if ($freeSpace !== false && $totalSpace !== false) {
            $basic['disk'] = [
                'total_gb' => round($totalSpace / 1073741824, 2),
                'free_gb' => round($freeSpace / 1073741824, 2),
                'used_percent' => round((1 - $freeSpace / $totalSpace) * 100, 1),
            ];
        }

        // Recent errors count
        $logFile = __DIR__ . '/../../logs/api.log';
        $basic['logs'] = [
            'api_log_exists' => file_exists($logFile),
            'api_log_size_kb' => file_exists($logFile) ? round(filesize($logFile) / 1024, 2) : 0,
        ];

        Response::success($basic, 'Detailed health check');
    }
}
?>
