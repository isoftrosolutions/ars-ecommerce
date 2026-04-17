<?php
/**
 * Upload Controller
 * Handles file uploads (product images, QR codes, payment proofs)
 */
class UploadController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();
        
        switch ($method) {
            case 'POST':
                switch ($action) {
                    case 'qr-code':
                        return $this->uploadQRCode();
                    case 'product-image':
                        return $this->uploadProductImage();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;
            
            default:
                Response::error('Method not allowed', 405);
        }
    }
    
    /**
     * Upload QR code for eSewa payments
     */
    private function uploadQRCode() {
        if (!isset($_FILES['qr_code']) || $_FILES['qr_code']['error'] !== UPLOAD_ERR_OK) {
            Response::error('No file uploaded', 400);
        }
        
        $file = $_FILES['qr_code'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            Response::error('Invalid file type. Only JPG, PNG, GIF, WebP allowed.', 400);
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            Response::error('File size exceeds 5MB limit.', 400);
        }
        
        $uploadDir = __DIR__ . '/../../uploads/qr/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Remove old QR code if exists
        $old_files = glob($uploadDir . 'seller_qr.*');
        if ($old_files) {
            @unlink($old_files[0]);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'seller_qr.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            chmod($filepath, 0644);
            
            // Save to settings
            $qr_path = '/uploads/qr/' . $filename;
            $stmt = $this->executeQuery(
                "INSERT INTO site_settings (`key`, `value`) VALUES ('qr_code_path', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                [$qr_path]
            );
            
            $this->logAction('upload_qr_code', ['path' => $qr_path]);
            Response::success(['path' => $qr_path], 'QR code uploaded successfully');
        } else {
            Response::error('Failed to upload file', 500);
        }
    }
    
    /**
     * Upload product image
     */
    private function uploadProductImage() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Response::error('No file uploaded', 400);
        }
        
        $file = $_FILES['image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            Response::error('Invalid file type', 400);
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            Response::error('File size exceeds 10MB', 400);
        }
        
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid('prod_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            chmod($filepath, 0644);
            $this->logAction('upload_product_image', ['filename' => $filename]);
            Response::success([
                'filename' => $filename,
                'url' => url('/uploads/products/' . $filename)
            ], 'Image uploaded successfully');
        } else {
            Response::error('Failed to upload image', 500);
        }
    }
}
?>