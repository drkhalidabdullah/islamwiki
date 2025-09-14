<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Files';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$success = '';
$error = '';

// Handle file actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_file') {
        $file_id = (int)($_POST['file_id'] ?? 0);
        
        if ($file_id > 0) {
            try {
                // Get file info first
                $stmt = $pdo->prepare("SELECT * FROM uploaded_files WHERE id = ?");
                $stmt->execute([$file_id]);
                $file = $stmt->fetch();
                
                if ($file) {
                    // Delete physical file
                    $file_path = $file['file_path'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    
                    // Delete database record
                    $stmt = $pdo->prepare("DELETE FROM uploaded_files WHERE id = ?");
                    if ($stmt->execute([$file_id])) {
                        $success = 'File deleted successfully.';
                        log_activity('file_deleted', "Deleted file: " . $file['original_name']);
                    } else {
                        $error = 'Failed to delete file record.';
                    }
                } else {
                    $error = 'File not found.';
                }
            } catch (Exception $e) {
                $error = 'Error deleting file: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'cleanup_orphaned') {
        try {
            // Find orphaned files (files that exist on disk but not in database)
            $upload_dir = '/var/www/html/public/uploads/';
            $orphaned_count = 0;
            
            if (is_dir($upload_dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $relative_path = str_replace($upload_dir, '', $file->getPathname());
                        $stmt = $pdo->prepare("SELECT id FROM uploaded_files WHERE file_path LIKE ?");
                        $stmt->execute(['%' . $relative_path]);
                        
                        if (!$stmt->fetch()) {
                            unlink($file->getPathname());
                            $orphaned_count++;
                        }
                    }
                }
            }
            
            $success = "Cleaned up $orphaned_count orphaned files.";
            log_activity('files_cleanup', "Cleaned up $orphaned_count orphaned files");
        } catch (Exception $e) {
            $error = 'Error cleaning up files: ' . $e->getMessage();
        }
    }
}

// Get file statistics
$stats = [];

// Total files
$stmt = $pdo->query("SELECT COUNT(*) as count FROM uploaded_files");
$stats['total_files'] = $stmt->fetch()['count'];

// Total size
$stmt = $pdo->query("SELECT SUM(file_size) as total_size FROM uploaded_files");
$stats['total_size'] = $stmt->fetch()['total_size'] ?: 0;

// Files by type
$stmt = $pdo->query("
    SELECT file_type, COUNT(*) as count, SUM(file_size) as total_size 
    FROM uploaded_files 
    GROUP BY file_type 
    ORDER BY count DESC
");
$files_by_type = $stmt->fetchAll();

// Recent files
$stmt = $pdo->query("
    SELECT uf.*, u.username 
    FROM uploaded_files uf 
    LEFT JOIN users u ON uf.uploaded_by = u.id 
    ORDER BY uf.uploaded_at DESC 
    LIMIT 20
");
$recent_files = $stmt->fetchAll();

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/manage_files.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-folder-open"></i> Manage Files</h1>
        <p>View and manage uploaded files</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- File Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_files']); ?></h3>
                <p>Total Files</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <h3><?php echo format_file_size($stats['total_size']); ?></h3>
                <p>Total Size</p>
            </div>
        </div>
    </div>

    <!-- File Actions -->
    <div class="card">
        <h3>File Management Actions</h3>
        <div class="action-buttons">
            <form method="POST" style="display: inline;" onsubmit="return confirm('This will delete all orphaned files. Continue?')">
                <input type="hidden" name="action" value="cleanup_orphaned">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-broom"></i> Cleanup Orphaned Files
                </button>
            </form>
        </div>
    </div>

    <!-- Files by Type -->
    <div class="card">
        <h3>Files by Type</h3>
        <?php if (!empty($files_by_type)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>File Type</th>
                            <th>Count</th>
                            <th>Total Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files_by_type as $type): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file<?php echo get_file_icon($type['file_type']); ?>"></i>
                                <?php echo htmlspecialchars($type['file_type'] ?: 'Unknown'); ?>
                            </td>
                            <td><?php echo number_format($type['count']); ?></td>
                            <td><?php echo format_file_size($type['total_size']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No files found.</p>
        <?php endif; ?>
    </div>

    <!-- Recent Files -->
    <div class="card">
        <h3>Recent Files</h3>
        <?php if (!empty($recent_files)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Uploaded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_files as $file): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file<?php echo get_file_icon($file['file_type']); ?>"></i>
                                <a href="<?php echo htmlspecialchars($file['file_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($file['original_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($file['file_type'] ?: 'Unknown'); ?></td>
                            <td><?php echo format_file_size($file['file_size']); ?></td>
                            <td><?php echo htmlspecialchars($file['username'] ?: 'System'); ?></td>
                            <td><?php echo format_date($file['uploaded_at']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($file['file_url']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this file?')">
                                    <input type="hidden" name="action" value="delete_file">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No recent files found.</p>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <a href="/admin" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>


<?php
// Helper functions
function format_file_size($bytes) {
    if ($bytes == 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

function get_file_icon($file_type) {
    $icons = [
        'image/jpeg' => '-image',
        'image/png' => '-image',
        'image/gif' => '-image',
        'image/webp' => '-image',
        'application/pdf' => '-pdf',
        'text/plain' => '-alt',
        'application/zip' => '-archive',
        'application/x-rar' => '-archive',
        'video/mp4' => '-video',
        'audio/mpeg' => '-audio',
    ];
    
    return $icons[$file_type] ?? '';
}

include "../../includes/footer.php";
?>
