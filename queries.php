<?php
/**
 * My Queries Page - Customer Submissions & Admin Responses
 * Easy Shopping A.R.S
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Protect this page - require login
if (!is_logged_in()) {
    header('Location: ' . url('/auth/login.php'));
    exit;
}

$user_id = $_SESSION['user']['id'];
$page_title = 'My Queries | Easy Shopping A.R.S Nepal';
include 'includes/header-bootstrap.php';

// Handle new query submission
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_query'])) {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($subject) || empty($message)) {
        $error_msg = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_submissions (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
            $stmt->execute([
                $user_id,
                $_SESSION['user']['full_name'],
                $_SESSION['user']['email'],
                $subject,
                $message
            ]);
            $success_msg = 'Query submitted successfully! We will respond soon.';
        } catch (PDOException $e) {
            $error_msg = 'Failed to submit query. Please try again.';
        }
    }
}

// Check and add user_id column if needed
try {
    $stmt = $pdo->query("DESCRIBE contact_submissions");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('user_id', $columns)) {
        $pdo->exec("ALTER TABLE contact_submissions ADD COLUMN user_id INT NULL");
    }
} catch (PDOException $e) {
    // Table might not exist yet
}

// Fetch user's queries
try {
    $stmt = $pdo->prepare("
        SELECT * FROM contact_submissions 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $queries = $stmt->fetchAll();
} catch (PDOException $e) {
    $queries = [];
}
?>

<style>
.query-card {
    border-left: 4px solid #dee2e6;
}
.query-card.status-new { border-left-color: #0d6efd; }
.query-card.status-read { border-left-color: #ffc107; }
.query-card.status-replied { border-left-color: #198754; }
.reply-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
}
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Submit New Query</h5>
                </div>
                <div class="card-body">
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success"><?php echo h($success_msg); ?></div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger"><?php echo h($error_msg); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g., Order status inquiry" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Describe your query in detail..." required></textarea>
                        </div>
                        <button type="submit" name="submit_query" class="btn btn-primary w-100">
                            <i class="bi bi-send me-2"></i>Submit Query
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <h4 class="mb-4">My Queries</h4>
            
            <?php if (empty($queries)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">No queries yet</h5>
                    <p class="text-muted">Submit a query and we'll respond within 24 hours.</p>
                </div>
            <?php else: ?>
                <div class="accordion" id="queriesAccordion">
                    <?php foreach ($queries as $index => $query): ?>
                        <div class="card border-0 shadow-sm mb-3 query-card status-<?php echo $query['status']; ?>">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <button class="btn btn-link text-decoration-none text-start w-100" type="button" data-bs-toggle="collapse" data-bs-target="#query_<?php echo $query['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo h($query['subject']); ?></strong>
                                            <span class="badge bg-<?php 
                                                echo match($query['status']) {
                                                    'new' => 'primary',
                                                    'read' => 'warning',
                                                    'replied' => 'success',
                                                    default => 'secondary'
                                                }; 
                                            ?> ms-2"><?php echo ucfirst($query['status']); ?></span>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($query['created_at'])); ?></small>
                                    </div>
                                </button>
                            </div>
                            <div id="query_<?php echo $query['id']; ?>" class="collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#queriesAccordion">
                                <div class="card-body">
                                    <p><?php echo nl2br(h($query['message'])); ?></p>
                                    
                                    <?php if (!empty($query['admin_reply'])): ?>
                                        <div class="reply-box">
                                            <h6 class="fw-bold text-success">
                                                <i class="bi bi-reply me-1"></i>Admin Response
                                            </h6>
                                            <p class="mb-0"><?php echo nl2br(h($query['admin_reply'])); ?></p>
                                            <small class="text-muted mt-2 d-block">
                                                Replied on: <?php echo date('M d, Y h:i A', strtotime($query['updated_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>