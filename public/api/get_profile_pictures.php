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

$user_id = $_SESSION['user_id'];

try {
    // Get user's current profile picture
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_avatar = $stmt->fetchColumn();
    
    // Get username for user-specific directory
    $username = $_SESSION['username'] ?? 'user_' . $user_id;
    $upload_dir = '../uploads/users/' . $username . '/images/';
    
    $photos = [];
    
    // Scan the uploads directory for all profile pictures
    if (is_dir($upload_dir)) {
        $files = scandir($upload_dir);
        $profile_pictures = [];
        
        foreach ($files as $file) {
            // Look for files that match the profile picture pattern (profile_USERID_TIMESTAMP.jpg)
            if (preg_match('/^profile_' . $user_id . '_\d+\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $file_path = $upload_dir . $file;
                $file_url = '/uploads/users/' . $username . '/images/' . $file;
                
                // Get file modification time for sorting
                $file_time = filemtime($file_path);
                
                $profile_pictures[] = [
                    'filename' => $file,
                    'url' => $file_url,
                    'timestamp' => $file_time,
                    'is_current' => ($file_url === $current_avatar)
                ];
            }
        }
        
        // Sort by timestamp (newest first)
        usort($profile_pictures, function($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });
        
        // Format for frontend
        foreach ($profile_pictures as $pic) {
            $photos[] = [
                'url' => $pic['url'],
                'is_current' => $pic['is_current'],
                'timestamp' => $pic['timestamp'],
                'filename' => $pic['filename']
            ];
        }
    }
    
    // If no profile pictures found in directory, add current avatar if it exists
    if (empty($photos) && $current_avatar) {
        $photos[] = [
            'url' => $current_avatar,
            'is_current' => true,
            'timestamp' => time(),
            'filename' => basename($current_avatar)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'photos' => $photos,
        'count' => count($photos)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load profile pictures: ' . $e->getMessage()
    ]);
}
?>
