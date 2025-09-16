<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'My Watchlist';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = (int)($_GET['per_page'] ?? 20);
$per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20;
$offset = ($page - 1) * $per_page;

// Get user's watchlist
$watchlist = get_user_watchlist($_SESSION['user_id'], $per_page, $offset);

// Get total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_watchlists WHERE user_id = ?");
$count_stmt->execute([$_SESSION['user_id']]);
$total_watched = $count_stmt->fetchColumn();
$total_pages = ceil($total_watched / $per_page);

// Get recent changes to watched articles
$recent_changes = get_recent_watchlist_changes($_SESSION['user_id'], 10);

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/user_watchlist.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/watchlist.css">
<?php
?>

<div class="watchlist-page">
    <div class="page-header">
        <h1>My Watchlist</h1>
        <p class="page-description">Articles you're watching for changes</p>
    </div>
    
    <?php if (!empty($recent_changes)): ?>
    <div class="recent-changes">
        <h2>Recent Changes</h2>
        <div class="changes-list">
            <?php foreach ($recent_changes as $change): ?>
            <div class="change-item">
                <div class="change-content">
                    <div class="change-header">
                        <h4 class="change-title">
                            <a href="/wiki/<?php echo $change['slug']; ?>"><?php echo htmlspecialchars($change['title']); ?></a>
                        </h4>
                        <div class="change-actions">
                            <a href="/wiki/<?php echo $change['slug']; ?>/history" class="btn-icon" title="View History">
                                <i class="iw iw-history"></i>
                            </a>
                        </div>
                    </div>
                    <div class="change-meta">
                        <div class="change-info">
                            <span class="change-author">Edited by <?php echo htmlspecialchars($change['display_name'] ?: $change['username']); ?></span>
                            <span class="change-separator">•</span>
                            <span class="change-version">Version <?php echo $change['version_number']; ?></span>
                        </div>
                        <div class="change-dates">
                            <span class="change-time"><?php echo time_ago($change['created_at']); ?></span>
                        </div>
                    </div>
                    <?php if ($change['changes_summary']): ?>
                    <div class="change-summary">
                        <i class="iw iw-edit"></i>
                        <span><?php echo htmlspecialchars($change['changes_summary']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Pagination Controls -->
    <div class="pagination-controls">
        <div class="pagination-info">
            <span>Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $total_watched); ?> of <?php echo $total_watched; ?> watched articles</span>
        </div>
        <div class="pagination-settings">
            <form method="GET" class="per-page-form">
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
    
    <div class="watchlist-content">
        <?php if (empty($watchlist)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="iw iw-eye-slash"></i>
                </div>
                <h3>No articles in your watchlist</h3>
                <p>Start watching articles by clicking the eye icon on any article page.</p>
                <a href="/wiki" class="btn btn-primary">Browse Articles</a>
            </div>
        <?php else: ?>
            <div class="watchlist-items">
                <?php foreach ($watchlist as $item): ?>
                <div class="watchlist-item">
                    <div class="item-content">
                        <div class="item-header">
                            <h3 class="item-title">
                                <a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                            </h3>
                            <div class="item-actions">
                                <a href="/wiki/<?php echo $item['slug']; ?>/history" class="btn-icon" title="View History">
                                    <i class="iw iw-history"></i>
                                </a>
                                <button class="btn-icon btn-remove" 
                                        onclick="removeFromWatchlist(<?php echo $item['article_id']; ?>, this)"
                                        title="Remove from watchlist">
                                    <i class="iw iw-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="item-meta">
                            <div class="item-info">
                                <span class="item-namespace"><?php echo htmlspecialchars($item['namespace_name'] ?? 'Main'); ?></span>
                                <span class="item-separator">•</span>
                                <span class="item-author">Last edited by <?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?></span>
                            </div>
                            <div class="item-dates">
                                <span class="item-updated">Updated <?php echo time_ago($item['last_edit_at']); ?></span>
                                <span class="item-watched">Watched <?php echo time_ago($item['created_at']); ?></span>
                            </div>
                        </div>
                        
                        <?php if ($item['notify_email']): ?>
                        <div class="item-notifications">
                            <i class="iw iw-bell"></i>
                            <span>Email notifications enabled</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination Navigation -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-nav">
        <?php if ($page > 1): ?>
            <a href="?page=1&per_page=<?php echo $per_page; ?>" class="btn btn-sm">First</a>
            <a href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Previous</a>
        <?php endif; ?>
        
        <span class="pagination-info">
            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
        </span>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Next</a>
            <a href="?page=<?php echo $total_pages; ?>&per_page=<?php echo $per_page; ?>" class="btn btn-sm">Last</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>



<?php include "../../includes/footer.php"; ?>
