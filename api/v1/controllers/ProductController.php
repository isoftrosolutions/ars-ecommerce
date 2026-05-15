<?php
/**
 * Product Controller
 * Public product listing and detail endpoints.
 *
 * @route /products/*
 * @auth public
 */

class ProductController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * GET /products?page=1&limit=20&category=&search=&sort=newest
     */
    public function index($params)
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $categoryId = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';

        $where = [];
        $bindings = [];

        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $bindings[] = (int)$categoryId;
        }

        if ($search) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
            $term = '%' . $search . '%';
            $bindings[] = $term;
            $bindings[] = $term;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Sort
        switch ($sort) {
            case 'price_asc':
                $orderBy = 'p.price ASC';
                break;
            case 'price_desc':
                $orderBy = 'p.price DESC';
                break;
            case 'popular':
                $orderBy = 'p.stock ASC, p.created_at DESC';
                break;
            default:
                $orderBy = 'p.created_at DESC';
        }

        // Count
        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM products p $whereClause");
        $countStmt->execute($bindings);
        $total = (int)$countStmt->fetchColumn();

        // Fetch
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.stock, p.image,
                   p.is_featured, p.created_at, c.id as category_id, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $whereClause
            ORDER BY $orderBy
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($bindings, [$limit, $offset]));
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrich
        foreach ($products as &$product) {
            $product['original_price'] = $product['price'];
            $product['discount_percent'] = $this->calcDiscountPercent($product['price'], $product['discount_price']);
            $product['image'] = product_image_url($product['image']);
            $product['in_stock'] = (int)$product['stock'] > 0;
            $product['rating'] = $this->getProductRating($product['id']);
            $product['review_count'] = $this->getReviewCount($product['id']);
        }
        unset($product);

        json_paginated($products, $total, $page, $limit);
    }

    /**
     * GET /products/{id}
     */
    public function show($params)
    {
        $id = (int)($params['id'] ?? 0);
        if (!$id) {
            json_error('Product ID is required', 400);
        }

        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            json_error('Product not found', 404);
        }

        $product['original_price'] = $product['price'];
        $product['discount_percent'] = $this->calcDiscountPercent($product['price'], $product['discount_price']);
        $product['image'] = product_image_url($product['image']);
        $product['in_stock'] = (int)$product['stock'] > 0;
        $product['images'] = $this->getProductImages($id);
        $product['rating'] = $this->getProductRating($id);
        $product['review_count'] = $this->getReviewCount($id);

        // Get reviews
        $stmt = $this->pdo->prepare("
            SELECT pr.rating, pr.comment, pr.created_at, u.full_name as user_name
            FROM product_reviews pr
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE pr.product_id = ? AND pr.status = 'approved'
            ORDER BY pr.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_success([
            'product' => $product,
            'reviews' => $reviews,
        ]);
    }

    /**
     * GET /products/featured
     */
    public function featured($params)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.stock, p.image,
                   p.is_featured, p.created_at, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['original_price'] = $product['price'];
            $product['discount_percent'] = $this->calcDiscountPercent($product['price'], $product['discount_price']);
            $product['image'] = product_image_url($product['image']);
            $product['in_stock'] = (int)$product['stock'] > 0;
            $product['rating'] = $this->getProductRating($product['id']);
            $product['review_count'] = $this->getReviewCount($product['id']);
        }
        unset($product);

        json_success($products);
    }

    /**
     * GET /products/new-arrivals
     */
    public function newArrivals($params)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.stock, p.image,
                   p.is_featured, p.created_at, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['original_price'] = $product['price'];
            $product['discount_percent'] = $this->calcDiscountPercent($product['price'], $product['discount_price']);
            $product['image'] = product_image_url($product['image']);
            $product['in_stock'] = (int)$product['stock'] > 0;
            $product['rating'] = $this->getProductRating($product['id']);
            $product['review_count'] = $this->getReviewCount($product['id']);
        }
        unset($product);

        json_success($products);
    }

    private function getProductImages($productId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, image_path, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC
        ");
        $stmt->execute([$productId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($images as &$img) {
            $img['image_path'] = product_image_url($img['image_path']);
        }
        return $images;
    }

    private function getProductRating($productId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(AVG(rating), 0) as avg_rating
            FROM product_reviews
            WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? round((float)$row['avg_rating'], 1) : 0;
    }

    private function getReviewCount($productId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$productId]);
        return (int)$stmt->fetchColumn();
    }

    private function calcDiscountPercent($price, $discountPrice)
    {
        if ($discountPrice && $price > 0) {
            return round((($price - $discountPrice) / $price) * 100);
        }
        return 0;
    }
}
