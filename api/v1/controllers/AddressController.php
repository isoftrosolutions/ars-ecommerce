<?php
/**
 * Address Controller
 * Authenticated user address management.
 *
 * @route /user/addresses/*
 * @auth required
 */

class AddressController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * GET /user/addresses
     */
    public function index($params)
    {
        $user = require_auth();

        $stmt = $this->pdo->prepare("
            SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_success($addresses);
    }

    /**
     * POST /user/addresses
     */
    public function store($params)
    {
        $user = require_auth();

        $data = get_json_input();
        validate_required($data, ['full_name', 'phone', 'province', 'district', 'municipality', 'ward']);
        validate_phone($data['phone'], 'phone');
        ValidationErrors::throwIfInvalid();

        $fullName = sanitize_string($data['full_name']);
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $province = sanitize_string($data['province']);
        $district = sanitize_string($data['district']);
        $municipality = sanitize_string($data['municipality']);
        $ward = sanitize_string($data['ward']);
        $street = isset($data['street']) ? sanitize_string($data['street']) : null;
        $tag = isset($data['tag']) ? sanitize_string($data['tag']) : 'Home';
        $isDefault = isset($data['is_default']) && $data['is_default'] ? 1 : 0;

        // If setting as default, unset others
        if ($isDefault) {
            $this->pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user['id']]);
        } else {
            // Check if this is the first address — make it default
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_addresses WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $count = (int)$stmt->fetchColumn();
            if ($count === 0) {
                $isDefault = 1;
            }
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO user_addresses (user_id, full_name, phone, province, district, municipality, ward, street, tag, is_default, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user['id'], $fullName, $phone, $province, $district, $municipality, $ward, $street, $tag, $isDefault]);

        $addressId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ?");
        $stmt->execute([$addressId]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        json_success($address, 'Address added successfully', 201);
    }

    /**
     * PATCH /user/addresses/{id}
     */
    public function update($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Address ID is required', 400);
        }

        // Verify ownership
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$address) {
            json_error('Address not found', 404);
        }

        $data = get_json_input();

        if (isset($data['phone'])) {
            validate_phone($data['phone'], 'phone');
        }
        ValidationErrors::throwIfInvalid();

        $fields = [];
        $bindings = [];

        $updatable = ['full_name', 'phone', 'province', 'district', 'municipality', 'ward', 'street', 'tag'];
        foreach ($updatable as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $bindings[] = sanitize_string($data[$field]);
            }
        }

        if (isset($data['is_default']) && $data['is_default']) {
            $this->pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user['id']]);
            $fields[] = 'is_default = ?';
            $bindings[] = 1;
        }

        if (empty($fields)) {
            json_error('No fields to update', 400);
        }

        $bindings[] = $id;
        $stmt = $this->pdo->prepare("UPDATE user_addresses SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($bindings);

        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ?");
        $stmt->execute([$id]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);

        json_success($updated, 'Address updated successfully');
    }

    /**
     * PATCH /user/addresses/{id}/set-default
     */
    public function setDefault($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Address ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        if (!$stmt->fetch()) {
            json_error('Address not found', 404);
        }

        $this->pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")->execute([$user['id']]);
        $this->pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ?")->execute([$id]);

        json_success(null, 'Default address updated');
    }

    /**
     * DELETE /user/addresses/{id}
     */
    public function destroy($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Address ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$address) {
            json_error('Address not found', 404);
        }

        $wasDefault = $address['is_default'];
        $this->pdo->prepare("DELETE FROM user_addresses WHERE id = ?")->execute([$id]);

        // If deleted was default, assign new default
        if ($wasDefault) {
            $stmt = $this->pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$user['id']]);
            $remaining = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($remaining) {
                $this->pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ?")->execute([$remaining['id']]);
            }
        }

        json_success(null, 'Address deleted successfully');
    }
}
