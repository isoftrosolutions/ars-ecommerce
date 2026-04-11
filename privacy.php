<?php
/**
 * Privacy Policy Page
 * Easy Shopping A.R.S
 */
$page_title = "Privacy Policy - Easy Shopping A.R.S";
$page_meta_desc = "Read our privacy policy to understand how Easy Shopping A.R.S collects, uses, and protects your personal information.";
include 'includes/header-bootstrap.php';
?>

<style>
/* Privacy Policy Styles */
.privacy-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
}

.privacy-content {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    padding: 40px;
    margin: -30px auto 0;
    position: relative;
    max-width: 900px;
}

.privacy-section {
    margin-bottom: 30px;
}

.privacy-section h3 {
    color: #0f172a;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.privacy-section h4 {
    color: #374151;
    font-weight: 500;
    margin: 20px 0 10px;
    font-size: 1.1rem;
}

.privacy-section p {
    line-height: 1.7;
    color: #6b7280;
    margin-bottom: 15px;
}

.privacy-section ul {
    padding-left: 20px;
    margin-bottom: 15px;
}

.privacy-section li {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 5px;
}

.highlight-box {
    background: #f8fafc;
    border-left: 4px solid var(--ember);
    padding: 20px;
    margin: 20px 0;
    border-radius: 0 8px 8px 0;
}

.contact-info {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
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
    .privacy-content {
        margin: -20px 15px 0;
        padding: 30px 20px;
    }
}
</style>

<!-- Hero Section -->
<section class="privacy-hero">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold mb-3">Privacy Policy</h1>
                <p class="lead mb-0">
                    Your privacy is important to us. Learn how we collect, use, and protect your information.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="py-5">
    <div class="container">
        <div class="privacy-content">
            <div class="privacy-section">
                <h3>1. Introduction</h3>
                <p>
                    Welcome to Easy Shopping A.R.S ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website and services.
                </p>
                <p>
                    By using our website, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our website.
                </p>
            </div>

            <div class="privacy-section">
                <h3>2. Information We Collect</h3>

                <h4>Personal Information</h4>
                <p>We may collect the following personal information:</p>
                <ul>
                    <li>Name and contact information (phone number, email address)</li>
                    <li>Billing and shipping addresses</li>
                    <li>Payment information (processed securely by third-party providers)</li>
                    <li>Account credentials (username, password)</li>
                    <li>Order history and preferences</li>
                </ul>

                <h4>Automatically Collected Information</h4>
                <p>We automatically collect certain information when you visit our website:</p>
                <ul>
                    <li>IP address and location information</li>
                    <li>Browser type and version</li>
                    <li>Device information</li>
                    <li>Pages visited and time spent on our site</li>
                    <li>Referral sources</li>
                </ul>

                <h4>Cookies and Tracking Technologies</h4>
                <p>
                    We use cookies and similar tracking technologies to enhance your browsing experience,
                    analyze site traffic, and personalize content. You can control cookie settings through your browser.
                </p>
            </div>

            <div class="privacy-section">
                <h3>3. How We Use Your Information</h3>
                <p>We use the collected information for the following purposes:</p>
                <ul>
                    <li><strong>Order Processing:</strong> To process and fulfill your orders</li>
                    <li><strong>Account Management:</strong> To create and manage your account</li>
                    <li><strong>Customer Service:</strong> To respond to your inquiries and provide support</li>
                    <li><strong>Communication:</strong> To send order updates, promotional offers, and important notifications</li>
                    <li><strong>Website Improvement:</strong> To analyze usage patterns and improve our services</li>
                    <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h3>4. Information Sharing and Disclosure</h3>
                <p>
                    We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:
                </p>
                <ul>
                    <li><strong>Service Providers:</strong> With trusted third-party service providers who assist our operations (payment processors, shipping companies, etc.)</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our rights and safety</li>
                    <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h3>5. Data Security</h3>
                <p>
                    We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                </p>
                <ul>
                    <li>SSL/TLS encryption for data transmission</li>
                    <li>Secure data storage and access controls</li>
                    <li>Regular security audits and updates</li>
                    <li>Employee training on data protection</li>
                </ul>

                <div class="highlight-box">
                    <strong>Important:</strong> While we strive to protect your information, no method of transmission over the internet or electronic storage is 100% secure. We cannot guarantee absolute security.
                </div>
            </div>

            <div class="privacy-section">
                <h3>6. Your Rights</h3>
                <p>You have the following rights regarding your personal information:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal information</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                    <li><strong>Portability:</strong> Request transfer of your data to another service</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
                </ul>
                <p>
                    To exercise these rights, please contact us using the information provided below.
                </p>
            </div>

            <div class="privacy-section">
                <h3>7. Cookies Policy</h3>
                <p>
                    We use cookies to improve your browsing experience. Cookies are small text files stored on your device. You can control cookie settings through your browser preferences.
                </p>

                <h4>Types of Cookies We Use:</h4>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for website functionality</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how you use our site</li>
                    <li><strong>Marketing Cookies:</strong> Used to show relevant advertisements</li>
                    <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h3>8. Third-Party Links</h3>
                <p>
                    Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of these external sites. We encourage you to review the privacy policies of any third-party websites you visit.
                </p>
            </div>

            <div class="privacy-section">
                <h3>9. Children's Privacy</h3>
                <p>
                    Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information.
                </p>
            </div>

            <div class="privacy-section">
                <h3>10. Changes to This Privacy Policy</h3>
                <p>
                    We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. We encourage you to review this Privacy Policy periodically.
                </p>
            </div>

            <div class="contact-info">
                <h4>Contact Us</h4>
                <p>
                    If you have any questions about this Privacy Policy or our data practices, please contact us:
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