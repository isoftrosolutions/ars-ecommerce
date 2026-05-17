<?php

class WishlistController
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
            SELECT w.id, w.product_id, w.created_at,
                   p.name as product_name, p.image as product_image, p.price, p.discount_price
            FROM wishlists w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
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

        $stmt = $this->pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user['id'], $productId]);
        if ($stmt->fetch()) {
            json_error('Product already in wishlist', 409);
        }

        $stmt = $this->pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user['id'], $productId]);

        json_success(null, 'Added to wishlist', 201);
    }

    public function destroy($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Wishlist item ID is required', 400);
        }

        $stmt = $this->pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);

        if ($stmt->rowCount() === 0) {
            json_error('Wishlist item not found', 404);
        }

        json_success(null, 'Removed from wishlist');
    }

    public function check($params)
    {
        $user = require_auth();
        $productId = (int)($params['product_id'] ?? 0);

        if (!$productId) {
            json_error('Product ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user['id'], $productId]);
        $count = (int)$stmt->fetchColumn();

        json_success(['wishlisted' => $count > 0]);
    }
}
