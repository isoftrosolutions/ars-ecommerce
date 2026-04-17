<?php
/**
 * Order Success Page
 * Easy Shopping A.R.S
 */
$page_title = "Order Placed Successfully";
include 'includes/header-bootstrap.php';

// Get order details
$order_id = isset($_GET['order']) ? (int)$_GET['order'] : null;
$order = null;

if ($order_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, oi.quantity, oi.price, oi.discount_price, p.name, p.image, p.slug
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order_data = $stmt->fetchAll();

        if (!empty($order_data)) {
            $order = $order_data[0]; // Order info
            $order['items'] = $order_data; // All items
        }
    } catch (Exception $e) {
        $order = null;
    }
}

if (!$order) {
    // Order not found or invalid
    header("Location: " . url('/shop'));
    exit;
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="success-icon mb-4">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="bi bi-check-lg text-white" style="font-size: 3rem;"></i>
                        </div>
                    </div>

                    <h1 class="h2 text-success mb-3">Order Placed Successfully!</h1>
                    <p class="text-muted mb-4">Thank you for shopping with us. Your order has been confirmed and will be processed soon.</p>

                    <!-- Order Details -->
                    <div class="order-details bg-light rounded p-4 mb-4 text-start">
                        <h5 class="mb-3">Order Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1"><strong>Status:</strong>
                                     <?php
                                         $s = $order['delivery_status'] ?? 'Pending';
                                         $c = (strtolower($s) == 'delivered') ? 'success' : ((strtolower($s) == 'cancelled') ? 'danger' : 'warning');
                                     ?>
                                     <span class="badge bg-<?php echo $c; ?>"><?php echo ucfirst($s); ?></span>
                                 </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Customer:</strong> <?php echo h($order['customer_name']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo h($order['customer_email']); ?></p>
                                <p class="mb-1"><strong>Phone:</strong> <?php echo h($order['customer_phone']); ?></p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Shipping Address</h6>
                        <p class="mb-0">
                            <?php echo h($order['shipping_address']); ?><br>
                            <?php echo h($order['shipping_city']); ?>, Nepal
                        </p>

                        <hr>

                        <h6 class="mb-3">Payment Method</h6>
                        <p class="mb-0">
                            <?php if ($order['payment_method'] === 'cod'): ?>
                                <i class="bi bi-cash text-success me-2"></i>Cash on Delivery
                            <?php elseif ($order['payment_method'] === 'esewa'): ?>
                                <i class="bi bi-credit-card text-primary me-2"></i>eSewa
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Order Items -->
                    <div class="order-items mb-4">
                        <h5 class="mb-3">Order Items</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo getProductImage($item['image'] ?? ''); ?>" alt="<?php echo h($item['name']); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold"><?php echo h($item['name']); ?></div>
                                                        <small class="text-muted">Rs. <?php echo format_price($item['discount_price'] ?? $item['price']); ?> each</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">Rs. <?php echo format_price($item['discount_price'] ?? $item['price']); ?></td>
                                            <td class="text-end fw-bold">Rs. <?php echo format_price(($item['discount_price'] ?? $item['price']) * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end">Subtotal:</td>
                                        <td class="text-end">Rs. <?php echo format_price($order['total_amount']); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end">Shipping:</td>
                                        <td class="text-end"><?php echo ($order['shipping_charge'] ?? 0) > 0 ? 'Rs. ' . format_price($order['shipping_charge']) : '<span class="text-success">Free</span>'; ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total Amount:</td>
                                        <td class="text-end fw-bold fs-5 text-success">Rs. <?php echo format_price(($order['total_amount'] ?? 0) + ($order['shipping_charge'] ?? 0)); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                        <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i>Continue Shopping
                        </a>
                        <a href="<?php echo url('/orders'); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-receipt me-2"></i>View My Orders
                        </a>
                        <a href="<?php echo url('/invoice?id=' . $order_id); ?>" target="_blank" class="btn btn-dark">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Download Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-truck text-primary display-4 mb-3"></i>
                            <h6>Shipping Information</h6>
                            <p class="text-muted small mb-0">Your order will be shipped within 1-2 business days. You'll receive a tracking number via SMS and email.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-headset text-success display-4 mb-3"></i>
                            <h6>Need Help?</h6>
                            <p class="text-muted small mb-0">Contact our support team at support@ars.com.np or call us at +977-123-456789.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-print functionality (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Could add auto-print after a delay if needed
});
</script>

<?php include 'includes/footer-bootstrap.php'; ?>