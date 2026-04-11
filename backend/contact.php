<?php
/**
 * Contact Submissions Management Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

protect_admin_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!validate_csrf_token()) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit();
    }

    try {
        switch ($_POST['action']) {
            case 'get_submissions':
                $page   = max(1, (int)($_POST['page']  ?? 1));
                $limit  = max(1, (int)($_POST['limit'] ?? 10));
                $status = trim($_POST['status'] ?? ''); // Raw — PDO handles it
                $search = trim($_POST['search'] ?? '');
                $offset = ($page - 1) * $limit;

                $where  = [];
                $params = [];

                if ($status !== '') {
                    $where[]  = "status = ?";
                    $params[] = $status;
                }
                if ($search !== '') {
                    $where[]  = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }

                $wc = $where ? "WHERE " . implode(" AND ", $where) : "";

                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contact_submissions $wc");
                $stmt->execute($params);
                $total = (int)$stmt->fetch()['total'];

                $params[] = $limit;
                $params[] = $offset;
                $stmt = $pdo->prepare("SELECT * FROM contact_submissions $wc ORDER BY created_at DESC LIMIT ? OFFSET ?");
                $stmt->execute($params);

                echo json_encode([
                    'success'    => true,
                    'data'       => $stmt->fetchAll(),
                    'pagination' => ['page' => $page, 'limit' => $limit, 'total' => $total, 'pages' => (int)ceil($total / $limit)]
                ]);
                break;

            case 'get_submission':
                $id   = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE id = ?");
                $stmt->execute([$id]);
                $sub  = $stmt->fetch();

                if ($sub) {
                    // Auto-mark as 'read' when admin opens it (if currently 'new')
                    if ($sub['status'] === 'new') {
                        $pdo->prepare("UPDATE contact_submissions SET status = 'read' WHERE id = ?")->execute([$id]);
                        $sub['status'] = 'read';
                    }
                    echo json_encode(['success' => true, 'data' => $sub]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Submission not found']);
                }
                break;

            case 'update_status':
                $id     = (int)$_POST['id'];
                $status = trim($_POST['status'] ?? '');
                if (!in_array($status, ['new', 'read', 'replied'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                $pdo->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?")->execute([$status, $id]);
                echo json_encode(['success' => true]);
                break;

            case 'send_reply':
                $submission_id = (int)$_POST['submission_id'];
                $reply_message = trim($_POST['reply_message'] ?? '');

                if ($reply_message === '') {
                    echo json_encode(['success' => false, 'message' => 'Reply message cannot be empty.']);
                    exit();
                }

                $stmt = $pdo->prepare("SELECT * FROM contact_submissions WHERE id = ?");
                $stmt->execute([$submission_id]);
                $submission = $stmt->fetch();

                if (!$submission) {
                    echo json_encode(['success' => false, 'message' => 'Submission not found']);
                    exit();
                }

                // Store the admin reply and mark as replied
                $pdo->prepare("
                    UPDATE contact_submissions SET status = 'replied', admin_reply = ?
                    WHERE id = ?
                ")->execute([$reply_message, $submission_id]);

                // Send actual email via SMTP
                require_once '../includes/email-service.php';
                $emailService = getEmailService();
                $sent = $emailService->sendCustomEmail(
                    $submission['email'], 
                    'Re: ' . $submission['subject'], 
                    nl2br(h($reply_message))
                );

                echo json_encode([
                    'success' => true,
                    'message' => $sent ? 'Reply sent successfully via email.' : 'Reply saved, but email sending failed. Check SMTP settings.'
                ]);
                break;

            case 'delete_submission':
                $id = (int)$_POST['id'];
                $pdo->prepare("DELETE FROM contact_submissions WHERE id = ?")->execute([$id]);
                echo json_encode(['success' => true]);
                break;

            case 'bulk_update_status':
                $submission_ids = array_map('intval', (array)($_POST['submission_ids'] ?? []));
                $status         = trim($_POST['status'] ?? '');

                if (!in_array($status, ['new', 'read', 'replied'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit();
                }
                if (empty($submission_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No submissions selected']);
                    exit();
                }

                $ph = implode(',', array_fill(0, count($submission_ids), '?'));
                $pdo->prepare("UPDATE contact_submissions SET status = ? WHERE id IN ($ph)")
                    ->execute(array_merge([$status], $submission_ids));

                echo json_encode(['success' => true, 'updated' => count($submission_ids)]);
                break;

            case 'bulk_delete':
                $submission_ids = array_map('intval', (array)($_POST['submission_ids'] ?? []));
                if (empty($submission_ids)) {
                    echo json_encode(['success' => false, 'message' => 'No submissions selected']);
                    exit();
                }
                $ph = implode(',', array_fill(0, count($submission_ids), '?'));
                $pdo->prepare("DELETE FROM contact_submissions WHERE id IN ($ph)")->execute($submission_ids);
                echo json_encode(['success' => true, 'deleted' => count($submission_ids)]);
                break;

            case 'get_stats':
                $stats = [];
                $stmt  = $pdo->query("SELECT COUNT(*) as total FROM contact_submissions");
                $stats['total_submissions'] = $stmt->fetch()['total'];

                $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM contact_submissions GROUP BY status");
                $stats['status_counts'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_submissions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
                $stmt->execute();
                $stats['recent_submissions'] = $stmt->fetch()['count'];

                echo json_encode(['success' => true, 'data' => $stats]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log('[ARS] backend/contact.php: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit();
}

header('Location: ../admin/contact.php');
exit();
?>