<?php
/**
 * Audit Logs Page
 * Easy Shopping A.R.S Admin
 */
$page_title = "Audit Logs";
require_once __DIR__ . '/../includes/audit-logger.php';
include __DIR__ . '/includes/header.php';

$limit = 100;
$logs = AuditLogger::getRecent($limit);
?>

<div class="page-header">
    <h1>System Audit Logs</h1>
    <div class="header-actions">
        <span class="badge badge-info"><?php echo count($logs); ?> Total Entries</span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fa-solid fa-list-check" style="font-size:3rem; opacity:0.1; display:block; margin-bottom:1rem;"></i>
                            No log entries found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $badgeClass = 'badge-secondary';
                        if (strpos($log['action'], 'create') !== false) $badgeClass = 'badge-success';
                        if (strpos($log['action'], 'delete') !== false) $badgeClass = 'badge-danger';
                        if (strpos($log['action'], 'login') !== false) $badgeClass = 'badge-info';
                    ?>
                        <tr>
                            <td class="text-nowrap" style="font-size:0.85rem; color:var(--text-secondary);">
                                <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?>
                            </td>
                            <td>
                                <strong><?php echo h($log['user_name']); ?></strong>
                                <div style="font-size:0.75rem; color:var(--text-secondary);">ID: <?php echo $log['user_id'] ?: 'Guest'; ?></div>
                            </td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo h($log['action']); ?></span>
                            </td>
                            <td>
                                <div style="font-size:0.9rem;"><?php echo h($log['description']); ?></div>
                                <?php if ($log['entity_type']): ?>
                                    <div style="font-size:0.75rem; color:var(--text-secondary);"><?php echo h($log['entity_type']); ?> #<?php echo $log['entity_id']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.85rem; color:var(--text-secondary);">
                                <?php echo h($log['ip_address']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.badge-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
.badge-secondary { background: rgba(100, 116, 139, 0.1); color: #64748b; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
