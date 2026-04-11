<?php
/**
 * User Logout Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';

// If session exists, destroy it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear remember me token if exists
if (isset($_SESSION['user']['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Clear remember me cookie
setcookie('remember_token', '', time() - 3600, '/');

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Redirect to home page
header("Location: ../index.php");
exit();
?>
