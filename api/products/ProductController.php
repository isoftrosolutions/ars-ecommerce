<?php
/**
 * Products Controller
 * Handles product CRUD operations and management
 */
class ProductController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();
        AuthMiddleware::checkRateLimit('products', 100, 3600);

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getProducts();
                    case 'detail':
                        return $this->getProduct();
                    case 'categories':
                        return $this->getCategories();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'create':
                        return $this->createProduct();
                    case 'update':
                        return $this->updateProduct();
                    case 'delete':
                        return $this->deleteProduct();
                    case 'bulk-delete':
                        return $this->bulkDeleteProducts();
                    case 'toggle-featured':
                        return $this->toggleFeatured();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get paginated products list
     */
    private function getProducts() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        // Search filter
        if (!empty($params['search'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm]);
        }

        // Category filter
        if (!empty($params['category_id'])) {
            $where[] = "p.category_id = ?";
            $queryParams[] = $params['category_id'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // Get total count
        $countStmt = $this->executeQuery(
            "SELECT COUNT(*) FROM products p $whereClause",
            $queryParams
        );
        $total = (int)$countStmt->fetchColumn();

        // Get products
        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $whereClause
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add image URLs
        foreach ($products as &$product) {
            $product['image_url'] = $product['image'] ? '/uploads/products/' . $product['image'] : null;
            $product['images'] = $this->getProductImages($product['id']);
        }

        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($products, $paginationInfo, 'Products retrieved successfully');
    }

    /**
     * Get single product details
     */
    private function getProduct() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);
        ValidationMiddleware::throwIfInvalid();

        $stmt = $this->executeQuery("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ", [$params['id']]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Response::error('Product not found', 404);
        }

        $product['image_url'] = $product['image'] ? '/uploads/products/' . $product['image'] : null;
        $product['images'] = $this->getProductImages($product['id']);

        Response::success($product, 'Product retrieved successfully');
    }

    /**
     * Create new product
     */
    private function createProduct() {
        $data = $this->getInputData();
        $this->validateProductData($data);

        $this->beginTransaction();

        try {
            // Insert product
            $stmt = $this->executeQuery("
                INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, sku, is_featured, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['price'],
                $data['discount_price'] ?? null,
                $data['category_id'] ?? null,
                $data['stock'] ?? 0,
                $data['sku'] ?? null,
                isset($data['is_featured']) ? 1 : 0
            ]);

            $productId = $this->pdo->lastInsertId();

            // Handle images
            if (!empty($data['images'])) {
                $this->handleProductImages($productId, $data['images']);
            }

            $this->commit();
            $this->logAction('create_product', ['product_id' => $productId, 'name' => $data['name']]);

            Response::success(['id' => $productId], 'Product created successfully', 201);

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Update existing product
     */
    private function updateProduct() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);
        $this->validateProductData($data);

        $this->beginTransaction();

        try {
            // Update product
            $stmt = $this->executeQuery("
                UPDATE products SET
                    name = ?, slug = ?, description = ?, price = ?, discount_price = ?,
                    category_id = ?, stock = ?, sku = ?, is_featured = ?
                WHERE id = ?
            ", [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $data['price'],
                $data['discount_price'] ?? null,
                $data['category_id'] ?? null,
                $data['stock'] ?? 0,
                $data['sku'] ?? null,
                isset($data['is_featured']) ? 1 : 0,
                $data['id']
            ]);

            // Handle images
            if (isset($data['images'])) {
                $this->handleProductImages($data['id'], $data['images']);
            }

            $this->commit();
            $this->logAction('update_product', ['product_id' => $data['id'], 'name' => $data['name']]);

            Response::success(null, 'Product updated successfully');

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Delete product
     */
    private function deleteProduct() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        // Check if product exists
        $stmt = $this->executeQuery("SELECT name FROM products WHERE id = ?", [$data['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Response::error('Product not found', 404);
        }

        // Check if product is used in orders
        $stmt = $this->executeQuery("SELECT COUNT(*) FROM order_items WHERE product_id = ?", [$data['id']]);
        $orderCount = (int)$stmt->fetchColumn();

        if ($orderCount > 0) {
            Response::error('Cannot delete product that has been ordered', 400);
        }

        $this->beginTransaction();

        try {
            // Delete product images
            $this->executeQuery("DELETE FROM product_images WHERE product_id = ?", [$data['id']]);

            // Delete product
            $this->executeQuery("DELETE FROM products WHERE id = ?", [$data['id']]);

            $this->commit();
            $this->logAction('delete_product', ['product_id' => $data['id'], 'name' => $product['name']]);

            Response::success(null, 'Product deleted successfully');

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Bulk delete products
     */
    private function bulkDeleteProducts() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['product_ids']);
        ValidationMiddleware::throwIfInvalid();

        if (!is_array($data['product_ids']) || empty($data['product_ids'])) {
            Response::error('Invalid product IDs', 400);
        }

        $placeholders = str_repeat('?,', count($data['product_ids']) - 1) . '?';

        // Check if any products are used in orders
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) FROM order_items WHERE product_id IN ($placeholders)",
            $data['product_ids']
        );
        $orderCount = (int)$stmt->fetchColumn();

        if ($orderCount > 0) {
            Response::error('Cannot delete products that have been ordered', 400);
        }

        $this->beginTransaction();

        try {
            // Delete product images
            $this->executeQuery(
                "DELETE FROM product_images WHERE product_id IN ($placeholders)",
                $data['product_ids']
            );

            // Delete products
            $this->executeQuery(
                "DELETE FROM products WHERE id IN ($placeholders)",
                $data['product_ids']
            );

            $this->commit();
            $this->logAction('bulk_delete_products', ['count' => count($data['product_ids'])]);

            Response::success(['deleted' => count($data['product_ids'])], 'Products deleted successfully');

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Toggle product featured status
     */
    private function toggleFeatured() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id', 'featured']);

        $stmt = $this->executeQuery(
            "UPDATE products SET is_featured = ? WHERE id = ?",
            [$data['featured'] ? 1 : 0, $data['id']]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Product not found', 404);
        }

        $this->logAction('toggle_featured', ['product_id' => $data['id'], 'featured' => $data['featured']]);
        Response::success(null, 'Product featured status updated');
    }

    /**
     * Get categories for dropdown
     */
    private function getCategories() {
        $stmt = $this->executeQuery("SELECT id, name FROM categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($categories, 'Categories retrieved successfully');
    }

    /**
     * Validate product data
     */
    private function validateProductData($data) {
        ValidationMiddleware::validateRequired($data, ['name', 'price']);
        ValidationMiddleware::validateLength($data['name'], 'name', 1, 255);
        ValidationMiddleware::validateNumeric($data['price'], 'price', 0);
        ValidationMiddleware::validateLength($data['slug'] ?? '', 'slug', 1, 255);

        if (!empty($data['sku'])) {
            ValidationMiddleware::validateLength($data['sku'], 'sku', 1, 50);
        }

        if (!empty($data['discount_price'])) {
            ValidationMiddleware::validateNumeric($data['discount_price'], 'discount_price', 0);
            if ($data['discount_price'] >= $data['price']) {
                ValidationMiddleware::addError('Discount price must be less than regular price');
            }
        }

        if (!empty($data['stock'])) {
            ValidationMiddleware::validateNumeric($data['stock'], 'stock', 0);
        }

        ValidationMiddleware::throwIfInvalid();
    }

    /**
     * Get product images
     */
    private function getProductImages($productId) {
        $stmt = $this->executeQuery(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC",
            [$productId]
        );
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add full URLs
        foreach ($images as &$image) {
            $image['full_url'] = '/uploads/products/' . $image['image_path'];
        }

        return $images;
    }

    /**
     * Handle product images upload/update
     */
    private function handleProductImages($productId, $images) {
        // Clear existing images
        $this->executeQuery("DELETE FROM product_images WHERE product_id = ?", [$productId]);

        if (empty($images)) {
            return;
        }

        // Insert new images
        foreach ($images as $index => $image) {
            $isPrimary = ($index === 0) ? 1 : 0;

            if (isset($image['type']) && $image['type'] === 'url') {
                // Handle URL images
                $this->executeQuery(
                    "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)",
                    [$productId, $image['path'], $isPrimary]
                );
            } elseif (isset($image['file'])) {
                // Handle uploaded files
                $this->handleFileUpload($productId, $image, $isPrimary);
            }
        }
    }

    /**
     * Handle file upload for product images
     */
    private function handleFileUpload($productId, $image, $isPrimary) {
        $uploadDir = __DIR__ . '/../../uploads/products/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid('prod_') . '.' . pathinfo($image['file']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($image['file']['tmp_name'], $filePath)) {
            $this->executeQuery(
                "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)",
                [$productId, $fileName, $isPrimary]
            );
        } else {
            $this->logger->error('Failed to upload product image', ['product_id' => $productId]);
        }
    }
}
?>