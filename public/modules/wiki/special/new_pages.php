<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

$page_title = 'New Pages';

// Get parameters
$days = (int)($_GET['days'] ?? 7); // Default to last 7 days
$limit = min((int)($_GET['limit'] ?? 50), 200); // Max 200 results
$namespace_id = $_GET['namespace'] ?? null;

// Get namespaces for filter
$namespaces = get_wiki_namespaces();

// Get new pages
$sql = "
    SELECT wa.*, u.username, u.display_name, wn.name as namespace_name, wn.display_name as namespace_display
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
    WHERE wa.status = 'published' 
    AND wa.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
";
$params = [$days];

if ($namespace_id !== null) {
    $sql .= " AND wa.namespace_id = ?";
    $params[] = $namespace_id;
}

$sql .= " ORDER BY wa.created_at DESC LIMIT ?";
$params[] = $limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$new_pages = $stmt->fetchAll();

// Get statistics
$stats_sql = "SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
$stats_params = [$days];
if ($namespace_id !== null) {
    $stats_sql .= " AND namespace_id = ?";
    $stats_params[] = $namespace_id;
}
$stmt = $pdo->prepare($stats_sql);
$stmt->execute($stats_params);
$total_new_pages = $stmt->fetch()['count'];

include "../../../includes/header.php";
?>

<div class="special-page-container">
    <div class="special-page-header">
        <h1>New Pages</h1>
        <p>Recently created wiki pages</p>
    </div>

    <!-- Filters -->
    <div class="special-filters">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="days">Created in last:</label>
                <select name="days" id="days">
                    <option value="1" <?php echo $days == 1 ? 'selected' : ''; ?>>1 day</option>
                    <option value="3" <?php echo $days == 3 ? 'selected' : ''; ?>>3 days</option>
                    <option value="7" <?php echo $days == 7 ? 'selected' : ''; ?>>7 days</option>
                    <option value="14" <?php echo $days == 14 ? 'selected' : ''; ?>>14 days</option>
                    <option value="30" <?php echo $days == 30 ? 'selected' : ''; ?>>30 days</option>
                </select>
            </div>
            
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
            <span class="stat-number"><?php echo number_format($total_new_pages); ?></span>
            <span class="stat-label">New Pages (<?php echo $days; ?> days)</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count($new_pages); ?></span>
            <span class="stat-label">Showing</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $total_new_pages > 0 ? round($total_new_pages / $days, 1) : 0; ?></span>
            <span class="stat-label">Pages/Day</span>
        </div>
    </div>

    <!-- New Pages List -->
    <div class="new-pages-list">
        <?php if (empty($new_pages)): ?>
            <div class="no-results">
                <p>No new pages found in the last <?php echo $days; ?> days.</p>
            </div>
        <?php else: ?>
            <div class="pages-header">
                <h2>New Pages (Last <?php echo $days; ?> days)</h2>
                <div class="pages-count"><?php echo count($new_pages); ?> pages</div>
            </div>
            
            <div class="pages-grid">
                <?php foreach ($new_pages as $page): ?>
                <div class="page-item">
                    <div class="page-header">
                        <a href="/wiki/<?php echo $page['slug']; ?>" class="page-link">
                            <?php if ($page['namespace_name'] !== 'Main'): ?>
                                <span class="namespace"><?php echo htmlspecialchars($page['namespace_display']); ?>:</span>
                            <?php endif; ?>
                            <span class="page-title"><?php echo htmlspecialchars($page['title']); ?></span>
                        </a>
                        <div class="page-badges">
                            <span class="new-badge">New</span>
                            <?php if ($page['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="page-meta">
                        <div class="page-author">
                            <i class="fas fa-user"></i>
                            <a href="/user/<?php echo $page['username']; ?>">
                                <?php echo htmlspecialchars($page['display_name'] ?: $page['username']); ?>
                            </a>
                        </div>
                        <div class="page-date">
                            <i class="fas fa-calendar"></i>
                            <span title="<?php echo format_date($page['created_at']); ?>">
                                <?php echo time_ago($page['created_at']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($page['excerpt']): ?>
                    <div class="page-excerpt">
                        <?php echo htmlspecialchars(truncate_text($page['excerpt'], 150)); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="page-stats">
                        <div class="stat">
                            <i class="fas fa-eye"></i>
                            <span><?php echo number_format($page['view_count']); ?> views</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-edit"></i>
                            <span><?php echo $page['edit_count']; ?> edits</span>
                        </div>
                        <?php if ($page['word_count'] > 0): ?>
                        <div class="stat">
                            <i class="fas fa-file-text"></i>
                            <span><?php echo number_format($page['word_count']); ?> words</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="page-actions">
                        <a href="/wiki/<?php echo $page['slug']; ?>" class="btn btn-sm btn-primary">Read</a>
                        <a href="/wiki/<?php echo $page['slug']; ?>/history" class="btn btn-sm btn-secondary">History</a>
                        <?php if (is_logged_in() && is_editor()): ?>
                            <a href="/wiki/<?php echo $page['slug']; ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
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
            <a href="/wiki/special/recent_changes.php" class="nav-link">Recent Changes</a>
            <a href="/wiki/special/all_pages.php" class="nav-link">All Pages</a>
            <a href="/wiki/special/user_contributions.php" class="nav-link">User Contributions</a>
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

.new-pages-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.pages-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.pages-header h2 {
    margin: 0;
    color: #2c3e50;
}

.pages-count {
    color: #6c757d;
    font-size: 0.9rem;
}

.pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
}

.page-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s;
    background: white;
}

.page-item:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.1);
    transform: translateY(-2px);
}

.page-header {
    margin-bottom: 1rem;
}

.page-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    display: block;
    margin-bottom: 0.5rem;
}

.page-link:hover {
    text-decoration: underline;
}

.namespace {
    color: #6c757d;
    font-weight: normal;
    font-size: 0.9rem;
}

.page-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.new-badge {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.featured-badge {
    background: #ffc107;
    color: #000;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.page-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.page-author,
.page-date {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.page-author a {
    color: #007bff;
    text-decoration: none;
}

.page-author a:hover {
    text-decoration: underline;
}

.page-excerpt {
    color: #6c757d;
    line-height: 1.5;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.page-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.stat {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.page-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    border: 1px solid transparent;
}

.btn-sm.btn-primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-sm.btn-primary:hover {
    background: #0056b3;
    border-color: #004085;
}

.btn-sm.btn-secondary {
    background: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn-sm.btn-secondary:hover {
    background: #545b62;
    border-color: #4e555b;
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
    justify-content: center;
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
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pages-grid {
        grid-template-columns: 1fr;
        padding: 1rem;
    }
    
    .page-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .page-stats {
        flex-wrap: wrap;
    }
    
    .page-actions {
        justify-content: center;
    }
    
    .nav-links {
        justify-content: center;
    }
}
</style>

<?php include "../../../includes/footer.php"; ?>
