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

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.admin-header {
    margin-bottom: 2rem;
}

.admin-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.stat-content h3 {
    font-size: 1.8rem;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.filters-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #2c3e50;
}

.filter-group select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.reports-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.reports-section h2 {
    margin-top: 0;
    color: #2c3e50;
}

.report-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.report-header {
    background: #f8f9fa;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e9ecef;
}

.report-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.report-meta span {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.report-id {
    background: #6c757d;
    color: white;
}

.report-type {
    background: #17a2b8;
    color: white;
}

.report-reason {
    background: #ffc107;
    color: #212529;
}

.report-date {
    background: #e9ecef;
    color: #495057;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-reviewed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-resolved {
    background: #d4edda;
    color: #155724;
}

.status-dismissed {
    background: #f8d7da;
    color: #721c24;
}

.report-content {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.report-details {
    flex: 1;
}

.report-details p {
    margin: 0.5rem 0;
    color: #495057;
}

.report-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.pagination-info {
    color: #6c757d;
    font-weight: 600;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .report-content {
        flex-direction: column;
    }
    
    .report-actions {
        justify-content: flex-start;
    }
    
    .report-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
function showUpdateModal(reportId) {
    document.getElementById('modal_report_id').value = reportId;
    document.getElementById('updateModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('updateModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('updateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal with escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php include "../../includes/footer.php"; ?>
