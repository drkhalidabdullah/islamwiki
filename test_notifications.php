<?php
// Simple test script for notifications API
require_once 'public/config/config.php';
require_once 'public/includes/functions.php';

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test script working',
    'session_status' => session_status(),
    'is_logged_in' => is_logged_in(),
    'user_id' => $_SESSION['user_id'] ?? 'not set'
]);
?>

