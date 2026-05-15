<?php
/**
 * JWT Helper
 * Token generation and verification using firebase/php-jwt.
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generate_token($userId, $role = 'customer')
{
    $now = time();
    $expiry = $now + (JWT_EXPIRY_DAYS * 86400);

    $payload = [
        'iss' => env('APP_BASE_URL', 'https://easyshoppingars.com'),
        'iat' => $now,
        'exp' => $expiry,
        'sub' => $userId,
        'role' => $role,
    ];

    return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
}

function verify_token($token)
{
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
        return (array)$decoded;
    } catch (\Exception $e) {
        error_log('[APIv1] JWT verification failed: ' . $e->getMessage(), 3, __DIR__ . '/../../logs/api-v1.log');
        return null;
    }
}
