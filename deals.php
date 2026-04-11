<?php
/**
 * Special Deals Page
 * Easy Shopping A.R.S
 */
$page_title = "Special Deals & Offers";
include 'includes/header-bootstrap.php';

try {
    // Fetch products with discounts
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        WHERE p.discount_price IS NOT NULL AND p.discount_price > 0
                        ORDER BY (p.price - p.discount_price) DESC");
    $deals = $stmt->fetchAll();
} catch (PDOException $e) {
    $deals = [];
    $error = $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="fw-bold">Special Deals</h2>
            <p class="text-muted">Grab them before they're gone!</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (count($deals) > 0): ?>
            <?php foreach ($deals as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative">
                        <span class="badge bg-danger position-absolute top-0 end-0 m-3 z-3">
                            <?php 
                            $saving = $product['price'] - $product['discount_price'];
                            $percent = round(($saving / $product['price']) * 100);
                            echo $percent . "% OFF";
                            ?>
                        </span>
                        <img src="<?php echo h($product['image'] ?? 'public/assets/images/placeholder.jpg'); ?>" class="card-img-top" alt="<?php echo h($product['name']); ?>" style="height: 250px; object-fit: cover;">
                        <div class="card-body">
                            <p class="text-xs text-uppercase text-muted mb-1"><?php echo h($product['category_name'] ?? 'Uncategorized'); ?></p>
                            <h5 class="card-title h6 fw-bold mb-2"><?php echo h($product['name']); ?></h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-danger"><?php echo format_price($product['discount_price']); ?></span>
                                <span class="text-muted text-decoration-line-through small"><?php echo format_price($product['price']); ?></span>
                            </div>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="stretched-link"></a>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <button class="btn btn-dark w-100 rounded-pill btn-sm">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-tag text-muted display-1"></i>
                <p class="mt-3 fs-5">There are no special deals at the moment. Please check back later!</p>
                <a href="shop.php" class="btn btn-dark mt-2">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>
