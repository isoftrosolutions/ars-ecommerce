<?php
/**
 * Login Tests
 * Easy Shopping A.R.S
 *
 * Comprehensive tests for user login functionality
 * Run with: php tests/LoginTest.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

class LoginTest {

    private $pdo;
    private $testUser = [
        'full_name' => 'Login Test User',
        'email' => 'login-test@example.com',
        'mobile' => '9812345678',
        'password' => 'LoginTest123!',
        'address' => 'Test Address'
    ];

    public function __construct() {
        $this->pdo = $GLOBALS['pdo'];
        $this->cleanup();
        $this->createTestUser();
    }

    private function cleanup() {
        try {
            // Remove test users
            $this->pdo->prepare("DELETE FROM users WHERE email LIKE ? OR mobile LIKE ?")
                     ->execute(['login-test%', '98%']);

            // Clear sessions
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION = [];
            }
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    private function createTestUser() {
        $hashedPassword = password_hash($this->testUser['password'], PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, mobile, password, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([
            $this->testUser['full_name'],
            $this->testUser['email'],
            $this->testUser['mobile'],
            $hashedPassword,
            $this->testUser['address']
        ]);
    }

    public function runTests() {
        echo "🧪 Running Login Tests...\n\n";

        $tests = [
            'testRequiredFields' => 'Required Fields Validation',
            'testInvalidCredentials' => 'Invalid Credentials Handling',
            'testEmailLogin' => 'Email-based Login',
            'testMobileLogin' => 'Mobile-based Login',
            'testCaseSensitivity' => 'Case Sensitivity Handling',
            'testSessionManagement' => 'Session Management',
            'testAdminLogin' => 'Admin User Login',
            'testRememberMeFunctionality' => 'Remember Me Functionality',
            'testOTPLoginFlow' => 'OTP Login Flow',
            'testLogoutFunctionality' => 'Logout Functionality'
        ];

        $results = [];

        foreach ($tests as $method => $description) {
            echo "Running: {$description}\n";
            try {
                $result = $this->{$method}();
                $results[$method] = $result;
                echo "✅ PASSED\n";
            } catch (Exception $e) {
                $results[$method] = false;
                echo "❌ FAILED: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }

        $this->showSummary($results);
        $this->cleanup();
    }

    private function testRequiredFields() {
        // Test empty login_id
        $_POST = ['password' => 'somepassword'];
        $this->simulateLoginPost();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require login_id field");
        }

        // Test empty password
        $_POST = ['login_id' => $this->testUser['email']];
        $this->simulateLoginPost();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require password field");
        }

        return true;
    }

    private function testInvalidCredentials() {
        // Test wrong email
        $_POST = [
            'login_id' => 'wrong@example.com',
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should reject invalid email");
        }

        // Test wrong password
        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => 'wrongpassword'
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should reject invalid password");
        }

        // Test wrong mobile
        $_POST = [
            'login_id' => '9999999999',
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should reject invalid mobile");
        }

        return true;
    }

    private function testEmailLogin() {
        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== $this->testUser['email']) {
            throw new Exception("Should login successfully with correct email");
        }

        if (!isset($_SESSION['user']['password'])) {
            throw new Exception("Password should not be stored in session");
        }

        return true;
    }

    private function testMobileLogin() {
        // Clear previous session
        session_destroy();
        session_start();

        $_POST = [
            'login_id' => $this->testUser['mobile'],
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['user']) || $_SESSION['user']['mobile'] !== $this->testUser['mobile']) {
            throw new Exception("Should login successfully with correct mobile");
        }

        return true;
    }

    private function testCaseSensitivity() {
        // Test email case insensitivity
        session_destroy();
        session_start();

        $_POST = [
            'login_id' => strtoupper($this->testUser['email']),
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['user'])) {
            throw new Exception("Email login should be case insensitive");
        }

        return true;
    }

    private function testSessionManagement() {
        // Test session contains correct user data
        session_destroy();
        session_start();

        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        $user = $_SESSION['user'];

        if ($user['full_name'] !== $this->testUser['full_name'] ||
            $user['email'] !== $this->testUser['email'] ||
            $user['mobile'] !== $this->testUser['mobile'] ||
            $user['role'] !== 'customer') {
            throw new Exception("Session should contain correct user data");
        }

        return true;
    }

    private function testAdminLogin() {
        // Create admin user for testing
        $adminData = [
            'full_name' => 'Admin User',
            'email' => 'admin-test@example.com',
            'mobile' => '9820210361',
            'password' => 'AdminPass123!',
            'address' => 'Admin Address'
        ];

        $hashedPassword = password_hash($adminData['password'], PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, mobile, password, address, role) VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([
            $adminData['full_name'],
            $adminData['email'],
            $adminData['mobile'],
            $hashedPassword,
            $adminData['address']
        ]);

        session_destroy();
        session_start();

        $_POST = [
            'login_id' => $adminData['email'],
            'password' => $adminData['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            throw new Exception("Admin should login successfully and have admin role");
        }

        // Cleanup admin user
        $this->pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$adminData['email']]);

        return true;
    }

    private function testRememberMeFunctionality() {
        session_destroy();
        session_start();

        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password'],
            'remember' => 'on'
        ];
        $this->simulateLoginPost();

        // Check if remember token was set in database
        $stmt = $this->pdo->prepare("SELECT remember_token FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!$user['remember_token']) {
            throw new Exception("Remember token should be set in database");
        }

        return true;
    }

    private function testOTPLoginFlow() {
        // Test OTP sending
        $_POST = ['email' => $this->testUser['email'], 'action' => 'send'];

        ob_start();
        include __DIR__ . '/../backend/send-otp.php';
        ob_end_clean();

        if (!isset($_SESSION['temp_otp']) || $_SESSION['temp_otp']['email'] !== $this->testUser['email']) {
            throw new Exception("OTP should be stored in session");
        }

        // Test OTP verification
        $otp = '123456';
        $_SESSION['temp_otp']['otp'] = password_hash($otp, PASSWORD_DEFAULT);

        $_POST = ['email' => $this->testUser['email'], 'otp' => $otp];

        ob_start();
        include __DIR__ . '/../backend/otp-login.php';
        ob_end_clean();

        if (!isset($_SESSION['user'])) {
            throw new Exception("Should login successfully with valid OTP");
        }

        return true;
    }

    private function testLogoutFunctionality() {
        // First login
        session_destroy();
        session_start();

        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];
        $this->simulateLoginPost();

        if (!isset($_SESSION['user'])) {
            throw new Exception("Should be logged in before testing logout");
        }

        // Now logout
        ob_start();
        include __DIR__ . '/../backend/logout.php';
        ob_end_clean();

        if (isset($_SESSION['user'])) {
            throw new Exception("User should be logged out");
        }

        return true;
    }

    private function simulateLoginPost() {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Clear previous session messages
        unset($_SESSION['error'], $_SESSION['success']);

        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();
    }

    private function showSummary($results) {
        $passed = 0;
        $total = count($results);

        foreach ($results as $result) {
            if ($result) $passed++;
        }

        echo "📊 Login Test Summary:\n";
        echo "Passed: {$passed}/{$total}\n";
        echo "Failed: " . ($total - $passed) . "/{$total}\n";

        if ($passed === $total) {
            echo "\n🎉 All login tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the login implementation.\n";
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new LoginTest();
    $test->runTests();
}
?>