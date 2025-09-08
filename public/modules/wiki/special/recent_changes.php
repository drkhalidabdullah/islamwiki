<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

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

<style>
.special-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.special-page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.special-page-header h1 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.special-filters {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.filter-form {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-weight: 600;
    color: #2c3e50;
}

.filter-group select {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    min-width: 150px;
}

.special-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.recent-changes-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.changes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.changes-header h2 {
    margin: 0;
    color: #2c3e50;
}

.changes-count {
    color: #6c757d;
    font-size: 0.9rem;
}

.changes-table {
    display: flex;
    flex-direction: column;
}

.change-item {
    display: grid;
    grid-template-columns: 120px 1fr 150px 200px 150px;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    align-items: center;
}

.change-item.header {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
}

.change-item:last-child {
    border-bottom: none;
}

.change-time {
    font-size: 0.9rem;
    color: #6c757d;
}

.change-page {
    font-weight: 500;
}

.page-link {
    color: #007bff;
    text-decoration: none;
}

.page-link:hover {
    text-decoration: underline;
}

.namespace {
    color: #6c757d;
    font-weight: normal;
}

.redirect-indicator {
    color: #ffc107;
    margin-left: 0.5rem;
}

.change-user {
    font-size: 0.9rem;
}

.user-link {
    color: #007bff;
    text-decoration: none;
}

.user-link:hover {
    text-decoration: underline;
}

.change-summary {
    font-size: 0.9rem;
    color: #6c757d;
}

.edit-count {
    font-style: italic;
}

.change-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-sm:hover {
    transform: translateY(-1px);
}

.special-navigation {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.nav-links {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.nav-link {
    color: #007bff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border: 1px solid #007bff;
    border-radius: 4px;
    transition: all 0.3s;
}

.nav-link:hover {
    background: #007bff;
    color: white;
}

.no-results {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .change-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .change-item.header {
        display: none;
    }
    
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .nav-links {
        justify-content: center;
    }
}
</style>

<?php include "../../../includes/footer.php"; ?>
