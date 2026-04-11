<?php
/**
 * Today's Deal Page
 * Easy Shopping A.R.S
 */
$page_title     = "Today's Deal — Best Prices in Nepal | Easy Shopping A.R.S";
$page_meta_desc = "Shop today's featured deal at Easy Shopping A.R.S Nepal. Massive discounts updated daily. Pay with eSewa or Cash on Delivery. Limited time offers!";
include 'includes/header-bootstrap.php';

// Get today's featured deal (product with highest discount)
try {
    // Get the product with the highest discount as today's deal
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, p.is_featured
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.stock > 0
        ORDER BY (p.price - p.discount_price) DESC
        LIMIT 1
    ");
    $todays_deal = $stmt->fetch();

    // Get other deals for the sidebar
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.stock > 0
        AND p.id != " . ($todays_deal ? $todays_deal['id'] : 0) . "
        ORDER BY (p.price - p.discount_price) DESC
        LIMIT 6
    ");
    $other_deals = $stmt->fetchAll();

} catch (PDOException $e) {
    $todays_deal = null;
    $other_deals = [];
}

// Calculate deal end time (end of day)
$deal_end_time = strtotime('tomorrow') - 1; // 11:59:59 PM today
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="fw-bold mb-3">Today's Deal</h1>
            <p class="text-muted fs-5">Limited time offer - Don't miss out!</p>
        </div>
    </div>

    <?php if ($todays_deal): ?>
        <div class="row g-5">
            <!-- Main Deal -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg overflow-hidden">
                    <!-- Deal Badge -->
                    <div class="position-absolute top-0 start-0 z-3">
                        <div class="badge bg-danger fs-6 px-3 py-2 m-3">
                            <i class="bi bi-star-fill me-1"></i>TODAY'S DEAL
                        </div>
                    </div>

                    <!-- Discount Badge -->
                    <?php if ($todays_deal['discount_price']): ?>
                        <div class="position-absolute top-0 end-0 z-3">
                            <div class="badge bg-success fs-6 px-3 py-2 m-3">
                                <?php
                                $saving = $todays_deal['price'] - $todays_deal['discount_price'];
                                $percent = round(($saving / $todays_deal['price']) * 100);
                                echo "SAVE {$percent}%";
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row g-0">
                        <!-- Product Image -->
                        <div class="col-md-6">
                            <div class="deal-image-container">
                                <img src="<?php echo h($todays_deal['image'] ?? 'public/assets/images/placeholder.jpg'); ?>"
                                     class="deal-image" alt="<?php echo h($todays_deal['name']); ?>">
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="col-md-6">
                            <div class="card-body p-4 h-100 d-flex flex-column">
                                <div class="mb-3">
                                    <p class="text-xs text-uppercase text-muted mb-2">
                                        <?php echo h($todays_deal['category_name'] ?? 'Uncategorized'); ?>
                                    </p>
                                    <h2 class="card-title h3 fw-bold mb-3"><?php echo h($todays_deal['name']); ?></h2>
                                    <p class="text-muted"><?php echo h($todays_deal['description'] ?? ''); ?></p>
                                </div>

                                <!-- Pricing -->
                                <div class="mb-4">
                                    <?php if ($todays_deal['discount_price']): ?>
                                        <div class="d-flex align-items-center gap-3 mb-2">
                                            <span class="h4 fw-bold text-danger mb-0">
                                                <?php echo format_price($todays_deal['discount_price']); ?>
                                            </span>
                                            <span class="text-muted text-decoration-line-through">
                                                <?php echo format_price($todays_deal['price']); ?>
                                            </span>
                                        </div>
                                        <div class="text-success fw-medium">
                                            You save <?php echo format_price($saving); ?> (<?php echo $percent; ?>% off)
                                        </div>
                                    <?php else: ?>
                                        <span class="h4 fw-bold text-dark">
                                            <?php echo format_price($todays_deal['price']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Countdown Timer -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">Deal ends in:</h6>
                                    <div id="countdown" class="d-flex gap-3 justify-content-center">
                                        <div class="text-center">
                                            <div id="hours" class="h4 fw-bold text-primary mb-1">00</div>
                                            <small class="text-muted">Hours</small>
                                        </div>
                                        <div class="text-center">
                                            <div id="minutes" class="h4 fw-bold text-primary mb-1">00</div>
                                            <small class="text-muted">Minutes</small>
                                        </div>
                                        <div class="text-center">
                                            <div id="seconds" class="h4 fw-bold text-primary mb-1">00</div>
                                            <small class="text-muted">Seconds</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-auto">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <button class="btn btn-dark w-100 py-3 rounded-pill fw-bold" onclick="addToCart(<?php echo $todays_deal['id']; ?>)">
                                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        </div>
                                        <div class="col-12">
                                            <a href="product.php?id=<?php echo $todays_deal['id']; ?>" class="btn btn-outline-dark w-100 py-2 rounded-pill">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Other Deals -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h5 class="fw-bold mb-0">More Great Deals</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($other_deals) > 0): ?>
                            <?php foreach ($other_deals as $deal): ?>
                                <div class="p-3 border-bottom">
                                    <div class="row g-3">
                                        <div class="col-4">
                                            <img src="<?php echo h($deal['image'] ?? 'public/assets/images/placeholder.jpg'); ?>"
                                                 class="img-fluid rounded" alt="<?php echo h($deal['name']); ?>">
                                        </div>
                                        <div class="col-8">
                                            <h6 class="fw-bold mb-1 small"><?php echo h($deal['name']); ?></h6>
                                            <div class="mb-2">
                                                <span class="fw-bold text-danger"><?php echo format_price($deal['discount_price']); ?></span>
                                                <span class="text-muted text-decoration-line-through small ms-2">
                                                    <?php echo format_price($deal['price']); ?>
                                                </span>
                                            </div>
                                            <a href="product.php?id=<?php echo $deal['id']; ?>" class="btn btn-sm btn-outline-primary">View Deal</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-tag display-4 mb-3"></i>
                                <p>No other deals available today</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-light border-0 text-center">
                        <a href="deals.php" class="btn btn-dark">View All Deals</a>
                    </div>
                </div>

                <!-- Deal Tips -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-light border-0">
                        <h6 class="fw-bold mb-0">Deal Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Deals refresh daily at midnight</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Limited quantities available</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Combine with other offers</li>
                            <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Free shipping on orders over Rs. 2000</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- No Deal Available -->
        <div class="row">
            <div class="col-12 text-center py-5">
                <div class="card border-0 shadow-sm p-5">
                    <i class="bi bi-clock text-muted display-1 mb-4"></i>
                    <h3 class="fw-bold mb-3">No Deal Available Today</h3>
                    <p class="text-muted mb-4">We're working on bringing you amazing deals. Check back tomorrow!</p>
                    <div class="row g-3 justify-content-center">
                        <div class="col-md-4">
                            <a href="shop.php" class="btn btn-dark w-100 py-3">Continue Shopping</a>
                        </div>
                        <div class="col-md-4">
                            <a href="deals.php" class="btn btn-outline-dark w-100 py-3">View All Deals</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Countdown Timer
function updateCountdown() {
    const endTime = <?php echo $deal_end_time; ?> * 1000;
    const now = new Date().getTime();
    const distance = endTime - now;

    if (distance > 0) {
        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
        document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
    } else {
        // Deal expired
        document.getElementById('countdown').innerHTML = '<div class="text-danger fw-bold">Deal Expired</div>';
    }
}

// Update countdown every second
updateCountdown();
setInterval(updateCountdown, 1000);

// Add to cart function
function addToCart(productId) {
    // Add your cart logic here
    alert('Added to cart! Product ID: ' + productId);
}
</script>

<style>
.deal-image-container {
    height: 400px;
    overflow: hidden;
    position: relative;
}

.deal-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.deal-image-container:hover .deal-image {
    transform: scale(1.05);
}

#countdown {
    background: rgba(234, 108, 0, 0.05);
    padding: 20px;
    border-radius: 12px;
    border: 2px solid rgba(234, 108, 0, 0.1);
}

.text-primary { color: #ea6c00 !important; }
</style>

<?php include 'includes/footer-bootstrap.php'; ?>