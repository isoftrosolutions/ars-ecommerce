<?php
/**
 * Products Management Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

protect_admin_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // CSRF check (skip for GET-like read actions? No — enforce on all POST)
    if (!validate_csrf_token()) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit();
    }

    try {
        switch ($_POST['action']) {
            case 'get_products':
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                // NOTE: Do NOT use h() here — PDO params handle SQL injection; h() is for HTML output only
                $search      = trim($_POST['search']      ?? '');
                $category_id = (int)($_POST['category_id'] ?? 0);
                $offset = ($page - 1) * $limit;

                $where = [];
                $params = [];

                if ($search !== '') {
                    $where[]  = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                if ($category_id > 0) {
                    $where[]  = "p.category_id = ?";
                    $params[] = $category_id;
                }

                $wc = $where ? "WHERE " . implode(" AND ", $where) : "";

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products p $wc");
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    $wc
                    ORDER BY p.created_at DESC
                    LIMIT ? OFFSET ?
                ");
                $stmt->execute($params);
                $products = $stmt->fetchAll();

                echo json_encode([
                    'success'    => true,
                    'data'       => $products,
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_product':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = ?
                ");
                $stmt->execute([$id]);
                $product = $stmt->fetch();

                if ($product) {
                    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
                    $stmt->execute([$id]);
                    $product['images'] = $stmt->fetchAll();
                    echo json_encode(['success' => true, 'data' => $product]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                }
                break;

            case 'save_product':
                $data = $_POST['product'];

                // ---- Process ordered images (files + URLs) ----
                $upload_dir = __DIR__ . '/../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $allowed_mime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $img_order    = isset($_POST['img_order']) ? (array)$_POST['img_order'] : [];
                $all_paths    = [];

                foreach ($img_order as $slot) {
                    if (strpos($slot, 'file:') === 0) {
                        $field = 'img_file_' . substr($slot, 5);
                        if (!empty($_FILES[$field]['tmp_name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                            $mime = mime_content_type($_FILES[$field]['tmp_name']);
                            if (!in_array($mime, $allowed_mime)) continue;
                            $ext  = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                            $fname = 'prod_' . uniqid('', true) . '.' . $ext;
                            if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $fname)) {
                                $all_paths[] = $fname;
                            }
                        }
                    } elseif (strpos($slot, 'url:') === 0) {
                        $idx  = substr($slot, 4);
                        $url  = trim($_POST['img_url_' . $idx] ?? '');
                        if ($url !== '') $all_paths[] = $url;
                    }
                }

                $primary_image = $all_paths[0] ?? null;

                $pdo->beginTransaction();

                if (!empty($data['id'])) {
                    // UPDATE
                    $stmt = $pdo->prepare("
                        UPDATE products SET
                            name = ?, slug = ?, description = ?, price = ?, discount_price = ?,
                            category_id = ?, stock = ?, sku = ?, is_featured = ?, image = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $data['name'],
                        $data['slug'],
                        $data['description'] ?? '',
                        (float)$data['price'],
                        $data['discount_price'] !== '' ? (float)$data['discount_price'] : null,
                        $data['category_id'] ? (int)$data['category_id'] : null,
                        (int)($data['stock'] ?? 0),
                        $data['sku'],
                        isset($data['is_featured']) ? 1 : 0,
                        $primary_image,
                        (int)$data['id']
                    ]);
                    $product_id = (int)$data['id'];
                } else {
                    // INSERT
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, slug, description, price, discount_price,
                                             category_id, stock, sku, is_featured, image)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $data['name'],
                        $data['slug'],
                        $data['description'] ?? '',
                        (float)$data['price'],
                        $data['discount_price'] !== '' ? (float)$data['discount_price'] : null,
                        $data['category_id'] ? (int)$data['category_id'] : null,
                        (int)($data['stock'] ?? 0),
                        $data['sku'],
                        isset($data['is_featured']) ? 1 : 0,
                        $primary_image
                    ]);
                    $product_id = (int)$pdo->lastInsertId();
                }

                // Sync product_images table
                if (!empty($img_order)) { // Only touch images if the admin submitted an img_order
                    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
                    $stmt->execute([$product_id]);

                    foreach ($all_paths as $i => $path) {
                        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                        $stmt->execute([$product_id, $path, $i === 0 ? 1 : 0]);
                    }
                }

                $pdo->commit();
                echo json_encode(['success' => true, 'product_id' => $product_id]);
                break;

            case 'delete_product':
                $id = (int)$_POST['id'];

                // Prevent deleting if product is in any non-cancelled order
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE oi.product_id = ? AND o.delivery_status NOT IN ('Delivered','Cancelled')
                ");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Cannot delete product with active open orders.']);
                    exit();
                }

                $pdo->beginTransaction();
                $pdo->prepare("DELETE FROM product_images   WHERE product_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM product_reviews  WHERE product_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM wishlist         WHERE product_id = ?")->execute([$id]);
                $pdo->prepare("DELETE FROM products         WHERE id = ?")->execute([$id]);
                $pdo->commit();

                echo json_encode(['success' => true]);
                break;

            case 'toggle_featured':
                $id       = (int)$_POST['id'];
                $featured = (int)$_POST['featured'];
                $pdo->prepare("UPDATE products SET is_featured = ? WHERE id = ?")->execute([$featured, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'update_stock':
                $id    = (int)$_POST['id'];
                $stock = (int)$_POST['stock'];
                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$stock, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'get_categories':
                $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
                echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('[ARS] backend/products.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
    exit();
}

header('Location: ../admin/products.php');
exit();
?>