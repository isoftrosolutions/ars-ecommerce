<?php
/**
 * Shopping Cart Page
 * Easy Shopping A.R.S
 */
$page_title     = 'Shopping Cart | Easy Shopping A.R.S Nepal';
$page_meta_desc = 'Review your cart and checkout securely at Easy Shopping A.R.S Nepal. Pay with eSewa or Cash on Delivery. Fast delivery across Nepal.';
include 'includes/header-bootstrap.php';

// Get cart items
$cart_items = get_cart();
$cart_total = get_cart_total();
$cart_count = get_cart_count();

// Calculate shipping charge
$free_shipping_threshold = 5000;
$shipping_charge = ($cart_total >= $free_shipping_threshold || $cart_total == 0) ? 0 : 150;
$grand_total = $cart_total + $shipping_charge;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Shopping Cart</h2>
                <?php if ($cart_count > 0): ?>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3 text-muted">Your cart is empty</h3>
                    <p class="text-muted">Add some products to get started!</p>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary btn-lg mt-3">
                        <i class="bi bi-shop me-2"></i>Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo getProductImage($item['image']); ?>"
                                                 alt="<?php echo h($item['name']); ?>"
                                                 class="img-fluid rounded"
                                                 style="max-height: 80px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="card-title mb-1">
                                                <a href="<?php echo url('/product/' . $item['slug']); ?>" class="text-decoration-none">
                                                    <?php echo h($item['name']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">Stock: <?php echo $item['stock']; ?> available</small>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold"><?php echo format_price($item['discount_price'] ?: $item['price']); ?></span>
                                                <?php if ($item['discount_price']): ?>
                                                    <small class="text-muted text-decoration-line-through ms-2">
                                                        <?php echo format_price($item['price']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button class="btn btn-outline-secondary btn-sm quantity-btn"
                                                        onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" class="form-control text-center quantity-input"
                                                       value="<?php echo $item['quantity']; ?>"
                                                       min="1" max="<?php echo $item['stock']; ?>"
                                                       onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                                <button class="btn btn-outline-secondary btn-sm quantity-btn"
                                                        onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <span class="fw-bold">
                                                <?php echo format_price(($item['discount_price'] ?: $item['price']) * $item['quantity']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-outline-danger btn-sm"
                                                    onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <button class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="bi bi-cart-x me-2"></i>Clear Cart
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Items (<?php echo $cart_count; ?>)</span>
                            <span><?php echo format_price($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <?php if ($shipping_charge > 0): ?>
                                <span>Rs. <?php echo format_price($shipping_charge); ?></span>
                            <?php else: ?>
                                <span class="text-success">Free</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($cart_total > 0 && $cart_total < $free_shipping_threshold): ?>
                            <div class="small text-success mb-2">
                                <i class="bi bi-truck"></i> Add Rs. <?php echo number_format($free_shipping_threshold - $cart_total); ?> more for FREE delivery!
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong><?php echo format_price($grand_total); ?></strong>
                        </div>
                        <a href="<?php echo url('/checkout'); ?>" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                        </a>
                        <small class="text-muted d-block text-center">
                            Secure checkout with eSewa & COD
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update quantity function
function updateQuantity(productId, quantity) {
    if (quantity < 1) return;

    fetch('<?php echo url("/cart-action"); ?>?action=update&id=' + productId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the cart');
    });
}

// Remove item function
function removeItem(productId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    fetch('<?php echo url("/cart-action"); ?>?action=remove&id=' + productId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing the item');
    });
}

// Clear cart function
function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    fetch('<?php echo url("/cart-action"); ?>?action=clear')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while clearing the cart');
    });
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>