<?php
/**
 * Products Controller
 * Handles product CRUD operations and management
 */
class ProductController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

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

        // Add image URLs + attributes + variants
        foreach ($products as &$product) {
            $product['image_url'] = $product['image'] ? url('/uploads/products/' . $product['image']) : null;
            $product['images'] = $this->getProductImages($product['id']);
            $product['has_variants'] = $this->productHasVariants($product['id']);
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

        $product['image_url'] = $product['image'] ? url('/uploads/products/' . $product['image']) : null;
        $product['images'] = $this->getProductImages($product['id']);
        $product['attributes'] = $this->getProductAttributes($product['id']);
        $product['variants'] = $this->getProductVariants($product['id']);

        Response::success($product, 'Product retrieved successfully');
    }

    /**
     * Create new product
     */
    private function createProduct() {
        $data = $this->getInputData();
        $attrJson = null;
        if (isset($data['product'])) {
            $productData = $data['product'];
            $productData['images'] = $this->parseFormDataImages($data);
            $attrJson = $data['attr_json'] ?? $productData['attr_json'] ?? null;
            $data = $productData;
        }
        $data['attr_json'] = $attrJson;

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
                !empty($data['description']) ? $data['description'] : null,
                $data['price'],
                !empty($data['discount_price']) ? $data['discount_price'] : null,
                !empty($data['category_id']) ? $data['category_id'] : null,
                $data['stock'] !== '' && isset($data['stock']) ? $data['stock'] : 0,
                !empty($data['sku']) ? $data['sku'] : null,
                isset($data['is_featured']) && $data['is_featured'] ? 1 : 0
            ]);

            $productId = $this->pdo->lastInsertId();

            // Handle images
            if (!empty($data['images'])) {
                $this->handleProductImages($productId, $data['images']);
            }

            // Handle attributes & variants
            if (!empty($data['attr_json'])) {
                $this->handleProductVariantsData($productId, $data['attr_json']);
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
        $attrJson = null;
        if (isset($data['product'])) {
            $productData = $data['product'];
            $productData['images'] = $this->parseFormDataImages($data);
            $attrJson = $data['attr_json'] ?? $productData['attr_json'] ?? null;
            $data = $productData;
        }
        $data['attr_json'] = $attrJson;

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
                !empty($data['description']) ? $data['description'] : null,
                $data['price'],
                !empty($data['discount_price']) ? $data['discount_price'] : null,
                !empty($data['category_id']) ? $data['category_id'] : null,
                $data['stock'] !== '' && isset($data['stock']) ? $data['stock'] : 0,
                !empty($data['sku']) ? $data['sku'] : null,
                isset($data['is_featured']) && $data['is_featured'] ? 1 : 0,
                $data['id']
            ]);

            // Handle images
            if (isset($data['images'])) {
                $this->handleProductImages($data['id'], $data['images']);
            }

            // Handle attributes & variants
            if (!empty($data['attr_json'])) {
                $this->handleProductVariantsData($data['id'], $data['attr_json']);
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
            $image['full_url'] = url('/uploads/products/' . $image['image_path']);
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
            $this->executeQuery("UPDATE products SET image = NULL WHERE id = ?", [$productId]);
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
                
                if ($isPrimary) {
                    $this->executeQuery("UPDATE products SET image = ? WHERE id = ?", [$image['path'], $productId]);
                }
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
            chmod($filePath, 0644); // Fix Windows permission inheritance issue
            $this->executeQuery(
                "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)",
                [$productId, $fileName, $isPrimary]
            );

            if ($isPrimary) {
                $this->executeQuery("UPDATE products SET image = ? WHERE id = ?", [$fileName, $productId]);
            }
        } else {
            $this->logger->error('Failed to upload product image', ['product_id' => $productId]);
        }
    }

    /**
     * Parse multipart/form-data images payload into standardized format
     */
    private function parseFormDataImages($data) {
        $images = [];
        if (!isset($data['img_order']) || !is_array($data['img_order'])) {
            return $images;
        }

        foreach ($data['img_order'] as $item) {
            $parts = explode(':', $item);
            if (count($parts) !== 2) continue;

            $type = $parts[0];
            $index = $parts[1];

            if ($type === 'file' && isset($_FILES["img_file_$index"])) {
                if ($_FILES["img_file_$index"]['error'] === UPLOAD_ERR_OK) {
                    $images[] = [
                        'type' => 'file',
                        'file' => $_FILES["img_file_$index"]
                    ];
                }
            } elseif ($type === 'url' && isset($data["img_url_$index"])) {
                // If the URL is just a relative path from an existing image, we extract just the filename
                $url = $data["img_url_$index"];
                if (strpos($url, '/uploads/products/') !== false) {
                    $url = basename($url);
                }
                $images[] = [
                    'type' => 'url',
                    'path' => $url
                ];
            }
        }
        return $images;
    }

    // ── Variant / Attribute Methods ─────────────────────────────────

    private function productHasVariants($productId) {
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) FROM product_variants WHERE product_id = ?", [$productId]
        );
        return (int)$stmt->fetchColumn() > 0;
    }

    private function getProductAttributes($productId) {
        $stmt = $this->executeQuery(
            "SELECT a.id, a.name FROM product_attributes a
             WHERE a.product_id = ? ORDER BY a.sort_order, a.id",
            [$productId]
        );
        $attributes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($attributes as &$attr) {
            $stmt = $this->executeQuery(
                "SELECT id, `value`, sort_order, image_path
                 FROM product_attribute_values
                 WHERE attribute_id = ? ORDER BY sort_order, id",
                [$attr['id']]
            );
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($values as &$v) {
                $v['image_url'] = $v['image_path']
                    ? url('/uploads/products/' . $v['image_path'])
                    : null;
            }
            $attr['values'] = $values;
        }

        return $attributes;
    }

    private function getProductVariants($productId) {
        $stmt = $this->executeQuery(
            "SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order, id",
            [$productId]
        );
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($variants as &$variant) {
            $stmt = $this->executeQuery(
                "SELECT attribute_value_id FROM product_variant_values WHERE variant_id = ?",
                [$variant['id']]
            );
            $variant['value_ids'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'attribute_value_id');
            $variant['image_url'] = $variant['image']
                ? url('/uploads/products/' . $variant['image'])
                : null;
        }

        return $variants;
    }

    /**
     * Parse attr_json from form data and save all attributes/values/variants.
     * Replaces all existing data for the product (delete + recreate).
     */
    private function handleProductVariantsData($productId, $attrJson) {
        $data = json_decode($attrJson, true);
        if (!$data || empty($data['attributes'])) {
            // Clear all existing attributes/variants
            $this->executeQuery("DELETE FROM product_attributes WHERE product_id = ?", [$productId]);
            return;
        }

        // Delete existing variants and attributes (cascade removes values, variant_values)
        $this->executeQuery("DELETE FROM product_variants WHERE product_id = ?", [$productId]);
        $this->executeQuery("DELETE FROM product_attributes WHERE product_id = ?", [$productId]);

        $attrIndexMap = []; // old temp index → new attribute DB id
        $valueIndexMap = []; // "attrIdx:valIdx" → new attribute_value DB id

        // 1. Create attributes and values
        foreach ($data['attributes'] as $aIdx => $attr) {
            $stmt = $this->executeQuery(
                "INSERT INTO product_attributes (product_id, name, sort_order) VALUES (?, ?, ?)",
                [$productId, $attr['name'], $aIdx]
            );
            $attrId = $this->pdo->lastInsertId();
            $attrIndexMap[$aIdx] = $attrId;

            if (!empty($attr['values'])) {
                foreach ($attr['values'] as $vIdx => $val) {
                    $imagePath = null;

                    // Check for uploaded file for this value
                    $tempId = $val['image_temp_id'] ?? null;
                    if ($tempId !== null && isset($_FILES["attr_value_img_$tempId"])) {
                        $imagePath = $this->handleAttributeValueFileUpload($_FILES["attr_value_img_$tempId"]);
                    } elseif (!empty($val['image_path'])) {
                        // Keep existing image path
                        $imagePath = basename($val['image_path']);
                    }

                    $stmt = $this->executeQuery(
                        "INSERT INTO product_attribute_values (attribute_id, `value`, sort_order, image_path) VALUES (?, ?, ?, ?)",
                        [$attrId, $val['value'], $vIdx, $imagePath]
                    );
                    $valueIndexMap["$aIdx:$vIdx"] = $this->pdo->lastInsertId();
                }
            }
        }

        // 2. Create variants
        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $vIdx => $variant) {
                $stmt = $this->executeQuery(
                    "INSERT INTO product_variants (product_id, sku, price, discount_price, stock, image, is_default, sort_order)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $productId,
                        !empty($variant['sku']) ? $variant['sku'] : null,
                        !empty($variant['price']) ? $variant['price'] : null,
                        !empty($variant['discount_price']) ? $variant['discount_price'] : null,
                        isset($variant['stock']) ? (int)$variant['stock'] : 0,
                        null,
                        !empty($variant['is_default']) ? 1 : 0,
                        $vIdx
                    ]
                );
                $variantId = $this->pdo->lastInsertId();

                // Link variant to attribute values via value_refs
                if (!empty($variant['value_refs'])) {
                    foreach ($variant['value_refs'] as $ref) {
                        $valueId = $valueIndexMap[$ref] ?? null;
                        if ($valueId) {
                            $this->executeQuery(
                                "INSERT INTO product_variant_values (variant_id, attribute_value_id) VALUES (?, ?)",
                                [$variantId, $valueId]
                            );
                        }
                    }
                }
            }
        }
    }

    private function handleAttributeValueFileUpload($file) {
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $fileName = uniqid('attr_') . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            chmod($uploadDir . $fileName, 0644);
            return $fileName;
        }

        return null;
    }
}
?>