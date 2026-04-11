<?php
/**
 * Categories Management Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

protect_admin_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!validate_csrf_token()) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit();
    }

    try {
        switch ($_POST['action']) {
            case 'get_categories':
                $stmt = $pdo->query("
                    SELECT c.*,
                           COALESCE(pc.product_count, 0) as product_count
                    FROM categories c
                    LEFT JOIN (
                        SELECT category_id, COUNT(*) as product_count
                        FROM products
                        GROUP BY category_id
                    ) pc ON c.id = pc.category_id
                    ORDER BY c.name ASC
                ");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
                break;

            case 'get_category':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $cat  = $stmt->fetch();
                if ($cat) echo json_encode(['success' => true, 'data' => $cat]);
                else      echo json_encode(['success' => false, 'message' => 'Category not found']);
                break;

            case 'save_category':
                $data = $_POST['category'];
                // Do NOT h() here — PDO params handle SQL injection; h() is for HTML output
                $slug    = trim($data['slug'] ?? '');
                $name    = trim($data['name'] ?? '');
                $id_check = isset($data['id']) ? (int)$data['id'] : 0;

                if (!$name || !$slug) {
                    echo json_encode(['success' => false, 'message' => 'Name and slug are required.']);
                    exit();
                }

                // Ensure slug uniqueness (excluding current id when editing)
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $id_check]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Slug already exists']);
                    exit();
                }

                if ($id_check) {
                    $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?")
                        ->execute([$name, $slug, $id_check]);
                    $category_id = $id_check;
                } else {
                    $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)")
                        ->execute([$name, $slug]);
                    $category_id = $pdo->lastInsertId();
                }

                echo json_encode(['success' => true, 'category_id' => $category_id]);
                break;

            case 'delete_category':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetch()['count'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot delete category with assigned products']);
                    exit();
                }
                $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            case 'generate_slug':
                $name = trim($_POST['name'] ?? '');
                $slug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $name));
                $slug = trim($slug, '-');

                // Ensure uniqueness
                $base = $slug;
                $n    = 1;
                while (true) {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
                    $stmt->execute([$slug]);
                    if (!$stmt->fetch()) break;
                    $slug = $base . '-' . $n++;
                }
                echo json_encode(['success' => true, 'slug' => $slug]);
                break;

            case 'get_category_stats':
                $stats = [];
                $stmt  = $pdo->query("SELECT COUNT(*) as total FROM categories");
                $stats['total_categories'] = $stmt->fetch()['total'];

                $stmt  = $pdo->query("SELECT COUNT(DISTINCT category_id) as count FROM products WHERE category_id IS NOT NULL");
                $stats['categories_with_products'] = $stmt->fetch()['count'];
                $stats['empty_categories'] = $stats['total_categories'] - $stats['categories_with_products'];

                $stmt = $pdo->query("
                    SELECT c.name, COUNT(p.id) as product_count
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id
                    GROUP BY c.id, c.name
                    ORDER BY product_count DESC
                    LIMIT 5
                ");
                $stats['top_categories'] = $stmt->fetchAll();

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            case 'bulk_delete':
                $category_ids = array_map('intval', (array)($_POST['category_ids'] ?? []));
                if (empty($category_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No categories selected']);
                    exit();
                }

                $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
                $stmt = $pdo->prepare("
                    SELECT category_id, COUNT(*) as count
                    FROM products WHERE category_id IN ($placeholders) GROUP BY category_id
                ");
                $stmt->execute($category_ids);
                $with_products = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $empty = array_values(array_diff($category_ids, array_keys($with_products)));
                if (empty($empty)) {
                    echo json_encode(['success' => false, 'message' => 'All selected categories have assigned products']);
                    exit();
                }

                $ph2 = implode(',', array_fill(0, count($empty), '?'));
                $pdo->prepare("DELETE FROM categories WHERE id IN ($ph2)")->execute($empty);

                echo json_encode(['success' => true, 'deleted' => count($empty), 'skipped' => count($category_ids) - count($empty)]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log('[ARS] backend/categories.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit();
}

header('Location: ../admin/categories.php');
exit();
?>