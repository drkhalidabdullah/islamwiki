<?php
// Handle account actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'deactivate_account') {
        $confirm_text = $_POST['confirm_text'] ?? '';
        if ($confirm_text === 'DEACTIVATE') {
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            if ($stmt->execute([$_SESSION['user_id']])) {
                $success = 'Account deactivated successfully. You will be logged out.';
                log_activity('account_deactivated', 'Account deactivated by user');
                // Logout user
                session_destroy();
                header('Location: /login?message=account_deactivated');
                exit;
            } else {
                $error = 'Failed to deactivate account.';
            }
        } else {
            $error = 'Please type DEACTIVATE to confirm.';
        }
    } elseif ($action === 'delete_account') {
        $confirm_text = $_POST['confirm_text'] ?? '';
        if ($confirm_text === 'DELETE') {
            // This is a serious action - in a real app, you might want additional verification
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$_SESSION['user_id']])) {
                $success = 'Account deleted successfully.';
                log_activity('account_deleted', 'Account deleted by user');
                // Logout user
                session_destroy();
                header('Location: /login?message=account_deleted');
                exit;
            } else {
                $error = 'Failed to delete account.';
            }
        } else {
            $error = 'Please type DELETE to confirm.';
        }
    } elseif ($action === 'export_data') {
        // Export user data
        $user_data = [
            'user' => $current_user,
            'profile' => $user_profile,
            'posts' => [],
            'articles' => [],
            'comments' => [],
            'activity' => []
        ];
        
        // Get user posts
        $stmt = $pdo->prepare("SELECT * FROM user_posts WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data['posts'] = $stmt->fetchAll();
        
        // Get user articles
        $stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE created_by = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data['articles'] = $stmt->fetchAll();
        
        // Get user comments
        $stmt = $pdo->prepare("SELECT * FROM post_comments WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data['comments'] = $stmt->fetchAll();
        
        // Get user activity
        $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data['activity'] = $stmt->fetchAll();
        
        // Set headers for download
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="user_data_' . date('Y-m-d') . '.json"');
        echo json_encode($user_data, JSON_PRETTY_PRINT);
        exit;
    }
}

// Get account statistics
$account_stats = [
    'member_since' => $current_user['created_at'],
    'last_login' => $current_user['last_login_at'],
    'last_seen' => $current_user['last_seen_at'],
    'email_verified' => $current_user['email_verified_at'] ? true : false,
    'account_status' => $current_user['is_active'] ? 'Active' : 'Inactive'
];

// Get storage usage (simplified)
$storage_usage = [
    'posts' => 0,
    'articles' => 0,
    'uploads' => 0
];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_posts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();
$storage_usage['posts'] = $result['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wiki_articles WHERE created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();
$storage_usage['articles'] = $result['count'];
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Account Management</h2>
        <p>Manage your account data, storage, and account status.</p>
    </div>

    <div class="account-sections">
        <!-- Account Information Section -->
        <div class="account-section">
            <h3>Account Information</h3>
            <div class="account-info">
                <div class="info-item">
                    <label>Username</label>
                    <span><?php echo htmlspecialchars($current_user['username']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($current_user['email']); ?></span>
                    <?php if ($account_stats['email_verified']): ?>
                        <span class="verified-badge">Verified</span>
                    <?php else: ?>
                        <a href="#" class="verify-link">Verify Email</a>
                    <?php endif; ?>
                </div>
                <div class="info-item">
                    <label>Member Since</label>
                    <span><?php echo date('M j, Y', strtotime($account_stats['member_since'])); ?></span>
                </div>
                <div class="info-item">
                    <label>Last Login</label>
                    <span><?php echo $account_stats['last_login'] ? date('M j, Y g:i A', strtotime($account_stats['last_login'])) : 'Never'; ?></span>
                </div>
                <div class="info-item">
                    <label>Account Status</label>
                    <span class="status-<?php echo strtolower($account_stats['account_status']); ?>"><?php echo $account_stats['account_status']; ?></span>
                </div>
            </div>
        </div>

        <!-- Storage Usage Section -->
        <div class="account-section">
            <h3>Storage Usage</h3>
            <div class="storage-info">
                <div class="storage-item">
                    <label>Posts</label>
                    <span><?php echo number_format($storage_usage['posts']); ?> posts</span>
                </div>
                <div class="storage-item">
                    <label>Articles</label>
                    <span><?php echo number_format($storage_usage['articles']); ?> articles</span>
                </div>
                <div class="storage-item">
                    <label>Uploads</label>
                    <span><?php echo number_format($storage_usage['uploads']); ?> files</span>
                </div>
            </div>
        </div>

        <!-- Data Management Section -->
        <div class="account-section">
            <h3>Data Management</h3>
            <p>Download or manage your personal data.</p>
            
            <div class="data-actions">
                <form method="POST" class="inline-form">
                    <input type="hidden" name="action" value="export_data">
                    <button type="submit" class="btn btn-secondary">
                        <i class="icon-download"></i>
                        Export My Data
                    </button>
                </form>
                
                <p class="data-info">
                    <small>Download all your data including posts, articles, comments, and activity logs in JSON format.</small>
                </p>
            </div>
        </div>

        <!-- Account Actions Section -->
        <div class="account-section danger-zone">
            <h3>Danger Zone</h3>
            <p>These actions are irreversible. Please proceed with caution.</p>
            
            <div class="danger-actions">
                <!-- Deactivate Account -->
                <div class="danger-action">
                    <div class="action-info">
                        <h4>Deactivate Account</h4>
                        <p>Your account will be deactivated and you will be logged out. You can reactivate it by logging in again.</p>
                    </div>
                    <button class="btn btn-warning" onclick="showDeactivateModal()">Deactivate Account</button>
                </div>
                
                <!-- Delete Account -->
                <div class="danger-action">
                    <div class="action-info">
                        <h4>Delete Account</h4>
                        <p>Permanently delete your account and all associated data. This action cannot be undone.</p>
                    </div>
                    <button class="btn btn-danger" onclick="showDeleteModal()">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div id="deactivateModal" class="modal">
    <div class="modal-content">
        <h3>Deactivate Account</h3>
        <p>Are you sure you want to deactivate your account? You will be logged out immediately.</p>
        <p>To reactivate your account, simply log in again.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="deactivate_account">
            <div class="form-group">
                <label for="deactivate_confirm">Type <strong>DEACTIVATE</strong> to confirm:</label>
                <input type="text" id="deactivate_confirm" name="confirm_text" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-warning">Deactivate Account</button>
                <button type="button" class="btn btn-secondary" onclick="hideDeactivateModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3>Delete Account</h3>
        <p><strong>Warning:</strong> This action is permanent and cannot be undone.</p>
        <p>All your data including posts, articles, comments, and profile information will be permanently deleted.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="delete_account">
            <div class="form-group">
                <label for="delete_confirm">Type <strong>DELETE</strong> to confirm:</label>
                <input type="text" id="delete_confirm" name="confirm_text" required>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">Delete Account</button>
                <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

