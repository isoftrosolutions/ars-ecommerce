<?php
/**
 * Professional Order Invoice / Summary
 * Optimized for Print-to-PDF
 * Easy Shopping A.R.S
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Authentication: Admin OR the customer who owns the order
$user = $_SESSION['user'] ?? null;
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$order_id) {
    die("Invalid Order ID");
}

// Fetch order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, oi.quantity, oi.price as unit_price, oi.discount_price as item_discount_price, 
               p.name as prod_name, p.sku as prod_sku, p.image as prod_image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        die("Order not found");
    }

    $order = $items[0];

    // Security Check: Only the owner or an admin can view this
    $is_admin = $user && isset($user['role']) && $user['role'] === 'admin';
    $is_owner = $user && isset($user['id']) && $user['id'] == $order['user_id'];
    $is_guest_order = is_null($order['user_id']); // Order was made by a guest

    if (!$is_admin && !$is_owner && !$is_guest_order) {
        die("Unauthorized access");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$site_name = get_setting('site_name', 'Easy Shopping A.R.S');
$site_email = get_setting('admin_email', 'support@ars.com');
$site_phone = get_setting('site_phone', '+977-980-0000000');
$site_address = get_setting('site_address', 'Birgunj-13 Radhemai, Parsa, Nepal');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
        :root {
            --primary: #ea6c00;
            --dark: #0f172a;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border: #e2e8f0;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
            background-color: #f8f9fa;
        }
        .invoice-wrapper {
            max-width: 850px;
            margin: 40px auto;
            background: #fff;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border-radius: 12px;
            border-top: 4px solid var(--primary);
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
        }
        .brand-logo-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--dark);
        }
        .brand-logo-text span {
            color: var(--primary);
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
            background-color: #f1f5f9;
        }
        .invoice-wrapper {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }
        .invoice-header {
            background: linear-gradient(135deg, var(--dark) 0%, #1e293b 100%);
            padding: 40px 50px;
            color: #fff;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-logo img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }
        .brand-logo-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .brand-logo-text span {
            color: var(--primary);
        }
        .brand-tagline {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }
.invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .invoice-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary) 0%, #ff8c00 100%);
            color: white;
            font-weight: 700;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .meta-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
        }
        .meta-list li {
            margin-bottom: 6px;
        }
        .meta-list li strong {
            color: #fff;
            min-width: 90px;
            display: inline-block;
        }
        .invoice-body {
            padding: 40px 50px;
        }
.billing-section {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
        }
        .billing-box {
            flex: 1;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        .billing-box h6 {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            color: var(--primary);
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }
        .billing-box p {
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            color: #334155;
        }
        .billing-box strong {
            color: #1e293b;
        }
        .billing-box {
            flex: 1;
        }
        .billing-box h6 {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            color: var(--primary);
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary);
        }
        .billing-box p {
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
            color: #334155;
        }
        .billing-box .name {
            font-weight: 700;
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 4px;
        }
.table-invoice {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-invoice thead tr th {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #fff;
            padding: 14px 15px;
            border: none;
        }
        .table-invoice thead tr th:first-child {
            border-radius: 8px 0 0 0;
        }
        .table-invoice thead tr th:last-child {
            border-radius: 0 8px 0 0;
        }
        .table-invoice tbody tr td {
            padding: 14px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .table-invoice tbody tr:hover {
            background: #fafbfc;
        }
        .item-name {
            font-weight: 600;
            color: #1e293b;
            display: block;
        }
        .item-sku {
            font-size: 11px;
            color: var(--gray);
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 4px;
        }
        .table-invoice thead th {
            background: var(--dark);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            color: #fff;
            padding: 14px 16px;
            letter-spacing: 0.5px;
        }
        .table-invoice thead th:first-child { border-radius: 8px 0 0 0; }
        .table-invoice thead th:last-child { border-radius: 0 8px 0 0; }
        .table-invoice td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 14px;
        }
        .table-invoice tbody tr:hover {
            background: #fafbfc;
        }
        .item-name {
            font-weight: 600;
            color: var(--dark);
            display: block;
        }
        .item-sku {
            font-size: 12px;
            color: var(--gray);
            margin-top: 3px;
        }
.totals-section {
            margin-left: auto;
            max-width: 320px;
            margin-top: 30px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: var(--gray);
        }
        .total-row.grand-total {
            border-top: 2px solid var(--primary);
            margin-top: 12px;
            padding-top: 12px;
            font-weight: 800;
            font-size: 22px;
            color: var(--dark);
            background: linear-gradient(135deg, var(--primary) 0%, #ff8c00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .payment-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed var(--border);
            font-size: 13px;
            color: var(--gray);
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            color: var(--gray);
        }
        .total-row span:last-child {
            font-weight: 600;
            color: var(--dark);
        }
        .total-row.grand-total {
            border-top: 2px solid var(--primary);
            margin-top: 12px;
            padding-top: 15px;
            font-weight: 700;
            font-size: 22px;
            color: var(--dark);
        }
        .total-row.grand-total span:last-child {
            color: var(--primary);
        }
        .payment-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            font-size: 13px;
            color: var(--gray);
        }
        .payment-info strong {
            color: var(--dark);
        }
        .invoice-footer {
            margin-top: 40px;
            padding: 25px 50px;
            background: #f8fafc;
            text-align: center;
            font-size: 13px;
            color: var(--gray);
            border-top: 1px solid var(--border);
        }
        .invoice-footer .thanks {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        @media print {
            body { background: white; }
            .invoice-wrapper { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; border-radius: 0; }
            .invoice-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table-invoice thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .btn-print { display: none; }
        }
        
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 24px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(234, 108, 0, 0.4);
            font-weight: 600;
            background: var(--primary);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-print:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="brand-logo mb-3">
                    <img src="<?php echo url('/public/assets/img/logo.jpg'); ?>" alt="ARS Logo" onerror="this.style.display='none'">
                    <span class="brand-logo-text"><span>ARS</span> Shop</span>
                </div>
                <p class="small text-white-50 mb-0">Nepal's Trusted Online Marketplace</p>
            </div>
            <div class="text-end">
                <div class="invoice-badge mb-2">INVOICE</div>
                <h1 class="invoice-title">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
                <ul class="meta-list">
                    <li><strong>Date:</strong> <?php echo date('d M, Y', strtotime($order['created_at'])); ?></li>
                    <li><strong>Order ID:</strong> #<?php echo $order['id']; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="billing-section">
        <div class="billing-box">
            <h6>Billed From</h6>
            <p><strong><?php echo h($site_name); ?></strong></p>
            <p><?php echo nl2br(h($site_address)); ?></p>
            <p>Email: <?php echo h($site_email); ?></p>
            <p>Phone: <?php echo h($site_phone); ?></p>
        </div>
        <div class="billing-box text-end">
            <h6>Billed To</h6>
            <p><strong><?php echo h($order['customer_name']); ?></strong></p>
            <p><?php echo h($order['shipping_address']); ?></p>
            <p><?php echo h($order['shipping_city']); ?>, Nepal</p>
            <p>Phone: <?php echo h($order['customer_phone']); ?></p>
            <?php if (!empty($order['customer_email'])): ?>
            <p>Email: <?php echo h($order['customer_email']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="px-5 py-3 bg-light border-bottom border-top">
        <div class="row">
            <div class="col-md-6">
                <span class="badge bg-<?php 
                    $status_colors = ['Pending' => 'warning', 'Confirmed' => 'info', 'Shipped' => 'primary', 'Out for Delivery' => 'info', 'Delivered' => 'success', 'Cancelled' => 'danger'];
                    echo $status_colors[$order['delivery_status']] ?? 'secondary';
                ?> fs-6 me-2"><?php echo strtoupper($order['delivery_status']); ?></span>
                <span class="text-muted small">Delivery Status</span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="badge bg-<?php 
                    $pay_colors = ['Pending' => 'warning', 'Paid' => 'success', 'Failed' => 'danger'];
                    echo $pay_colors[$order['payment_status']] ?? 'secondary';
                ?> fs-6 me-2"><?php echo strtoupper($order['payment_status']); ?></span>
                <span class="text-muted small">Payment Status</span>
            </div>
        </div>
    </div>

    <table class="table-invoice">
        <thead>
            <tr>
                <th style="width: 50%;">Product Description</th>
                <th class="text-center">Rate</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            foreach ($items as $item): 
                $item_total = ($item['item_discount_price'] ?? $item['unit_price']) * $item['quantity'];
                $subtotal += $item_total;
            ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <?php if (!empty($item['prod_image'])): ?>
                        <img src="<?php echo url('/' . $item['prod_image']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div>
                            <span class="item-name"><?php echo h($item['prod_name']); ?></span>
                            <?php if ($item['prod_sku']): ?>
                                <span class="item-sku">SKU: <?php echo h($item['prod_sku']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <?php if (!empty($item['item_discount_price']) && $item['item_discount_price'] < $item['unit_price']): ?>
                        <span class="text-decoration-line-through text-muted small">Rs. <?php echo number_format($item['unit_price'], 2); ?></span><br>
                        <span class="text-success fw-bold">Rs. <?php echo number_format($item['item_discount_price'], 2); ?></span>
                    <?php else: ?>
                        Rs. <?php echo number_format($item['unit_price'], 2); ?>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-end fw-bold">Rs. <?php echo number_format($item_total, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <span class="text-muted">Subtotal:</span>
            <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
        </div>
        <?php 
        $itemDiscount = 0;
        foreach ($items as $item) {
            if (!empty($item['item_discount_price'])) {
                $itemDiscount += ($item['unit_price'] - $item['item_discount_price']) * $item['quantity'];
            }
        }
        $orderDiscount = $order['discount_amount'] ?? 0;
        $totalDiscount = $itemDiscount + $orderDiscount;
        ?>
        <?php if ($totalDiscount > 0): ?>
        <div class="total-row">
            <span class="text-muted">Item Discount:</span>
            <span class="text-success">-Rs. <?php echo number_format($itemDiscount, 2); ?></span>
        </div>
        <?php if ($orderDiscount > 0): ?>
        <div class="total-row">
            <span class="text-muted">Coupon (<?php echo h($order['coupon_code']); ?>):</span>
            <span class="text-success">-Rs. <?php echo number_format($orderDiscount, 2); ?></span>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="total-row">
            <span class="text-muted">Shipping:</span>
            <?php if (isset($order['shipping_charge']) && $order['shipping_charge'] > 0): ?>
                <span>Rs. <?php echo number_format($order['shipping_charge'], 2); ?></span>
            <?php else: ?>
                <span class="text-success fw-bold">FREE</span>
            <?php endif; ?>
        </div>
        <div class="total-row grand-total">
            <span>Total:</span>
            <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
        <div class="mt-2 text-end small text-muted">
            Payment via: <strong><?php echo strtoupper($order['payment_method']); ?></strong>
            <?php if (!empty($order['transaction_id'])): ?>
            <br><span class="text-muted">Transaction ID: <?php echo h($order['transaction_id']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?php echo url('/orders.php'); ?>" class="btn btn-outline-secondary btn-sm">
            &larr; Back to Orders
        </a>
    </div>

    <div class="invoice-footer">
        <p class="thanks">Thank you for shopping with <?php echo h($site_name); ?>!</p>
        <p class="mb-2">This is a computer-generated invoice and does not require a signature.</p>
        <div class="row mt-3">
            <div class="col-md-6 text-md-start">
                <strong>Contact:</strong> <?php echo h($site_email); ?> | <?php echo h($site_phone); ?>
            </div>
            <div class="col-md-6 text-md-end">
                <strong>Terms:</strong> For any discrepancies, contact us within 48 hours of delivery.
            </div>
        </div>
    </div>
</div>

<button onclick="window.print()" class="btn btn-primary btn-print">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer-fill me-2" viewBox="0 0 16 16">
        <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
        <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
    </svg>
    Download / Print PDF
</button>

</body>
</html>
