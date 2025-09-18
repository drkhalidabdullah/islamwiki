<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    // Return empty notifications instead of error for better UX
    echo json_encode([
        'success' => true,
        'notifications' => [],
        'unread_count' => 0,
        'total' => 0,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = $_GET['limit'] ?? 10;

try {
    $notifications = [];
    
    // Get recent messages (last 7 days)
    try {
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
            AND m.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY m.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching messages: " . $e->getMessage());
        $messages = [];
    }
    
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
    
    // Get recent posts from followed users (last 7 days)
    try {
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
            WHERE up.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND up.is_public = 1
            ORDER BY up.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching posts: " . $e->getMessage());
        $posts = [];
    }
    
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
    
    // Get recent wiki articles from followed users (last 7 days)
    try {
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
            WHERE wa.published_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND wa.status = 'published'
            ORDER BY wa.published_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching articles: " . $e->getMessage());
        $articles = [];
    }
    
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
    
    // Get watchlist updates (last 7 days)
    try {
        $stmt = $pdo->prepare("
            SELECT 
                uw.id,
                uw.article_id,
                wa.title,
                wa.updated_at,
                u.username,
                u.display_name,
                u.avatar,
                uw.last_viewed
            FROM user_watchlist uw
            JOIN wiki_articles wa ON uw.article_id = wa.id
            JOIN users u ON wa.author_id = u.id
            WHERE uw.user_id = ?
            AND wa.updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND (uw.last_viewed IS NULL OR wa.updated_at > uw.last_viewed)
            ORDER BY wa.updated_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $watchlist_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching watchlist updates: " . $e->getMessage());
        $watchlist_updates = [];
    }
    
    foreach ($watchlist_updates as $update) {
        $notifications[] = [
            'id' => 'watch_' . $update['id'],
            'type' => 'watchlist',
            'title' => 'Article Updated',
            'description' => 'An article in your watchlist was updated by ' . ($update['display_name'] ?: $update['username']),
            'content' => $update['title'],
            'avatar' => !empty($update['avatar']) ? $update['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $update['updated_at'],
            'url' => '/wiki/' . $update['title'],
            'unread' => true
        ];
    }
    
    // Get post interactions (likes, comments, shares)
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pi.id,
                pi.post_id,
                pi.interaction_type,
                pi.created_at,
                up.title,
                up.content,
                u.username,
                u.display_name,
                u.avatar
            FROM post_interactions pi
            JOIN user_posts up ON pi.post_id = up.id
            JOIN users u ON pi.user_id = u.id
            WHERE up.user_id = ? 
            AND pi.user_id != ?
            AND pi.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY pi.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id, $user_id]);
        $interactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching post interactions: " . $e->getMessage());
        $interactions = [];
    }
    
    foreach ($interactions as $interaction) {
        $type_text = $interaction['interaction_type'] == 'like' ? 'liked' : 
                    ($interaction['interaction_type'] == 'share' ? 'shared' : 'interacted with');
        $notifications[] = [
            'id' => 'interact_' . $interaction['id'],
            'type' => 'interaction',
            'title' => 'Post ' . ucfirst($interaction['interaction_type']),
            'description' => ($interaction['display_name'] ?: $interaction['username']) . ' ' . $type_text . ' your post',
            'content' => $interaction['title'] ?: substr(strip_tags($interaction['content']), 0, 100) . '...',
            'avatar' => !empty($interaction['avatar']) ? $interaction['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $interaction['created_at'],
            'url' => '/user/' . $interaction['username'],
            'unread' => true
        ];
    }
    
    // Get post comments
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pc.id,
                pc.post_id,
                pc.comment,
                pc.created_at,
                up.title,
                up.content,
                u.username,
                u.display_name,
                u.avatar
            FROM post_comments pc
            JOIN user_posts up ON pc.post_id = up.id
            JOIN users u ON pc.user_id = u.id
            WHERE up.user_id = ? 
            AND pc.user_id != ?
            AND pc.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY pc.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id, $user_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching post comments: " . $e->getMessage());
        $comments = [];
    }
    
    foreach ($comments as $comment) {
        $notifications[] = [
            'id' => 'comment_' . $comment['id'],
            'type' => 'comment',
            'title' => 'New Comment',
            'description' => ($comment['display_name'] ?: $comment['username']) . ' commented on your post',
            'content' => substr(strip_tags($comment['comment']), 0, 100) . (strlen(strip_tags($comment['comment'])) > 100 ? '...' : ''),
            'avatar' => !empty($comment['avatar']) ? $comment['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $comment['created_at'],
            'url' => '/user/' . $comment['username'],
            'unread' => true
        ];
    }
    
    // Get new followers
    try {
        $stmt = $pdo->prepare("
            SELECT 
                uf.id,
                uf.created_at,
                u.username,
                u.display_name,
                u.avatar
            FROM user_follows uf
            JOIN users u ON uf.follower_id = u.id
            WHERE uf.following_id = ?
            AND uf.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY uf.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $followers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching followers: " . $e->getMessage());
        $followers = [];
    }
    
    foreach ($followers as $follower) {
        $notifications[] = [
            'id' => 'follow_' . $follower['id'],
            'type' => 'follow',
            'title' => 'New Follower',
            'description' => ($follower['display_name'] ?: $follower['username']) . ' started following you',
            'content' => '',
            'avatar' => !empty($follower['avatar']) ? $follower['avatar'] : '/assets/images/default-avatar.svg',
            'time' => $follower['created_at'],
            'url' => '/user/' . $follower['username'],
            'unread' => true
        ];
    }
    
    // Sort all notifications by time (newest first)
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Limit to requested number
    $notifications = array_slice($notifications, 0, $limit);
    
    // Count unread notifications
    $unread_count = count(array_filter($notifications, function($n) {
        return $n['unread'];
    }));
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count,
        'total' => count($notifications)
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch notifications',
        'error' => $e->getMessage()
    ]);
}
?>