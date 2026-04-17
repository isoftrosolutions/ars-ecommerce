<?php
/**
 * Categories Controller
 * Handles category CRUD operations
 */
class CategoryController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getCategories();
                    case 'detail':
                        return $this->getCategory();
                    case 'stats':
                        return $this->getCategoryStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'create':
                        return $this->createCategory();
                    case 'update':
                        return $this->updateCategory();
                    case 'delete':
                        return $this->deleteCategory();
                    case 'generate-slug':
                        return $this->generateSlug();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get all categories
     */
    private function getCategories() {
        $stmt = $this->executeQuery("
            SELECT c.*,
                   COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($categories, 'Categories retrieved successfully');
    }

    /**
     * Get single category
     */
    private function getCategory() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);

        $stmt = $this->executeQuery("SELECT * FROM categories WHERE id = ?", [$params['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            Response::error('Category not found', 404);
        }

        Response::success($category, 'Category retrieved successfully');
    }

    /**
     * Get category statistics
     */
    private function getCategoryStats() {
        $stats = [];

        // Total categories
        $stmt = $this->executeQuery("SELECT COUNT(*) FROM categories");
        $stats['total_categories'] = (int)$stmt->fetchColumn();

        // Categories with products
        $stmt = $this->executeQuery("
            SELECT COUNT(DISTINCT c.id)
            FROM categories c
            JOIN products p ON c.id = p.category_id
        ");
        $stats['categories_with_products'] = (int)$stmt->fetchColumn();

        // Empty categories
        $stats['empty_categories'] = $stats['total_categories'] - $stats['categories_with_products'];

        Response::success($stats, 'Category statistics retrieved successfully');
    }

    /**
     * Create new category
     */
    private function createCategory() {
        $data = $this->getInputData();
        $this->validateCategoryData($data);

        // Check if slug is unique
        $stmt = $this->executeQuery("SELECT id FROM categories WHERE slug = ?", [$data['slug']]);
        if ($stmt->fetch()) {
            ValidationMiddleware::addError('Category slug already exists');
            ValidationMiddleware::throwIfInvalid();
        }

        $stmt = $this->executeQuery(
            "INSERT INTO categories (name, slug) VALUES (?, ?)",
            [$data['name'], $data['slug']]
        );

        $categoryId = $this->pdo->lastInsertId();
        $this->logAction('create_category', ['category_id' => $categoryId, 'name' => $data['name']]);

        Response::success(['id' => $categoryId], 'Category created successfully', 201);
    }

    /**
     * Update category
     */
    private function updateCategory() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);
        $this->validateCategoryData($data);

        // Check if slug is unique (excluding current category)
        $stmt = $this->executeQuery(
            "SELECT id FROM categories WHERE slug = ? AND id != ?",
            [$data['slug'], $data['id']]
        );
        if ($stmt->fetch()) {
            ValidationMiddleware::addError('Category slug already exists');
            ValidationMiddleware::throwIfInvalid();
        }

        $stmt = $this->executeQuery(
            "UPDATE categories SET name = ?, slug = ? WHERE id = ?",
            [$data['name'], $data['slug'], $data['id']]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Category not found', 404);
        }

        $this->logAction('update_category', ['category_id' => $data['id'], 'name' => $data['name']]);

        Response::success(null, 'Category updated successfully');
    }

    /**
     * Delete category
     */
    private function deleteCategory() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        // Check if category has products
        $stmt = $this->executeQuery(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$data['id']]
        );
        $productCount = (int)$stmt->fetchColumn();

        if ($productCount > 0) {
            Response::error('Cannot delete category that contains products', 400);
        }

        // Get category name for logging
        $stmt = $this->executeQuery("SELECT name FROM categories WHERE id = ?", [$data['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            Response::error('Category not found', 404);
        }

        $stmt = $this->executeQuery("DELETE FROM categories WHERE id = ?", [$data['id']]);

        $this->logAction('delete_category', ['category_id' => $data['id'], 'name' => $category['name']]);

        Response::success(null, 'Category deleted successfully');
    }

    /**
     * Generate unique slug
     */
    private function generateSlug() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['name']);

        $baseSlug = $this->createSlug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        // Ensure uniqueness
        while (true) {
            $stmt = $this->executeQuery("SELECT id FROM categories WHERE slug = ?", [$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        Response::success(['slug' => $slug], 'Slug generated successfully');
    }

    /**
     * Validate category data
     */
    private function validateCategoryData($data) {
        ValidationMiddleware::validateRequired($data, ['name', 'slug']);
        ValidationMiddleware::validateLength($data['name'], 'name', 1, 100);
        ValidationMiddleware::validateLength($data['slug'], 'slug', 1, 100);

        // Validate slug format
        if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            ValidationMiddleware::addError('Slug can only contain lowercase letters, numbers, and hyphens');
        }

        ValidationMiddleware::throwIfInvalid();
    }

    /**
     * Create slug from name
     */
    private function createSlug($name) {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
    }
}
?>