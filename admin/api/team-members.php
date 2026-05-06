<?php
/**
 * Team Members API
 * Easy Shopping A.R.S
 */

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

protect_admin_page();

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Merge JSON body into $_POST so all handlers can use $_POST uniformly.
// Skip for multipart/file-upload requests — those already populate $_POST.
if (empty($_FILES)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonBody = json_decode($rawInput, true) ?? [];
        $_POST = array_merge($jsonBody, $_POST);
    }
}

// Validate CSRF for all state-changing POST requests.
// admin-core.js automatically sends X-CSRF-Token header; form submits include hidden csrf_token field.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'list':
            handleList();
            break;
        case 'get':
            handleGet();
            break;
        case 'create':
        case 'update':
            handleSave();
            break;
        case 'delete':
            handleDelete();
            break;
        case 'bulk_activate':
            handleBulkActivate();
            break;
        case 'bulk_deactivate':
            handleBulkDeactivate();
            break;
        case 'bulk_delete':
            handleBulkDelete();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log('Team Members API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}

function handleList() {
    $page = (int)($_POST['page'] ?? 1);
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    $search = trim($_POST['search'] ?? '');
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';

    global $pdo;

    // Build WHERE clause
    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ? OR position LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($role)) {
        $where[] = "role = ?";
        $params[] = $role;
    }

    if ($status !== '') {
        $where[] = "is_active = ?";
        $params[] = (int)$status;
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM team_members $whereClause");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    // Get members
    $stmt = $pdo->prepare("
        SELECT id, name, email, mobile, role, position, profile_image, fb_link, bio, is_active, display_order, created_at
        FROM team_members
        $whereClause
        ORDER BY display_order ASC, created_at DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalize paths and fall back to default avatar
    foreach ($members as &$member) {
        $img = str_replace('\\', '/', $member['profile_image'] ?? '');
        $member['profile_image'] = !empty($img) ? $img : '/public/assets/img/default-avatar.png';
    }

    $totalPages = ceil($total / $perPage);

    echo json_encode([
        'success' => true,
        'members' => $members,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $total,
            'per_page' => $perPage
        ]
    ]);
}

function handleGet() {
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        throw new Exception('Team member ID is required');
    }

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT id, name, email, mobile, role, position, profile_image, fb_link, bio, is_active, display_order
        FROM team_members
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('Team member not found');
    }

    $member['profile_image'] = str_replace('\\', '/', $member['profile_image'] ?? '');

    echo json_encode([
        'success' => true,
        'member' => $member
    ]);
}

function handleSave() {
    $id = (int)($_POST['member_id'] ?? 0);
    $isUpdate = $id > 0;

    // Validate required fields
    $required = ['name', 'role', 'position'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst($field) . ' is required');
        }
    }

    // Validate email format if provided
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate mobile format if provided
    if (!empty($_POST['mobile']) && !preg_match('/^[0-9]{10}$/', $_POST['mobile'])) {
        throw new Exception('Mobile number must be 10 digits');
    }

    // Check for duplicate email
    if (!empty($_POST['email'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM team_members WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $id]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already exists');
        }
    }

    // Check for duplicate mobile
    if (!empty($_POST['mobile'])) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM team_members WHERE mobile = ? AND id != ?");
        $stmt->execute([$_POST['mobile'], $id]);
        if ($stmt->fetch()) {
            throw new Exception('Mobile number already exists');
        }
    }

    // Handle profile image upload
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $profileImage = handleImageUpload($_FILES['profile_image'], $id);
    }

    global $pdo;

    if ($isUpdate) {
        // Update existing member
        $stmt = $pdo->prepare("
            UPDATE team_members SET
                name = ?, email = ?, mobile = ?, role = ?, position = ?,
                fb_link = ?, bio = ?, is_active = ?, display_order = ?
                " . ($profileImage ? ", profile_image = ?" : "") . "
            WHERE id = ?
        ");

        $params = [
            $_POST['name'],
            $_POST['email'] ?: null,
            $_POST['mobile'] ?: null,
            $_POST['role'],
            $_POST['position'],
            $_POST['fb_link'] ?: null,
            $_POST['bio'] ?: null,
            (int)$_POST['is_active'],
            (int)$_POST['display_order']
        ];

        if ($profileImage) {
            $params[] = $profileImage;
        }
        $params[] = $id;

        $stmt->execute($params);
        $message = 'Team member updated successfully';
    } else {
        // Create new member
        $stmt = $pdo->prepare("
            INSERT INTO team_members (name, email, mobile, role, position, profile_image, fb_link, bio, is_active, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['name'],
            $_POST['email'] ?: null,
            $_POST['mobile'] ?: null,
            $_POST['role'],
            $_POST['position'],
            $profileImage ?: '/public/assets/img/default-avatar.png',
            $_POST['fb_link'] ?: null,
            $_POST['bio'] ?: null,
            (int)$_POST['is_active'],
            (int)$_POST['display_order']
        ]);
        $message = 'Team member created successfully';
    }

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
}

function handleDelete() {
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        throw new Exception('Team member ID is required');
    }

    global $pdo;

    // Get current image path for deletion
    $stmt = $pdo->prepare("SELECT profile_image FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        throw new Exception('Team member not found');
    }

    // Delete the member
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->execute([$id]);

    // Delete profile image file if it's not the default
    if ($member['profile_image'] && $member['profile_image'] !== '/public/assets/img/default-avatar.png') {
        $imagePath = __DIR__ . '/../../' . ltrim($member['profile_image'], '/');
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Team member deleted successfully'
    ]);
}

function handleBulkActivate() {
    handleBulkStatusUpdate(1, 'activated');
}

function handleBulkDeactivate() {
    handleBulkStatusUpdate(0, 'deactivated');
}

function handleBulkDelete() {
    $ids = $_POST['ids'] ?? [];

    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No team members selected');
    }

    global $pdo;

    // Get current images for deletion
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT profile_image FROM team_members WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete the members
    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    // Delete profile image files
    foreach ($members as $member) {
        if ($member['profile_image'] && $member['profile_image'] !== '/public/assets/img/default-avatar.png') {
            $imagePath = __DIR__ . '/../../' . ltrim($member['profile_image'], '/');
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => count($ids) . ' team member(s) deleted successfully'
    ]);
}

function handleBulkStatusUpdate($status, $action) {
    $ids = $_POST['ids'] ?? [];

    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No team members selected');
    }

    global $pdo;

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("UPDATE team_members SET is_active = ? WHERE id IN ($placeholders)");

    $params = [$status];
    $params = array_merge($params, $ids);

    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => count($ids) . ' team member(s) ' . $action . ' successfully'
    ]);
}

function handleImageUpload($file, $memberId = null) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid image type. Only JPG, PNG, and WebP are allowed.');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('Image size must be less than 2MB.');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../../public/assets/img/team/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'team_' . ($memberId ?: 'temp') . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload image.');
    }

    // Resize and optimize image if needed (you can add image processing here)

    return '/public/assets/img/team/' . $filename;
}
?>