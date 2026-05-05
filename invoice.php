<?php
/**
 * Order Invoice - Clean Minimal Style
 * Easy Shopping A.R.S
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$user = $_SESSION['user'] ?? null;
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$order_id) {
    die("Invalid Order ID");
}

try {
    $stmt = $pdo->prepare("
        SELECT o.*, oi.quantity, oi.price as unit_price, oi.discount_price as item_discount_price,
               p.name as prod_name, p.sku as prod_sku
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

    $is_admin = $user && isset($user['role']) && $user['role'] === 'admin';
    $is_owner = $user && isset($user['id']) && $user['id'] == $order['user_id'];
    $is_guest_order = is_null($order['user_id']);

    if (!$is_admin && !$is_owner && !$is_guest_order) {
        die("Unauthorized access");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$site_name    = get_setting('site_name', 'Easy Shopping A.R.S');
$site_email   = get_setting('admin_email', 'easyshoppinga.r.s1@gmail.com');
$site_phone   = get_setting('site_phone', '+977-982-0210361');
$site_address = get_setting('site_address', 'Birgunj-13, Radhemai, Parsa, Nepal');

$invoice_number = 'ARS-' . date('Y', strtotime($order['created_at'])) . '-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
$invoice_date   = date('F j, Y', strtotime($order['created_at']));

// Totals
$original_subtotal = 0;
$item_discount     = 0;
foreach ($items as $item) {
    $original_subtotal += $item['unit_price'] * $item['quantity'];
    if (!empty($item['item_discount_price']) && $item['item_discount_price'] < $item['unit_price']) {
        $item_discount += ($item['unit_price'] - $item['item_discount_price']) * $item['quantity'];
    }
}
$order_discount = $order['discount_amount'] ?? 0;
$shipping       = $order['shipping_charge'] ?? 0;
$total_amount   = $order['total_amount'];

// Payment method label
$pay_method_map = ['COD' => 'Cash on Delivery', 'esewa' => 'eSewa', 'BankQR' => 'Bank QR'];
$pay_method = $pay_method_map[$order['payment_method']] ?? strtoupper($order['payment_method']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo $invoice_number; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        color: #1a1a1a;
        background: #f0f0f0;
        line-height: 1.5;
    }

    .page {
        max-width: 720px;
        margin: 40px auto;
        background: #fff;
        padding: 60px 60px 50px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    /* ── Header ── */
    .inv-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 36px;
    }
    .inv-header h1 {
        font-size: 36px;
        font-weight: 700;
        letter-spacing: -0.5px;
        color: #111;
    }
    .site-logo {
        font-size: 28px;
        font-weight: 800;
        color: #111;
        letter-spacing: -1px;
        text-align: right;
        line-height: 1;
    }
    .site-logo span { color: #ea6c00; }

    /* ── Meta info ── */
    .inv-meta {
        display: grid;
        grid-template-columns: max-content 1fr;
        gap: 4px 16px;
        margin-bottom: 36px;
        font-size: 13.5px;
    }
    .inv-meta .label { color: #555; }
    .inv-meta .value { color: #111; font-weight: 500; }

    hr.divider {
        border: none;
        border-top: 1px solid #ddd;
        margin: 0 0 32px;
    }

    /* ── From / Bill to ── */
    .address-row {
        display: flex;
        gap: 60px;
        margin-bottom: 36px;
    }
    .address-col { flex: 1; }
    .address-col .col-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #777;
        margin-bottom: 8px;
    }
    .address-col strong {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #111;
        margin-bottom: 4px;
    }
    .address-col p {
        font-size: 13.5px;
        color: #444;
        line-height: 1.7;
    }

    /* ── Amount due banner ── */
    .amount-due {
        margin-bottom: 10px;
    }
    .amount-due h2 {
        font-size: 26px;
        font-weight: 700;
        color: #111;
        letter-spacing: -0.5px;
    }
    .status-badges {
        display: flex;
        gap: 10px;
        margin: 10px 0 28px;
        flex-wrap: wrap;
    }
    .badge {
        display: inline-block;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 4px;
        background: #f3f3f3;
        color: #444;
    }
    .badge.paid     { background: #d1fae5; color: #065f46; }
    .badge.pending  { background: #fef3c7; color: #92400e; }
    .badge.failed   { background: #fee2e2; color: #991b1b; }
    .badge.delivered{ background: #dbeafe; color: #1e40af; }
    .badge.shipped  { background: #e0e7ff; color: #3730a3; }
    .badge.cancelled{ background: #fee2e2; color: #991b1b; }

    /* ── Items table ── */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    thead th {
        font-size: 12px;
        font-weight: 600;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    thead th.right { text-align: right; }
    thead th.center { text-align: center; }

    tbody td {
        padding: 14px 0;
        border-bottom: 1px solid #eee;
        vertical-align: top;
        font-size: 13.5px;
        color: #222;
    }
    tbody td.right { text-align: right; }
    tbody td.center { text-align: center; }

    .prod-name { font-weight: 500; color: #111; }
    .prod-sub  { font-size: 12px; color: #888; margin-top: 2px; }
    .strike    { text-decoration: line-through; color: #bbb; font-size: 12px; }

    /* ── Totals ── */
    .totals-wrap {
        display: flex;
        justify-content: flex-end;
        margin-top: 4px;
        margin-bottom: 36px;
    }
    .totals {
        width: 280px;
    }
    .t-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        font-size: 13.5px;
        color: #555;
        border-bottom: 1px solid #eee;
    }
    .t-row:last-child { border-bottom: none; }
    .t-row .t-val { font-weight: 500; color: #222; }
    .t-row .t-val.green { color: #16a34a; }
    .t-row.grand {
        padding-top: 12px;
        font-size: 15px;
        font-weight: 700;
        color: #111;
        border-top: 2px solid #111;
        border-bottom: none;
    }
    .t-row.grand .t-val { color: #111; }

    /* ── Payment note ── */
    .pay-note {
        background: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 16px 20px;
        font-size: 13px;
        color: #555;
        margin-bottom: 36px;
        line-height: 1.7;
    }
    .pay-note strong { color: #222; }

    /* ── Footer ── */
    .inv-footer {
        border-top: 1px solid #ddd;
        padding-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #888;
    }

    /* ── Print button ── */
    .btn-print {
        position: fixed;
        bottom: 28px;
        right: 28px;
        background: #111;
        color: #fff;
        border: none;
        padding: 12px 22px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        font-family: inherit;
    }
    .btn-print:hover { background: #333; }

    .btn-back {
        position: fixed;
        bottom: 28px;
        left: 28px;
        background: #fff;
        color: #111;
        border: 1px solid #ddd;
        padding: 12px 22px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
    }
    .btn-back:hover { background: #f5f5f5; color: #111; }

    @media print {
        body { background: #fff; }
        .page { box-shadow: none; margin: 0; max-width: 100%; padding: 40px 50px; }
        .btn-print, .btn-back { display: none; }
    }
</style>
</head>
<body>

<div class="page">

    <!-- Header -->
    <div class="inv-header">
        <h1>Invoice</h1>
        <div class="site-logo">
            A<span>.</span>R<span>.</span>S
        </div>
    </div>

    <!-- Invoice Meta -->
    <div class="inv-meta">
        <span class="label">Invoice number</span>
        <span class="value"><?php echo $invoice_number; ?></span>

        <span class="label">Date of issue</span>
        <span class="value"><?php echo $invoice_date; ?></span>

        <span class="label">Order ID</span>
        <span class="value">#<?php echo $order['id']; ?></span>
    </div>

    <hr class="divider">

    <!-- From / Bill To -->
    <div class="address-row">
        <div class="address-col">
            <div class="col-label">From</div>
            <strong><?php echo h($site_name); ?></strong>
            <p>
                <?php echo nl2br(h($site_address)); ?><br>
                Nepal<br>
                <?php echo h($site_email); ?><br>
                <?php echo h($site_phone); ?>
            </p>
        </div>
        <div class="address-col">
            <div class="col-label">Bill to</div>
            <strong><?php echo h($order['customer_name']); ?></strong>
            <p>
                <?php echo h($order['shipping_address']); ?><br>
                <?php echo h($order['shipping_city']); ?>, Nepal<br>
                <?php echo h($order['customer_phone']); ?>
                <?php if (!empty($order['customer_email'])): ?>
                <br><?php echo h($order['customer_email']); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Amount Due -->
    <div class="amount-due">
        <h2>Rs. <?php echo number_format($total_amount, 2); ?> due <?php echo $invoice_date; ?></h2>
    </div>

    <!-- Status badges -->
    <div class="status-badges">
        <?php
        $d_class = ['Delivered' => 'delivered', 'Shipped' => 'shipped', 'Cancelled' => 'cancelled', 'Pending' => 'pending', 'Confirmed' => 'delivered', 'Out for Delivery' => 'shipped'];
        $p_class = ['Paid' => 'paid', 'Pending' => 'pending', 'Failed' => 'failed'];
        ?>
        <span class="badge <?php echo $d_class[$order['delivery_status']] ?? ''; ?>">
            Delivery: <?php echo $order['delivery_status']; ?>
        </span>
        <span class="badge <?php echo $p_class[$order['payment_status']] ?? ''; ?>">
            Payment: <?php echo $order['payment_status']; ?>
        </span>
    </div>

    <hr class="divider">

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="center">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item):
            $eff_price  = (!empty($item['item_discount_price']) && $item['item_discount_price'] < $item['unit_price'])
                          ? $item['item_discount_price'] : $item['unit_price'];
            $line_total = $eff_price * $item['quantity'];
        ?>
            <tr>
                <td>
                    <div class="prod-name"><?php echo h($item['prod_name']); ?></div>
                    <?php if ($item['prod_sku']): ?>
                    <div class="prod-sub">SKU: <?php echo h($item['prod_sku']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="center"><?php echo $item['quantity']; ?></td>
                <td class="right">
                    <?php if ($eff_price < $item['unit_price']): ?>
                        <div>Rs. <?php echo number_format($eff_price, 2); ?></div>
                        <div class="strike">Rs. <?php echo number_format($item['unit_price'], 2); ?></div>
                    <?php else: ?>
                        Rs. <?php echo number_format($item['unit_price'], 2); ?>
                    <?php endif; ?>
                </td>
                <td class="right">Rs. <?php echo number_format($line_total, 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-wrap">
        <div class="totals">
            <div class="t-row">
                <span>Subtotal</span>
                <span class="t-val">Rs. <?php echo number_format($original_subtotal, 2); ?></span>
            </div>
            <?php if ($item_discount > 0): ?>
            <div class="t-row">
                <span>Item discount</span>
                <span class="t-val green">-Rs. <?php echo number_format($item_discount, 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order_discount > 0): ?>
            <div class="t-row">
                <span>Coupon (<?php echo h($order['coupon_code']); ?>)</span>
                <span class="t-val green">-Rs. <?php echo number_format($order_discount, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="t-row">
                <span>Shipping</span>
                <?php if ($shipping > 0): ?>
                    <span class="t-val">Rs. <?php echo number_format($shipping, 2); ?></span>
                <?php else: ?>
                    <span class="t-val green">Free</span>
                <?php endif; ?>
            </div>
            <div class="t-row grand">
                <span>Amount due</span>
                <span class="t-val">Rs. <?php echo number_format($total_amount, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Note -->
    <div class="pay-note">
        <strong>Payment method:</strong> <?php echo h($pay_method); ?><br>
        <?php if (!empty($order['transaction_id'])): ?>
        <strong>Transaction ID:</strong> <?php echo h($order['transaction_id']); ?><br>
        <?php endif; ?>
        For any discrepancies, please contact us within 48 hours of delivery at
        <strong><?php echo h($site_email); ?></strong> or <strong><?php echo h($site_phone); ?></strong>.
    </div>

    <!-- Footer -->
    <div class="inv-footer">
        <span>Thank you for shopping with <?php echo h($site_name); ?>!</span>
        <span>Page 1 of 1</span>
    </div>

</div>

<button class="btn-print" onclick="window.print()">
    <svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16">
        <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
        <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
    </svg>
    Print / Save PDF
</button>

<a href="<?php echo url('/orders.php'); ?>" class="btn-back">&larr; Back to Orders</a>

</body>
</html>
