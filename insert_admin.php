<?php
/**
 * Script to insert/update admin user
 * Easy Shopping A.R.S eCommerce Platform
 */

require_once __DIR__ . '/includes/db.php';

// User details
$email = 'easyshoppinga.r.s1@gmail.com';
$mobile = '9820210361';
$password = 'Nepal@123';
$role = 'admin';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        // Update existing user
        $stmt = $pdo->prepare("UPDATE users SET mobile = ?, password = ?, role = ? WHERE email = ?");
        $stmt->execute([$mobile, $hashed_password, $role, $email]);
        echo "Admin user updated successfully!\n";
        echo "Email: $email\n";
        echo "Mobile: $mobile\n";
        echo "Role: $role\n";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', $email, $mobile, $hashed_password, $role]);
        echo "New admin user created successfully!\n";
        echo "Email: $email\n";
        echo "Mobile: $mobile\n";
        echo "Role: $role\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>