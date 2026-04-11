<?php
/**
 * Contact Us Page
 * Easy Shopping A.R.S
 */
$page_title     = 'Contact Us | Easy Shopping A.R.S Nepal';
$page_meta_desc = 'Get in touch with Easy Shopping A.R.S. We are here to help with orders, returns, and any queries. Reach us by email or visit our store in Nepal.';
include 'includes/header-bootstrap.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = h($_POST['name']);
    $email = h($_POST['email']);
    $subject = h($_POST['subject']);
    $message = h($_POST['message']);

    try {
        $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success_msg = "Thank you for contacting us! We'll get back to you soon.";
    } catch (PDOException $e) {
        $error_msg = "Something went wrong. Please try again later.";
    }
}
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h2 class="fw-bold">Contact Us</h2>
            <p class="text-muted">We're here to help you</p>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100">
                <h4 class="fw-bold mb-4">Get in Touch</h4>
                
                <div class="d-flex mb-4">
                    <div class="icon-box me-3">
                        <i class="bi bi-geo-alt-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="text-muted mb-0">Birgunj, Parsa, Nepal</p>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="icon-box me-3">
                        <i class="bi bi-telephone-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Phone</h6>
                        <p class="text-muted mb-0">+977 9820210361</p>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="icon-box me-3">
                        <i class="bi bi-envelope-fill fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="text-muted mb-0">support@easyshoppingars.com</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Follow Us</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-primary btn-sm"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-danger btn-sm"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-info btn-sm"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold mb-4">Send a Message</h4>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Subject</label>
                            <input type="text" name="subject" class="form-control rounded-3" placeholder="Inquiry about Product" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Message</label>
                            <textarea name="message" class="form-control rounded-3" rows="5" placeholder="How can we help you?" required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark w-100 rounded-pill py-2">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 45px;
    height: 45px;
    background: rgba(234, 108, 0, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.text-primary { color: #ea6c00 !important; }
.btn-outline-primary { border-color: #ea6c00; color: #ea6c00; }
.btn-outline-primary:hover { background-color: #ea6c00; border-color: #ea6c00; }
</style>

<?php include 'includes/footer-bootstrap.php'; ?>
