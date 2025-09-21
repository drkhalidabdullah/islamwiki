<?php
// Right Header Sidebar - Friends and Messages
if (!is_logged_in() || !$enable_social) return;

// Placeholder data for now - will be replaced with real functions
$online_friends = [
    ['id' => 1, 'display_name' => 'John Doe', 'status' => 'Online'],
    ['id' => 2, 'display_name' => 'Jane Smith', 'status' => 'Away'],
    ['id' => 3, 'display_name' => 'Mike Johnson', 'status' => 'Online'],
    ['id' => 4, 'display_name' => 'Sarah Wilson', 'status' => 'Online']
];

$recent_messages = [
    ['conversation_id' => 1, 'sender_id' => 1, 'sender_name' => 'John Doe', 'content' => 'Hey, how are you doing?', 'created_at' => '2024-01-15 10:30:00', 'unread_count' => 2],
    ['conversation_id' => 2, 'sender_id' => 2, 'sender_name' => 'Jane Smith', 'content' => 'Thanks for the help earlier!', 'created_at' => '2024-01-15 09:15:00', 'unread_count' => 0],
    ['conversation_id' => 3, 'sender_id' => 3, 'sender_name' => 'Mike Johnson', 'content' => 'Are we still meeting tomorrow?', 'created_at' => '2024-01-15 08:45:00', 'unread_count' => 1]
];

// Helper functions
function get_user_avatar($user_id) {
    $avatar_path = "/uploads/profile_pictures/user_{$user_id}.jpg";
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $avatar_path)) {
        return $avatar_path;
    }
    return "/assets/images/default-avatar.svg";
}

function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    return floor($time/86400) . 'd ago';
}

function truncate_text($text, $length) {
    return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
}
?>

<div class="right-sidebar" id="rightSidebar">
    <div class="right-sidebar-content">
        <!-- Friends Section -->
        <div class="right-sidebar-section">
            <div class="right-sidebar-header">
                <h3>Friends</h3>
                <span class="online-count"><?php echo count($online_friends); ?> online</span>
            </div>
            
            <!-- Online Friends -->
            <div class="friends-list">
                <?php if (empty($online_friends)): ?>
                    <div class="no-friends">
                        <i class="iw iw-user-friends"></i>
                        <span>No friends online</span>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($online_friends, 0, 8) as $friend): ?>
                        <div class="friend-item" data-friend-id="<?php echo $friend['id']; ?>" title="<?php echo htmlspecialchars($friend['display_name']); ?>">
                            <div class="friend-avatar">
                                <img src="<?php echo get_user_avatar($friend['id']); ?>" alt="<?php echo htmlspecialchars($friend['display_name']); ?>">
                                <div class="online-indicator"></div>
                            </div>
                            <div class="friend-info">
                                <span class="friend-name"><?php echo htmlspecialchars($friend['display_name']); ?></span>
                                <span class="friend-status"><?php echo htmlspecialchars($friend['status'] ?? 'Online'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Friends Actions -->
            <div class="friends-actions">
                <a href="/pages/social/friends.php" class="action-btn">
                    <i class="iw iw-users"></i>
                    <span>All Friends</span>
                </a>
                <a href="/pages/social/friends.php?tab=requests" class="action-btn">
                    <i class="iw iw-user-plus"></i>
                    <span>Requests</span>
                </a>
            </div>
        </div>
        
        <!-- Messages Section -->
        <div class="right-sidebar-section">
            <div class="right-sidebar-header">
                <h3>Messages</h3>
                <a href="/pages/social/messages.php" class="view-all">View All</a>
            </div>
            
            <!-- Recent Messages -->
            <div class="messages-list">
                <?php if (empty($recent_messages)): ?>
                    <div class="no-messages">
                        <i class="iw iw-comment"></i>
                        <span>No recent messages</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_messages as $message): ?>
                        <div class="message-item" data-conversation-id="<?php echo $message['conversation_id']; ?>">
                            <div class="message-avatar">
                                <img src="<?php echo get_user_avatar($message['sender_id']); ?>" alt="<?php echo htmlspecialchars($message['sender_name']); ?>">
                                <?php if ($message['unread_count'] > 0): ?>
                                    <div class="unread-badge"><?php echo $message['unread_count']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="sender-name"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                                    <span class="message-time"><?php echo time_ago($message['created_at']); ?></span>
                                </div>
                                <div class="message-preview">
                                    <?php echo htmlspecialchars(truncate_text($message['content'], 50)); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Message Actions -->
            <div class="messages-actions">
                <a href="/pages/social/messages.php?action=compose" class="action-btn primary">
                    <i class="iw iw-edit"></i>
                    <span>New Message</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.right-sidebar {
    position: fixed !important;
    top: 60px !important;
    right: 0 !important;
    width: 300px !important;
    height: calc(100vh - 60px) !important;
    background: #1a1a1a !important;
    border-left: 1px solid #333 !important;
    z-index: 10000 !important;
    overflow-y: auto !important;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.3) !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.right-sidebar-content {
    padding: 20px;
}

.right-sidebar-section {
    margin-bottom: 30px;
}

.right-sidebar-section:last-child {
    margin-bottom: 0;
}

.right-sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #333;
}

.right-sidebar-header h3 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.online-count {
    color: #4CAF50;
    font-size: 12px;
    font-weight: 500;
}

.view-all {
    color: #666;
    font-size: 12px;
    text-decoration: none;
    transition: color 0.2s;
}

.view-all:hover {
    color: #fff;
}

/* Friends Styles */
.friends-list {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.friend-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    cursor: pointer;
    transition: background-color 0.2s;
    border-radius: 6px;
}

.friend-item:hover {
    background-color: #2a2a2a;
}

.friend-avatar {
    position: relative;
    margin-right: 12px;
}

.friend-avatar img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.online-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: #4CAF50;
    border-radius: 50%;
    border: 2px solid #1a1a1a;
}

.friend-info {
    flex: 1;
    min-width: 0;
}

.friend-name {
    display: block;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.friend-status {
    display: block;
    color: #666;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Messages Styles */
.messages-list {
    max-height: 250px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.message-item {
    display: flex;
    align-items: flex-start;
    padding: 10px 0;
    cursor: pointer;
    transition: background-color 0.2s;
    border-radius: 6px;
}

.message-item:hover {
    background-color: #2a2a2a;
}

.message-avatar {
    position: relative;
    margin-right: 12px;
}

.message-avatar img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.unread-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
}

.message-content {
    flex: 1;
    min-width: 0;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.sender-name {
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-time {
    color: #666;
    font-size: 11px;
    white-space: nowrap;
}

.message-preview {
    color: #999;
    font-size: 13px;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Action Buttons */
.friends-actions,
.messages-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: #2a2a2a;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
    flex: 1;
    justify-content: center;
}

.action-btn:hover {
    background: #3a3a3a;
    color: #fff;
}

.action-btn.primary {
    background: #6c5ce7;
}

.action-btn.primary:hover {
    background: #5a4fcf;
}

.action-btn i {
    margin-right: 6px;
    font-size: 14px;
}

/* Empty States */
.no-friends,
.no-messages {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    color: #666;
    text-align: center;
}

.no-friends i,
.no-messages i {
    font-size: 24px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.no-friends span,
.no-messages span {
    font-size: 13px;
}

/* Responsive */
@media (max-width: 1200px) {
    .right-sidebar {
        width: 250px;
    }
}

@media (max-width: 768px) {
    .right-sidebar {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle friend item clicks
    document.querySelectorAll('.friend-item').forEach(item => {
        item.addEventListener('click', function() {
            const friendId = this.dataset.friendId;
            if (friendId) {
                window.location.href = `/user/${friendId}`;
            }
        });
    });
    
    // Handle message item clicks
    document.querySelectorAll('.message-item').forEach(item => {
        item.addEventListener('click', function() {
            const conversationId = this.dataset.conversationId;
            if (conversationId) {
                window.location.href = `/pages/social/messages.php?conversation=${conversationId}`;
            }
        });
    });
    
    // Auto-refresh friends and messages every 30 seconds
    setInterval(function() {
        // This would typically make an AJAX call to refresh the data
        // For now, we'll just refresh the page
        // location.reload();
    }, 30000);
});
</script>
