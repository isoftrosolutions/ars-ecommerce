<?php
/**
 * AJAX endpoint to check login status
 * Returns user data if logged in, or empty if not
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (is_logged_in()) {
    $user = $_SESSION['user'];
    // Return only necessary user data for the header
    echo json_encode([
        'logged_in' => true,
        'full_name' => $user['full_name'],
        'email' => $user['email']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>