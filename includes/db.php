<?php
/**
 * Database Connection & Security Bootstrap
 * Easy Shopping A.R.S eCommerce Platform
 * 
 * This file initializes: env config, DB connection, session hardening,
 * security headers, and remember-me token handling.
 */

// ── 1. Load Environment Configuration ─────────────────────────────
require_once __DIR__ . '/env.php';

// ── 2. Database Configuration (from .env) ─────────────────────────
$host    = env('DB_HOST', 'localhost');
$db      = env('DB_NAME', 'ars_ecommerce');
$user    = env('DB_USER', 'root');
$pass    = env('DB_PASS', '');
$charset = env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log('[ARS] Database connection failed: ' . $e->getMessage());
    // Detect API context: check Content-Type header or AJAX indicators
    $isApi = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
          || !empty($_POST['action'])
          || (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);
    
    // Also check if Content-Type was already set to JSON (by api/index.php)
    $headers = headers_list();
    foreach ($headers as $h) {
        if (stripos($h, 'Content-Type: application/json') !== false) {
            $isApi = true;
            break;
        }
    }
    
    if ($isApi) {
        // Clean any buffered output that might have leaked
        while (ob_get_level()) {
            ob_end_clean();
        }
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database service temporarily unavailable.']);
    } else {
        http_response_code(503);
        echo '<div style="font-family:sans-serif;padding:2rem;color:#ef4444"><h2>Service Temporarily Unavailable</h2><p>Please try again later.</p></div>';
    }
    exit();
}

// ── 3. Hardened Session Configuration ─────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
             || env('SESSION_SECURE', false);

    session_set_cookie_params([
        'lifetime' => (int) env('SESSION_LIFETIME', 0),
        'path'     => '/',
        'domain'   => '',
        'secure'   => (bool) $isSecure,
        'httponly'  => true,
        'samesite'  => 'Lax',
    ]);

    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);

    session_start();
}

// ── 4. Security Headers (applied on every request) ────────────────
if (!headers_sent()) {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');
    // Control referrer information
    header('Referrer-Policy: strict-origin-when-cross-origin');
    // Restrict browser features
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // Legacy XSS protection for older browsers
    header('X-XSS-Protection: 1; mode=block');

    // HSTS — only enable when SSL is confirmed in production
    if (env('APP_ENV') === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// ── 5. Session Validation ─────────────────────────────────────────
require_once __DIR__ . '/middleware/SessionMiddleware.php';
SessionMiddleware::validateSession();

// ── 6. Check for Remember-Me Token ────────────────────────────────
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $hashed_token = hash('sha256', $token);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$hashed_token]);
        $user = $stmt->fetch();

        if ($user) {
            // Valid token, log user in
            unset($user['password']);
            $_SESSION['user'] = $user;
            $_SESSION['session_created'] = time(); // Track session creation time

            // Generate new token for security (rotation)
            $new_token = bin2hex(random_bytes(32));
            $new_hashed_token = hash('sha256', $new_token);

            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$new_hashed_token, $user['id']]);

            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            setcookie('remember_token', $new_token, time() + (30 * 24 * 60 * 60), '/', '', $secure, true);
        } else {
            // Invalid token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
    } catch (PDOException $e) {
        // Silently fail for remember me functionality
        error_log('Remember me token check failed: ' . $e->getMessage());
    }
}
?>
