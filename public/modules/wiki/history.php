<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Article History';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    show_message('Article slug is required.', 'error');
    redirect('/');
}

// Get article by slug
$stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/');
}

$article_id = $article['id'];

// Pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)($_GET['per_page'] ?? 20);
$per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM article_versions WHERE article_id = ?");
$count_stmt->execute([$article_id]);
$total_versions = $count_stmt->fetchColumn();
$total_pages = ceil($total_versions / $per_page);

// Get versions with pagination
$stmt = $pdo->prepare("SELECT av.*, u.username, u.display_name 
    FROM article_versions av 
    JOIN users u ON av.created_by = u.id 
    WHERE av.article_id = ? 
    ORDER BY av.version_number DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$article_id, $per_page, $offset]);
$versions = $stmt->fetchAll();

include "../../includes/header.php";;
?>

<div class="article-history">
    <div class="history-header">
        <h1>History: <?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="history-actions">
            <a href="/wiki/<?php echo $article['slug']; ?>" class="btn">View Article</a>
            <?php if (is_logged_in() && (is_admin() || $article['author_id'] == $_SESSION['user_id'])): ?>
                <a href="/wiki/<?php echo $article['slug']; ?>/edit" class="btn">Edit Article</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Pagination Controls -->
    <div class="pagination-controls">
        <div class="pagination-info">
            <span>Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_versions); ?> of <?php echo $total_versions; ?> versions</span>
        </div>
        <div class="pagination-settings">
            <form method="GET" class="per-page-form">
                <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
                <label for="per_page">Show:</label>
                <select name="per_page" id="per_page" onchange="this.form.submit()">
                    <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </form>
        </div>
    </div>
    
    <div class="versions-list">
        <?php if (empty($versions)): ?>
            <div class="card">
                <p>No version history available for this article.</p>
            </div>
        <?php else: ?>
            <?php foreach ($versions as $version): ?>
            <div class="version-item-compact">
                <div class="version-info">
                    <div class="version-number">v<?php echo $version['version_number']; ?></div>
                    <div class="version-details">
                        <div class="version-author"><?php echo htmlspecialchars($version['display_name'] ?: $version['username']); ?></div>
                        <div class="version-date">
                            <?php echo format_date($version['created_at'], 'M j, Y g:i A'); ?>
                            <span class="version-relative">(<?php echo time_ago($version['created_at']); ?>)</span>
                        </div>
                        <?php if ($version['changes_summary']): ?>
                        <div class="version-changes"><?php echo htmlspecialchars($version['changes_summary']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="version-actions">
                    <a href="/wiki/<?php echo $article['slug']; ?>" class="btn-icon" title="View Current">
                        <i class="fas fa-eye"></i>
                    </a>
                    <?php if (is_logged_in() && (is_admin() || $version['created_by'] == $_SESSION['user_id'])): ?>
                        <a href="../restore_version.php?id=<?php echo $version['id']; ?>" 
                           class="btn-icon btn-warning"
                           title="Restore Version"
                           onclick="return confirm('Are you sure you want to restore this version?')">
                            <i class="fas fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination Navigation -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-nav">
        <?php if ($page > 1): ?>
            <a href="?slug=<?php echo urlencode($slug); ?>&page=1&per_page=<?php echo $per_page; ?>" class="btn btn-sm">First</a>
            <a href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page - 1; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Previous</a>
        <?php endif; ?>
        
        <span class="pagination-info">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
        </span>
        
        <?php if ($page < $total_pages): ?>
            <a href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page + 1; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Next</a>
            <a href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $total_pages; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Last</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.article-history {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1rem;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.history-header h1 {
    color: #2c3e50;
    margin: 0;
    font-size: 1.5rem;
}

.history-actions {
    display: flex;
    gap: 0.75rem;
}

/* Pagination Controls */
.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.pagination-info {
    font-size: 0.9rem;
    color: #666;
}

.pagination-settings {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.per-page-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.per-page-form label {
    font-size: 0.9rem;
    color: #666;
}

.per-page-form select {
    padding: 0.25rem 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
}

/* Compact Version Items */
.versions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.version-item-compact {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    border-left: 4px solid #3498db;
    transition: all 0.2s ease;
}

.version-item-compact:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left-color: #2980b9;
}

.version-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.version-number {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
    min-width: 40px;
    text-align: center;
}

.version-details {
    flex: 1;
}

.version-author {
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.9rem;
}

.version-date {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
}

.version-relative {
    color: #999;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.version-changes {
    font-size: 0.8rem;
    color: #666;
    margin-top: 0.25rem;
    font-style: italic;
}

.version-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    color: #666;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: #e9ecef;
    color: #495057;
}

.btn-icon.btn-warning {
    background: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.btn-icon.btn-warning:hover {
    background: #ffeaa7;
    color: #533f03;
}

/* Pagination Navigation */
.pagination-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.pagination-nav .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.pagination-nav .pagination-info {
    margin: 0 1rem;
    font-weight: 500;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.btn-warning {
    background: #ffc107;
    color: #000;
    border-color: #ffc107;
}

.btn-warning:hover {
    background: #e0a800;
    border-color: #d39e00;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .history-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .version-item-compact {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .version-info {
        width: 100%;
    }
    
    .version-actions {
        align-self: flex-end;
    }
    
    .pagination-nav {
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .pagination-nav .pagination-info {
        margin: 0.5rem 0;
        order: -1;
        width: 100%;
        text-align: center;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
