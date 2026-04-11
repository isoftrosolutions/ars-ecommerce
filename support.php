<?php
/**
 * Support & Help Center Page
 * Easy Shopping A.R.S
 */
$page_title = "Support & Help Center";
include 'includes/header-bootstrap.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="fw-bold mb-3">How Can We Help You?</h1>
            <p class="text-muted fs-5">Find answers to common questions or get in touch with our support team</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4">
                <div class="icon-box mx-auto mb-3">
                    <i class="bi bi-chat-dots fs-1 text-primary"></i>
                </div>
                <h5 class="fw-bold">Live Chat</h5>
                <p class="text-muted">Chat with our support team for instant help</p>
                <button class="btn btn-primary mt-3">Start Chat</button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4">
                <div class="icon-box mx-auto mb-3">
                    <i class="bi bi-envelope fs-1 text-primary"></i>
                </div>
                <h5 class="fw-bold">Email Support</h5>
                <p class="text-muted">Send us an email and we'll respond within 24 hours</p>
                <a href="mailto:support@easyshoppingars.com" class="btn btn-primary mt-3">Send Email</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 text-center p-4">
                <div class="icon-box mx-auto mb-3">
                    <i class="bi bi-telephone fs-1 text-primary"></i>
                </div>
                <h5 class="fw-bold">Call Us</h5>
                <p class="text-muted">Speak directly with our customer service team</p>
                <a href="tel:+9779820210361" class="btn btn-primary mt-3">Call Now</a>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="fw-bold text-center mb-4">Frequently Asked Questions</h2>
        </div>
    </div>

    <div class="accordion" id="faqAccordion">
        <!-- Order Related FAQs -->
        <div class="accordion-item border-0 shadow-sm mb-3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#orders">
                    <i class="bi bi-cart me-2"></i>Orders & Shopping
                </button>
            </h2>
            <div id="orders" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">How do I place an order?</h6>
                        <p class="text-muted">Browse our products, add items to your cart, and proceed to checkout. You can pay using eSewa, bank transfer, or cash on delivery.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">What payment methods do you accept?</h6>
                        <p class="text-muted">We accept eSewa payments, bank transfers, and cash on delivery (COD) for orders within Nepal.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">How long does delivery take?</h6>
                        <p class="text-muted">Delivery typically takes 3-5 business days within Nepal. You will receive tracking information once your order ships.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Returns & Refunds -->
        <div class="accordion-item border-0 shadow-sm mb-3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#returns">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Returns & Refunds
                </button>
            </h2>
            <div id="returns" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">What is your return policy?</h6>
                        <p class="text-muted">We offer returns within 7 days of delivery for unused items in original packaging. Contact our support team to initiate a return.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">How do I return an item?</h6>
                        <p class="text-muted">Contact our support team with your order ID and reason for return. We'll provide return instructions and arrange pickup if eligible.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">When will I receive my refund?</h6>
                        <p class="text-muted">Refunds are processed within 3-5 business days after we receive the returned item. Amount will be credited to your original payment method.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account & Login -->
        <div class="accordion-item border-0 shadow-sm mb-3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#account">
                    <i class="bi bi-person me-2"></i>Account & Login
                </button>
            </h2>
            <div id="account" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">How do I create an account?</h6>
                        <p class="text-muted">Click on "Sign Up" in the top navigation and fill out the registration form with your details.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">I forgot my password. What should I do?</h6>
                        <p class="text-muted">Click "Forgot Password" on the login page and enter your email address. We'll send you a reset link.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">How do I update my account information?</h6>
                        <p class="text-muted">Go to your profile page after logging in to update your personal information, address, and preferences.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping & Delivery -->
        <div class="accordion-item border-0 shadow-sm mb-3">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#shipping">
                    <i class="bi bi-truck me-2"></i>Shipping & Delivery
                </button>
            </h2>
            <div id="shipping" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Do you ship outside Nepal?</h6>
                        <p class="text-muted">Currently, we only ship within Nepal, primarily to Birgunj, Parsa, and surrounding areas.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">What are the shipping charges?</h6>
                        <p class="text-muted">Free shipping on orders over Rs. 2,000. Orders under Rs. 2,000 have a flat shipping rate of Rs. 100.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Can I track my order?</h6>
                        <p class="text-muted">Yes, you'll receive tracking information via email and SMS once your order is shipped.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="fw-bold text-center mb-4">Contact Information</h2>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h4 class="fw-bold mb-4">Customer Support</h4>

                <div class="d-flex mb-3">
                    <div class="icon-box me-3">
                        <i class="bi bi-envelope-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="text-muted mb-0">support@easyshoppingars.com</p>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="icon-box me-3">
                        <i class="bi bi-telephone-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Phone</h6>
                        <p class="text-muted mb-0">+977 9820210361</p>
                        <p class="text-muted small mb-0">Mon-Fri: 9AM-6PM NPT</p>
                    </div>
                </div>

                <div class="d-flex mb-3">
                    <div class="icon-box me-3">
                        <i class="bi bi-geo-alt-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="text-muted mb-0">Birgunj, Parsa, Nepal</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h4 class="fw-bold mb-4">Business Hours</h4>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">Monday - Friday</span>
                        <span class="text-muted">9:00 AM - 6:00 PM</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">Saturday</span>
                        <span class="text-muted">10:00 AM - 4:00 PM</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">Sunday</span>
                        <span class="text-muted">Closed</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">Public Holidays</span>
                        <span class="text-muted">Closed</span>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>24/7 Support:</strong> For urgent orders or issues, contact us via email - we respond within 24 hours.
                </div>
            </div>
        </div>
    </div>

    <!-- Still Need Help Section -->
    <div class="row mt-5">
        <div class="col-12 text-center">
            <div class="card border-0 shadow-sm p-5">
                <h3 class="fw-bold mb-3">Still Need Help?</h3>
                <p class="text-muted mb-4">Can't find what you're looking for? Our support team is here to help.</p>

                <div class="row g-3 justify-content-center">
                    <div class="col-md-3">
                        <a href="contact.php" class="btn btn-dark w-100 py-3">
                            <i class="bi bi-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="mailto:support@easyshoppingars.com" class="btn btn-outline-dark w-100 py-3">
                            <i class="bi bi-chat-dots me-2"></i>Email Support
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="tel:+9779820210361" class="btn btn-outline-dark w-100 py-3">
                            <i class="bi bi-telephone me-2"></i>Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 50px;
    height: 50px;
    background: rgba(234, 108, 0, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.text-primary { color: #ea6c00 !important; }
.accordion-button:not(.collapsed) {
    background-color: rgba(234, 108, 0, 0.05);
    color: #ea6c00;
}
.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(234, 108, 0, 0.25);
}
</style>

<?php include 'includes/footer-bootstrap.php'; ?>