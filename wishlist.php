<?php
/**
 * User Wishlist Page
 * Easy Shopping A.R.S
 */

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login'));
    exit;
}

$user = $_SESSION['user'];
$page_title = "My Wishlist";
include 'includes/header-bootstrap.php';

// Handle wishlist actions
if (isset($_GET['action']) && isset($_GET['product_id'])) {
    $action = $_GET['action'];
    $product_id = (int)$_GET['product_id'];

    try {
        if ($action === 'add') {
            // Check if already in wishlist
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user['id'], $product_id]);

            if (!$stmt->fetch()) {
                // Add to wishlist
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$user['id'], $product_id]);
            }
        } elseif ($action === 'remove') {
            // Remove from wishlist
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user['id'], $product_id]);
        }
    } catch (Exception $e) {
        // Handle error silently or log it
    }

    // Redirect to avoid form resubmission
    header('Location: ' . url('/wishlist'));
    exit;
}

// Get user's wishlist items
try {
    $stmt = $pdo->prepare("
        SELECT w.*, p.name, p.price, p.discount_price, p.image, p.slug, c.name as category_name
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $wishlist_items = $stmt->fetchAll();
} catch (Exception $e) {
    $wishlist_items = [];
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- Wishlist Sidebar -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h5><?php echo h($user['name']); ?></h5>
                    <p class="text-muted small"><?php echo h($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-person me-2"></i>Profile Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-receipt me-2"></i>My Orders
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action active">
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
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="bi bi-heart-fill text-danger me-2"></i>My Wishlist
                        </h4>
                        <small class="text-muted"><?php echo count($wishlist_items); ?> items saved</small>
                    </div>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i>Continue Shopping
                    </a>
                </div>

                <div class="card-body">
                    <?php if (empty($wishlist_items)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-heart text-muted display-1"></i>
                            <h3 class="mt-3">Your wishlist is empty</h3>
                            <p class="text-muted">Save items you love for later. Start shopping and add items to your wishlist!</p>
                            <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($wishlist_items as $item): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm wishlist-item">
                                        <div class="position-relative">
                                            <div class="wishlist-badge">
                                                <i class="bi bi-heart-fill text-danger"></i>
                                            </div>
                                            <?php if ($item['discount_price']): ?>
                                                <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
                                            <?php endif; ?>
                                            <img src="<?php echo getProductImage($item['image']); ?>" class="card-img-top" alt="<?php echo h($item['name']); ?>" style="height: 200px; object-fit: cover;">
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <p class="text-xs text-uppercase text-muted mb-1"><?php echo h($item['category_name'] ?? 'General'); ?></p>
                                            <h6 class="card-title fw-bold mb-2"><?php echo h($item['name']); ?></h6>

                                            <div class="mt-auto">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <?php if ($item['discount_price']): ?>
                                                        <span class="fw-bold text-dark"><?php echo format_price($item['discount_price']); ?></span>
                                                        <span class="text-muted text-decoration-line-through small"><?php echo format_price($item['price']); ?></span>
                                                        <span class="badge bg-danger small">
                                                            -<?php echo round((($item['price'] - $item['discount_price']) / $item['price']) * 100); ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-dark"><?php echo format_price($item['price']); ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <a href="<?php echo url('/product/' . $item['slug']); ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger btn-sm" onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <button class="btn btn-primary btn-sm flex-fill" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                        <i class="bi bi-cart-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Wishlist Actions -->
                        <div class="text-center mt-4">
                            <button class="btn btn-outline-danger" onclick="clearWishlist()">
                                <i class="bi bi-trash me-2"></i>Clear All Items
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wishlist-item {
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
}

.wishlist-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.wishlist-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: white;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 2;
}
</style>

<script>
function removeFromWishlist(productId) {
    if (confirm('Remove this item from your wishlist?')) {
        window.location.href = '<?php echo url('/wishlist'); ?>?action=remove&product_id=' + productId;
    }
}

function clearWishlist() {
    if (confirm('Are you sure you want to clear all items from your wishlist?')) {
        // This would need to be implemented as a separate action
        alert('Clear wishlist feature not yet implemented');
    }
}

function addToCart(productId) {
    // Add to cart functionality (would need to be implemented)
    alert('Add to cart functionality would be implemented here');
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>