<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $notifications = [];
    $user_id = $_SESSION['user_id'];
    
    // Get recent messages (last 7 days) as notifications
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
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($messages as $message) {
            $notifications[] = [
                'id' => 'msg_' . $message['id'],
                'type' => 'message',
                'icon' => 'iw iw-comment',
                'text' => ($message['display_name'] ?: $message['username']) . ' sent you a message',
                'time' => time_ago($message['created_at']),
                'unread' => $message['is_read'] == 0,
                'url' => '/pages/social/messages.php'
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching message notifications: " . $e->getMessage());
    }
    
    // Get recent posts from followed users (last 7 days)
    try {
        $stmt = $pdo->prepare("
            SELECT 
                up.id,
                up.content,
                up.created_at,
                u.username,
                u.display_name,
                u.avatar
            FROM user_posts up
            JOIN user_follows uf ON up.user_id = uf.following_id
            JOIN users u ON up.user_id = u.id
            WHERE uf.follower_id = ? 
            AND uf.status = 'accepted'
            AND up.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY up.created_at DESC
            LIMIT 3
        ");
        $stmt->execute([$user_id]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($posts as $post) {
            $notifications[] = [
                'id' => 'post_' . $post['id'],
                'type' => 'post',
                'icon' => 'iw iw-heart',
                'text' => ($post['display_name'] ?: $post['username']) . ' shared a new post',
                'time' => time_ago($post['created_at']),
                'unread' => false,
                'url' => '/pages/social/posts.php?id=' . $post['id']
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching post notifications: " . $e->getMessage());
    }
    
    // Get friend requests
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
            AND uf.status = 'pending'
            ORDER BY uf.created_at DESC
            LIMIT 3
        ");
        $stmt->execute([$user_id]);
        $friend_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($friend_requests as $request) {
            $notifications[] = [
                'id' => 'friend_' . $request['id'],
                'type' => 'friend_request',
                'icon' => 'iw iw-user-plus',
                'text' => ($request['display_name'] ?: $request['username']) . ' sent you a friend request',
                'time' => time_ago($request['created_at']),
                'unread' => true,
                'url' => '/pages/social/friends.php'
            ];
        }
    } catch (Exception $e) {
        error_log("Error fetching friend request notifications: " . $e->getMessage());
    }
    
    // Sort notifications by time (most recent first)
    usort($notifications, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
    
    // Limit to 5 most recent notifications
    $notifications = array_slice($notifications, 0, 5);
    
    // Count total unread notifications
    $unread_count = 0;
    foreach ($notifications as $notification) {
        if ($notification['unread']) {
            $unread_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ]);

} catch (Exception $e) {
    error_log("Get sidebar notifications error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get notifications']);
}
?>
