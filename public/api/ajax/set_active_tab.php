<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For testing, allow without authentication
// TODO: Re-enable authentication in production
// if (!isset($_SESSION['user_id']) || !is_admin()) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Access denied']);
//     exit;
// }

// Get the tab name from POST data
$tab = $_POST['tab'] ?? '';

if (empty($tab)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tab name required']);
    exit;
}

// Store the active tab in session
$_SESSION['active_settings_tab'] = $tab;

// Return success
echo json_encode(['success' => true, 'tab' => $tab]);
?>
