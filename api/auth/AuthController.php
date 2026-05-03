<?php
/**
 * Auth Controller
 * Handles authentication-related operations
 */
class AuthController extends BaseController {
    public function handleRequest($method, $action) {
        switch ($method) {
            case 'POST':
                switch ($action) {
                    case 'login':
                        return $this->login();
                    case 'logout':
                        return $this->logout();
                    case 'check':
                        return $this->checkSession();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'GET':
                switch ($action) {
                    case 'user':
                        return $this->getCurrentUser();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Admin login
     */
    private function login() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['email', 'password']);
        ValidationMiddleware::validateEmail($data['email']);
        ValidationMiddleware::throwIfInvalid();

        // Check user credentials
        $stmt = $this->executeQuery(
            "SELECT * FROM users WHERE email = ? AND role = 'admin'",
            [$data['email']]
        );

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            $this->logger->warning('Failed admin login attempt', [
                'email' => $data['email'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            Response::error('Invalid credentials', 401);
        }

        // Check if email is verified
        if (empty($user['email_verified_at'])) {
            Response::error('Email not verified', 403);
        }

        // Start session and set user data
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Update last activity
        $this->executeQuery(
            "INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE last_activity = NOW()",
            [
                $user['id'],
                session_id(),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]
        );

        $this->logger->info('Admin login successful', [
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        Response::success([
            'user' => [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Login successful');
    }

    /**
     * Logout
     */
    private function logout() {
        AuthMiddleware::authenticate();

        $user = AuthMiddleware::getCurrentUser();
        $this->logger->info('Admin logout', ['user_id' => $user['id']]);

        // Clear session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();

        Response::success(null, 'Logout successful');
    }

    /**
     * Check if session is valid
     */
    private function checkSession() {
        if (!isset($_SESSION['user'])) {
            Response::error('Session expired', 401);
        }

        // Update last activity
        $this->executeQuery(
            "UPDATE user_sessions SET last_activity = NOW() WHERE session_id = ?",
            [session_id()]
        );

        Response::success([
            'user' => $_SESSION['user'],
            'valid' => true
        ], 'Session is valid');
    }

    /**
     * Get current user info
     */
    private function getCurrentUser() {
        AuthMiddleware::authenticate();

        $user = AuthMiddleware::getCurrentUser();

        Response::success(['user' => $user], 'User info retrieved');
    }
}
?>