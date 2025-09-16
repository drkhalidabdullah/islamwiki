<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check maintenance mode
check_maintenance_mode();

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
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_history.css">
<?php
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
                        <i class="iw iw-eye"></i>
                    </a>
                    <?php if (is_logged_in() && (is_admin() || $version['created_by'] == $_SESSION['user_id'])): ?>
                        <a href="../restore_version.php?id=<?php echo $version['id']; ?>" 
                           class="btn-icon btn-warning"
                           title="Restore Version"
                           onclick="return confirm('Are you sure you want to restore this version?')">
                            <i class="iw iw-undo"></i>
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


<?php include "../../includes/footer.php";; ?>
