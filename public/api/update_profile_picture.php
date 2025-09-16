<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['profile_picture'];
$user_id = $_SESSION['user_id'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
    exit();
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit();
}

// Get username for user-specific directory
$username = $_SESSION['username'] ?? 'user_' . $user_id;

// Create user-specific uploads directory
$upload_dir = '../uploads/users/' . $username . '/images/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
$file_path = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit();
}

// Create different sizes of the profile picture
$sizes = [
    'small' => [50, 50],
    'medium' => [100, 100],
    'large' => [200, 200]
];

$resized_images = [];
foreach ($sizes as $size_name => $dimensions) {
    $resized_path = $upload_dir . $size_name . '_' . $filename;
    if (resizeImage($file_path, $resized_path, $dimensions[0], $dimensions[1])) {
        $resized_images[$size_name] = '/uploads/users/' . $username . '/images/' . $size_name . '_' . $filename;
    }
}

// Update user's avatar in database
$new_avatar_url = '/uploads/users/' . $username . '/images/' . $filename;
$stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
if ($stmt->execute([$new_avatar_url, $user_id])) {
    // Update session with new avatar
    $_SESSION['avatar'] = $new_avatar_url;
    // Set cache control headers to prevent caching
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Profile picture updated successfully',
        'new_avatar_url' => $new_avatar_url,
        'resized_images' => $resized_images,
        'timestamp' => time()
    ]);
} else {
    // Delete uploaded file if database update failed
    unlink($file_path);
    foreach ($resized_images as $resized_path) {
        $full_path = '../' . ltrim($resized_path, '/');
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update database']);
}

/**
 * Resize an image to specified dimensions
 */
function resizeImage($source_path, $dest_path, $width, $height) {
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }
    
    $source_width = $image_info[0];
    $source_height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // Create source image resource
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
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // Create destination image
    $dest_image = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG and GIF
    if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
        imagealphablending($dest_image, false);
        imagesavealpha($dest_image, true);
        $transparent = imagecolorallocatealpha($dest_image, 255, 255, 255, 127);
        imagefilledrectangle($dest_image, 0, 0, $width, $height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $width, $height, $source_width, $source_height);
    
    // Save resized image
    $result = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $result = imagejpeg($dest_image, $dest_path, 90);
            break;
        case 'image/png':
            $result = imagepng($dest_image, $dest_path, 9);
            break;
        case 'image/gif':
            $result = imagegif($dest_image, $dest_path);
            break;
        case 'image/webp':
            $result = imagewebp($dest_image, $dest_path, 90);
            break;
    }
    
    // Clean up
    imagedestroy($source_image);
    imagedestroy($dest_image);
    
    return $result;
}
?>
