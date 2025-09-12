<?php
// Handle security updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in all password fields.';
        } elseif (!verify_password($current_password, $current_user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
            $error = 'New password must contain at least one uppercase letter, one lowercase letter, and one number.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $password_hash = hash_password($new_password);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$password_hash, $_SESSION['user_id']])) {
                $success = 'Password updated successfully.';
                log_activity('password_changed', 'Changed password');
            } else {
                $error = 'Failed to update password.';
            }
        }
    } elseif ($action === 'terminate_session') {
        $session_id = $_POST['session_id'] ?? '';
        if ($session_id) {
            $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$session_id, $_SESSION['user_id']])) {
                $success = 'Session terminated successfully.';
                log_activity('session_terminated', 'Terminated session');
            } else {
                $error = 'Failed to terminate session.';
            }
        }
    }
}

// Get active sessions
$stmt = $pdo->prepare("
    SELECT id, ip_address, user_agent, last_activity, created_at 
    FROM user_sessions 
    WHERE user_id = ? AND expires_at > NOW() 
    ORDER BY last_activity DESC
");
$stmt->execute([$_SESSION['user_id']]);
$active_sessions = $stmt->fetchAll();

// Get security logs
$stmt = $pdo->prepare("
    SELECT action, description, ip_address, created_at 
    FROM activity_logs 
    WHERE user_id = ? AND action IN ('login_success', 'login_failed', 'password_changed', 'session_terminated')
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$_SESSION['user_id']]);
$security_logs = $stmt->fetchAll();
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Security Settings</h2>
        <p>Manage your account security, password, and active sessions.</p>
    </div>

    <div class="security-sections">
        <!-- Password Section -->
        <div class="security-section">
            <h3>Change Password</h3>
            <form method="POST" class="security-form">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                    <small>Password must be at least 8 characters with uppercase, lowercase, and numbers.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <!-- Active Sessions Section -->
        <div class="security-section">
            <h3>Active Sessions</h3>
            <p>Manage your active login sessions across different devices.</p>
            
            <?php if (empty($active_sessions)): ?>
                <p class="no-sessions">No active sessions found.</p>
            <?php else: ?>
                <div class="sessions-list">
                    <?php foreach ($active_sessions as $session): ?>
                        <div class="session-item">
                            <div class="session-info">
                                <div class="session-details">
                                    <strong><?php echo htmlspecialchars($session['ip_address']); ?></strong>
                                    <small><?php echo htmlspecialchars($session['user_agent']); ?></small>
                                </div>
                                <div class="session-meta">
                                    <span>Last active: <?php echo date('M j, Y g:i A', strtotime($session['last_activity'])); ?></span>
                                    <?php if ($session['id'] == session_id()): ?>
                                        <span class="current-session">Current Session</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($session['id'] != session_id()): ?>
                                <form method="POST" class="terminate-form">
                                    <input type="hidden" name="action" value="terminate_session">
                                    <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Terminate</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Security Logs Section -->
        <div class="security-section">
            <h3>Security Activity</h3>
            <p>Recent security-related activity on your account.</p>
            
            <?php if (empty($security_logs)): ?>
                <p class="no-logs">No security activity to display.</p>
            <?php else: ?>
                <div class="security-logs">
                    <?php foreach ($security_logs as $log): ?>
                        <div class="log-item">
                            <div class="log-icon">
                                <i class="icon-<?php echo $log['action']; ?>"></i>
                            </div>
                            <div class="log-content">
                                <p><?php echo htmlspecialchars($log['description']); ?></p>
                                <small>
                                    <?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?> • 
                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
