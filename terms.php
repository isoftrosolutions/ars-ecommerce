<?php
/**
 * Terms & Conditions Page
 * Easy Shopping A.R.S
 */
$page_title = "Terms & Conditions - Easy Shopping A.R.S";
$page_meta_desc = "Read our terms and conditions for using Easy Shopping A.R.S website and services.";
include 'includes/header-bootstrap.php';
?>

<style>
/* Terms & Conditions Styles */
.terms-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
}

.terms-content {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    padding: 40px;
    margin: -30px auto 0;
    position: relative;
    max-width: 900px;
}

.terms-section {
    margin-bottom: 30px;
}

.terms-section h3 {
    color: #0f172a;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.terms-section h4 {
    color: #374151;
    font-weight: 500;
    margin: 20px 0 10px;
    font-size: 1.1rem;
}

.terms-section p {
    line-height: 1.7;
    color: #6b7280;
    margin-bottom: 15px;
}

.terms-section ul {
    padding-left: 20px;
    margin-bottom: 15px;
}

.terms-section li {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 5px;
}

.important-note {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 20px;
    margin: 20px 0;
    border-radius: 0 8px 8px 0;
}

.definition-list {
    background: #f8fafc;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
}

.definition-item {
    margin-bottom: 15px;
}

.definition-term {
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 5px;
}

.definition-desc {
    color: #6b7280;
    margin-left: 10px;
}

.last-updated {
    text-align: center;
    color: #9ca3af;
    font-size: 0.9rem;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .terms-content {
        margin: -20px 15px 0;
        padding: 30px 20px;
    }
}
</style>

<!-- Hero Section -->
<section class="terms-hero">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">Terms & Conditions</h1>
                <p class="lead mb-0">
                    Please read these terms carefully before using Easy Shopping A.R.S
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5">
    <div class="container">
        <div class="terms-content">
            <div class="terms-section">
                <h3>1. Acceptance of Terms</h3>
                <p>
                    By accessing and using Easy Shopping A.R.S ("the Website"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                </p>
                <p>
                    These terms apply to all visitors, users, and others who access or use our services.
                </p>
            </div>

            <div class="terms-section">
                <h3>2. Use License</h3>
                <p>
                    Permission is granted to temporarily access the materials (information or software) on Easy Shopping A.R.S for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
                </p>
                <ul>
                    <li>Modify or copy the materials</li>
                    <li>Use the materials for any commercial purpose or for any public display</li>
                    <li>Attempt to decompile or reverse engineer any software contained on the website</li>
                    <li>Remove any copyright or other proprietary notations from the materials</li>
                </ul>
                <p>
                    This license shall automatically terminate if you violate any of these restrictions and may be terminated by Easy Shopping A.R.S at any time.
                </p>
            </div>

            <div class="terms-section">
                <h3>3. User Accounts</h3>

                <h4>Account Creation</h4>
                <p>
                    To access certain features of our service, you must create an account. You must provide accurate, complete, and current information during the registration process and keep your account information updated.
                </p>

                <h4>Account Security</h4>
                <p>
                    You are responsible for safeguarding your account credentials and for all activities that occur under your account. You must immediately notify us of any unauthorized use of your account or any other breach of security.
                </p>

                <h4>Account Termination</h4>
                <p>
                    We reserve the right to terminate or suspend your account and access to our services at our sole discretion, without prior notice, for conduct that we believe violates these Terms or is harmful to other users, us, or third parties.
                </p>
            </div>

            <div class="terms-section">
                <h3>4. Products and Services</h3>

                <h4>Product Information</h4>
                <p>
                    We strive to provide accurate product descriptions, pricing, and availability information. However, we do not warrant that product descriptions or other content on our site is accurate, complete, reliable, current, or error-free.
                </p>

                <h4>Pricing</h4>
                <p>
                    All prices are subject to change without notice. We reserve the right to modify or discontinue products or services without notice at any time.
                </p>

                <h4>Product Availability</h4>
                <p>
                    We will make reasonable efforts to display the availability of our products. However, we cannot guarantee that products will be available when you place an order.
                </p>
            </div>

            <div class="terms-section">
                <h3>5. Orders and Payment</h3>

                <h4>Order Acceptance</h4>
                <p>
                    Your order is an offer to purchase the products listed. We reserve the right to accept or decline your order for any reason, including limitations on quantities available for purchase or errors in product information.
                </p>

                <h4>Payment</h4>
                <p>
                    Payment must be made at the time of order placement. We accept payments through eSewa, bank transfer, and cash on delivery. All payments are processed securely through authorized payment gateways.
                </p>

                <h4>Payment Security</h4>
                <p>
                    We use industry-standard encryption to protect your payment information. We do not store your payment details on our servers.
                </p>
            </div>

            <div class="terms-section">
                <h3>6. Shipping and Delivery</h3>

                <h4>Delivery Areas</h4>
                <p>
                    We currently deliver to Birgunj, Parsa, and surrounding areas in Nepal. We may expand our delivery areas in the future.
                </p>

                <h4>Delivery Times</h4>
                <p>
                    Delivery times may vary depending on your location and product availability. We will provide estimated delivery times during checkout.
                </p>

                <h4>Shipping Costs</h4>
                <p>
                    Shipping costs are calculated based on order value and delivery location. Free shipping is available on orders over Rs. 1,000.
                </p>

                <h4>Delivery Issues</h4>
                <p>
                    If you receive damaged or incorrect items, please contact us within 24 hours of delivery for resolution.
                </p>
            </div>

            <div class="terms-section">
                <h3>7. Returns and Refunds</h3>

                <h4>Return Policy</h4>
                <p>
                    We offer a 5-day return policy for most items. Items must be in their original condition and packaging. Certain items may not be eligible for return.
                </p>

                <h4>Refund Process</h4>
                <p>
                    Refunds will be processed within 5-7 business days after we receive the returned item. Refunds will be issued to the original payment method.
                </p>

                <h4>Non-Returnable Items</h4>
                <ul>
                    <li>Personal care items</li>
                    <li>Custom or personalized products</li>
                    <li>Items damaged due to misuse</li>
                    <li>Items without original packaging</li>
                </ul>
            </div>

            <div class="terms-section">
                <h3>8. User Conduct</h3>
                <p>You agree not to use our service:</p>
                <ul>
                    <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                    <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                    <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                    <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                    <li>To submit false or misleading information</li>
                    <li>To interfere with or circumvent the security features of the service</li>
                </ul>
            </div>

            <div class="terms-section">
                <h3>9. Intellectual Property</h3>
                <p>
                    The service and its original content, features, and functionality are and will remain the exclusive property of Easy Shopping A.R.S and its licensors. The service is protected by copyright, trademark, and other laws.
                </p>
                <p>
                    Our trademarks and trade dress may not be used in connection with any product or service without our prior written consent.
                </p>
            </div>

            <div class="terms-section">
                <h3>10. Limitation of Liability</h3>
                <p>
                    In no event shall Easy Shopping A.R.S, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses.
                </p>
                <div class="important-note">
                    <strong>Important:</strong> Our total liability shall not exceed the amount paid by you for the products purchased.
                </div>
            </div>

            <div class="terms-section">
                <h3>11. Disclaimer</h3>
                <p>
                    The information on this website is provided on an "as is" basis. To the fullest extent permitted by law, Easy Shopping A.R.S excludes all representations, warranties, conditions, and terms whether express or implied, statutory or otherwise.
                </p>
            </div>

            <div class="terms-section">
                <h3>12. Governing Law</h3>
                <p>
                    These Terms shall be interpreted and governed by the laws of Nepal, without regard to its conflict of law provisions. Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
                </p>
            </div>

            <div class="terms-section">
                <h3>13. Changes to Terms</h3>
                <p>
                    We reserve the right to modify these terms at any time. We will notify users of any changes by posting the new Terms on this page. Your continued use of the service after such modifications constitutes acceptance of the updated terms.
                </p>
            </div>

            <div class="terms-section">
                <h3>14. Contact Information</h3>
                <p>
                    If you have any questions about these Terms & Conditions, please contact us:
                </p>
                <ul>
                    <li><strong>Email:</strong> easyshoppinga.r.s1@gmail.com</li>
                    <li><strong>Phone:</strong> +977 9820210361</li>
                    <li><strong>Address:</strong> Birgunj, Parsa, Nepal</li>
                </ul>
            </div>

            <div class="last-updated">
                Last Updated: <?php echo date('F d, Y'); ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer-bootstrap.php'; ?>