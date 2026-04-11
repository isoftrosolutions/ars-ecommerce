<?php
/**
 * About Us Page
 * Easy Shopping A.R.S
 */
$page_title = "About Us - Easy Shopping A.R.S";
$page_meta_desc = "Learn about Easy Shopping A.R.S - Nepal's trusted online store. Discover our mission, values, and commitment to quality products and excellent customer service.";
include 'includes/header-bootstrap.php';
?>

<style>
/* About Page Styles */
.hero-about {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.hero-about::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.03"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.03"/><circle cx="50" cy="50" r="0.5" fill="%23ffffff" opacity="0.02"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.hero-about .container {
    position: relative;
    z-index: 2;
}

.about-stats {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
    margin-top: -60px;
    position: relative;
    z-index: 3;
}

.stat-item {
    text-align: center;
    padding: 20px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--gold);
    margin-bottom: 10px;
}

.stat-label {
    color: #64748b;
    font-weight: 500;
}

.mission-section {
    background: #f8fafc;
    padding: 80px 0;
}

.mission-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    height: 100%;
}

.mission-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 20px;
}

.values-section {
    padding: 80px 0;
    background: white;
}

.value-card {
    text-align: center;
    padding: 30px 20px;
    border-radius: 15px;
    transition: all 0.3s;
    height: 100%;
}

.value-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.value-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 1.5rem;
}

.team-section {
    background: #f8fafc;
    padding: 80px 0;
}

.team-member {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    text-align: center;
    height: 100%;
}

.team-photo {
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.cta-section {
    background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.cta-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    border: 1px solid rgba(255,255,255,0.2);
}

@media (max-width: 768px) {
    .hero-about {
        padding: 60px 0;
    }

    .about-stats {
        margin-top: -40px;
        padding: 30px 20px;
    }

    .stat-number {
        font-size: 2rem;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4">
                    About Easy Shopping A.R.S
                </h1>
                <p class="lead mb-4">
                    Nepal's trusted online marketplace connecting quality products with discerning customers.
                    We're committed to delivering exceptional shopping experiences with reliable service,
                    authentic products, and unbeatable value.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-light btn-lg px-4">
                        <i class="bi bi-shop me-2"></i>Start Shopping
                    </a>
                    <a href="#contact" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 20px; padding: 40px; text-align: center; border: 1px solid rgba(255,255,255,0.2);">
                    <i class="bi bi-bag-heart display-1 text-white mb-3"></i>
                    <h4 class="text-white">Premium Quality</h4>
                    <p class="text-white-50 mb-0">Curated products for the modern lifestyle</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="about-stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Quality Products</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Customer Support</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">4.8</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="mission-section">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="display-5 fw-bold mb-4">Our Mission</h2>
                <p class="lead text-muted mb-4">
                    To revolutionize online shopping in Nepal by providing a seamless, trustworthy,
                    and enjoyable shopping experience that brings quality products closer to every home.
                </p>
                <p class="text-muted">
                    We believe that great shopping should be simple, secure, and satisfying.
                    That's why we've built a platform that prioritizes customer satisfaction,
                    product authenticity, and reliable service.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="mission-card">
                    <div class="mission-icon" style="background: rgba(234,108,0,0.1); color: var(--ember);">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h4 class="mb-3">Customer First Approach</h4>
                    <p class="text-muted mb-0">
                        Every decision we make is guided by our commitment to our customers.
                        From product selection to delivery, your satisfaction is our top priority.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="mission-card">
                    <div class="mission-icon" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="mb-3">Authentic Products</h5>
                    <p class="text-muted mb-0">
                        We carefully curate and verify all products to ensure authenticity,
                        quality, and value for money.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mission-card">
                    <div class="mission-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="mb-3">Fast Delivery</h5>
                    <p class="text-muted mb-0">
                        Quick and reliable delivery across Nepal with real-time tracking
                        and secure packaging.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mission-card">
                    <div class="mission-icon" style="background: rgba(168,85,247,0.1); color: #a855f7;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5 class="mb-3">24/7 Support</h5>
                    <p class="text-muted mb-0">
                        Round-the-clock customer support to assist you with any questions
                        or concerns you may have.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold mb-4">Our Core Values</h2>
                <p class="lead text-muted">
                    The principles that guide everything we do at Easy Shopping A.R.S
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="value-card">
                    <div class="value-icon" style="background: rgba(234,108,0,0.1); color: var(--ember);">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h5 class="mb-3">Integrity</h5>
                    <p class="text-muted mb-0">
                        Honest business practices and transparent dealings with all stakeholders.
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card">
                    <div class="value-icon" style="background: rgba(34,197,94,0.1); color: #22c55e;">
                        <i class="bi bi-star"></i>
                    </div>
                    <h5 class="mb-3">Quality</h5>
                    <p class="text-muted mb-0">
                        Commitment to excellence in products, service, and customer experience.
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card">
                    <div class="value-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <h5 class="mb-3">Innovation</h5>
                    <p class="text-muted mb-0">
                        Continuously improving our platform and services to serve you better.
                    </p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="value-card">
                    <div class="value-icon" style="background: rgba(168,85,247,0.1); color: #a855f7;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h5 class="mb-3">Community</h5>
                    <p class="text-muted mb-0">
                        Building strong relationships and contributing to the local economy.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold mb-4">Meet Our Team</h2>
                <p class="lead text-muted">
                    The dedicated professionals behind Easy Shopping A.R.S
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="team-member">
                    <div class="team-photo">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="p-4">
                        <h5 class="mb-2">Admin Team</h5>
                        <p class="text-muted mb-3">Operations & Management</p>
                        <div class="social-links">
                            <a href="#" class="text-muted me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-muted me-3"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-muted"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="team-member">
                    <div class="team-photo">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="p-4">
                        <h5 class="mb-2">Support Team</h5>
                        <p class="text-muted mb-3">Customer Service</p>
                        <div class="social-links">
                            <a href="#" class="text-muted me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-muted me-3"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-muted"><i class="bi bi-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="team-member">
                    <div class="team-photo">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="p-4">
                        <h5 class="mb-2">Technical Team</h5>
                        <p class="text-muted mb-3">Development & Maintenance</p>
                        <div class="social-links">
                            <a href="#" class="text-muted me-3"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-muted me-3"><i class="bi bi-github"></i></a>
                            <a href="#" class="text-muted"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2 class="display-5 fw-bold mb-4">Ready to Start Shopping?</h2>
            <p class="lead mb-4">
                Join thousands of satisfied customers who trust Easy Shopping A.R.S
                for their online shopping needs.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo url('/shop'); ?>" class="btn btn-light btn-lg px-4">
                    <i class="bi bi-shop me-2"></i>Browse Products
                </a>
                <a href="<?php echo url('/auth/signup'); ?>" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h3 class="mb-4">Get in Touch</h3>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-geo-alt text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Address</h6>
                                <p class="text-muted mb-0">Birgunj, Parsa<br>Nepal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-telephone text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Phone</h6>
                                <p class="text-muted mb-0">+977 9820210361</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-envelope text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Email</h6>
                                <p class="text-muted mb-0">easyshoppinga.r.s1@gmail.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-clock text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Support Hours</h6>
                                <p class="text-muted mb-0">24/7 Available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-light p-4 rounded">
                    <h5 class="mb-4">Send us a Message</h5>
                    <form action="<?php echo url('/contact'); ?>" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="col-12">
                                <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                            </div>
                            <div class="col-12">
                                <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer-bootstrap.php'; ?>