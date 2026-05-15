<?php
/**
 * User Controller
 * Authenticated user profile management.
 *
 * @route /user/*
 * @auth required
 */

class UserController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * GET /user/me
     */
    public function me($params)
    {
        $user = require_auth();

        $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, address, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            json_error('User not found', 404);
        }

        json_success([
            'user' => [
                'id' => (int)$userData['id'],
                'name' => $userData['full_name'],
                'email' => $userData['email'],
                'phone' => $userData['mobile'],
                'address' => $userData['address'],
                'role' => $userData['role'],
                'created_at' => $userData['created_at'],
            ],
        ]);
    }

    /**
     * PATCH /user/me
     */
    public function updateMe($params)
    {
        $user = require_auth();

        $data = get_json_input();

        if (!empty($data['email'])) {
            validate_email($data['email']);
            // Check uniqueness
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $user['id']]);
            if ($stmt->fetch()) {
                ValidationErrors::add('email', 'Email already in use');
            }
        }
        ValidationErrors::throwIfInvalid();

        $fields = [];
        $bindings = [];

        if (isset($data['name'])) {
            $fields[] = 'full_name = ?';
            $bindings[] = sanitize_string($data['name']);
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $bindings[] = sanitize_string($data['email']);
        }
        if (isset($data['address'])) {
            $fields[] = 'address = ?';
            $bindings[] = sanitize_string($data['address']);
        }

        if (empty($fields)) {
            json_error('No fields to update', 400);
        }

        $bindings[] = $user['id'];
        $stmt = $this->pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($bindings);

        // Return updated user
        $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, address, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        json_success([
            'user' => [
                'id' => (int)$userData['id'],
                'name' => $userData['full_name'],
                'email' => $userData['email'],
                'phone' => $userData['mobile'],
                'address' => $userData['address'],
                'role' => $userData['role'],
            ],
        ], 'Profile updated successfully');
    }
}
