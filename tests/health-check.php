<?php
/**
 * Authentication Health Check
 * Easy Shopping A.R.S
 *
 * Quick verification that authentication system is working
 * Run with: php tests/health-check.php
 */

echo "🏥 ARS Authentication Health Check\n";
echo "==================================\n\n";

$checks = [];
$errors = [];

// Check 1: Required files exist
$requiredFiles = [
    '../includes/db.php',
    '../includes/functions.php',
    '../includes/email-service.php',
    '../backend/login.php',
    '../backend/signup.php',
    '../backend/forgot-password.php',
    '../backend/reset-password.php',
    '../backend/send-otp.php',
    '../auth/login.php',
    '../auth/signup.php',
    '../auth/forgot-password.php',
    '../auth/reset-password.php'
];

echo "Checking required files...\n";
foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ {$file}\n";
        $checks[] = true;
    } else {
        echo "❌ {$file} - MISSING\n";
        $checks[] = false;
        $errors[] = "Missing file: {$file}";
    }
}
echo "\n";

// Check 2: Database connection
echo "Checking database connection...\n";
try {
    require_once __DIR__ . '/../includes/db.php';
    // Try a simple query
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "✅ Database connection successful\n";
        $checks[] = true;
    } else {
        throw new Exception("Query failed");
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    $checks[] = false;
    $errors[] = "Database connection failed";
}
echo "\n";

// Check 3: Required database tables
echo "Checking database tables...\n";
$requiredTables = ['users', 'cart_items', 'orders', 'products'];

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ Table '{$table}' exists\n";
            $checks[] = true;
        } else {
            echo "❌ Table '{$table}' missing\n";
            $checks[] = false;
            $errors[] = "Missing table: {$table}";
        }
    }
} catch (Exception $e) {
    echo "❌ Could not check tables: " . $e->getMessage() . "\n";
    $checks[] = false;
    $errors[] = "Table check failed";
}
echo "\n";

// Check 4: Logs directory
echo "Checking logs directory...\n";
$logsDir = __DIR__ . '/../logs';
if (is_dir($logsDir) && is_writable($logsDir)) {
    echo "✅ Logs directory exists and is writable\n";
    $checks[] = true;
} elseif (is_dir($logsDir) && !is_writable($logsDir)) {
    echo "⚠️  Logs directory exists but is not writable\n";
    $checks[] = true; // Not critical
} elseif (!is_dir($logsDir)) {
    echo "❌ Logs directory does not exist\n";
    $checks[] = false;
    $errors[] = "Logs directory missing";
}
echo "\n";

// Check 5: Admin user exists
echo "Checking admin user...\n";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        echo "✅ Admin user exists\n";
        $checks[] = true;
    } else {
        echo "⚠️  No admin user found - you may need to create one\n";
        $checks[] = true; // Not critical for basic functionality
    }
} catch (Exception $e) {
    echo "❌ Could not check admin user: " . $e->getMessage() . "\n";
    $checks[] = false;
    $errors[] = "Admin user check failed";
}
echo "\n";

// Summary
$passed = array_sum($checks);
$total = count($checks);
$failed = $total - $passed;

echo "📊 Health Check Summary:\n";
echo "========================\n";
echo "Passed: {$passed}/{$total}\n";
echo "Failed: {$failed}/{$total}\n\n";

if ($failed === 0) {
    echo "🎉 All health checks passed! Authentication system is ready.\n\n";
    echo "Next steps:\n";
    echo "- Run full test suite: php tests/run-tests.php\n";
    echo "- Access the application at your web server root\n";
    echo "- Default admin login: mobile 9820210361, password 12345678\n";
} else {
    echo "⚠️  Some health checks failed. Please address the following issues:\n\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    echo "\nAfter fixing these issues, run the health check again.\n";
}

echo "\nFor detailed testing, run: php tests/run-tests.php\n";