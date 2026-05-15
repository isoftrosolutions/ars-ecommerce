<?php
/**
 * Auth Middleware
 * Extracts and validates JWT Bearer token from request headers.
 */

function get_bearer_token()
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (empty($header)) {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
    }

    if (preg_match('/Bearer\s+(.+)$/i', $header, $matches)) {
        return $matches[1];
    }

    return null;
}

function require_auth()
{
    $token = get_bearer_token();

    if (!$token) {
        json_error('Authentication required', 401);
    }

    $payload = verify_token($token);

    if (!$payload) {
        json_error('Invalid or expired token', 401);
    }

    global $pdo;
    $stmt = $pdo->prepare("SELECT id, full_name, email, mobile, role FROM users WHERE id = ?");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        json_error('User not found', 401);
    }

    return $user;
}
