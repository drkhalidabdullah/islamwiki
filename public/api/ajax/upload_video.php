<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if video was uploaded
if (!isset($_FILES['video'])) {
    echo json_encode(['success' => false, 'message' => 'No video file in request']);
    exit();
}

if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error_message = $error_messages[$_FILES['video']['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $error_message]);
    exit();
}

$file = $_FILES['video'];

// Validate file type
$allowed_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm', 'video/quicktime'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only MP4, AVI, MOV, WMV, WebM, and QuickTime are allowed']);
    exit();
}

// Validate file size (max 50MB for videos)
$max_size = 50 * 1024 * 1024; // 50MB
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Video too large. Maximum size is 50MB']);
    exit();
}

// Create user-specific uploads directory
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$upload_dir = __DIR__ . "/../../uploads/social/posts/{$username}/videos/";

// Create directory structure if it doesn't exist
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

// Ensure directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
    exit();
}

// Generate unique filename with timestamp
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$timestamp = date('Y-m-d_H-i-s');
$random = substr(md5(uniqid(rand(), true)), 0, 8);
$filename = "video_{$timestamp}_{$random}.{$extension}";
$file_path = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Generate URL for the uploaded video
    $video_url = "/uploads/social/posts/{$username}/videos/" . $filename;
    
    echo json_encode([
        'success' => true,
        'url' => $video_url,
        'filename' => $filename,
        'user_id' => $user_id,
        'file_size' => $file['size'],
        'message' => 'Video uploaded successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save video file'
    ]);
}
?>
