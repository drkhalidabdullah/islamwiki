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

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    echo json_encode(['success' => false, 'message' => 'Social features are disabled']);
    exit;
}

try {
    // Get recent conversations for the sidebar (last 5 conversations)
    $stmt = $pdo->prepare("
        SELECT DISTINCT 
            CASE 
                WHEN m.sender_id = ? THEN m.recipient_id 
                ELSE m.sender_id 
            END as other_user_id,
            u.username,
            u.display_name,
            u.avatar,
            m.message,
            m.created_at,
            m.is_read,
            m.sender_id,
            m.id as message_id,
            COUNT(CASE WHEN m.sender_id != ? AND m.is_read = 0 THEN 1 END) as unread_count
        FROM messages m
        JOIN users u ON (
            CASE 
                WHEN m.sender_id = ? THEN m.recipient_id 
                ELSE m.sender_id 
            END = u.id
        )
        WHERE (m.sender_id = ? OR m.recipient_id = ?)
        AND m.id IN (
            SELECT MAX(id) 
            FROM messages 
            WHERE (sender_id = ? AND recipient_id = u.id) 
               OR (sender_id = u.id AND recipient_id = ?)
            GROUP BY LEAST(sender_id, recipient_id), GREATEST(sender_id, recipient_id)
        )
        GROUP BY other_user_id, u.username, u.display_name, u.avatar, m.message, m.created_at, m.is_read, m.sender_id, m.id
        ORDER BY m.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([
        $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], 
        $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']
    ]);
    $conversations = $stmt->fetchAll();

    // Format conversations for sidebar
    $formatted_conversations = [];
    foreach ($conversations as $conv) {
        $formatted_conversations[] = [
            'id' => $conv['other_user_id'],
            'username' => $conv['username'],
            'display_name' => $conv['display_name'] ?: $conv['username'],
            'avatar' => !empty($conv['avatar']) ? $conv['avatar'] : '/assets/images/default-avatar.svg',
            'last_message' => substr(strip_tags($conv['message']), 0, 50) . (strlen(strip_tags($conv['message'])) > 50 ? '...' : ''),
            'time' => time_ago($conv['created_at']),
            'unread_count' => (int)$conv['unread_count'],
            'is_from_me' => $conv['sender_id'] == $_SESSION['user_id']
        ];
    }

    // Get total unread message count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM messages 
        WHERE recipient_id = ? AND is_read = 0
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_unread = $stmt->fetch()['unread_count'];

    echo json_encode([
        'success' => true,
        'conversations' => $formatted_conversations,
        'total_unread' => (int)$total_unread
    ]);

} catch (PDOException $e) {
    error_log("Get sidebar messages error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get messages']);
}
?>
