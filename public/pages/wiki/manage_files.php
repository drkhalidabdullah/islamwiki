<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Manage Files';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = '';

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $file_id = (int)($_POST['file_id'] ?? 0);
    
    if ($file_id > 0) {
        try {
            // Get file info
            $stmt = $pdo->prepare("SELECT * FROM wiki_files WHERE id = ?");
            $stmt->execute([$file_id]);
            $file = $stmt->fetch();
            
            if ($file) {
                // Delete physical file
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
                
                // Delete database record
                $stmt = $pdo->prepare("DELETE FROM wiki_files WHERE id = ?");
                $stmt->execute([$file_id]);
                
                $success = 'File deleted successfully.';
            } else {
                $errors[] = 'File not found.';
            }
        } catch (Exception $e) {
            $errors[] = 'Error deleting file: ' . $e->getMessage();
        }
    }
}

// Get all files with pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$where_clause = '';
$params = [];

if ($search) {
    $where_clause = "WHERE original_name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get total count
$count_sql = "SELECT COUNT(*) FROM wiki_files $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_files = $stmt->fetchColumn();

// Get files
$sql = "
    SELECT f.*, u.username, u.display_name 
    FROM wiki_files f
    JOIN users u ON f.uploaded_by = u.id
    $where_clause
    ORDER BY f.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll();

$total_pages = ceil($total_files / $limit);

include '../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Manage Files</h1>
        <div class="admin-actions">
            <a href="/wiki/upload" class="btn btn-primary">Upload New File</a>
            <a href="/admin" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
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

    <!-- Search and Filters -->
    <div class="card">
        <h2>Search Files</h2>
        <form method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by filename or description...">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if ($search): ?>
                    <a href="manage_files.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Files List -->
    <div class="card">
        <h2>All Files (<?php echo number_format($total_files); ?> total)</h2>
        
        <?php if (empty($files)): ?>
            <p>No files found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Type</th>
                            <th>Uploaded By</th>
                            <th>Upload Date</th>
                            <th>Usage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td>
                                    <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                        <img src="/uploads/<?php echo htmlspecialchars($file['filename']); ?>" 
                                             alt="Preview" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div class="file-icon-small">
                                            <?php
                                            $icon = '📄';
                                            if (strpos($file['mime_type'], 'pdf') !== false) $icon = '📕';
                                            elseif (strpos($file['mime_type'], 'word') !== false) $icon = '📘';
                                            elseif (strpos($file['mime_type'], 'text') !== false) $icon = '📄';
                                            echo $icon;
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($file['original_name']); ?></strong>
                                    <?php if ($file['description']): ?>
                                        <br><small><?php echo htmlspecialchars($file['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_file_size($file['file_size']); ?></td>
                                <td>
                                    <span class="file-type"><?php echo htmlspecialchars($file['mime_type']); ?></span>
                                    <?php if ($file['width'] && $file['height']): ?>
                                        <br><small><?php echo $file['width']; ?>×<?php echo $file['height']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/user/<?php echo htmlspecialchars($file['username']); ?>">
                                        <?php echo htmlspecialchars($file['display_name'] ?: $file['username']); ?>
                                    </a>
                                </td>
                                <td><?php echo format_date($file['created_at']); ?></td>
                                <td>
                                    <span class="usage-count"><?php echo $file['usage_count']; ?> uses</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="/uploads/<?php echo htmlspecialchars($file['filename']); ?>" 
                                           target="_blank" class="btn btn-sm btn-primary">View</a>
                                        <button onclick="copyWikiLink('<?php echo htmlspecialchars($file['filename']); ?>')" 
                                                class="btn btn-sm btn-secondary">Copy Link</button>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this file? This action cannot be undone.')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.admin-header h1 {
    margin: 0;
    color: #2c3e50;
}

.admin-actions {
    display: flex;
    gap: 1rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.search-form {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.search-form .form-group {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.search-form input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
    vertical-align: top;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table tr:hover {
    background: #f8f9fa;
}

.file-icon-small {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 1.5rem;
}

.file-type {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
}

.usage-count {
    color: #6c757d;
    font-size: 0.875rem;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
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

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.page-info {
    color: #6c757d;
    font-weight: 500;
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
