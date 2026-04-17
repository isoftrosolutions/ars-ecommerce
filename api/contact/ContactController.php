<?php
/**
 * Contact Controller
 * Handles contact form submissions management
 */
class ContactController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'list':
                        return $this->getSubmissions();
                    case 'detail':
                        return $this->getSubmission();
                    case 'stats':
                        return $this->getStats();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'update-status':
                        return $this->updateStatus();
                    case 'bulk-update-status':
                        return $this->bulkUpdateStatus();
                    case 'send-reply':
                        return $this->sendReply();
                    case 'delete':
                        return $this->deleteSubmission();
                    case 'bulk-delete':
                        return $this->bulkDelete();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get paginated contact submissions
     */
    private function getSubmissions() {
        $params = $this->getQueryParams();
        $pagination = $this->validatePagination($params);

        $where = [];
        $queryParams = [];

        // Search filter
        if (!empty($params['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
            $searchTerm = '%' . $params['search'] . '%';
            $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        // Status filter
        if (!empty($params['status'])) {
            $where[] = "status = ?";
            $queryParams[] = $params['status'];
        }

        $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        // Get total count
        $countStmt = $this->executeQuery(
            "SELECT COUNT(*) FROM contact_submissions $whereClause",
            $queryParams
        );
        $total = (int)$countStmt->fetchColumn();

        // Get submissions
        $offset = ($pagination['page'] - 1) * $pagination['limit'];
        $stmt = $this->executeQuery("
            SELECT * FROM contact_submissions
            $whereClause
            ORDER BY
                CASE status
                    WHEN 'new' THEN 1
                    WHEN 'read' THEN 2
                    WHEN 'replied' THEN 3
                END,
                created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($queryParams, [$pagination['limit'], $offset]));

        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $paginationInfo = $this->buildPagination($total, $pagination['page'], $pagination['limit']);

        Response::paginated($submissions, $paginationInfo, 'Contact submissions retrieved successfully');
    }

    /**
     * Get single submission details
     */
    private function getSubmission() {
        $params = $this->getQueryParams();
        ValidationMiddleware::validateRequired($params, ['id']);

        $stmt = $this->executeQuery("SELECT * FROM contact_submissions WHERE id = ?", [$params['id']]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) {
            Response::error('Submission not found', 404);
        }

        // Mark as read if it's new
        if ($submission['status'] === 'new') {
            $this->executeQuery(
                "UPDATE contact_submissions SET status = 'read' WHERE id = ?",
                [$submission['id']]
            );
            $submission['status'] = 'read';
        }

        Response::success($submission, 'Submission retrieved successfully');
    }

    /**
     * Get contact statistics
     */
    private function getStats() {
        $stats = [];

        // Total submissions
        $stmt = $this->executeQuery("SELECT COUNT(*) FROM contact_submissions");
        $stats['total_submissions'] = (int)$stmt->fetchColumn();

        // Status counts
        $stmt = $this->executeQuery("
            SELECT status, COUNT(*) as count
            FROM contact_submissions
            GROUP BY status
        ");
        $stats['status_counts'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'status');

        Response::success($stats, 'Contact statistics retrieved successfully');
    }

    /**
     * Update submission status
     */
    private function updateStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id', 'status']);
        ValidationMiddleware::validateEnum($data['status'], ['new', 'read', 'replied']);

        $stmt = $this->executeQuery(
            "UPDATE contact_submissions SET status = ? WHERE id = ?",
            [$data['status'], $data['id']]
        );

        if ($stmt->rowCount() === 0) {
            Response::error('Submission not found', 404);
        }

        $this->logAction('update_submission_status', [
            'submission_id' => $data['id'],
            'status' => $data['status']
        ]);

        Response::success(null, 'Submission status updated successfully');
    }

    /**
     * Bulk update submission status
     */
    private function bulkUpdateStatus() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['submission_ids', 'status']);
        ValidationMiddleware::validateEnum($data['status'], ['new', 'read', 'replied']);

        if (!is_array($data['submission_ids']) || empty($data['submission_ids'])) {
            Response::error('Invalid submission IDs', 400);
        }

        $placeholders = str_repeat('?,', count($data['submission_ids']) - 1) . '?';
        $params = array_merge($data['submission_ids'], [$data['status']]);

        $stmt = $this->executeQuery(
            "UPDATE contact_submissions SET status = ? WHERE id IN ($placeholders)",
            array_reverse($params) // status first, then IDs
        );

        $updated = $stmt->rowCount();

        $this->logAction('bulk_update_submission_status', [
            'count' => $updated,
            'status' => $data['status']
        ]);

        Response::success(['updated' => $updated], 'Submissions updated successfully');
    }

    /**
     * Send reply to customer
     */
    private function sendReply() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['submission_id', 'reply_message']);
        ValidationMiddleware::throwIfInvalid();

        // Get submission details
        $stmt = $this->executeQuery(
            "SELECT * FROM contact_submissions WHERE id = ?",
            [$data['submission_id']]
        );
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$submission) {
            Response::error('Submission not found', 404);
        }

        $this->beginTransaction();

        try {
            // Update submission with admin reply
            $this->executeQuery(
                "UPDATE contact_submissions SET
                    admin_reply = ?,
                    status = 'replied'
                 WHERE id = ?",
                [$data['reply_message'], $data['submission_id']]
            );

            // Send email reply (if SMTP is configured)
            $this->sendReplyEmail($submission, $data['reply_message']);

            $this->commit();

            $this->logAction('send_reply', ['submission_id' => $data['submission_id']]);

            Response::success(null, 'Reply sent successfully');

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Delete submission
     */
    private function deleteSubmission() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['id']);

        $stmt = $this->executeQuery("DELETE FROM contact_submissions WHERE id = ?", [$data['id']]);

        if ($stmt->rowCount() === 0) {
            Response::error('Submission not found', 404);
        }

        $this->logAction('delete_submission', ['submission_id' => $data['id']]);

        Response::success(null, 'Submission deleted successfully');
    }

    /**
     * Bulk delete submissions
     */
    private function bulkDelete() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['submission_ids']);

        if (!is_array($data['submission_ids']) || empty($data['submission_ids'])) {
            Response::error('Invalid submission IDs', 400);
        }

        $placeholders = str_repeat('?,', count($data['submission_ids']) - 1) . '?';

        $stmt = $this->executeQuery(
            "DELETE FROM contact_submissions WHERE id IN ($placeholders)",
            $data['submission_ids']
        );

        $deleted = $stmt->rowCount();

        $this->logAction('bulk_delete_submissions', ['count' => $deleted]);

        Response::success(['deleted' => $deleted], 'Submissions deleted successfully');
    }

    /**
     * Send reply email to customer
     */
    private function sendReplyEmail($submission, $replyMessage) {
        // Get email settings
        $stmt = $this->executeQuery("SELECT `key`, `value` FROM site_settings WHERE `key` IN ('smtp_host', 'smtp_username', 'smtp_password', 'smtp_encryption', 'smtp_port', 'admin_email')");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // If SMTP not configured, just log the reply
        if (empty($settings['smtp_host'])) {
            $this->logger->info('Reply email not sent - SMTP not configured', [
                'submission_id' => $submission['id'],
                'customer_email' => $submission['email']
            ]);
            return;
        }

        // This would integrate with your email service
        // For now, just log that we would send the email
        $this->logger->info('Reply email would be sent', [
            'to' => $submission['email'],
            'subject' => 'Re: ' . $submission['subject'],
            'submission_id' => $submission['id']
        ]);
    }
}
?>