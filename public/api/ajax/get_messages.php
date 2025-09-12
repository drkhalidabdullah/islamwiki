<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    echo json_encode(['error' => 'Social features are currently disabled']);
    exit;
}

$count_only = isset($_GET['count_only']) && $_GET['count_only'] == '1';

try {
    if ($count_only) {
        // Get unread message count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE recipient_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['count' => (int)$result['count']]);
    } else {
        // Get recent messages
        $stmt = $pdo->prepare("
            SELECT m.*, u.username, u.display_name 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE m.recipient_id = ? 
            ORDER BY m.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['messages' => $messages]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
