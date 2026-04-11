<?php
/**
 * Authentication Middleware
 * Handles admin authentication and authorization
 */
class AuthMiddleware {
    /**
     * Check if user is authenticated and is admin
     */
    public static function authenticate() {
        if (!isset($_SESSION['user'])) {
            Response::error('Authentication required', 401);
        }

        if ($_SESSION['user']['role'] !== 'admin') {
            Response::error('Admin access required', 403);
        }
    }

    /**
     * Get current authenticated user
     */
    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            Response::error('Invalid CSRF token', 403);
        }
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit($action = 'api', $limit = 100, $window = 3600) {
        $userId = $_SESSION['user']['id'] ?? session_id();
        $key = "rate_limit:{$action}:{$userId}";

        // Simple file-based rate limiting (in production, use Redis)
        $rateFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);

        $current = file_exists($rateFile) ? (int)file_get_contents($rateFile) : 0;
        $lastReset = file_exists($rateFile . '.time') ? (int)file_get_contents($rateFile . '.time') : time();

        // Reset counter if window has passed
        if (time() - $lastReset >= $window) {
            $current = 0;
            file_put_contents($rateFile . '.time', time());
        }

        if ($current >= $limit) {
            Response::error('Rate limit exceeded', 429);
        }

        file_put_contents($rateFile, $current + 1);
    }
}
?>