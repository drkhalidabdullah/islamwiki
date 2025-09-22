<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['site_logo']) || $_FILES['site_logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['site_logo'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
$file_type = mime_content_type($file['tmp_name']);

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and SVG files are allowed.']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit;
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'site_logo_' . time() . '_' . uniqid() . '.' . $file_extension;
$upload_path = '../../uploads/site_logos/' . $filename;

// Create uploads directory if it doesn't exist
$upload_dir = '../../uploads/site_logos/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Get file dimensions for images
    $dimensions = null;
    if (in_array($file_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
        $image_info = getimagesize($upload_path);
        if ($image_info) {
            $dimensions = [
                'width' => $image_info[0],
                'height' => $image_info[1]
            ];
        }
    }
    
    // Save logo info to database
    try {
        global $pdo;
        
        // First, remove any existing site logo setting
        $stmt = $pdo->prepare("DELETE FROM system_settings WHERE `key` = 'site_logo'");
        $stmt->execute();
        
        // Insert new logo setting
        $logo_data = [
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $file_type,
            'dimensions' => $dimensions,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (`key`, `value`, `type`, `description`) 
            VALUES ('site_logo', ?, 'json', 'Site logo file information')
        ");
        $stmt->execute([json_encode($logo_data)]);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Site logo uploaded successfully',
            'filename' => $filename,
            'url' => '/uploads/site_logos/' . $filename,
            'dimensions' => $dimensions
        ]);
        
    } catch (Exception $e) {
        // Delete the uploaded file if database save fails
        unlink($upload_path);
        error_log("Site logo upload error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save logo information to database']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}
?>
