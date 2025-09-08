<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

$page_title = 'All Pages';

// Get parameters
$namespace_id = $_GET['namespace'] ?? null;
$letter = $_GET['letter'] ?? '';
$limit = min((int)($_GET['limit'] ?? 100), 500); // Max 500 results
$offset = (int)($_GET['offset'] ?? 0);

// Get namespaces for filter
$namespaces = get_wiki_namespaces();

// Get all pages
$all_pages = get_all_pages($namespace_id, $limit, $offset);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'";
$count_params = [];
if ($namespace_id !== null) {
    $count_sql .= " AND namespace_id = ?";
    $count_params[] = $namespace_id;
}
$stmt = $pdo->prepare($count_sql);
$stmt->execute($count_params);
$total_pages = $stmt->fetch()['count'];

// Get alphabet letters for navigation
$alphabet_sql = "SELECT DISTINCT UPPER(LEFT(title, 1)) as letter FROM wiki_articles WHERE status = 'published'";
$alphabet_params = [];
if ($namespace_id !== null) {
    $alphabet_sql .= " AND namespace_id = ?";
    $alphabet_params[] = $namespace_id;
}
$alphabet_sql .= " ORDER BY letter";
$stmt = $pdo->prepare($alphabet_sql);
$stmt->execute($alphabet_params);
$alphabet_letters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Filter by letter if specified
if ($letter) {
    $all_pages = array_filter($all_pages, function($page) use ($letter) {
        return strtoupper(substr($page['title'], 0, 1)) === strtoupper($letter);
    });
}

include "../../../includes/header.php";
?>

<div class="special-page-container">
    <div class="special-page-header">
        <h1>All Pages</h1>
        <p>Complete list of all wiki pages</p>
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
                <label for="letter">Starting with:</label>
                <select name="letter" id="letter">
                    <option value="">All letters</option>
                    <?php foreach ($alphabet_letters as $l): ?>
                        <option value="<?php echo $l; ?>" 
                                <?php echo $letter === $l ? 'selected' : ''; ?>>
                            <?php echo $l; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="limit">Results per page:</label>
                <select name="limit" id="limit">
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?php echo $limit == 500 ? 'selected' : ''; ?>>500</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="special-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($total_pages); ?></span>
            <span class="stat-label">Total Pages</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count($all_pages); ?></span>
            <span class="stat-label">Showing</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count($alphabet_letters); ?></span>
            <span class="stat-label">Letters</span>
        </div>
    </div>

    <!-- Alphabet Navigation -->
    <?php if (!empty($alphabet_letters)): ?>
    <div class="alphabet-navigation">
        <h3>Browse by Letter</h3>
        <div class="alphabet-links">
            <?php foreach ($alphabet_letters as $l): ?>
                <a href="?namespace=<?php echo $namespace_id; ?>&letter=<?php echo $l; ?>&limit=<?php echo $limit; ?>" 
                   class="alphabet-link <?php echo $letter === $l ? 'active' : ''; ?>">
                    <?php echo $l; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pages List -->
    <div class="all-pages-list">
        <?php if (empty($all_pages)): ?>
            <div class="no-results">
                <p>No pages found.</p>
            </div>
        <?php else: ?>
            <div class="pages-header">
                <h2>Pages</h2>
                <div class="pages-count"><?php echo count($all_pages); ?> pages</div>
            </div>
            
            <div class="pages-grid">
                <?php 
                $current_letter = '';
                foreach ($all_pages as $page): 
                    $first_letter = strtoupper(substr($page['title'], 0, 1));
                    if ($first_letter !== $current_letter && !$letter):
                        if ($current_letter !== ''): ?>
                            </div>
                        <?php endif; ?>
                        <div class="letter-section">
                            <h3 class="letter-header"><?php echo $first_letter; ?></h3>
                            <div class="letter-pages">
                        <?php 
                        $current_letter = $first_letter;
                    endif;
                ?>
                    <div class="page-item">
                        <a href="/wiki/<?php echo $page['slug']; ?>" class="page-link">
                            <?php if ($page['namespace_name'] !== 'Main'): ?>
                                <span class="namespace"><?php echo htmlspecialchars($page['namespace_display']); ?>:</span>
                            <?php endif; ?>
                            <span class="page-title"><?php echo htmlspecialchars($page['title']); ?></span>
                        </a>
                        <div class="page-meta">
                            <span class="page-author">by <?php echo htmlspecialchars($page['display_name'] ?: $page['username']); ?></span>
                            <span class="page-date"><?php echo format_date($page['created_at']); ?></span>
                            <?php if ($page['view_count'] > 0): ?>
                                <span class="page-views"><?php echo number_format($page['view_count']); ?> views</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($page['is_redirect']): ?>
                            <span class="redirect-indicator" title="Redirect">↪</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($current_letter !== '' && !$letter): ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > $limit): ?>
    <div class="pagination">
        <?php 
        $current_page = floor($offset / $limit) + 1;
        $total_pages_count = ceil($total_pages / $limit);
        $base_url = "?namespace=" . $namespace_id . "&letter=" . $letter . "&limit=" . $limit;
        ?>
        
        <?php if ($current_page > 1): ?>
            <a href="<?php echo $base_url; ?>&offset=<?php echo max(0, $offset - $limit); ?>" class="page-link">← Previous</a>
        <?php endif; ?>
        
        <span class="page-info">
            Page <?php echo $current_page; ?> of <?php echo $total_pages_count; ?>
            (<?php echo number_format($total_pages); ?> total pages)
        </span>
        
        <?php if ($current_page < $total_pages_count): ?>
            <a href="<?php echo $base_url; ?>&offset=<?php echo $offset + $limit; ?>" class="page-link">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="special-navigation">
        <div class="nav-links">
            <a href="/wiki/special/recent_changes.php" class="nav-link">Recent Changes</a>
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

.alphabet-navigation {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.alphabet-navigation h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
}

.alphabet-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.alphabet-link {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    color: #007bff;
    text-decoration: none;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    transition: all 0.3s;
}

.alphabet-link:hover,
.alphabet-link.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.all-pages-list {
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
    padding: 1.5rem;
}

.letter-section {
    margin-bottom: 2rem;
}

.letter-section:last-child {
    margin-bottom: 0;
}

.letter-header {
    color: #2c3e50;
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #007bff;
    font-size: 1.5rem;
}

.letter-pages {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.page-item {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    transition: all 0.3s;
    position: relative;
}

.page-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.page-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    display: block;
    margin-bottom: 0.5rem;
}

.page-link:hover {
    text-decoration: underline;
}

.namespace {
    color: #6c757d;
    font-weight: normal;
}

.page-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.page-author,
.page-date,
.page-views {
    display: inline-block;
}

.redirect-indicator {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    color: #ffc107;
    font-size: 1.2rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin: 2rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.page-link {
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}

.page-link:hover {
    background: #0056b3;
}

.page-info {
    color: #6c757d;
    font-size: 0.9rem;
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
    
    .letter-pages {
        grid-template-columns: 1fr;
    }
    
    .pagination {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .nav-links {
        justify-content: center;
    }
}
</style>

<?php include "../../../includes/footer.php"; ?>
