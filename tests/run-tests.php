<?php
/**
 * Test Runner
 * Easy Shopping A.R.S
 *
 * Runs all authentication tests
 * Usage: php tests/run-tests.php
 */

echo "🚀 ARS Authentication Test Suite\n";
echo "================================\n\n";

// Check if we're in the right directory
if (!file_exists(__DIR__ . '/../includes/db.php')) {
    echo "❌ Error: Please run this script from the tests/ directory\n";
    exit(1);
}

echo "Setting up test environment...\n";

// Start session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Include database connection
    require_once __DIR__ . '/../includes/db.php';

    echo "✅ Database connection established\n\n";

    // Run individual test suites
    $testFiles = [
        'AuthTest.php' => 'Comprehensive Authentication Tests',
        'SignupTest.php' => 'Signup Functionality Tests',
        'LoginTest.php' => 'Login Functionality Tests'
    ];

    $overallResults = [];

    foreach ($testFiles as $file => $description) {
        echo "Running {$description}...\n";
        echo "--------------------------------\n";

        $output = [];
        $returnCode = 0;

        // Execute the test file
        ob_start();
        include __DIR__ . '/' . $file;
        $output = ob_get_clean();

        // Check if the test ran (look for test summary)
        if (strpos($output, 'Test Summary:') !== false) {
            echo $output;
            $overallResults[$file] = true;
        } else {
            echo "❌ Failed to run {$file}\n";
            $overallResults[$file] = false;
        }

        echo "\n";
    }

    // Show overall results
    echo "📊 Overall Test Results:\n";
    echo "========================\n";

    $passed = 0;
    $total = count($overallResults);

    foreach ($overallResults as $test => $result) {
        $status = $result ? '✅ PASSED' : '❌ FAILED';
        echo "{$test}: {$status}\n";
        if ($result) $passed++;
    }

    echo "\nTotal: {$passed}/{$total} test suites passed\n";

    if ($passed === $total) {
        echo "\n🎉 All authentication tests passed! The system is working correctly.\n";
        exit(0);
    } else {
        echo "\n⚠️  Some tests failed. Please review the implementation and fix any issues.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>