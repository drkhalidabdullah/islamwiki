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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$recipient_id = $input['recipient_id'] ?? null;
$message = trim($input['message'] ?? '');
$subject = trim($input['subject'] ?? '');

if (!$recipient_id || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Recipient ID and message are required']);
    exit;
}

// Validate recipient exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$recipient_id]);
if (!$stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Recipient not found']);
    exit;
}

// Prevent sending message to self
if ($recipient_id == $_SESSION['user_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot send message to yourself']);
    exit;
}

try {
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, recipient_id, message, created_at, is_read) 
        VALUES (?, ?, ?, NOW(), 0)
    ");
    $stmt->execute([$_SESSION['user_id'], $recipient_id, $message]);
    
    $message_id = $pdo->lastInsertId();
    
    // Get the created message with user details
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.display_name, u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$message_id]);
    $message_data = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'data' => $message_data
    ]);
    
} catch (PDOException $e) {
    error_log("Message send error: " . $e->getMessage());
    error_log("Message send error details: " . print_r($e, true));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("General error details: " . print_r($e, true));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message: ' . $e->getMessage()]);
}
?>