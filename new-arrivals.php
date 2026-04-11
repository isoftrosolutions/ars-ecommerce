<?php
/**
 * New Arrivals Page
 * Easy Shopping A.R.S
 */
$page_title     = 'New Arrivals — Latest Products in Nepal | Easy Shopping A.R.S';
$page_meta_desc = 'Discover the latest products just added to Easy Shopping A.R.S Nepal. New electronics, fashion, and home goods with fast delivery and easy returns.';
include 'includes/header-bootstrap.php';

try {
    // Fetch newest products (last 30 days or just latest 20)
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC LIMIT 20");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error = $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="fw-bold">New Arrivals</h2>
            <p class="text-muted">Fresh drops, just for you. Explore our latest collection.</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="position-relative">
                            <img src="<?php echo h($product['image'] ?? 'public/assets/images/placeholder.jpg'); ?>" class="card-img-top" alt="<?php echo h($product['name']); ?>" style="height: 250px; object-fit: cover;">
                            <span class="badge bg-primary position-absolute top-0 start-0 m-2">New</span>
                        </div>
                        <div class="card-body">
                            <p class="text-xs text-uppercase text-muted mb-1"><?php echo h($product['category_name'] ?? 'Uncategorized'); ?></p>
                            <h5 class="card-title h6 fw-bold mb-2"><?php echo h($product['name']); ?></h5>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($product['discount_price']): ?>
                                    <span class="fw-bold text-dark"><?php echo format_price($product['discount_price']); ?></span>
                                    <span class="text-muted text-decoration-line-through small"><?php echo format_price($product['price']); ?></span>
                                <?php else: ?>
                                    <span class="fw-bold text-dark"><?php echo format_price($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="stretched-link"></a>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <button class="btn btn-outline-dark w-100 rounded-pill btn-sm">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-clock-history text-muted display-1"></i>
                <p class="mt-3 fs-5">We are preparing some exciting new products. Stay tuned!</p>
                <a href="shop.php" class="btn btn-dark mt-2">Explore Shop</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>
