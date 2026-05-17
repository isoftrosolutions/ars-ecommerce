<?php

class CartController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function index($params)
    {
        $user = require_auth();

        $stmt = $this->pdo->prepare("
            SELECT c.id, c.product_id, c.quantity, c.created_at, c.updated_at,
                   p.name as product_name, p.image as product_image, p.price, p.discount_price, p.stock
            FROM carts c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['product_image'] = product_image_url($item['product_image']);
        }
        unset($item);

        json_success($items);
    }

    public function store($params)
    {
        $user = require_auth();
        $data = get_json_input();
        validate_required($data, ['product_id']);
        ValidationErrors::throwIfInvalid();

        $productId = (int)$data['product_id'];
        $quantity = max(1, (int)($data['quantity'] ?? 1));

        $stmt = $this->pdo->prepare("
            INSERT INTO carts (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = NOW()
        ");
        $stmt->execute([$user['id'], $productId, $quantity]);

        json_success(null, 'Cart updated', 201);
    }

    public function destroy($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Cart item ID is required', 400);
        }

        $stmt = $this->pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);

        if ($stmt->rowCount() === 0) {
            json_error('Cart item not found', 404);
        }

        json_success(null, 'Removed from cart');
    }

    public function sync($params)
    {
        $user = require_auth();
        $data = get_json_input();

        if (!isset($data['items']) || !is_array($data['items'])) {
            json_error('Items array is required', 400);
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("DELETE FROM carts WHERE user_id = ?");
            $stmt->execute([$user['id']]);

            $stmt = $this->pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
            foreach ($data['items'] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $quantity = max(1, (int)($item['quantity'] ?? 1));
                if ($productId) {
                    $stmt->execute([$user['id'], $productId, $quantity]);
                }
            }

            $this->pdo->commit();
            json_success(null, 'Cart synced successfully');
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            json_error('Failed to sync cart', 500);
        }
    }
}
