<?php
/**
 * Database Connection File
 * Easy Shopping A.R.S eCommerce Platform
 */

// Database Configuration
$host = 'localhost';
$db   = 'ars_ecommerce';
$user = 'root';
$pass = ''; // Default XAMPP/Apache password is empty
$charset = 'utf8mb4';

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
    // Return JSON for AJAX requests, HTML for page requests
    if (!empty($_POST['action']) || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database service temporarily unavailable.']);
    } else {
        http_response_code(503);
        echo '<div style="font-family:sans-serif;padding:2rem;color:#ef4444"><h2>Service Temporarily Unavailable</h2><p>Please try again later.</p></div>';
    }
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for remember me token
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

            // Generate new token for security (optional rotation)
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
