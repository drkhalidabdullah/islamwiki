<?php
// Image serving script with proper cache control
$image_path = $_GET['path'] ?? '';

// Security check - only allow images from uploads directory
if (empty($image_path) || !str_starts_with($image_path, '/uploads/')) {
    http_response_code(404);
    exit();
}

$full_path = '../' . ltrim($image_path, '/');

// Check if file exists
if (!file_exists($full_path)) {
    http_response_code(404);
    exit();
}

// Get file info
$mime_type = mime_content_type($full_path);
$file_size = filesize($full_path);
$last_modified = filemtime($full_path);

// Set headers
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . $file_size);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
header('ETag: "' . md5($last_modified . $file_size) . '"');

// Check if client has cached version
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    $if_modified_since = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
    $if_none_match = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    
    if ($if_modified_since === gmdate('D, d M Y H:i:s', $last_modified) . ' GMT' ||
        $if_none_match === '"' . md5($last_modified . $file_size) . '"') {
        http_response_code(304);
        exit();
    }
}

// Output the file
readfile($full_path);
?>
