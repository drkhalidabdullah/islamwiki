<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    error_log("Upload failed: User not logged in");
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

error_log("Upload request received from user: " . $_SESSION['username']);


/**
 * Scale image to fit within size limit
 */
function scaleImage($source_path, $destination_path, $mime_type, $max_size) {
    error_log("scaleImage called with: source=$source_path, dest=$destination_path, type=$mime_type, max_size=$max_size");
    
    // Get original image dimensions
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        error_log("Failed to get image info for: $source_path");
        return false;
    }
    
    error_log("Image info: " . print_r($image_info, true));
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    
    // Create image resource based on type
    error_log("Creating image resource for type: $mime_type");
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source_image = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            error_log("Unsupported image type: $mime_type");
            return false;
    }
    
    if (!$source_image) {
        error_log("Failed to create image resource from: $source_path");
        return false;
    }
    
    error_log("Successfully created image resource");
    
    // Calculate scaling factor to fit within size limit
    $current_size = filesize($source_path);
    error_log("Current file size: $current_size, max size: $max_size");
    
    $scale_factor = 1.0;
    if ($current_size > $max_size) {
        // Estimate scaling factor based on file size
        $scale_factor = sqrt($max_size / $current_size) * 0.8; // 0.8 for safety margin
        
        // Ensure minimum scale factor
        $scale_factor = max($scale_factor, 0.1);
        error_log("Calculated scale factor: $scale_factor");
    }
    
    // Calculate new dimensions
    $new_width = (int)($original_width * $scale_factor);
    $new_height = (int)($original_height * $scale_factor);
    
    // Ensure minimum dimensions
    $new_width = max($new_width, 100);
    $new_height = max($new_height, 100);
    
    error_log("New dimensions: {$new_width}x{$new_height} (original: {$original_width}x{$original_height})");
    
    // Create scaled image
    $scaled_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($scaled_image, false);
        imagesavealpha($scaled_image, true);
        $transparent = imagecolorallocatealpha($scaled_image, 255, 255, 255, 127);
        imagefilledrectangle($scaled_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Scale the image
    imagecopyresampled(
        $scaled_image, $source_image,
        0, 0, 0, 0,
        $new_width, $new_height,
        $original_width, $original_height
    );
    
    // Save the scaled image
    error_log("Saving scaled image to: $destination_path");
    $saved = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $saved = imagejpeg($scaled_image, $destination_path, 85); // 85% quality
            break;
        case 'image/png':
            $saved = imagepng($scaled_image, $destination_path, 8); // 8 compression level
            break;
        case 'image/gif':
            $saved = imagegif($scaled_image, $destination_path);
            break;
        case 'image/webp':
            $saved = imagewebp($scaled_image, $destination_path, 85); // 85% quality
            break;
    }
    
    error_log("Save result: " . ($saved ? 'success' : 'failed'));
    
    // Clean up memory
    imagedestroy($source_image);
    imagedestroy($scaled_image);
    
    return $saved;
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in. Please log in first.']);
    exit();
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if image was uploaded
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No image file in request']);
    exit();
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error_message = $error_messages[$_FILES['image']['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $error_message]);
    exit();
}

$file = $_FILES['image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
    exit();
}

// Validate file size (max 2MB - PHP limit)
$max_size = 2 * 1024 * 1024; // 2MB
$needs_scaling = $file['size'] > $max_size;

// Create user-specific social uploads directory
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$upload_dir = __DIR__ . "/../../uploads/social/posts/{$username}/";

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
$filename = "post_{$timestamp}_{$random}.{$extension}";
$file_path = $upload_dir . $filename;

// Process image (scale if needed)
$processed = false;
try {
    if ($needs_scaling) {
        error_log("Attempting to scale image: " . $file['tmp_name'] . " to " . $file_path);
        $processed = scaleImage($file['tmp_name'], $file_path, $file['type'], $max_size);
        error_log("Scaling result: " . ($processed ? 'success' : 'failed'));
        
        // If scaling failed, try to just move the file anyway
        if (!$processed) {
            error_log("Scaling failed, attempting to move file as-is");
            $processed = move_uploaded_file($file['tmp_name'], $file_path);
        }
    } else {
        error_log("Moving uploaded file: " . $file['tmp_name'] . " to " . $file_path);
        $processed = move_uploaded_file($file['tmp_name'], $file_path);
        error_log("Move result: " . ($processed ? 'success' : 'failed'));
    }
} catch (Exception $e) {
    error_log("Exception during image processing: " . $e->getMessage());
    $processed = false;
}

if ($processed) {
    // Generate URL for the uploaded image
    $image_url = "/uploads/social/posts/{$username}/" . $filename;
    
    $final_size = filesize($file_path);
    $was_scaled = $needs_scaling;
    
    echo json_encode([
        'success' => true,
        'url' => $image_url,
        'filename' => $filename,
        'user_id' => $user_id,
        'original_size' => $file['size'],
        'final_size' => $final_size,
        'was_scaled' => $was_scaled,
        'message' => $was_scaled ? 'Image uploaded and automatically scaled to fit size limit' : 'Image uploaded successfully'
    ]);
} else {
    // Get more specific error information
    $error = error_get_last();
    $error_message = 'Failed to save image';
    if ($error) {
        $error_message .= ': ' . $error['message'];
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $error_message,
        'debug' => [
            'tmp_name' => $file['tmp_name'],
            'file_path' => $file_path,
            'upload_dir_exists' => is_dir($upload_dir),
            'upload_dir_writable' => is_writable($upload_dir),
            'file_exists' => file_exists($file['tmp_name'])
        ]
    ]);
}
?>
