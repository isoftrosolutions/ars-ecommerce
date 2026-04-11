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
        SELECT o.*, oi.quantity, oi.price as unit_price, p.name as prod_name, p.sku as prod_sku
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
    if (!$user || ($user['role'] !== 'admin' && $user['id'] != $order['user_id'] && $user['email'] !== $order['customer_email'])) {
        die("Unauthorized access");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$site_name = get_setting('site_name', 'Easy Shopping A.R.S');
$site_email = get_setting('admin_email', 'support@ars.com');
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .brand-logo {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
            color: #0f172a;
        }
        .brand-logo span {
            color: #ea6c00;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 50px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 30px;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .meta-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        .meta-list li strong {
            color: #1e293b;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .billing-box h6 {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            color: #94a3b8;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .billing-box p {
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
        }
        .table-invoice {
            width: 100%;
            margin-top: 20px;
        }
        .table-invoice th {
            background: #f8fafc;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #64748b;
            padding: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-invoice td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .item-name {
            font-weight: 600;
            color: #1e293b;
            display: block;
        }
        .item-sku {
            font-size: 12px;
            color: #94a3b8;
        }
        .totals-section {
            margin-left: auto;
            max-width: 300px;
            margin-top: 30px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        .total-row.grand-total {
            border-top: 2px solid #0f172a;
            margin-top: 10px;
            font-weight: 700;
            font-size: 20px;
            color: #0f172a;
        }
        .invoice-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
        }
        
        @media print {
            body { background: white; }
            .invoice-wrapper { box-shadow: none; margin: 0; padding: 20px; width: 100%; max-width: 100%; border: none; }
            .btn-print { display: none; }
        }
        
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <div class="invoice-header">
        <div>
            <div class="brand-logo mb-3"><span>ARS</span> Shopping</div>
            <p class="small text-muted mb-0">Nepal's Trusted Online Marketplace</p>
        </div>
        <div class="text-end">
            <h1 class="invoice-title">INVOICE</h1>
            <ul class="meta-list">
                <li><strong>Invoice No:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></li>
                <li><strong>Date:</strong> <?php echo date('d M, Y', strtotime($order['created_at'])); ?></li>
                <li><strong>Status:</strong> <span class="text-success fw-bold"><?php echo strtoupper($order['delivery_status']); ?></span></li>
            </ul>
        </div>
    </div>

    <div class="billing-section">
        <div class="billing-box">
            <h6>Billed From</h6>
            <p><strong><?php echo h($site_name); ?></strong></p>
            <p>Main Street, Kathmandu</p>
            <p>Bagmati, Nepal</p>
            <p>Email: <?php echo h($site_email); ?></p>
        </div>
        <div class="billing-box text-end">
            <h6>Billed To</h6>
            <p><strong><?php echo h($order['customer_name']); ?></strong></p>
            <p><?php echo h($order['shipping_address']); ?></p>
            <p><?php echo h($order['shipping_city']); ?>, Nepal</p>
            <p>Phone: <?php echo h($order['customer_phone']); ?></p>
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
                $item_total = $item['unit_price'] * $item['quantity'];
                $subtotal += $item_total;
            ?>
            <tr>
                <td>
                    <span class="item-name"><?php echo h($item['prod_name']); ?></span>
                    <?php if ($item['prod_sku']): ?>
                        <span class="item-sku">SKU: <?php echo h($item['prod_sku']); ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="text-center"><?php echo $item['quantity']; ?></td>
                <td class="text-end">Rs. <?php echo number_format($item_total, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <span class="text-muted">Subtotal:</span>
            <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="total-row">
            <span class="text-muted">Shipping:</span>
            <span class="text-success fw-bold">FREE</span>
        </div>
        <div class="total-row grand-total">
            <span>Total:</span>
            <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
        </div>
        <div class="mt-2 text-end small text-muted">
            Payment via: <strong><?php echo strtoupper($order['payment_method']); ?></strong>
        </div>
    </div>

    <div class="invoice-footer">
        <p class="mb-1"><strong>Thank you for choosing <?php echo h($site_name); ?>!</strong></p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p class="mt-3">Terms & Conditions apply. For any discrepancies, contact us within 48 hours.</p>
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
