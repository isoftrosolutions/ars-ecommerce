<?php
/**
 * About Us Page
 * Easy Shopping A.R.S
 */
$page_title = "About Us - Easy Shopping A.R.S";
$page_meta_desc = "Learn about Easy Shopping A.R.S - Nepal's trusted online store. Discover our mission, values, and commitment to quality products and excellent customer service.";
include 'includes/header-bootstrap.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

<style>
/* ═══ CSS Variables — Consistent with Website ════════════════════ */
:root {
  --void:        #ffffff;
  --deep:        #f8fafc;
  --surface:     #ffffff;
  --glass:       rgba(255,255,255,0.85);
  --ember:       #ea6c00;
  --ember-glow:  rgba(234,108,0,0.10);
  --gold:        #d97706;
  --gold-glow:   rgba(217,119,6,0.10);
  --ice:         #0f172a;
  --muted:       #64748b;
  --edge:        rgba(15,23,42,0.08);
  --shadow-sm:   0 2px 8px rgba(15,23,42,0.06);
  --shadow-md:   0 8px 32px rgba(15,23,42,0.10);
  --shadow-lg:   0 24px 64px rgba(15,23,42,0.12);
  --font-d:      'Fraunces', Georgia, serif;
  --font-b:      'DM Sans', sans-serif;
  --ease-out:    cubic-bezier(0.22,1,0.36,1);
}

/* ═══ About Page Styles — Aligned with Website ═════════════════════ */

/* Section Components - Consistent with Website */
.sec-eyebrow {
  font-size: 10px; letter-spacing: 3.5px; text-transform: uppercase;
  color: var(--ember); font-weight: 700; display: block; margin-bottom: 10px;
}
.sec-h2 {
  font-family: var(--font-d);
  font-size: clamp(2rem, 4.5vw, 3.2rem);
  font-weight: 900; color: var(--ice);
  line-height: 1.0; margin-bottom: 0;
  letter-spacing: -1px;
}
.sec-sub { color: var(--muted); font-size: 14px; font-weight: 300; margin-top: 10px; }

/* Button Styles - Consistent with Website */
.btn-void {
  display: inline-flex; align-items: center;
  background: var(--ice); color: white;
  border: 1px solid var(--ice);
  border-radius: 12px; padding: 12px 20px;
  font-size: 13px; font-weight: 600; letter-spacing: 0.5px;
  text-decoration: none; transition: all 0.3s var(--ease-out);
}
.btn-void:hover {
  background: var(--ember); border-color: var(--ember);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(234,108,0,0.2);
}
.btn-outline {
  display: inline-flex; align-items: center;
  background: transparent; color: var(--ice);
  border: 1px solid var(--ice);
  border-radius: 12px; padding: 12px 20px;
  font-size: 13px; font-weight: 600; letter-spacing: 0.5px;
  text-decoration: none; transition: all 0.3s var(--ease-out);
}
.btn-outline:hover {
  background: var(--ice); color: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(15,23,42,0.1);
}

.hero-about {
    background: #fffbf5;
    padding: 90px 0;
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
    background: linear-gradient(135deg, rgba(234,108,0,0.05), rgba(217,119,6,0.03));
    pointer-events: none;
}

.hero-about .container {
    position: relative;
    z-index: 2;
}



/* Mission Section */
.mission-section {
    background: var(--deep);
    padding: 90px 0;
    border-top: 1px solid var(--edge);
    border-bottom: 1px solid var(--edge);
}

.mission-card {
    background: var(--surface);
    border: 1px solid var(--edge);
    border-radius: 20px;
    padding: 32px 24px;
    height: 100%;
    transition: all 0.3s var(--ease-out);
    text-align: center;
}

.mission-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.mission-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 20px;
}

/* Values Section */
.values-section {
    background: var(--void);
    padding: 90px 0;
}

.value-card {
    background: var(--surface);
    border: 1px solid var(--edge);
    text-align: center;
    padding: 32px 24px;
    border-radius: 20px;
    transition: all 0.3s var(--ease-out);
    height: 100%;
}

.value-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.value-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 24px;
}

/* Team Section */
.team-section {
    background: var(--deep);
    padding: 90px 0;
}

.team-member {
    background: var(--surface);
    border: 1px solid var(--edge);
    border-radius: 20px;
    overflow: hidden;
    text-align: center;
    height: 100%;
    transition: all 0.3s var(--ease-out);
}

.team-member:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
}

.team-photo {
    width: 100%;
    height: 280px;
    background: linear-gradient(135deg, rgba(234,108,0,0.1), rgba(217,119,6,0.1));
    position: relative;
    overflow: hidden;
}

.team-photo::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.05);
    transition: opacity 0.3s ease;
}

.team-member:hover .team-photo::before {
    opacity: 0.1;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
    color: white;
    padding: 90px 0;
    text-align: center;
}

.cta-card {
    background: var(--glass);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 48px 32px;
    border: 1px solid rgba(255,255,255,0.2);
    max-width: 600px;
    margin: 0 auto;
    box-shadow: var(--shadow-lg);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-about {
        padding: 60px 0;
    }

    .mission-section,
    .values-section,
    .team-section,
    .cta-section {
        padding: 60px 0;
    }

    .cta-card {
        padding: 32px 24px;
    }

    .sec-h2 {
        font-size: clamp(1.8rem, 6vw, 2.5rem);
    }
}
</style>

<!-- Hero Section -->
<section class="hero-about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 style="font-family: var(--font-d); font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 900; color: var(--ice); line-height: 1.0; margin-bottom: 24px; letter-spacing: -2px;">
                    About Easy<br>Shopping A.R.S
                </h1>
                <p style="font-size: 18px; color: var(--muted); line-height: 1.6; margin-bottom: 32px;">
                    Nepal's trusted online marketplace connecting quality products with discerning customers.
                    We're committed to delivering exceptional shopping experiences with reliable service,
                    authentic products, and unbeatable value.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo url('/shop'); ?>" class="btn-void">
                        Start Shopping &nbsp; <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="#contact" class="btn-outline">
                        Contact Us &nbsp; <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div style="background: var(--glass); backdrop-filter: blur(20px); border-radius: 24px; padding: 48px 32px; text-align: center; border: 1px solid rgba(255,255,255,0.2); box-shadow: var(--shadow-lg);">
                    <i class="bi bi-bag-heart" style="font-size: 4rem; color: var(--ember); margin-bottom: 20px;"></i>
                    <h4 style="color: var(--ice); font-family: var(--font-d); font-weight: 700; margin-bottom: 12px;">Premium Quality</h4>
                    <p style="color: var(--muted); margin-bottom: 0;">Curated products for the modern lifestyle</p>
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
                <span class="sec-eyebrow">Our Story</span>
                <h2 class="sec-h2">Mission<br>& Vision</h2>
                <p class="sec-sub mb-4">
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
            <div class="col-md-6 col-lg-3">
                <div class="mission-card">
                    <div class="mission-icon" style="background: var(--ember-glow); color: var(--ember);">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h5 class="mb-3">Customer First</h5>
                    <p class="text-muted mb-0">
                        Every decision we make is guided by our commitment to our customers.
                        From product selection to delivery, your satisfaction is our top priority.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
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
            <div class="col-md-6 col-lg-3">
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
            <div class="col-md-6 col-lg-3">
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

<!-- ════ VALUES ════════════════════════════════════════════════ -->
<section class="values-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <span class="sec-eyebrow">Our Principles</span>
                <h2 class="sec-h2">Core<br>Values</h2>
                <p class="sec-sub">
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

<!-- ════ TEAM ══════════════════════════════════════════════════ -->
<section class="team-section">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <span class="sec-eyebrow">Our People</span>
                <h2 class="sec-h2">Meet Our<br>Team</h2>
                <p class="sec-sub">
                    The dedicated professionals behind Easy Shopping A.R.S
                </p>
            </div>
        </div>

        <?php
        // Fetch team members from database
        try {
            $stmt = $pdo->prepare("
                SELECT name, role, position, profile_image, fb_link, bio
                FROM team_members
                WHERE is_active = 1
                ORDER BY display_order ASC, created_at ASC
            ");
            $stmt->execute();
            $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fallback to default image if not set
            foreach ($team_members as &$member) {
                if (empty($member['profile_image'])) {
                    $member['profile_image'] = '/public/assets/img/default-avatar.png';
                }
            }
        } catch (Exception $e) {
            // Fallback data if database is not available
            $team_members = [
                [
                    'name' => 'Aaditya Kumar Kushwaha (A.R.K)',
                    'position' => 'Founder & CEO',
                    'role' => 'admin',
                    'profile_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face',
                    'fb_link' => 'https://www.facebook.com/share/1E6Gn8hf6Z/',
                    'bio' => 'Experienced entrepreneur leading Easy Shopping A.R.S with passion for quality products and customer satisfaction.'
                ],
                [
                    'name' => 'Devbarat Prasad Patel',
                    'position' => 'Operations Manager',
                    'role' => 'admin',
                    'profile_image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop&crop=face',
                    'fb_link' => 'https://facebook.com/devbarat',
                    'bio' => 'Dedicated operations manager ensuring smooth logistics and customer satisfaction across Nepal.'
                ],
                [
                    'name' => 'Roshan Kushwaha',
                    'position' => 'Customer Support Lead',
                    'role' => 'support',
                    'profile_image' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=300&fit=crop&crop=face',
                    'fb_link' => 'https://www.facebook.com/share/1PZkU2JsD5/',
                    'bio' => 'Leading our customer support team, ensuring every customer query is resolved with care and efficiency.'
                ],
                [
                    'name' => 'Sushil Shah',
                    'position' => 'Technical Support Specialist',
                    'role' => 'support',
                    'profile_image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=300&fit=crop&crop=face',
                    'fb_link' => 'https://www.facebook.com/share/1LPtsG1odR/',
                    'bio' => 'Expert in troubleshooting technical issues and providing IT support for our online platform.'
                ],
                [
                    'name' => 'Mukesh Yadav',
                    'position' => 'Customer Service Representative',
                    'role' => 'support',
                    'profile_image' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=300&h=300&fit=crop&crop=face',
                    'fb_link' => 'https://www.facebook.com/share/18qtWAMbgf/',
                    'bio' => 'Committed to providing exceptional customer service and building long-lasting relationships with our valued customers.'
                ]
            ];
        }

        // Separate admin and support team members
        $admin_members = array_filter($team_members, function($member) { return $member['role'] === 'admin'; });
        $support_members = array_filter($team_members, function($member) { return $member['role'] === 'support'; });
        ?>

        <!-- Admin Team Section -->
        <div class="mb-5">
            <h3 class="text-center mb-4" style="color: var(--ember); font-weight: 600;">Management Team</h3>
            <div class="row g-4 justify-content-center">
                <?php foreach ($admin_members as $member): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="team-member">
                        <div class="team-photo" style="background-image: url('<?php echo h($member['profile_image']); ?>'); background-size: cover; background-position: center;">
                            <!-- Fallback icon in case image fails to load -->
                        </div>
                        <div class="p-4">
                            <h5 class="mb-1"><?php echo h($member['name']); ?></h5>
                            <p class="text-primary mb-2 fw-semibold"><?php echo h($member['position']); ?></p>
                            <p class="text-muted small mb-3"><?php echo h($member['bio']); ?></p>
                            <div class="social-links">
                                <a href="<?php echo h($member['fb_link']); ?>" target="_blank" class="text-muted me-3" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="mailto:easyshoppinga.r.s1@gmail.com" class="text-muted me-3" title="Email">
                                    <i class="bi bi-envelope"></i>
                                </a>
                                <a href="tel:+9779820210361" class="text-muted" title="Phone">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Support Team Section -->
        <div>
            <h3 class="text-center mb-4" style="color: var(--gold); font-weight: 600;">Support Team</h3>
            <div class="row g-4">
                <?php foreach ($support_members as $member): ?>
                <div class="col-md-4">
                    <div class="team-member">
                        <div class="team-photo" style="background-image: url('<?php echo h($member['profile_image']); ?>'); background-size: cover; background-position: center;">
                            <div style="width: 100%; height: 100%; background: rgba(255,193,7,0.1); display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-circle" style="font-size: 3rem; color: rgba(255,255,255,0.8);"></i>
                            </div>
                        </div>
                        <div class="p-4">
                            <h5 class="mb-1"><?php echo h($member['name']); ?></h5>
                            <p class="text-warning mb-2 fw-semibold"><?php echo h($member['position']); ?></p>
                            <p class="text-muted small mb-3"><?php echo h($member['bio']); ?></p>
                            <div class="social-links">
                                <a href="<?php echo h($member['fb_link']); ?>" target="_blank" class="text-muted me-3" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="mailto:easyshoppinga.r.s1@gmail.com" class="text-muted me-3" title="Email">
                                    <i class="bi bi-envelope"></i>
                                </a>
                                <a href="tel:+9779820210361" class="text-muted" title="Phone">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ════ CTA ════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <h2 class="sec-h2 mb-4">Ready to Start<br>Shopping?</h2>
            <p class="sec-sub mb-4">
                Join thousands of satisfied customers who trust Easy Shopping A.R.S
                for their online shopping needs.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo url('/shop'); ?>" class="btn-void">
                    Browse Products &nbsp; <i class="bi bi-arrow-right"></i>
                </a>
                <a href="<?php echo url('/auth/signup'); ?>" class="btn-outline">
                    Create Account &nbsp; <i class="bi bi-arrow-right"></i>
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