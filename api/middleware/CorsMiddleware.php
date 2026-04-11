<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing
 */
class CorsMiddleware {
    /**
     * Handle CORS preflight request
     */
    public static function handlePreflight() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
            header('Access-Control-Max-Age: 86400');
            http_response_code(200);
            exit;
        }
    }

    /**
     * Set CORS headers for response
     */
    public static function setHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Expose-Headers: Content-Length, X-Custom-Header');
    }
}
?>