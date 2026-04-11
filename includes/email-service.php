<?php
/**
 * Email Service Class
 * Easy Shopping A.R.S
 * 
 * Dynamically uses templates from config/email_templates.php
 */

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService {

    private static $instance = null;
    private $templates = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $templatePath = __DIR__ . '/../config/email_templates.php';
        if (file_exists($templatePath)) {
            $this->templates = include $templatePath;
        }
    }

    /**
     * Send OTP email
     */
    public function sendOTP($email, $otp, $name = 'Valued Customer') {
        return $this->processAndSend('otp_email', $email, [
            'otp' => $otp,
            'name' => $name
        ]);
    }

    /**
     * Send SMS OTP
     */
    public function sendSMSOTP($mobile, $otp) {
        $message = "Your ARS verification code is: $otp. Valid for 5 minutes.";
        
        // In dev, log to emails.log
        $log = "[".date('Y-m-d H:i:s')."] SMS to: $mobile\n";
        $log .= "Message: $message\n";
        $log .= str_repeat("-", 50) . "\n\n";
        error_log($log, 3, __DIR__ . '/../logs/emails.log');
        
        // For production, integrate with SMS gateway like Twilio, Sparrow SMS, etc.
        return true; 
    }

    /**
     * Send Welcome email
     */
    public function sendWelcome($email, $name) {
        return $this->processAndSend('welcome_email', $email, [
            'name' => $name,
            'shop_url' => url('/shop')
        ]);
    }

    /**
     * Send Password Reset email
     */
    public function sendPasswordReset($email, $name, $reset_url) {
        return $this->processAndSend('password_reset', $email, [
            'name' => $name,
            'reset_url' => $reset_url
        ]);
    }

    /**
     * Send Order Confirmation email
     */
    public function sendOrderConfirmation($email, $name, $order_id, $total, $invoice_url = '') {
        return $this->processAndSend('order_confirmation', $email, [
            'name' => $name,
            'order_id' => $order_id,
            'total' => format_price($total),
            'invoice_url' => $invoice_url
        ]);
    }

    /**
     * Send Custom/Public Email
     */
    public function sendCustomEmail($to, $subject, $body) {
        return $this->sendEmail($to, $subject, $body);
    }

    /**
     * Core logic to fetch, parse and send a template
     */
    private function processAndSend($templateKey, $to, $replacements) {
        if (!$this->templates || !isset($this->templates[$templateKey])) {
            return false;
        }

        $template = $this->templates[$templateKey];
        $subject = $template['subject'];
        $body = $template['content_html'];

        // Replace placeholders {{key}} with value
        foreach ($replacements as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $this->sendEmail($to, $subject, $body);
    }

    /**
     * Native PHPMailer logic
     */
    private function sendEmail($to, $subject, $body) {
        $smtp_host = get_setting('smtp_host');
        $smtp_port = get_setting('smtp_port', 587);
        $smtp_user = get_setting('smtp_username');
        $smtp_pass = get_setting('smtp_password');
        $smtp_enc  = get_setting('smtp_encryption', 'tls');
        
        $from_email = get_setting('support_email', 'noreply@easyshoppingars.com');
        $site_name  = get_setting('site_name', 'Easy Shopping A.R.S');

        if (empty($smtp_host)) {
            // Dev logging
            $log = "[".date('Y-m-d H:i:s')."] Email to: $to\n";
            $log .= "Subject: $subject\n";
            $log .= "Body: $body\n";
            $log .= str_repeat("-", 50) . "\n\n";
            error_log($log, 3, __DIR__ . '/../logs/emails.log');
            return true;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $smtp_pass;
            $mail->SMTPSecure = ($smtp_enc === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtp_port;

            $mail->setFrom($from_email, $site_name);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}

function getEmailService() {
    return EmailService::getInstance();
}