<?php
/**
 * User Profile Page
 * Easy Shopping A.R.S
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login.php'));
    exit;
}

$user = $_SESSION['user'];
$page_title = "My Profile";
include 'includes/header-bootstrap.php';

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";

    if (empty($errors)) {
        try {
            $update_fields = [
                'full_name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'address' => $address
            ];

            $set_clause = [];
            $params = [];
            foreach ($update_fields as $field => $value) {
                $set_clause[] = "$field = ?";
                $params[] = $value;
            }

            if (!empty($new_password)) {
                $set_clause[] = "password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $params[] = $user['id'];
            $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $set_clause) . " WHERE id = ?");
            $stmt->execute($params);

            // Update session
            $_SESSION['user']['full_name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['mobile'] = $mobile;
            $_SESSION['user']['address'] = $address;
            $user = $_SESSION['user'];
            $success = true;
        } catch (Exception $e) {
            $errors[] = "Failed to update profile";
        }
    }
}
?>

<style>
/* ═══ Profile Responsiveness ═══ */
.profile-card {
    border-radius: 15px;
    overflow: hidden;
}
.profile-nav-mobile {
    display: none;
    background: white;
    margin-bottom: 20px;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.profile-nav-mobile a {
    flex: 1;
    text-align: center;
    color: #64748b;
    text-decoration: none;
    font-size: 0.75rem;
    padding: 8px 0;
}
.profile-nav-mobile a.active {
    color: var(--primary-color);
    font-weight: 700;
}
.profile-nav-mobile i {
    display: block;
    font-size: 1.25rem;
    margin-bottom: 2px;
}

@media (max-width: 991px) {
    .profile-sidebar-desktop { display: none; }
    .profile-nav-mobile { display: flex; }
    .stat-card { margin-bottom: 15px; }
}

.stat-card {
    border-radius: 15px;
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-5px); }
</style>

<div class="container py-4">
    <!-- Breadcrumbs -->
    <nav class="mb-4 d-none d-md-block" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/'); ?>">Home</a></li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol>
    </nav>

    <!-- Mobile Nav Tabs -->
    <div class="profile-nav-mobile shadow-sm border">
        <a href="<?php echo url('/profile'); ?>" class="active">
            <i class="bi bi-person-fill"></i> Profile
        </a>
        <a href="<?php echo url('/orders'); ?>">
            <i class="bi bi-bag-check"></i> Orders
        </a>
        <a href="<?php echo url('/wishlist'); ?>">
            <i class="bi bi-heart"></i> Wishlist
        </a>
        <a href="<?php echo url('/backend/logout.php'); ?>" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> Exit
        </a>
    </div>

    <div class="row">
        <!-- 💻 Desktop Sidebar -->
        <div class="col-lg-3 profile-sidebar-desktop">
            <div class="card border-0 shadow-sm profile-card mb-4">
                <div class="card-body text-center py-4 bg-light">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; font-weight:700;">
                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                        </div>
                    </div>
                    <h6 class="mb-1 fw-bold"><?php echo h($user['full_name']); ?></h6>
                    <p class="text-muted small mb-0"><?php echo h($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush small">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action py-3 active border-0">
                        <i class="bi bi-person me-2"></i> Account Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-receipt me-2"></i> Order History
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-heart me-2"></i> My Wishlist
                    </a>
                    <a href="<?php echo url('/backend/logout.php'); ?>" class="list-group-item list-group-item-action py-3 border-0 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm profile-card mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold">Edit Profile Info</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo h($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo h($user['email']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Mobile</label>
                                <input type="tel" class="form-control" name="mobile" value="<?php echo h($user['mobile']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Default Address</label>
                                <input type="text" class="form-control" name="address" value="<?php echo h($user['address'] ?? ''); ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Security Settings</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Current Password</label>
                                <input type="password" class="form-control bg-light" name="current_password" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">New Password</label>
                                <input type="password" class="form-control bg-light" name="new_password" placeholder="At least 6 chars">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Confirm New</label>
                                <input type="password" class="form-control bg-light" name="confirm_password" placeholder="Confirm">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="row g-3">
                <?php
                try {
                    $stats_stmt = $pdo->prepare("SELECT COUNT(*) as ords FROM orders WHERE user_id = ?");
                    $stats_stmt->execute([$user['id']]);
                    $order_count = $stats_stmt->fetch()['ords'] ?? 0;

                    $wish_stmt = $pdo->prepare("SELECT COUNT(*) as wish FROM wishlist WHERE user_id = ?");
                    $wish_stmt->execute([$user['id']]);
                    $wish_count = $wish_stmt->fetch()['wish'] ?? 0;
                } catch (Exception $e) { $order_count = 0; $wish_count = 0; }
                ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center stat-card h-100">
                        <div class="card-body p-4">
                            <div class="display-6 text-primary fw-bold mb-1"><?php echo $order_count; ?></div>
                            <div class="text-muted small fw-bold text-uppercase">Total Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center stat-card h-100">
                        <div class="card-body p-4">
                            <div class="display-6 text-danger fw-bold mb-1"><?php echo $wish_count; ?></div>
                            <div class="text-muted small fw-bold text-uppercase">Wishlist Items</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center stat-card h-100">
                        <div class="card-body p-4">
                            <div class="display-6 text-success fw-bold mb-1"><i class="bi bi-shield-check"></i></div>
                            <div class="text-muted small fw-bold text-uppercase">Secured Account</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>