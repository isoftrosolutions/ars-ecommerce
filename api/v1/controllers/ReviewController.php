<?php
/**
 * Review Controller
 * Product reviews — public read of approved reviews, authenticated create.
 *
 * @route /products/{id}/reviews
 */

class ReviewController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * GET /products/{id}/reviews
     */
    public function index($params)
    {
        $productId = (int)($params['id'] ?? 0);
        if (!$productId) {
            json_error('Product ID is required', 400);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved'");
        $countStmt->execute([$productId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            SELECT pr.id, pr.rating, pr.comment, pr.created_at,
                   u.full_name as user_name
            FROM product_reviews pr
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE pr.product_id = ? AND pr.status = 'approved'
            ORDER BY pr.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$productId, $limit, $offset]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avgStmt = $this->pdo->prepare("SELECT COALESCE(AVG(rating), 0) FROM product_reviews WHERE product_id = ? AND status = 'approved'");
        $avgStmt->execute([$productId]);
        $avg = round((float)$avgStmt->fetchColumn(), 1);

        json_paginated(
            [
                'reviews' => $reviews,
                'average_rating' => $avg,
                'total_reviews' => $total,
            ],
            $total,
            $page,
            $limit,
        );
    }

    /**
     * POST /products/{id}/reviews
     * Body: { rating: 1-5, comment: "..." }
     */
    public function store($params)
    {
        $user = require_auth();
        $productId = (int)($params['id'] ?? 0);
        if (!$productId) {
            json_error('Product ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        if (!$stmt->fetch()) {
            json_error('Product not found', 404);
        }

        $data = get_json_input();
        validate_required($data, ['rating']);
        validate_numeric($data['rating'] ?? null, 'rating', 1, 5);
        if (isset($data['comment'])) {
            validate_max_length($data['comment'], 'comment', 2000);
        }
        ValidationErrors::throwIfInvalid();

        $rating = (int)$data['rating'];
        $comment = isset($data['comment']) ? sanitize_string($data['comment']) : null;

        // Upsert: one review per user per product
        $stmt = $this->pdo->prepare("SELECT id, status FROM product_reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user['id'], $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE product_reviews SET rating = ?, comment = ?, status = 'pending' WHERE id = ?");
            $stmt->execute([$rating, $comment, $existing['id']]);
            json_success(['id' => (int)$existing['id'], 'status' => 'pending'], 'Review updated and pending approval');
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO product_reviews (product_id, user_id, rating, comment, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$productId, $user['id'], $rating, $comment]);
            $newId = (int)$this->pdo->lastInsertId();
            json_success(['id' => $newId, 'status' => 'pending'], 'Review submitted and pending approval', 201);
        }
    }
}
