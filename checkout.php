<?php
/**
 * Checkout Page
 * Easy Shopping A.R.S
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get cart items from database
$cart_items = get_cart();
$cart_total = get_cart_total();

// Get user details if logged in
$user = $_SESSION['user'] ?? null;

// Handle form submission — must happen before any output
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name']           ?? '');
    $email          = trim($_POST['email']          ?? '');
    $phone          = trim($_POST['phone']          ?? '');
    $address        = trim($_POST['address']        ?? '');
    $city           = trim($_POST['city']           ?? '');
    $payment_method = trim($_POST['payment_method'] ?? '');

    if (empty($name))           $errors[] = "Name is required";
    if (empty($email))          $errors[] = "Email is required";
    if (empty($phone))          $errors[] = "Phone number is required";
    if (empty($address))        $errors[] = "Address is required";
    if (empty($city))           $errors[] = "City is required";
    if (empty($payment_method)) $errors[] = "Payment method is required";
    if (empty($cart_items))     $errors[] = "Your cart is empty";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method, delivery_status, shipping_address, shipping_city, customer_name, customer_email, customer_phone, created_at) VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $user ? $user['id'] : null,
                $cart_total,
                $payment_method,
                $address,
                $city,
                $name,
                $email,
                $phone,
            ]);

            $order_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['discount_price'] ?? null,
                ]);
            }

            clear_cart();
            $pdo->commit();

            // Send order confirmation email
            try {
                require_once 'includes/email-service.php';
                $emailService = getEmailService();
                
                // Construct absolute URL for the invoice
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                $invoice_url = $protocol . "://" . $host . url('/invoice?id=' . $order_id);
                
                $emailService->sendOrderConfirmation($email, $name, $order_id, $cart_total, $invoice_url);
            } catch (Exception $e) {
                // Log error but don't stop the user from seeing their success page
                error_log("Failed to send order email: " . $e->getMessage());
            }

            header("Location: " . url('/order-success?order=' . $order_id));
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to process order: " . $e->getMessage();
        }
    }
}

$page_title = "Checkout";
include 'includes/header-bootstrap.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Checkout</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cart-x text-muted display-1"></i>
                            <h3 class="mt-3">Your cart is empty</h3>
                            <p class="text-muted">Add some products to your cart before checkout.</p>
                            <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <h5 class="mb-3">Shipping Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo h($user['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo h($user['email'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo h($user['mobile'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo h($user['city'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Shipping Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo h($user['address'] ?? ''); ?></textarea>
                            </div>

                            <h5 class="mb-3 mt-4">Payment Method</h5>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                    <label class="form-check-label" for="cod">
                                        <strong>Cash on Delivery</strong>
                                        <br><small class="text-muted">Pay when you receive your order</small>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="esewa" value="esewa">
                                    <label class="form-check-label" for="esewa">
                                        <strong>eSewa</strong>
                                        <br><small class="text-muted">Pay securely with eSewa</small>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-check-circle me-2"></i>Place Order (Rs. <?php echo format_price($cart_total); ?>)
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($cart_items)): ?>
                        <div class="order-items mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <img src="<?php echo getProductImage($item['image'] ?? ''); ?>" alt="<?php echo h($item['name']); ?>" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small"><?php echo h($item['name']); ?></div>
                                        <div class="text-muted small">Qty: <?php echo $item['quantity']; ?> × Rs. <?php echo format_price($item['discount_price'] ?? $item['price']); ?></div>
                                    </div>
                                    <div class="fw-bold">Rs. <?php echo format_price(($item['discount_price'] ?? $item['price']) * $item['quantity']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rs. <?php echo format_price($cart_total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span>Rs. <?php echo format_price($cart_total); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-truck text-success me-2"></i>
                        <span class="small">Free shipping on orders over Rs. 1,000</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-shield-check text-success me-2"></i>
                        <span class="small">Secure checkout</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-arrow-repeat text-success me-2"></i>
                        <span class="small">5-day easy returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill form if user is logged in
<?php if ($user): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Form is already filled from PHP
});
<?php endif; ?>
</script>

<?php include 'includes/footer-bootstrap.php'; ?>