<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$count_only = isset($_GET['count_only']) && $_GET['count_only'] == '1';

try {
    if ($count_only) {
        // Get unread notification count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['count' => (int)$result['count']]);
    } else {
        // Get actual notifications
        $stmt = $pdo->prepare("
            SELECT n.*, u.username, u.display_name 
            FROM notifications n 
            LEFT JOIN users u ON n.from_user_id = u.id 
            WHERE n.user_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['notifications' => $notifications]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
