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
                                <i class="fas fa-history"></i>
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
                        <i class="fas fa-edit"></i>
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
                    <i class="fas fa-eye-slash"></i>
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
                                    <i class="fas fa-history"></i>
                                </a>
                                <button class="btn-icon btn-remove" 
                                        onclick="removeFromWatchlist(<?php echo $item['article_id']; ?>, this)"
                                        title="Remove from watchlist">
                                    <i class="fas fa-eye-slash"></i>
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
                            <i class="fas fa-bell"></i>
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

<style>
.watchlist-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1rem;
}

.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
}

.page-description {
    color: #666;
    margin: 0;
    font-size: 1.1rem;
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

/* Recent Changes */
.recent-changes {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.recent-changes h2 {
    color: #2c3e50;
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.recent-changes h2::before {
    content: "🔔";
    font-size: 1.2rem;
}

.changes-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.change-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    transition: all 0.2s ease;
    border-left: 4px solid #28a745;
}

.change-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left-color: #20c997;
}

.change-content {
    width: 100%;
}

.change-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.change-title {
    margin: 0;
    flex: 1;
}

.change-title a {
    color: #2c3e50;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 600;
}

.change-title a:hover {
    color: #28a745;
    text-decoration: underline;
}

.change-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-left: 1rem;
}

.change-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.change-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #666;
}

.change-separator {
    color: #ccc;
}

.change-dates {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.8rem;
    color: #999;
}

.change-summary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #666;
    font-style: italic;
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 4px;
    border-left: 3px solid #28a745;
}

.change-summary i {
    color: #28a745;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.empty-icon {
    font-size: 3rem;
    color: #ccc;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #666;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #999;
    margin-bottom: 1.5rem;
}

/* Watchlist Items */
.watchlist-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.watchlist-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.2s ease;
    border-left: 4px solid #3498db;
}

.watchlist-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left-color: #2980b9;
}

.item-content {
    width: 100%;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.item-title {
    margin: 0;
    flex: 1;
}

.item-title a {
    color: #2c3e50;
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
}

.item-title a:hover {
    color: #3498db;
    text-decoration: underline;
}

.item-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-left: 1rem;
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
    cursor: pointer;
}

.btn-icon:hover {
    background: #e9ecef;
    color: #495057;
}

.btn-remove:hover {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.item-namespace {
    background: #e9ecef;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.item-separator {
    color: #ccc;
}

.item-dates {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.85rem;
    color: #999;
}

.item-notifications {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #28a745;
    margin-top: 0.5rem;
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

/* Mobile Responsive */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .item-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .item-actions {
        margin-left: 0;
        align-self: flex-end;
    }
    
    .item-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .item-dates {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .change-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .change-actions {
        margin-left: 0;
        align-self: flex-end;
    }
    
    .change-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .change-dates {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
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

<script>
function removeFromWatchlist(articleId, button) {
    if (!confirm('Remove this article from your watchlist?')) {
        return;
    }
    
    fetch('/api/ajax/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            article_id: articleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the item from the page
            button.closest('.watchlist-item').remove();
            showToast('Removed from watchlist', 'success');
            
            // Check if watchlist is now empty
            const remainingItems = document.querySelectorAll('.watchlist-item');
            if (remainingItems.length === 0) {
                location.reload(); // Reload to show empty state
            }
        } else {
            showToast(data.message || 'Error removing from watchlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing from watchlist', 'error');
    });
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        padding: 12px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        font-size: 14px;
        max-width: 300px;
    `;
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}
</script>

<?php include "../../includes/footer.php"; ?>
