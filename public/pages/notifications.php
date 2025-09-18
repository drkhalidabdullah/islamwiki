<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Require user to be logged in
require_login();

$page_title = 'Notifications';

// Get comprehensive notifications (messages, posts, articles, etc.)
$notifications = [];

try {
    // Get recent messages (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            m.id,
            m.message,
            m.created_at,
            m.is_read,
            u.username,
            u.display_name,
            u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.recipient_id = ? 
        AND m.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY m.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($messages as $message) {
        $notifications[] = [
            'id' => 'msg_' . $message['id'],
            'type' => 'message',
            'title' => 'New Message',
            'description' => ($message['display_name'] ?: $message['username']) . ' sent you a message',
            'content' => substr(strip_tags($message['message']), 0, 100) . (strlen(strip_tags($message['message'])) > 100 ? '...' : ''),
            'avatar' => !empty($message['avatar']) ? $message['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $message['created_at'],
            'url' => '/messages',
            'unread' => $message['is_read'] == 0
        ];
    }
    
    // Get recent posts from followed users (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            up.id,
            up.content,
            up.link_title,
            up.post_type,
            up.created_at,
            u.username,
            u.display_name,
            u.avatar,
            up.likes_count,
            up.comments_count
        FROM user_posts up
        JOIN users u ON up.user_id = u.id
        JOIN user_follows uf ON uf.follower_id = ? AND uf.following_id = u.id
        WHERE up.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($posts as $post) {
        $content = $post['link_title'] ?: substr(strip_tags($post['content']), 0, 100);
        if (strlen(strip_tags($post['content'])) > 100) {
            $content .= '...';
        }
        
        $notifications[] = [
            'id' => 'post_' . $post['id'],
            'type' => 'post',
            'title' => 'New Post',
            'description' => ($post['display_name'] ?: $post['username']) . ' shared a new post',
            'content' => $content,
            'avatar' => !empty($post['avatar']) ? $post['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $post['created_at'],
            'url' => '/user/' . $post['username'],
            'unread' => true,
            'stats' => [
                'likes' => $post['likes_count'],
                'comments' => $post['comments_count']
            ]
        ];
    }
    
    // Get recent wiki articles from followed users (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            wa.id,
            wa.title,
            wa.content,
            wa.published_at as created_at,
            wa.updated_at,
            u.username,
            u.display_name,
            u.avatar,
            cc.name as category_name,
            COALESCE(view_counts.views_count, 0) as views_count
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        JOIN user_follows uf ON uf.follower_id = ? AND uf.following_id = u.id
        LEFT JOIN content_categories cc ON wa.category_id = cc.id
        LEFT JOIN (
            SELECT article_id, COUNT(*) as views_count 
            FROM wiki_article_views 
            GROUP BY article_id
        ) view_counts ON wa.id = view_counts.article_id
        WHERE wa.published_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND wa.status = 'published'
        ORDER BY wa.published_at DESC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($articles as $article) {
        $notifications[] = [
            'id' => 'art_' . $article['id'],
            'type' => 'article',
            'title' => 'New Article',
            'description' => ($article['display_name'] ?: $article['username']) . ' published a new article',
            'content' => $article['title'] . ($article['category_name'] ? ' in ' . $article['category_name'] : ''),
            'avatar' => !empty($article['avatar']) ? $article['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $article['created_at'],
            'url' => '/wiki/' . $article['title'],
            'unread' => true,
            'stats' => [
                'views' => $article['views_count']
            ]
        ];
    }
    
    // Get watchlist updates (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            uw.id,
            wa.title,
            wa.updated_at as created_at,
            u.username,
            u.display_name,
            u.avatar
        FROM user_watchlist uw
        JOIN wiki_articles wa ON uw.article_id = wa.id
        JOIN users u ON wa.last_edit_by = u.id
        WHERE uw.user_id = ? 
        AND wa.updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY wa.updated_at DESC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $watchlist_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($watchlist_updates as $update) {
        $notifications[] = [
            'id' => 'watch_' . $update['id'],
            'type' => 'watchlist',
            'title' => 'Article Updated',
            'description' => ($update['display_name'] ?: $update['username']) . ' updated an article in your watchlist',
            'content' => $update['title'],
            'avatar' => !empty($update['avatar']) ? $update['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $update['created_at'],
            'url' => '/wiki/' . $update['title'],
            'unread' => true
        ];
    }
    
    // Get basic notifications from notifications table
    $basic_notifications = get_user_notifications($_SESSION['user_id'], 20);
    foreach ($basic_notifications as $notification) {
        $notifications[] = [
            'id' => 'notif_' . $notification['id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'description' => $notification['message'],
            'content' => $notification['message'],
            'avatar' => '/assets/images/default-avatar.svg',
            'time' => $notification['created_at'],
            'url' => '/notifications',
            'unread' => !$notification['is_read']
        ];
    }
    
    // Sort all notifications by time (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
} catch (Exception $e) {
    error_log("Error loading notifications: " . $e->getMessage());
    $notifications = [];
}

// Helper function to get type icon
function getTypeIcon($type) {
    $icons = [
        'message' => 'iw-envelope',
        'post' => 'iw-share',
        'article' => 'iw-file-alt',
        'watchlist' => 'iw-eye',
        'interaction' => 'iw-heart',
        'comment' => 'iw-comment',
        'follow' => 'iw-user-plus',
        'like' => 'iw-heart',
        'share' => 'iw-share',
        'view' => 'iw-eye',
        'friend_request' => 'iw-user-plus',
        'friend_accepted' => 'iw-user-check',
        'system' => 'iw-cog'
    ];
    return $icons[$type] ?? 'iw-bell';
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
    
    <div class="notifications-container" id="notificationsContainer">
        <?php if (empty($notifications)): ?>
            <div class="empty-state" id="emptyState">
                <i class="iw iw-bell-slash"></i>
                <h3>No notifications yet</h3>
                <p>You'll see notifications here when you receive messages, posts, and updates from people you follow.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo $notification['unread'] ? 'unread' : ''; ?>" 
                     data-notification-id="<?php echo htmlspecialchars($notification['id']); ?>"
                     data-type="<?php echo htmlspecialchars($notification['type']); ?>">
                    <img src="<?php echo htmlspecialchars($notification['avatar']); ?>" 
                         alt="Avatar" class="notification-avatar" 
                         onerror="this.src='/assets/images/default-avatar.svg'">
                    <div class="notification-details">
                        <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                        <div class="notification-description"><?php echo htmlspecialchars($notification['description']); ?></div>
                        <div class="notification-content-text"><?php echo htmlspecialchars($notification['content']); ?></div>
                        <?php if (isset($notification['stats']) && !empty($notification['stats'])): ?>
                            <div class="notification-stats">
                                <?php if (isset($notification['stats']['likes'])): ?>
                                    <span class="stat"><i class="iw iw-heart"></i> <?php echo $notification['stats']['likes']; ?></span>
                                <?php endif; ?>
                                <?php if (isset($notification['stats']['comments'])): ?>
                                    <span class="stat"><i class="iw iw-comment"></i> <?php echo $notification['stats']['comments']; ?></span>
                                <?php endif; ?>
                                <?php if (isset($notification['stats']['views'])): ?>
                                    <span class="stat"><i class="iw iw-eye"></i> <?php echo $notification['stats']['views']; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="notification-time"><?php echo time_ago($notification['time']); ?></div>
                    </div>
                    <div class="notification-actions">
                        <i class="iw <?php echo getTypeIcon($notification['type']); ?> notification-type-icon"></i>
                        <?php if ($notification['unread']): ?>
                            <button class="mark-read-btn" onclick="markAsRead('<?php echo $notification['id']; ?>')">
                                <i class="iw iw-check"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
    position: relative;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
    text-decoration: none;
    color: inherit;
}

.notification-item.unread {
    background-color: #f0f8ff;
    border-left: 4px solid #007bff;
}

.notification-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
    flex-shrink: 0;
}

.notification-details {
    flex: 1;
    margin-right: 15px;
}

.notification-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

.mark-read-btn {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
    transition: background-color 0.2s;
}

.mark-read-btn:hover {
    background-color: #e3f2fd;
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
    opacity: 0.7;
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
// Mark notification as read
function markAsRead(notificationId) {
    // For now, just update the UI locally since we have different notification types
    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
    if (notificationItem) {
        notificationItem.classList.remove('unread');
        const markReadBtn = notificationItem.querySelector('.mark-read-btn');
        if (markReadBtn) {
            markReadBtn.remove();
        }
    }
    
    // TODO: Implement proper API calls for different notification types
    // For basic notifications from the notifications table
    if (notificationId.startsWith('notif_')) {
        const basicId = notificationId.replace('notif_', '');
        fetch('/api/ajax/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + basicId
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to mark notification as read:', data.message);
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
}

// Mark all notifications as read
function markAllAsRead() {
    // Update UI immediately
    const unreadNotifications = document.querySelectorAll('.notification-item.unread');
    unreadNotifications.forEach(item => {
        item.classList.remove('unread');
        const markReadBtn = item.querySelector('.mark-read-btn');
        if (markReadBtn) {
            markReadBtn.remove();
        }
    });
    
    // Mark basic notifications as read
    fetch('/api/ajax/mark_all_notifications_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to mark all notifications as read:', data.message);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

// Make notifications clickable
document.addEventListener('DOMContentLoaded', function() {
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking the mark as read button
            if (e.target.closest('.mark-read-btn')) {
                return;
            }
            
            const notificationId = this.getAttribute('data-notification-id');
            const notificationType = this.getAttribute('data-type');
            
            // Mark as read if unread
            if (this.classList.contains('unread')) {
                markAsRead(notificationId);
            }
            
            // Navigate based on notification type
            let url = '/notifications'; // default
            
            if (notificationType === 'message') {
                url = '/messages';
            } else if (notificationType === 'post') {
                // Extract username from description or use a default
                url = '/user/' + (this.querySelector('.notification-description').textContent.split(' ')[0] || '');
            } else if (notificationType === 'article' || notificationType === 'watchlist') {
                // Extract article title from content
                const title = this.querySelector('.notification-content-text').textContent;
                url = '/wiki/' + encodeURIComponent(title);
            }
            
            window.location.href = url;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>