<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    // For testing purposes, show the page even when not logged in
    $page_title = 'Notifications - Please Log In';
    $show_login_message = true;
} else {
    $page_title = 'Notifications';
    $show_login_message = false;
}

include '../includes/header.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Notifications</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="markAllAsRead()">Mark All as Read</button>
        </div>
    </div>
    
    <div class="notifications-container">
        <?php if ($show_login_message): ?>
            <div class="empty-state">
                <i class="iw iw-sign-in-alt"></i>
                <h3>Please Log In</h3>
                <p>You need to be logged in to view your notifications.</p>
                <a href="/login" class="btn btn-primary">Log In</a>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="iw iw-bell-slash"></i>
                <h3>No notifications yet</h3>
                <p>You'll see notifications here when you receive messages, posts, and updates from people you follow.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.notifications-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
    text-decoration: none;
    color: inherit;
}

.notification-item:hover {
    background-color: #f8f9fa;
    text-decoration: none;
    color: inherit;
}

.notification-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.notification-details {
    flex: 1;
}

.notification-title {
    font-weight: bold;
    margin-bottom: 5px;
    color: #333;
}

.notification-description {
    color: #666;
    margin-bottom: 5px;
}

.notification-content-text {
    color: #888;
    font-size: 14px;
    margin-bottom: 5px;
}

.notification-time {
    color: #999;
    font-size: 12px;
}

.notification-stats {
    display: flex;
    gap: 10px;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.notification-stats .stat {
    display: flex;
    align-items: center;
    gap: 3px;
}

.notification-stats .stat i {
    font-size: 10px;
}

.notification-type-icon {
    width: 16px;
    height: 16px;
    margin-left: 8px;
    flex-shrink: 0;
    opacity: 0.7;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #ddd;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #333;
}

.empty-state p {
    color: #666;
    line-height: 1.5;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.header-actions .btn {
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.header-actions .btn:hover {
    background-color: #0056b3;
}

.notification-loading {
    text-align: center;
    padding: 40px;
    color: #666;
}

.notification-empty {
    text-align: center;
    padding: 40px;
    color: #666;
}
</style>

<script>
// Initialize notification manager for the page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof NotificationManager !== 'undefined') {
        window.notificationManager = new NotificationManager();
        // Load notifications for the page
        window.notificationManager.loadNotifications();
    }
});
</script>

<?php include '../includes/footer.php'; ?>