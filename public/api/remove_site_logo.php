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

try {
    global $pdo;
    
    // Get current logo data
    $logo_data = get_system_setting('site_logo', null);
    
    if ($logo_data && is_array($logo_data) && isset($logo_data['filename'])) {
        // Delete the physical file
        $file_path = '../../uploads/site_logos/' . $logo_data['filename'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Remove from database
        $stmt = $pdo->prepare("DELETE FROM system_settings WHERE `key` = 'site_logo'");
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Site logo removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No site logo found to remove']);
    }
    
} catch (Exception $e) {
    error_log("Site logo removal error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to remove site logo']);
}
?>
