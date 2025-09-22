<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get current site logo
$logo_data = get_system_setting('site_logo', null);

if ($logo_data && is_array($logo_data)) {
    $logo_url = '/uploads/site_logos/' . $logo_data['filename'];
    
    // Check if file actually exists
    $file_path = '../../uploads/site_logos/' . $logo_data['filename'];
    if (file_exists($file_path)) {
        echo json_encode([
            'success' => true,
            'logo' => [
                'url' => $logo_url,
                'filename' => $logo_data['filename'],
                'original_name' => $logo_data['original_name'] ?? '',
                'file_size' => $logo_data['file_size'] ?? 0,
                'file_type' => $logo_data['file_type'] ?? '',
                'dimensions' => $logo_data['dimensions'] ?? null,
                'uploaded_at' => $logo_data['uploaded_at'] ?? ''
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Logo file not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No site logo set']);
}
?>
