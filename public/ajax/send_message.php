<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$recipient_id = $input['recipient_id'] ?? null;
$message = $input['message'] ?? null;

if (!$recipient_id || !is_numeric($recipient_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient ID']);
    exit;
}

if (!$message || trim($message) === '') {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

try {
    // Check if recipient exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$recipient_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Recipient not found']);
        exit;
    }
    
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, recipient_id, message, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $recipient_id, trim($message)]);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    
    // Create notification for recipient
    $sender = get_user($_SESSION["user_id"]);
    if ($sender) {
        create_notification(
            $recipient_id,
            "message",
            "New Message",
            "You have a new message from " . $sender["username"],
            ["from_user_id" => $_SESSION["user_id"], "from_username" => $sender["username"], "message_id" => $pdo->lastInsertId()]
        );
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
