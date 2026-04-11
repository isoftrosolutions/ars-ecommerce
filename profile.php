<?php
/**
 * User Profile Page
 * Easy Shopping A.R.S
 */

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login'));
    exit;
}

$user = $_SESSION['user'];
$page_title = "My Profile";
include 'includes/header-bootstrap.php';

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";

    // Check if email is already taken by another user
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            $errors[] = "Email is already taken";
        }
    }

    // Password change validation
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    if (empty($errors)) {
        try {
            // Update user profile
            $update_fields = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city
            ];

            $set_clause = [];
            $params = [];
            foreach ($update_fields as $field => $value) {
                $set_clause[] = "$field = ?";
                $params[] = $value;
            }

            // Add password update if provided
            if (!empty($new_password)) {
                $set_clause[] = "password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $params[] = $user['id'];

            $stmt = $pdo->prepare("UPDATE users SET " . implode(', ', $set_clause) . " WHERE id = ?");
            $stmt->execute($params);

            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
            $_SESSION['user']['city'] = $city;

            $user = $_SESSION['user']; // Refresh local variable
            $success = true;

        } catch (Exception $e) {
            $errors[] = "Failed to update profile: " . $e->getMessage();
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- Profile Sidebar -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h5><?php echo h($user['name']); ?></h5>
                    <p class="text-muted small"><?php echo h($user['email']); ?></p>
                    <p class="badge bg-primary"><?php echo ucfirst($user['role']); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action active">
                        <i class="bi bi-person me-2"></i>Profile Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-receipt me-2"></i>My Orders
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-heart me-2"></i>Wishlist
                    </a>
                    <a href="<?php echo url('/auth/logout'); ?>" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Profile Settings</h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo h($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo h($user['email']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo h($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo h($user['city'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo h($user['address'] ?? ''); ?></textarea>
                        </div>

                        <hr>
                        <h5 class="mb-3">Change Password</h5>
                        <p class="text-muted small">Leave blank if you don't want to change your password</p>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="row mt-4">
                <?php
                try {
                    // Get user statistics
                    $stats = [];

                    // Total orders
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ? OR customer_email = ?");
                    $stmt->execute([$user['id'], $user['email']]);
                    $stats['total_orders'] = $stmt->fetch()['total_orders'];

                    // Total spent
                    $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE (user_id = ? OR customer_email = ?) AND status != 'cancelled'");
                    $stmt->execute([$user['id'], $user['email']]);
                    $stats['total_spent'] = $stmt->fetch()['total_spent'] ?? 0;

                    // Wishlist items
                    $stmt = $pdo->prepare("SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    $stats['wishlist_count'] = $stmt->fetch()['wishlist_count'];

                } catch (Exception $e) {
                    $stats = ['total_orders' => 0, 'total_spent' => 0, 'wishlist_count' => 0];
                }
                ?>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-receipt text-primary display-4 mb-2"></i>
                            <h3 class="text-primary"><?php echo $stats['total_orders']; ?></h3>
                            <p class="text-muted mb-0">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-cash text-success display-4 mb-2"></i>
                            <h3 class="text-success">Rs. <?php echo format_price($stats['total_spent']); ?></h3>
                            <p class="text-muted mb-0">Total Spent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-heart text-danger display-4 mb-2"></i>
                            <h3 class="text-danger"><?php echo $stats['wishlist_count']; ?></h3>
                            <p class="text-muted mb-0">Wishlist Items</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;

    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer-bootstrap.php'; ?>