<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Social features are disabled']);
    exit;
}

$conversation_id = $_GET['conversation_id'] ?? null;
$last_message_id = $_GET['last_message_id'] ?? 0;

if (!$conversation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Conversation ID is required']);
    exit;
}

try {
    // Get messages for the conversation
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.display_name, u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE ((m.sender_id = ? AND m.recipient_id = ?) 
               OR (m.sender_id = ? AND m.recipient_id = ?))
        AND m.id > ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$_SESSION['user_id'], $conversation_id, $conversation_id, $_SESSION['user_id'], $last_message_id]);
    $messages = $stmt->fetchAll();
    
    // Mark messages as read
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE sender_id = ? AND recipient_id = ? AND is_read = 0
    ");
    $stmt->execute([$conversation_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (PDOException $e) {
    error_log("Get messages error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to get messages']);
}
?>