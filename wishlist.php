<?php
/**
 * User Wishlist Page
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
$page_title = "My Wishlist";
include 'includes/header-bootstrap.php';

// Handle wishlist actions
if (isset($_GET['action']) && isset($_GET['product_id'])) {
    $action = $_GET['action'];
    $product_id = (int)$_GET['product_id'];

    try {
        if ($action === 'remove') {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user['id'], $product_id]);
        }
    } catch (Exception $e) { }

    header('Location: ' . url('/wishlist.php'));
    exit;
}

// Get wishlist items
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
} catch (Exception $e) { $wishlist_items = []; }
?>

<style>
/* ═══ Wishlist Responsiveness ═══ */
.profile-nav-mobile {
    display: none;
    background: white;
    margin-bottom: 20px;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.profile-nav-mobile a {
    flex: 1; text-align: center; color: #64748b; text-decoration: none; font-size: 0.75rem; padding: 8px 0;
}
.profile-nav-mobile a.active { color: var(--primary-color); font-weight: 700; }
.profile-nav-mobile i { display: block; font-size: 1.25rem; margin-bottom: 2px; }

@media (max-width: 991px) {
    .profile-sidebar-desktop { display: none; }
    .profile-nav-mobile { display: flex; }
}

.wishlist-card {
    border-radius: 12px;
    border: 1px solid #f1f5f9 !important;
    transition: transform 0.2s;
    height: 100%;
}
.wishlist-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }

.btn-circle {
    width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0;
}

@media (max-width: 767px) {
    .wishlist-grid .col-6 { padding: 6px; }
    .wishlist-card .card-body { padding: 10px !important; }
    .wishlist-card .card-title { font-size: 0.85rem !important; height: 2.6em; overflow: hidden; }
}
</style>

<div class="container py-4">
    <!-- Mobile Nav -->
    <div class="profile-nav-mobile shadow-sm border">
        <a href="<?php echo url('/profile'); ?>">
            <i class="bi bi-person"></i> Profile
        </a>
        <a href="<?php echo url('/orders'); ?>">
            <i class="bi bi-bag-check"></i> Orders
        </a>
        <a href="<?php echo url('/wishlist'); ?>" class="active">
            <i class="bi bi-heart-fill"></i> Wishlist
        </a>
        <a href="<?php echo url('/backend/logout.php'); ?>" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> Exit
        </a>
    </div>

    <div class="row">
        <!-- Desktop Sidebar -->
        <div class="col-lg-3 profile-sidebar-desktop">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body text-center py-4 bg-light">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight:700;">
                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                        </div>
                    </div>
                    <h6 class="mb-1 fw-bold"><?php echo h($user['full_name']); ?></h6>
                    <p class="text-muted small mb-0"><?php echo h($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush small">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-person me-2"></i> Account Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-bag-check me-2"></i> Order History
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action py-3 active border-0">
                        <i class="bi bi-heart me-2"></i> My Wishlist
                    </a>
                    <a href="<?php echo url('/backend/logout.php'); ?>" class="list-group-item list-group-item-action py-3 border-0 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Wishlist Main -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">My Wishlist</h4>
                    <span class="text-muted small"><?php echo count($wishlist_items); ?> items saved for later</span>
                </div>
            </div>

            <?php if (empty($wishlist_items)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                    <i class="bi bi-heart display-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 fw-bold">Wishlist is empty</h5>
                    <p class="text-muted mb-4">No stars here yet! Save things you love for later.</p>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary px-5 rounded-pill">Explore Shop</a>
                </div>
            <?php else: ?>
                <div class="row g-3 wishlist-grid">
                    <?php foreach ($wishlist_items as $item): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card wishlist-card shadow-sm border-0">
                                <div class="position-relative">
                                    <img src="<?php echo getProductImage($item['image']); ?>" class="card-img-top p-2 rounded-4" style="aspect-ratio:1/1; object-fit:contain;">
                                    <button class="btn btn-danger btn-circle position-absolute top-0 end-0 m-2 shadow-sm" style="opacity:0.9;" onclick="window.location.href='<?php echo url('/wishlist.php?action=remove&product_id='.$item['product_id']); ?>'">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="text-uppercase mb-1" style="font-size:0.6rem; color:#ea6c00; font-weight:700;"><?php echo h($item['category_name'] ?? 'General'); ?></div>
                                    <h6 class="card-title fw-bold mb-2 small"><?php echo h($item['name']); ?></h6>
                                    
                                    <div class="mt-auto">
                                        <div class="mb-2">
                                            <span class="fw-bold fs-6">Rs. <?php echo number_format($item['discount_price'] ?: $item['price'], 0); ?></span>
                                            <?php if ($item['discount_price']): ?>
                                                <div class="text-muted text-decoration-line-through x-small" style="font-size:0.7rem;">Rs. <?php echo number_format($item['price'], 0); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <a href="<?php echo url('/product/' . $item['slug']); ?>" class="btn btn-outline-dark btn-sm flex-grow-1 py-1 rounded-pill" style="font-size:0.75rem;">View</a>
                                            <button class="btn btn-primary btn-sm rounded-circle p-0" style="width:30px; height:30px;" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(id) {
    fetch('<?php echo url("/cart-action"); ?>?action=add&id=' + id)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Added to cart!');
            window.dispatchEvent(new Event('cartUpdated'));
        }
    });
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>