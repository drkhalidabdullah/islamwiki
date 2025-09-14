<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/moderation_functions.php';
require_once '../../includes/rate_limiter.php';

$page_title = 'Content Moderation';

// Check admin permissions
if (!is_admin()) {
    redirect('/dashboard');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_report_status':
            $report_id = (int)$_POST['report_id'];
            $status = $_POST['status'];
            $resolution_notes = $_POST['resolution_notes'] ?? '';
            
            if (update_report_status($report_id, $status, $_SESSION['user_id'], $resolution_notes)) {
                show_message('Report status updated successfully.', 'success');
            } else {
                show_message('Failed to update report status.', 'error');
            }
            break;
            
        case 'flag_content':
            $content_type = $_POST['content_type'];
            $content_id = (int)$_POST['content_id'];
            $reason = $_POST['flag_reason'];
            
            if (flag_content($content_type, $content_id, $reason)) {
                show_message('Content flagged successfully.', 'success');
            } else {
                show_message('Failed to flag content.', 'error');
            }
            break;
            
        case 'unflag_content':
            $content_type = $_POST['content_type'];
            $content_id = (int)$_POST['content_id'];
            
            if (unflag_content($content_type, $content_id)) {
                show_message('Content unflagged successfully.', 'success');
            } else {
                show_message('Failed to unflag content.', 'error');
            }
            break;
    }
    
    // Redirect to prevent resubmission
    redirect($_SERVER['REQUEST_URI']);
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$content_type = $_GET['content_type'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get reports
$reports = get_content_reports($status, $limit, $offset);

// Get moderation statistics
$stats = get_moderation_stats(7);

// Get total count for pagination
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM content_reports 
    WHERE status = ?" . ($content_type ? " AND content_type = ?" : "")
);
$params = [$status];
if ($content_type) {
    $params[] = $content_type;
}
$stmt->execute($params);
$total_reports = $stmt->fetchColumn();
$total_pages = ceil($total_reports / $limit);

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/admin_content_moderation.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/content_moderation.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Content Moderation</h1>
        <p>Review and manage content reports and flagged content</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-flag"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_reports']); ?></h3>
                <p>Total Reports (7 days)</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['pending_reports']); ?></h3>
                <p>Pending Review</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['resolved_reports']); ?></h3>
                <p>Resolved</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['dismissed_reports']); ?></h3>
                <p>Dismissed</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="dismissed" <?php echo $status === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="content_type">Content Type:</label>
                <select name="content_type" id="content_type">
                    <option value="">All Types</option>
                    <option value="wiki_article" <?php echo $content_type === 'wiki_article' ? 'selected' : ''; ?>>Wiki Articles</option>
                    <option value="user_post" <?php echo $content_type === 'user_post' ? 'selected' : ''; ?>>User Posts</option>
                    <option value="user_profile" <?php echo $content_type === 'user_profile' ? 'selected' : ''; ?>>User Profiles</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Reports List -->
    <div class="reports-section">
        <h2>Content Reports</h2>
        
        <?php if (empty($reports)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No reports found</h3>
            <p>No content reports match your current filters.</p>
        </div>
        <?php else: ?>
        
        <div class="reports-list">
            <?php foreach ($reports as $report): ?>
            <div class="report-item">
                <div class="report-header">
                    <div class="report-meta">
                        <span class="report-id">#<?php echo $report['id']; ?></span>
                        <span class="report-type"><?php echo ucfirst(str_replace('_', ' ', $report['content_type'])); ?></span>
                        <span class="report-reason"><?php echo ucfirst($report['report_reason']); ?></span>
                        <span class="report-date"><?php echo format_date($report['created_at']); ?></span>
                    </div>
                    <div class="report-status">
                        <span class="status-badge status-<?php echo $report['status']; ?>">
                            <?php echo ucfirst($report['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="report-content">
                    <div class="report-details">
                        <p><strong>Reporter:</strong> 
                            <?php if ($report['reporter_username']): ?>
                                <a href="/user/<?php echo $report['reporter_username']; ?>">
                                    <?php echo htmlspecialchars($report['reporter_display_name'] ?: $report['reporter_username']); ?>
                                </a>
                            <?php else: ?>
                                Anonymous (IP: <?php echo htmlspecialchars($report['reporter_ip']); ?>)
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($report['report_description']): ?>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($report['report_description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($report['reviewer_username']): ?>
                        <p><strong>Reviewed by:</strong> 
                            <a href="/user/<?php echo $report['reviewer_username']; ?>">
                                <?php echo htmlspecialchars($report['reviewer_display_name'] ?: $report['reviewer_username']); ?>
                            </a>
                            on <?php echo format_date($report['reviewed_at']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if ($report['resolution_notes']): ?>
                        <p><strong>Resolution Notes:</strong> <?php echo htmlspecialchars($report['resolution_notes']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="report-actions">
                        <a href="/moderate-content/<?php echo $report['content_type']; ?>/<?php echo $report['content_id']; ?>" 
                           class="btn btn-sm btn-outline" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Content
                        </a>
                        
                        <?php if ($report['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-primary" onclick="showUpdateModal(<?php echo $report['id']; ?>)">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline">
                Next <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Report Status</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="update_report_status">
            <input type="hidden" name="report_id" id="modal_report_id">
            
            <div class="form-group">
                <label for="modal_status">Status:</label>
                <select name="status" id="modal_status" required>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="modal_resolution_notes">Resolution Notes:</label>
                <textarea name="resolution_notes" id="modal_resolution_notes" rows="4" 
                          placeholder="Add any notes about the resolution..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>



<?php include "../../includes/footer.php"; ?>
