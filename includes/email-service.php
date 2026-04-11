<?php
/**
 * Email Service Class
 * Easy Shopping A.R.S
 *
 * Integrated with PHPMailer and Database Settings.
 */

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService {

    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send a custom email (public wrapper)
     */
    public function sendCustomEmail($to, $subject, $body) {
        return $this->sendEmail($to, $subject, $body);
    }

    /**
     * Send OTP email
     */
    public function sendOTP($email, $otp) {
        $subject = "Your OTP for ARS Login";
        $body = $this->getOTPEmailTemplate($otp);

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($email, $reset_link) {
        $subject = "Reset Your ARS Password";
        $body = $this->getPasswordResetEmailTemplate($reset_link);

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Send welcome email
     */
    public function sendWelcome($email, $name) {
        $subject = "Welcome to ARS!";
        $body = $this->getWelcomeEmailTemplate($name);

        return $this->sendEmail($email, $subject, $body);
    }

    /**
     * Send email using PHPMailer and Database SMTP settings
     */
    private function sendEmail($to, $subject, $body) {
        // Fetch SMTP settings from database
        // These are added to includes/functions.php
        $smtp_host = get_setting('smtp_host');
        $smtp_port = get_setting('smtp_port', 587);
        $smtp_user = get_setting('smtp_username');
        $smtp_pass = get_setting('smtp_password');
        $smtp_enc  = get_setting('smtp_encryption', 'tls');
        
        $from_email = get_setting('support_email', get_setting('admin_email', 'noreply@easyshoppingars.com'));
        $site_name  = get_setting('site_name', 'Easy Shopping A.R.S');

        // If no SMTP host is configured, fallback to logging for development
        if (empty($smtp_host)) {
            $log_message = sprintf(
                "[%s] [DEV-LOG] Email to: %s\nSubject: %s\nBody: %s\nNote: SMTP not configured. Set 'smtp_host' in Settings.\n\n",
                date('Y-m-d H:i:s'),
                $to,
                $subject,
                $body
            );
            error_log($log_message, 3, __DIR__ . '/../logs/emails.log');
            return true; 
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = ($smtp_enc === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp_port;

            // Recipients
            $mail->setFrom($from_email, $site_name);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Mailer Error: {$mail->ErrorInfo}\n", 3, __DIR__ . '/../logs/emails.log');
            return false;
        }
    }

    /**
     * Send SMS OTP (for mobile OTP)
     */
    public function sendSMSOTP($mobile, $otp) {
        // In production, integrate with SMS service like Twilio, Nexmo, etc.

        $log_message = sprintf(
            "[%s] SMS to: %s\nOTP: %s\n\n",
            date('Y-m-d H:i:s'),
            $mobile,
            $otp
        );

        error_log($log_message, 3, __DIR__ . '/../logs/sms.log');

        // Simulate sending delay
        usleep(300000); // 0.3 seconds

        return true; // Return success for demo
    }

    private function getOTPEmailTemplate($otp) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #ea6c00;'>Your OTP for ARS Login</h2>
            <p>Hello,</p>
            <p>Your One-Time Password (OTP) for logging into your ARS account is:</p>
            <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0;'>
                <span style='font-size: 24px; font-weight: bold; color: #ea6c00;'>{$otp}</span>
            </div>
            <p>This OTP will expire in 5 minutes.</p>
            <p>If you didn't request this OTP, please ignore this email.</p>
            <br>
            <p>Best regards,<br>ARS Team</p>
        </div>
        ";
    }

    private function getPasswordResetEmailTemplate($reset_link) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #ea6c00;'>Reset Your Password</h2>
            <p>Hello,</p>
            <p>You have requested to reset your password for your ARS account.</p>
            <p>Click the link below to reset your password:</p>
            <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0;'>
                <a href='{$reset_link}' style='background: #ea6c00; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
            </div>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this password reset, please ignore this email.</p>
            <br>
            <p>Best regards,<br>ARS Team</p>
        </div>
        ";
    }

    private function getWelcomeEmailTemplate($name) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #ea6c00;'>Welcome to ARS!</h2>
            <p>Hello {$name},</p>
            <p>Thank you for joining ARS! Your account has been successfully created.</p>
            <p>You can now:</p>
            <ul>
                <li>Browse our products</li>
                <li>Place orders online</li>
                <li>Track your order status</li>
                <li>Manage your wishlist</li>
            </ul>
            <p>Start shopping now: <a href='" . url('/') . "'>" . url('/') . "</a></p>
            <br>
            <p>Best regards,<br>ARS Team</p>
        </div>
        ";
    }
}

// Helper function to get email service instance
function getEmailService() {
    return EmailService::getInstance();
}
?>