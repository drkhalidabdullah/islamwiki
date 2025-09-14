<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'Recent Changes';

// Get parameters
$namespace_id = $_GET['namespace'] ?? null;
$limit = min((int)($_GET['limit'] ?? 50), 200); // Max 200 results
$days = (int)($_GET['days'] ?? 7); // Default to last 7 days

// Get namespaces for filter
$namespaces = get_wiki_namespaces();

// Get recent changes
$recent_changes = get_recent_changes($limit, $namespace_id);

// Get statistics
$stats = get_wiki_statistics();

include "../../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_special_recent_changes.css">
<?php
?>

<div class="special-page-container">
    <div class="special-page-header">
        <h1>Recent Changes</h1>
        <p>Recent edits to all wiki pages</p>
    </div>

    <!-- Filters -->
    <div class="special-filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="namespace">Namespace:</label>
                <select name="namespace" id="namespace">
                    <option value="">All namespaces</option>
                    <?php foreach ($namespaces as $ns): ?>
                        <option value="<?php echo $ns['id']; ?>" 
                                <?php echo $namespace_id == $ns['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ns['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="limit">Results per page:</label>
                <select name="limit" id="limit">
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="special-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($stats['total_pages']); ?></span>
            <span class="stat-label">Total Pages</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($stats['total_edits']); ?></span>
            <span class="stat-label">Total Edits</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($stats['total_users']); ?></span>
            <span class="stat-label">Active Users</span>
        </div>
    </div>

    <!-- Recent Changes List -->
    <div class="recent-changes-list">
        <?php if (empty($recent_changes)): ?>
            <div class="no-results">
                <p>No recent changes found.</p>
            </div>
        <?php else: ?>
            <div class="changes-header">
                <h2>Recent Changes (Last <?php echo $days; ?> days)</h2>
                <div class="changes-count"><?php echo count($recent_changes); ?> changes</div>
            </div>
            
            <div class="changes-table">
                <div class="change-item header">
                    <div class="change-time">Time</div>
                    <div class="change-page">Page</div>
                    <div class="change-user">User</div>
                    <div class="change-summary">Summary</div>
                    <div class="change-actions">Actions</div>
                </div>
                
                <?php foreach ($recent_changes as $change): ?>
                <div class="change-item">
                    <div class="change-time">
                        <span class="time-ago" title="<?php echo format_date($change['last_edit_at']); ?>">
                            <?php echo time_ago($change['last_edit_at']); ?>
                        </span>
                    </div>
                    
                    <div class="change-page">
                        <a href="/wiki/<?php echo $change['slug']; ?>" class="page-link">
                            <?php if ($change['namespace_name'] !== 'Main'): ?>
                                <span class="namespace"><?php echo htmlspecialchars($change['namespace_display']); ?>:</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($change['title']); ?>
                        </a>
                        <?php if ($change['is_redirect']): ?>
                            <span class="redirect-indicator" title="Redirect">↪</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="change-user">
                        <a href="/user/<?php echo $change['username']; ?>" class="user-link">
                            <?php echo htmlspecialchars($change['display_name'] ?: $change['username']); ?>
                        </a>
                    </div>
                    
                    <div class="change-summary">
                        <?php if ($change['edit_count'] > 1): ?>
                            <span class="edit-count">(<?php echo $change['edit_count']; ?> edits)</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="change-actions">
                        <a href="/wiki/<?php echo $change['slug']; ?>" class="btn btn-sm" title="View">View</a>
                        <a href="/wiki/<?php echo $change['slug']; ?>/history" class="btn btn-sm" title="History">History</a>
                        <?php if (is_logged_in() && is_editor()): ?>
                            <a href="/wiki/<?php echo $change['slug']; ?>/edit" class="btn btn-sm" title="Edit">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navigation -->
    <div class="special-navigation">
        <div class="nav-links">
            <a href="/wiki/special/all_pages.php" class="nav-link">All Pages</a>
            <a href="/wiki/special/user_contributions.php" class="nav-link">User Contributions</a>
            <a href="/wiki/special/new_pages.php" class="nav-link">New Pages</a>
            <a href="/wiki/special/orphaned_pages.php" class="nav-link">Orphaned Pages</a>
        </div>
    </div>
</div>


<?php include "../../../includes/footer.php"; ?>
