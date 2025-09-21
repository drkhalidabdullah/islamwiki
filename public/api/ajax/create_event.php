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
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$location = trim($_POST['location'] ?? '');
$privacy = $_POST['privacy'] ?? 'public';

// Validate required fields
if (empty($title) || empty($start_date)) {
    echo json_encode(['success' => false, 'message' => 'Title and start date are required']);
    exit();
}

// Validate privacy level
$allowed_privacy = ['public', 'community', 'followers', 'private'];
if (!in_array($privacy, $allowed_privacy)) {
    $privacy = 'public';
}

try {
    // Combine date and time
    $start_datetime = $start_date;
    if (!empty($start_time)) {
        $start_datetime .= ' ' . $start_time;
    }
    
    $end_datetime = null;
    if (!empty($end_date)) {
        $end_datetime = $end_date;
        if (!empty($end_time)) {
            $end_datetime .= ' ' . $end_time;
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO user_events (
            user_id, 
            title, 
            description, 
            start_date, 
            end_date, 
            location, 
            privacy_level, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $user_id,
        $title,
        $description,
        $start_datetime,
        $end_datetime,
        $location,
        $privacy
    ]);
    
    $event_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ]);
    
} catch (Exception $e) {
    error_log("Error creating event: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to create event: ' . $e->getMessage()]);
}
?>
