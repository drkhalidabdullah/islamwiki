<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'] ?? '';

if (empty($event_id)) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

try {
    // Check if user is already attending
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM event_attendees WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$user_id, $event_id]);
    $result = $stmt->fetch();
    $is_attending = $result['count'] > 0;
    
    if ($is_attending) {
        // Remove attendance
        $stmt = $pdo->prepare("DELETE FROM event_attendees WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        $attending = false;
    } else {
        // Add attendance
        $stmt = $pdo->prepare("INSERT INTO event_attendees (user_id, event_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $event_id]);
        $attending = true;
    }
    
    echo json_encode([
        'success' => true,
        'attending' => $attending,
        'message' => $attending ? 'You are now attending this event' : 'You are no longer attending this event'
    ]);
    
} catch (Exception $e) {
    error_log("Error toggling event attendance: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update attendance: ' . $e->getMessage()]);
}
?>
