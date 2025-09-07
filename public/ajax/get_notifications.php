<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

$notifications = get_user_notifications($user_id, 20, $unread_only);
$unread_count = get_unread_notification_count($user_id);

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);
?>
