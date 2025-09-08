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
            $upload_dir = '../../uploads/';
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
                                <img src="/uploads/<?php echo htmlspecialchars($file['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($file['original_name']); ?>"
                                     style="max-width: 100%; max-height: 150px; object-fit: cover;">
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
                                <a href="/uploads/<?php echo htmlspecialchars($file['filename']); ?>" 
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

<script>
function copyWikiLink(filename) {
    const wikiLink = `[[File:${filename}]]`;
    navigator.clipboard.writeText(wikiLink).then(() => {
        alert('Wiki link copied to clipboard: ' + wikiLink);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = wikiLink;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Wiki link copied to clipboard: ' + wikiLink);
    });
}
</script>

<style>
.upload-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #666;
    font-size: 1.1rem;
}

.upload-form-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 3rem;
}

.upload-form {
    display: grid;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group small {
    margin-top: 0.25rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.file-management {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 2rem;
}

.file-management h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.file-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.file-card:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.file-preview {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.file-icon {
    font-size: 3rem;
    color: #6c757d;
}

.file-info {
    padding: 1rem;
}

.file-info h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1rem;
    word-break: break-word;
}

.file-meta {
    margin: 0 0 0.5rem 0;
    color: #6c757d;
    font-size: 0.875rem;
}

.file-description {
    margin: 0 0 0.5rem 0;
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.4;
}

.file-license {
    margin: 0 0 1rem 0;
    color: #6c757d;
    font-size: 0.875rem;
    font-style: italic;
}

.file-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}
</style>

<?php include '../../includes/footer.php'; ?>
