<?php
// Handle notification updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_notifications') {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
    $post_notifications = isset($_POST['post_notifications']) ? 1 : 0;
    $comment_notifications = isset($_POST['comment_notifications']) ? 1 : 0;
    $follow_notifications = isset($_POST['follow_notifications']) ? 1 : 0;
    $message_notifications = isset($_POST['message_notifications']) ? 1 : 0;
    $article_notifications = isset($_POST['article_notifications']) ? 1 : 0;
    $weekly_digest = isset($_POST['weekly_digest']) ? 1 : 0;
    $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
    
    // Update or create user profile with notification settings
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, notification_settings) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE notification_settings = VALUES(notification_settings)
    ");
    
    $notification_settings = json_encode([
        'email_notifications' => $email_notifications,
        'push_notifications' => $push_notifications,
        'post_notifications' => $post_notifications,
        'comment_notifications' => $comment_notifications,
        'follow_notifications' => $follow_notifications,
        'message_notifications' => $message_notifications,
        'article_notifications' => $article_notifications,
        'weekly_digest' => $weekly_digest,
        'marketing_emails' => $marketing_emails
    ]);
    
    if ($stmt->execute([$_SESSION['user_id'], $notification_settings])) {
        $success = 'Notification settings updated successfully.';
        log_activity('notifications_updated', 'Updated notification settings');
        // Refresh user profile
        $user_profile = get_user_profile($_SESSION['user_id']);
    } else {
        $error = 'Failed to update notification settings.';
    }
}

// Get current notification settings
$notification_settings = [];
if ($user_profile && !empty($user_profile['notification_settings'])) {
    $notification_settings = json_decode($user_profile['notification_settings'], true) ?: [];
}

// Default values
$email_notifications = $notification_settings['email_notifications'] ?? 1;
$push_notifications = $notification_settings['push_notifications'] ?? 1;
$post_notifications = $notification_settings['post_notifications'] ?? 1;
$comment_notifications = $notification_settings['comment_notifications'] ?? 1;
$follow_notifications = $notification_settings['follow_notifications'] ?? 1;
$message_notifications = $notification_settings['message_notifications'] ?? 1;
$article_notifications = $notification_settings['article_notifications'] ?? 1;
$weekly_digest = $notification_settings['weekly_digest'] ?? 0;
$marketing_emails = $notification_settings['marketing_emails'] ?? 0;
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Notification Settings</h2>
        <p>Choose how and when you want to be notified about activity.</p>
    </div>

    <div class="notification-sections">
        <form method="POST" class="notification-form">
            <input type="hidden" name="action" value="update_notifications">
            
            <!-- General Notifications Section -->
            <div class="notification-section">
                <h3>General Notifications</h3>
                <p>Control your overall notification preferences.</p>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="email_notifications" <?php echo $email_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Email Notifications</strong>
                            <small>Receive notifications via email</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="push_notifications" <?php echo $push_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Push Notifications</strong>
                            <small>Receive browser push notifications</small>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Activity Notifications Section -->
            <div class="notification-section">
                <h3>Activity Notifications</h3>
                <p>Choose what activities you want to be notified about.</p>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="post_notifications" <?php echo $post_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>New Posts</strong>
                            <small>When people you follow create new posts</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="comment_notifications" <?php echo $comment_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Comments</strong>
                            <small>When someone comments on your posts or articles</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="follow_notifications" <?php echo $follow_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>New Followers</strong>
                            <small>When someone starts following you</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="message_notifications" <?php echo $message_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Messages</strong>
                            <small>When you receive new messages</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="article_notifications" <?php echo $article_notifications ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Article Updates</strong>
                            <small>When articles you follow are updated</small>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Email Preferences Section -->
            <div class="notification-section">
                <h3>Email Preferences</h3>
                <p>Manage your email notification preferences.</p>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="weekly_digest" <?php echo $weekly_digest ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Weekly Digest</strong>
                            <small>Receive a weekly summary of activity</small>
                        </div>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="marketing_emails" <?php echo $marketing_emails ? 'checked' : ''; ?>>
                        <div class="checkbox-content">
                            <strong>Marketing Emails</strong>
                            <small>Receive updates about new features and announcements</small>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                <a href="?page=overview" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
