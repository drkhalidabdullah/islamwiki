<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$messages = get_recent_messages($user_id, 20);
$unread_count = get_unread_message_count($user_id);

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'unread_count' => $unread_count
]);
?>
