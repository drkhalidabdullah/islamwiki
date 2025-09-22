<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Upload File';
require_login();

$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize_input($_POST['description'] ?? '');
    $license = sanitize_input($_POST['license'] ?? '');
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        
        // Validate file
        $allowed_types = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf', 'text/plain', 'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'File type not allowed. Allowed types: JPEG, PNG, GIF, WebP, SVG, PDF, TXT, DOC, DOCX';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'File size too large. Maximum size is 10MB.';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $upload_dir = '../../uploads/wiki/files/';
            $file_path = $upload_dir . $filename;
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Get image dimensions if it's an image
                $width = null;
                $height = null;
                if (strpos($file['type'], 'image/') === 0) {
                    $image_info = getimagesize($file_path);
                    if ($image_info) {
                        $width = $image_info[0];
                        $height = $image_info[1];
                    }
                }
                
                // Save to database
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO wiki_files 
                        (filename, original_name, file_path, file_size, mime_type, width, height, description, license, uploaded_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $filename,
                        $file['name'],
                        $file_path,
                        $file['size'],
                        $file['type'],
                        $width,
                        $height,
                        $description,
                        $license,
                        $_SESSION['user_id']
                    ]);
                    
                    $success = 'File uploaded successfully!';
                } catch (Exception $e) {
                    $errors[] = 'Error saving file information: ' . $e->getMessage();
                    // Clean up uploaded file
                    unlink($file_path);
                }
            } else {
                $errors[] = 'Error uploading file.';
            }
        }
    } else {
        $errors[] = 'No file selected or upload error.';
    }
}

include '../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/wiki_upload_file.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_upload_file.css">
<?php
?>

<div class="upload-page">
    <div class="page-header">
        <h1>Upload File</h1>
        <p>Upload images, documents, and other media files to the wiki</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="upload-form-container">
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="file">Select File:</label>
                <input type="file" id="file" name="file" required 
                       accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.txt,.doc,.docx">
                <small>Maximum file size: 10MB. Allowed types: Images (JPEG, PNG, GIF, WebP, SVG), Documents (PDF, TXT, DOC, DOCX)</small>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optional):</label>
                <textarea id="description" name="description" rows="3" 
                          placeholder="Describe the file content..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="license">License (optional):</label>
                <select id="license" name="license">
                    <option value="">Select a license...</option>
                    <option value="Public Domain">Public Domain</option>
                    <option value="CC BY">Creative Commons Attribution</option>
                    <option value="CC BY-SA">Creative Commons Attribution-ShareAlike</option>
                    <option value="CC BY-NC">Creative Commons Attribution-NonCommercial</option>
                    <option value="CC BY-NC-SA">Creative Commons Attribution-NonCommercial-ShareAlike</option>
                    <option value="Fair Use">Fair Use</option>
                    <option value="All Rights Reserved">All Rights Reserved</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Upload File</button>
        </form>
    </div>

    <!-- File Management Section -->
    <div class="file-management">
        <h2>Your Uploaded Files</h2>
        
        <?php
        // Get user's uploaded files
        $stmt = $pdo->prepare("
            SELECT * FROM wiki_files 
            WHERE uploaded_by = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user_files = $stmt->fetchAll();
        ?>
        
        <?php if (empty($user_files)): ?>
            <p>You haven't uploaded any files yet.</p>
        <?php else: ?>
            <div class="files-grid">
                <?php foreach ($user_files as $file): ?>
                    <div class="file-card">
                        <div class="file-preview">
                            <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                <img src="/uploads/wiki/files/<?php echo htmlspecialchars($file['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($file['original_name']); ?>"
                                     >
                            <?php else: ?>
                                <div class="file-icon">
                                    <?php
                                    $icon = '📄';
                                    if (strpos($file['mime_type'], 'pdf') !== false) $icon = '📕';
                                    elseif (strpos($file['mime_type'], 'word') !== false) $icon = '📘';
                                    elseif (strpos($file['mime_type'], 'text') !== false) $icon = '📄';
                                    echo $icon;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="file-info">
                            <h4><?php echo htmlspecialchars($file['original_name']); ?></h4>
                            <p class="file-meta">
                                <?php echo format_file_size($file['file_size']); ?> • 
                                <?php echo format_date($file['created_at']); ?>
                            </p>
                            <?php if ($file['description']): ?>
                                <p class="file-description"><?php echo htmlspecialchars($file['description']); ?></p>
                            <?php endif; ?>
                            <?php if ($file['license']): ?>
                                <p class="file-license">License: <?php echo htmlspecialchars($file['license']); ?></p>
                            <?php endif; ?>
                            
                            <div class="file-actions">
                                <a href="/uploads/wiki/files/<?php echo htmlspecialchars($file['filename']); ?>" 
                                   target="_blank" class="btn btn-sm btn-primary">View</a>
                                <button onclick="copyWikiLink('<?php echo htmlspecialchars($file['filename']); ?>')" 
                                        class="btn btn-sm btn-secondary">Copy Wiki Link</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
