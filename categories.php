<?php
/**
 * Categories Page
 * Easy Shopping A.R.S
 */
$page_title     = 'Shop by Category — Electronics, Fashion & More | Easy Shopping A.R.S';
$page_meta_desc = 'Browse all product categories at Easy Shopping A.R.S Nepal. Find electronics, fashion, home goods and more with fast delivery and eSewa & COD payment options.';
include 'includes/header-bootstrap.php';

try {
    // Fetch categories with product counts
    $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                        FROM categories c ORDER BY c.name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
    $error = $e->getMessage();
}
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="fw-bold">Shop by Category</h2>
            <p class="text-muted">Find exactly what you're looking for</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm text-center py-4 px-3 category-card transition-all">
                        <div class="category-icon mb-3">
                            <i class="bi bi-tag-fill display-5 text-primary opacity-25"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2"><?php echo h($category['name']); ?></h4>
                        <p class="text-muted small"><?php echo $category['product_count']; ?> Products</p>
                        <a href="shop.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill mt-2">Browse Category</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No categories found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
</style>

<?php include 'includes/footer-bootstrap.php'; ?>
