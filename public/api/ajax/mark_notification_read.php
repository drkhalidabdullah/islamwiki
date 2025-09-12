<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if notifications are enabled
$enable_notifications = get_system_setting('enable_notifications', true);
if (!$enable_notifications) {
    echo json_encode(['success' => false, 'message' => 'Notifications are currently disabled']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$notification_id = $input['notification_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$notification_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

$result = mark_notification_read($notification_id, $user_id);

echo json_encode([
    'success' => $result,
    'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
]);
?>
