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

// Calculate shipping charge from settings
$free_shipping_threshold = (float)get_setting('free_shipping_threshold', 5000);
$shipping_charge = ($cart_total >= $free_shipping_threshold) ? 0 : (float)get_setting('shipping_cost', 150);
$grand_total = $cart_total + $shipping_charge;

// Get user details if logged in
$user = $_SESSION['user'] ?? null;

// Handle form submission — must happen before any output
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validate_csrf_token()) {
        $errors[] = "Invalid security token. Please refresh the page and try again.";
    }

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

    $payment_proof_path = null;
    if ($payment_method === 'esewa') {
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Payment screenshot is required for eSewa payment.";
        } else {
            $file = $_FILES['payment_proof'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Only JPG and PNG are allowed.";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "File size exceeds 5MB limit.";
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'esewa_' . time() . '_' . uniqid() . '.' . $ext;
                $upload_dir = __DIR__ . '/uploads/payments/';
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                    $payment_proof_path = 'uploads/payments/' . $filename;
                } else {
                    $errors[] = "Failed to upload payment screenshot.";
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_charge, payment_method, payment_proof, delivery_status, shipping_address, shipping_city, customer_name, customer_email, customer_phone, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $user ? $user['id'] : null,
                $cart_total,
                $shipping_charge,
                $payment_method,
                $payment_proof_path,
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

            // Audit log: record order creation
            require_once 'includes/audit-logger.php';
            AuditLogger::logOrderCreate($order_id, $cart_total);

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
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
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
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked onchange="toggleEsewaDetails()">
                                    <label class="form-check-label" for="cod">
                                        <strong>Cash on Delivery</strong>
                                        <br><small class="text-muted">Pay when you receive your order</small>
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="esewa" value="esewa" onchange="toggleEsewaDetails()">
                                    <label class="form-check-label" for="esewa">
                                        <strong>eSewa</strong>
                                        <br><small class="text-muted">Pay securely with eSewa</small>
                                    </label>
                                </div>

                                <div id="esewa_details" class="mt-3 p-3 border rounded bg-light" style="display: none;">
                                    <h6>eSewa Payment Details</h6>
                                    <p class="small mb-2">Please send the total amount to our eSewa ID or scan the QR code below.</p>
                                    <div class="mb-3 text-center">
                                        <?php 
                                        $qr_code_path = get_setting('qr_code_path', '');
                                        if ($qr_code_path): ?>
                                            <img src="<?php echo url($qr_code_path); ?>" alt="eSewa QR Code" class="img-fluid" style="max-width: 180px; border-radius: 8px; border: 2px solid #ddd;">
                                        <?php else: ?>
                                            <div class="text-muted small">QR code not available. Please pay via eSewa app manually.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="payment_proof" class="form-label small fw-bold">Upload Payment Screenshot <span class="text-danger">*</span></label>
                                        <input class="form-control form-control-sm" type="file" id="payment_proof" name="payment_proof" accept="image/png, image/jpeg, image/jpg">
                                        <div class="form-text" style="font-size: 0.75rem;">Allowed formats: JPG, PNG, JPEG. Max size: 5MB.</div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-check-circle me-2"></i>Place Order (Rs. <?php echo format_price($grand_total); ?>)
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
                            <?php if ($shipping_charge > 0): ?>
                                <span>Rs. <?php echo format_price($shipping_charge); ?></span>
                            <?php else: ?>
                                <span class="text-success">Free</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($cart_total < $free_shipping_threshold): ?>
                            <div class="small text-success mb-2">
                                <i class="bi bi-truck"></i> Add Rs. <?php echo number_format($free_shipping_threshold - $cart_total); ?> more for FREE delivery!
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span>Rs. <?php echo format_price($grand_total); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-truck text-success me-2"></i>
                        <span class="small">Free shipping on orders over Rs. <?php echo number_format($free_shipping_threshold); ?></span>
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
// Toggle eSewa payment details visibility based on selected payment method
function toggleEsewaDetails() {
    var esewaDetails = document.getElementById('esewa_details');
    var isEsewa = document.getElementById('esewa').checked;
    var paymentProof = document.getElementById('payment_proof');
    
    if (isEsewa) {
        esewaDetails.style.display = 'block';
        paymentProof.setAttribute('required', 'required');
    } else {
        esewaDetails.style.display = 'none';
        paymentProof.removeAttribute('required');
    }
}

// Initial call to set correct state
document.addEventListener('DOMContentLoaded', function() {
    toggleEsewaDetails();
});

// Auto-fill form if user is logged in
<?php if ($user): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Form is already filled from PHP
});
<?php endif; ?>
</script>

<?php include 'includes/footer-bootstrap.php'; ?>