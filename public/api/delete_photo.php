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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? '';
$type = $input['type'] ?? '';

if (empty($filename) || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing filename or type']);
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'user_' . $user_id;

try {
    $was_current = false;
    $new_avatar_url = null;
    
    if ($type === 'profile_picture') {
        // Check if this is the current profile picture
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_avatar = $stmt->fetchColumn();
        
        $file_url = '/uploads/users/' . $username . '/images/' . $filename;
        if ($current_avatar === $file_url) {
            $was_current = true;
        }
        
        // Delete the profile picture files (original and resized versions)
        $upload_dir = '../uploads/users/' . $username . '/images/';
        $files_to_delete = [
            $upload_dir . $filename,
            $upload_dir . 'small_' . $filename,
            $upload_dir . 'medium_' . $filename,
            $upload_dir . 'large_' . $filename
        ];
        
        foreach ($files_to_delete as $file_path) {
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // If this was the current profile picture, set avatar to null
        if ($was_current) {
            $stmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            $new_avatar_url = '';
        }
        
    } else {
        // Handle other photo types (user photos, cover photos, etc.)
        $upload_dir = '../uploads/users/' . $username . '/images/';
        $file_path = $upload_dir . $filename;
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Photo deleted successfully',
        'was_current' => $was_current,
        'new_avatar_url' => $new_avatar_url,
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete photo: ' . $e->getMessage()
    ]);
}
?>
