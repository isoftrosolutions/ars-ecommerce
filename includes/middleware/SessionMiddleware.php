<?php
/**
 * Session Validation Middleware
 * Validates user sessions and handles expiration
 */
class SessionMiddleware {
    /**
     * Check if user session is valid and not expired
     */
    public static function validateSession() {
        if (!isset($_SESSION['user'])) {
            return; // No user logged in, nothing to validate
        }

        if (!isset($_SESSION['session_created'])) {
            // Session created before this implementation, treat as valid
            $_SESSION['session_created'] = time();
            return;
        }

        $session_lifetime = (int) env('SESSION_LIFETIME', 2592000); // 30 days in seconds
        $session_age = time() - $_SESSION['session_created'];

        if ($session_age > $session_lifetime) {
            // Session expired, destroy session and redirect
            session_destroy();

            // Check if this is an AJAX/API request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            if ($isAjax || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
                exit();
            } else {
                header("Location: " . url('/auth/login.php?expired=1'));
                exit();
            }
        }
    }

    /**
     * Extend session lifetime (optional - call this on user activity)
     */
    public static function extendSession() {
        if (isset($_SESSION['user'])) {
            $_SESSION['session_created'] = time();
        }
    }
}
?>