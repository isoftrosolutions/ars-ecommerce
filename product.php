<?php
/**
 * Individual Product Detail Page
 * Easy Shopping A.R.S
 */

// Get product slug from URL
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: ' . url('/shop'));
    exit;
}

$page_title = "Product Details";
include 'includes/header-bootstrap.php';

try {
    // Fetch product with category info
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
               (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as review_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.id IS NOT NULL
    ");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: ' . url('/shop'));
        exit;
    }

    // Fetch related products (same category)
    $stmt = $pdo->prepare("
        SELECT * FROM products
        WHERE category_id = ? AND id != ? AND stock > 0
        ORDER BY created_at DESC LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related_products = $stmt->fetchAll();

    // Fetch approved reviews
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name
        FROM product_reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC LIMIT 10
    ");
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = $e->getMessage();
    $product = null;
}
?>

<style>
/* Product Detail Styles */
.product-gallery {
    position: relative;
}

.main-image {
    width: 100%;
    height: 500px;
    object-fit: contain;
    background: #f8f9fa;
    border-radius: 10px;
    cursor: zoom-in;
}

.thumbnail-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: var(--ember);
}

.product-info {
    padding: 30px;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #0f172a;
}

.product-price {
    font-size: 2rem;
    font-weight: 600;
    color: var(--gold);
    margin-bottom: 20px;
}

.original-price {
    text-decoration: line-through;
    color: #64748b;
    font-size: 1.2rem;
    margin-left: 10px;
}

.discount-badge {
    background: #ef4444;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 500;
    margin-bottom: 20px;
}

.stock-in {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.stock-low {
    background: rgba(249, 115, 22, 0.1);
    color: var(--ember);
}

.stock-out {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 25px 0;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.quantity-btn:hover {
    background: var(--ember);
    color: white;
    border-color: var(--ember);
}

.quantity-input {
    width: 80px;
    height: 40px;
    text-align: center;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 500;
}

.add-to-cart-btn {
    background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
    color: white;
    border: none;
    padding: 15px 40px;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
    margin-bottom: 15px;
}

.add-to-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.3);
}

.add-to-cart-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.wishlist-btn {
    background: transparent;
    border: 2px solid var(--ember);
    color: var(--ember);
    padding: 12px 30px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
}

.wishlist-btn:hover {
    background: var(--ember);
    color: white;
}

.tab-content {
    padding: 30px 0;
}

.rating-stars {
    display: flex;
    gap: 2px;
    margin-bottom: 10px;
}

.star {
    color: #d1d5db;
    font-size: 1.2rem;
}

.star.filled {
    color: #fbbf24;
}

.review-card {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.related-products .product-card {
    transition: all 0.3s;
}

.related-products .product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .product-title {
        font-size: 2rem;
    }

    .main-image {
        height: 300px;
    }

    .product-info {
        padding: 20px 0;
    }
}
</style>

<?php if (isset($error)): ?>
    <div class="container py-5">
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo h($error); ?>
        </div>
        <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">Back to Shop</a>
    </div>
<?php elseif ($product): ?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo url('/'); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/shop'); ?>">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/shop?category=' . $product['category_id']); ?>"><?php echo h($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo h($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-gallery">
                <img src="<?php echo getProductImage($product['image']); ?>" alt="<?php echo h($product['name']); ?>" class="main-image" id="mainImage">

                <!-- Thumbnail Gallery (for future multiple images) -->
                <div class="thumbnail-container">
                    <img src="<?php echo getProductImage($product['image']); ?>" alt="Main" class="thumbnail active" onclick="changeImage(this.src)">
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-lg-6">
            <div class="product-info">
                <div class="mb-3">
                    <span class="badge bg-light text-muted"><?php echo h($product['category_name']); ?></span>
                </div>

                <h1 class="product-title"><?php echo h($product['name']); ?></h1>

                <!-- Rating -->
                <?php if ($product['avg_rating']): ?>
                    <div class="rating-stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star-fill star <?php echo $i <= round($product['avg_rating']) ? 'filled' : ''; ?>"></i>
                        <?php endfor; ?>
                        <span class="ms-2 text-muted">(<?php echo $product['review_count']; ?> reviews)</span>
                    </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="product-price">
                    <?php if ($product['discount_price']): ?>
                        Rs. <?php echo number_format($product['discount_price'], 2); ?>
                        <span class="original-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
                        <span class="discount-badge">
                            -<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                        </span>
                    <?php else: ?>
                        Rs. <?php echo number_format($product['price'], 2); ?>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="stock-status <?php
                    if ($product['stock'] > 10) echo 'stock-in';
                    elseif ($product['stock'] > 0) echo 'stock-low';
                    else echo 'stock-out';
                ?>">
                    <i class="bi bi-circle-fill"></i>
                    <?php
                    if ($product['stock'] > 10) echo 'In Stock (' . $product['stock'] . ' available)';
                    elseif ($product['stock'] > 0) echo 'Low Stock (' . $product['stock'] . ' left)';
                    else echo 'Out of Stock';
                    ?>
                </div>

                <!-- Quantity Selector -->
                <div class="quantity-selector">
                    <span class="fw-medium">Quantity:</span>
                    <button class="quantity-btn" onclick="updateQuantity(-1)">
                        <i class="bi bi-dash"></i>
                    </button>
                    <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                    <button class="quantity-btn" onclick="updateQuantity(1)">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>

                <!-- Action Buttons -->
                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, <?php echo $product['stock']; ?>)" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <i class="bi bi-cart-plus me-2"></i>
                    <?php echo $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                </button>

                <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                    <i class="bi bi-heart me-2"></i>Add to Wishlist
                </button>

                <!-- SKU -->
                <?php if ($product['sku']): ?>
                    <div class="mt-3 text-muted">
                        <small>SKU: <?php echo h($product['sku']); ?></small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
                </li>
                <?php if (!empty($reviews)): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews (<?php echo count($reviews); ?>)</button>
                </li>
                <?php endif; ?>
            </ul>

            <div class="tab-content" id="productTabsContent">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php if ($product['description']): ?>
                                <div class="product-description">
                                    <?php echo nl2br(h($product['description'])); ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No description available for this product.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="bg-light p-4 rounded">
                                <h5 class="mb-3">Product Details</h5>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Category:</strong> <?php echo h($product['category_name']); ?></li>
                                    <li class="mb-2"><strong>Price:</strong> Rs. <?php echo number_format($product['discount_price'] ?: $product['price'], 2); ?></li>
                                    <li class="mb-2"><strong>Stock:</strong> <?php echo $product['stock']; ?> available</li>
                                    <?php if ($product['sku']): ?>
                                        <li class="mb-2"><strong>SKU:</strong> <?php echo h($product['sku']); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <?php if (!empty($reviews)): ?>
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <?php echo strtoupper(substr($review['full_name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo h($review['full_name'] ?? 'Anonymous'); ?></h6>
                                                    <div class="rating-stars mb-2">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi bi-star-fill star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                            <?php if ($review['comment']): ?>
                                                <p class="mb-0"><?php echo nl2br(h($review['comment'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-lg-4">
                            <div class="bg-light p-4 rounded">
                                <h5 class="mb-3">Write a Review</h5>
                                <?php if (isset($_SESSION['user'])): ?>
                                    <form method="POST" action="<?php echo url('/product/' . $product['slug']); ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Rating</label>
                                            <select name="rating" class="form-select" required>
                                                <option value="">Select rating</option>
                                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                                <option value="3">⭐⭐⭐ Average</option>
                                                <option value="2">⭐⭐ Poor</option>
                                                <option value="1">⭐ Very Poor</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Your Review</label>
                                            <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this product..."></textarea>
                                        </div>
                                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                                    </form>
                                <?php else: ?>
                                    <p class="mb-0">Please <a href="<?php echo url('/auth/login'); ?>">login</a> to write a review.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">Related Products</h3>
            <div class="row g-4 related-products">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo getProductImage($related['image']); ?>" class="card-img-top" alt="<?php echo h($related['name']); ?>">
                                <?php if ($related['discount_price']): ?>
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title"><?php echo h($related['name']); ?></h6>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($related['discount_price']): ?>
                                                <span class="fw-bold text-dark"><?php echo format_price($related['discount_price']); ?></span>
                                                <span class="text-muted text-decoration-line-through small"><?php echo format_price($related['price']); ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold text-dark"><?php echo format_price($related['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo url('/product/' . ($related['slug'] ?? $related['id'])); ?>" class="btn btn-outline-primary w-100 mt-2 btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Handle review submission
<?php if (isset($_POST['submit_review']) && isset($_SESSION['user'])): ?>
    <?php
    try {
        $stmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $product['id'],
            $_SESSION['user']['id'],
            (int)$_POST['rating'],
            trim($_POST['comment'])
        ]);
        echo "alert('Thank you for your review! It will be published after approval.');";
    } catch (Exception $e) {
        echo "alert('Error submitting review. Please try again.');";
    }
    ?>
<?php endif; ?>

// Image gallery functions
function changeImage(src) {
    document.getElementById('mainImage').src = src;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Quantity functions
function updateQuantity(change) {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const newValue = currentValue + change;
    const maxStock = parseInt(input.getAttribute('max'));

    if (newValue >= 1 && newValue <= maxStock) {
        input.value = newValue;
    }
}

// Add to cart function
function addToCart(productId, maxStock) {
    const quantity = parseInt(document.getElementById('quantity').value);

    if (quantity > maxStock) {
        alert('Not enough stock available.');
        return;
    }

    const button = event.target.closest('.add-to-cart-btn');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Adding...';

    fetch('<?php echo url("/cart-action"); ?>?action=add&id=' + productId + '&quantity=' + quantity)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCounter = document.querySelector('.cart-count');
            if (cartCounter && data.cart_count !== undefined) {
                cartCounter.textContent = data.cart_count;
            }

            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Added to Cart!';
            button.classList.add('btn-success');
            button.classList.remove('btn-primary');

            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            }, 2000);
        } else {
            alert(data.message || 'Failed to add item to cart');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the item to cart');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Wishlist function
function toggleWishlist(productId) {
    <?php if (!isset($_SESSION['user'])): ?>
        if (confirm('Please login to add items to your wishlist. Would you like to login now?')) {
            window.location.href = '<?php echo url("/auth/login"); ?>';
        }
        return;
    <?php endif; ?>

    const button = event.target.closest('.wishlist-btn');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Adding...';

    fetch('<?php echo url("/wishlist"); ?>?action=add&product_id=' + productId)
    .then(() => {
        button.innerHTML = '<i class="bi bi-heart-fill me-2"></i>Added to Wishlist!';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-primary');

        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-primary');
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding to wishlist');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Image zoom functionality
document.getElementById('mainImage').addEventListener('click', function() {
    // Simple zoom - could be enhanced with a modal
    this.style.transform = this.style.transform === 'scale(1.5)' ? 'scale(1)' : 'scale(1.5)';
    this.style.transition = 'transform 0.3s';
});
</script>

<?php endif; ?>

<?php include 'includes/footer-bootstrap.php'; ?>