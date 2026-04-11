<?php
/**
 * Shipping Info Page
 * Easy Shopping A.R.S
 */
$page_title     = 'Shipping & Delivery Information | Easy Shopping A.R.S Nepal';
$page_meta_desc = 'Easy Shopping A.R.S delivers fast across Nepal. Free shipping on eligible orders. Track your order and learn about our delivery timelines and coverage areas.';
include 'includes/header-bootstrap.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="fw-bold">Shipping & Delivery</h2>
            <p class="text-muted">Everything you need to know about how we get your orders to you.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm p-4 p-md-5">
                <section class="mb-5">
                    <h4 class="fw-bold mb-3"><i class="bi bi-truck me-2 text-primary"></i> Delivery Timelines</h4>
                    <p>We strive to deliver your orders as quickly as possible. Our standard delivery timelines are:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 py-3 d-flex justify-content-between">
                            <span>Inside Birgunj</span>
                            <span class="fw-bold">24 - 48 Hours</span>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex justify-content-between">
                            <span>Major Cities (Kathmandu, Pokhara, etc.)</span>
                            <span class="fw-bold">2 - 4 Business Days</span>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex justify-content-between">
                            <span>Remote Areas</span>
                            <span class="fw-bold">5 - 7 Business Days</span>
                        </li>
                    </ul>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold mb-3"><i class="bi bi-cash-stack me-2 text-primary"></i> Shipping Charges</h4>
                    <p>Our shipping rates are flat and affordable:</p>
                    <div class="bg-light p-3 rounded-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Local Delivery (Birgunj)</span>
                            <span>Rs. 50</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Outside Birgunj</span>
                            <span>Rs. 150</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold text-success">
                            <span>Orders above Rs. 5000</span>
                            <span>FREE</span>
                        </div>
                    </div>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i> Order Tracking</h4>
                    <p>Once your order is shipped, you will receive a notification with a tracking ID. You can track your order status directly from your "My Orders" page in your account dashboard.</p>
                </section>

                <section>
                    <h4 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i> Delivery Policy</h4>
                    <p>Please ensure someone is available to receive the package at the provided address. If our delivery partner is unable to reach you after three attempts, the order will be returned to our warehouse.</p>
                    <p class="mb-0">For any urgent queries regarding your delivery, please call us at <strong>+977 9820210361</strong>.</p>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
.text-primary { color: #ea6c00 !important; }
</style>

<?php include 'includes/footer-bootstrap.php'; ?>
