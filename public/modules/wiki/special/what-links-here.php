<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

$page_title = 'What Links Here';

$slug = $_GET['slug'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(20, min(500, (int)($_GET['limit'] ?? 50))); // Items per page, 20-500 range
$offset = ($page - 1) * $limit;

// Filtering options
$hide_transclusions = isset($_GET['hide_transclusions']) && $_GET['hide_transclusions'] === '1';
$hide_links = isset($_GET['hide_links']) && $_GET['hide_links'] === '1';
$hide_redirects = isset($_GET['hide_redirects']) && $_GET['hide_redirects'] === '1';
$namespace = $_GET['namespace'] ?? 'all';
$sort = $_GET['sort'] ?? 'title'; // title, date, views

if (!$slug) {
    redirect('index.php');
}

// Get the article to find what links to it
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Build the base query
$where_conditions = ["wa.status = 'published'", "wa.id != ?"];
$params = [$article['id']];

// Add content search conditions
$content_conditions = [];
$content_conditions[] = "wa.content LIKE ?";
$params[] = '%[[' . $article['title'] . ']]%';

$content_conditions[] = "wa.content LIKE ?";
$params[] = '%[[' . $article['slug'] . ']]%';

$where_conditions[] = "(" . implode(" OR ", $content_conditions) . ")";

// Apply filters
if ($hide_transclusions) {
    // For now, we don't have transclusions, but this is where we'd filter them
}

if ($hide_links) {
    // This would hide certain types of links if we had link categorization
}

if ($hide_redirects) {
    // This would hide redirect pages if we had redirect functionality
}

// Build sorting
$order_by = "wa.title ASC";
switch ($sort) {
    case 'date':
        $order_by = "wa.updated_at DESC";
        break;
    case 'views':
        $order_by = "wa.view_count DESC";
        break;
    case 'title':
    default:
        $order_by = "wa.title ASC";
        break;
}

// Get total count for pagination
$count_sql = "
    SELECT COUNT(DISTINCT wa.id) as total
    FROM wiki_articles wa 
    WHERE " . implode(" AND ", $where_conditions);
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_count = $stmt->fetch()['total'];

// Get paginated results
$sql = "
    SELECT DISTINCT wa.id, wa.title, wa.slug, wa.view_count, wa.updated_at, u.username, u.display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE " . implode(" AND ", $where_conditions) . "
    ORDER BY " . $order_by . "
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$linking_articles = $stmt->fetchAll();

// Calculate pagination info
$total_pages = ceil($total_count / $limit);
$has_previous = $page > 1;
$has_next = $page < $total_pages;

include '../../../includes/header.php';
?>

<style>
.what-links-here-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.what-links-here-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.what-links-here-filters {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.what-links-here-results {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.result-item {
    border-bottom: 1px solid #e9ecef;
    padding: 16px 0;
    transition: background-color 0.2s ease;
}

.result-item:hover {
    background-color: #f8f9fa;
    margin: 0 -12px;
    padding: 16px 12px;
    border-radius: 6px;
}

.result-item:last-child {
    border-bottom: none;
}

.result-title {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 6px;
}

.result-title a {
    color: #0d6efd;
    text-decoration: none;
    transition: color 0.2s ease;
}

.result-title a:hover {
    color: #0a58ca;
    text-decoration: underline;
}

.result-meta {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 4px;
}

.result-meta a {
    color: #0d6efd;
    text-decoration: none;
}

.result-meta a:hover {
    text-decoration: underline;
}

.result-stats {
    font-size: 12px;
    color: #6c757d;
}

.pagination-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.external-tools {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.external-tools h4 {
    margin: 0 0 12px 0;
    font-size: 16px;
    color: #495057;
}

.external-tools a {
    color: #0d6efd;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s ease;
}

.external-tools a:hover {
    color: #0a58ca;
    text-decoration: underline;
}

.filter-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}

.filter-left {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filter-right {
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: flex-end;
}

.filter-row {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    flex-wrap: wrap;
    min-height: 40px; /* Ensure consistent row height */
}

.filter-item {
    display: flex;
    align-items: center;
    gap: 8px;
    min-height: 32px; /* Ensure consistent height */
}

.filter-item label {
    font-weight: 500;
    color: #495057;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    line-height: 1.4;
}

.filter-item input[type="checkbox"] {
    margin: 0;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    vertical-align: middle;
    position: relative;
    top: -1px; /* Fine-tune vertical alignment */
}

.filter-item select {
    padding: 6px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background: white;
    font-size: 14px;
    min-width: 120px;
}

.filter-item select:focus {
    outline: none;
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.sort-controls {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.sort-controls button {
    padding: 8px 16px;
    background: #0d6efd;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.sort-controls button:hover {
    background: #0b5ed7;
}

.pagination-controls a {
    padding: 8px 16px;
    background: #f8f9fa;
    color: #0d6efd;
    text-decoration: none;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-controls a:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.breadcrumb-nav {
    margin-top: 20px;
    padding: 12px 0;
    border-top: 1px solid #e9ecef;
    font-size: 14px;
    color: #6c757d;
}

.breadcrumb-nav a {
    color: #0d6efd;
    text-decoration: none;
}

.breadcrumb-nav a:hover {
    text-decoration: underline;
}

.breadcrumb-nav span {
    margin: 0 8px;
    color: #adb5bd;
}

@media (max-width: 768px) {
    .filter-group {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .filter-right {
        align-items: flex-start;
    }
    
    .filter-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .sort-controls {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
}
</style>

<div class="what-links-here-container">
    <!-- Header Section -->
    <div class="what-links-here-header">
        <h1 style="margin: 0 0 10px 0; font-size: 24px;">
            <i class="fas fa-link"></i> What links here
        </h1>
        <p style="margin: 0; color: #6c757d;">
            Pages that link to: 
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" style="color: #0d6efd; text-decoration: none;">
                <strong><?php echo htmlspecialchars($article['title']); ?></strong>
            </a>
        </p>
    </div>

    <!-- Filters Section -->
    <div class="what-links-here-filters">
        <form method="GET" action="">
            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
            
            <div class="filter-group">
                <div class="filter-left">
                    <div class="filter-row">
                        <div class="filter-item">
                            <label for="namespace">Namespace:</label>
                            <select name="namespace" id="namespace">
                                <option value="all" <?php echo $namespace === 'all' ? 'selected' : ''; ?>>all (Article)</option>
                                <option value="main" <?php echo $namespace === 'main' ? 'selected' : ''; ?>>Article</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-item">
                            <label>
                                <input type="checkbox" name="hide_transclusions" value="1" <?php echo $hide_transclusions ? 'checked' : ''; ?>>
                                Hide transclusions
                            </label>
                        </div>
                        
                        <div class="filter-item">
                            <label>
                                <input type="checkbox" name="hide_links" value="1" <?php echo $hide_links ? 'checked' : ''; ?>>
                                Hide links
                            </label>
                        </div>
                        
                        <div class="filter-item">
                            <label>
                                <input type="checkbox" name="hide_redirects" value="1" <?php echo $hide_redirects ? 'checked' : ''; ?>>
                                Hide redirects
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="filter-right">
                    <div class="sort-controls">
                        <div class="filter-item">
                            <label for="sort">Sort by:</label>
                            <select name="sort" id="sort">
                                <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                                <option value="date" <?php echo $sort === 'date' ? 'selected' : ''; ?>>Last modified</option>
                                <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>View count</option>
                            </select>
                        </div>
                        
                        <div class="filter-item">
                            <label for="limit">Show:</label>
                            <select name="limit" id="limit">
                                <option value="20" <?php echo $limit === 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                                <option value="250" <?php echo $limit === 250 ? 'selected' : ''; ?>>250</option>
                                <option value="500" <?php echo $limit === 500 ? 'selected' : ''; ?>>500</option>
                            </select>
                        </div>
                        
                        <button type="submit">Go</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div class="what-links-here-results">
        <?php if (empty($linking_articles)): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-unlink" style="font-size: 48px; color: #6c757d; margin-bottom: 16px;"></i>
                <h3 style="color: #6c757d; margin-bottom: 8px;">No pages link here</h3>
                <p style="color: #6c757d; margin: 0;">
                    No other pages currently link to 
                    <strong><?php echo htmlspecialchars($article['title']); ?></strong>.
                </p>
            </div>
        <?php else: ?>
            <!-- Results Header -->
            <div style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e9ecef;">
                <p style="margin: 0; color: #6c757d;">
                    The following pages link to <strong><?php echo htmlspecialchars($article['title']); ?></strong>:
                </p>
                <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">
                    <strong><?php echo number_format($total_count); ?></strong> 
                    <?php echo $total_count === 1 ? 'page' : 'pages'; ?> total
                </p>
            </div>

            <!-- Results List -->
            <div>
                <?php foreach ($linking_articles as $linking_article): ?>
                    <div class="result-item">
                        <div class="result-title">
                            <a href="/wiki/<?php echo htmlspecialchars($linking_article['slug']); ?>">
                                <?php echo htmlspecialchars($linking_article['title']); ?>
                            </a>
                        </div>
                        <div class="result-meta">
                            by 
                            <a href="/user/<?php echo htmlspecialchars($linking_article['username']); ?>">
                                <?php echo htmlspecialchars($linking_article['display_name'] ?: $linking_article['username']); ?>
                            </a>
                            • Last edited <?php echo date('M j, Y', strtotime($linking_article['updated_at'])); ?>
                        </div>
                        <div class="result-stats">
                            <i class="fas fa-eye"></i> 
                            <?php echo number_format($linking_article['view_count']); ?> views
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-controls">
                    <div class="pagination-info">
                        Showing <?php echo number_format($offset + 1); ?>-<?php echo number_format(min($offset + $limit, $total_count)); ?> 
                        of <?php echo number_format($total_count); ?> results
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <?php if ($has_previous): ?>
                            <a href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page - 1; ?>&sort=<?php echo urlencode($sort); ?>&limit=<?php echo $limit; ?>&hide_transclusions=<?php echo $hide_transclusions ? '1' : '0'; ?>&hide_links=<?php echo $hide_links ? '1' : '0'; ?>&hide_redirects=<?php echo $hide_redirects ? '1' : '0'; ?>&namespace=<?php echo urlencode($namespace); ?>">
                                ← Previous <?php echo $limit; ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($has_next): ?>
                            <a href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page + 1; ?>&sort=<?php echo urlencode($sort); ?>&limit=<?php echo $limit; ?>&hide_transclusions=<?php echo $hide_transclusions ? '1' : '0'; ?>&hide_links=<?php echo $hide_links ? '1' : '0'; ?>&hide_redirects=<?php echo $hide_redirects ? '1' : '0'; ?>&namespace=<?php echo urlencode($namespace); ?>">
                                Next <?php echo $limit; ?> →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- External Tools Section -->
    <div class="external-tools">
        <h4>External tools</h4>
        <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <a href="#">
                (link count • transclusion count • sorted list)
            </a>
            <span style="color: #6c757d;">•</span>
            <a href="#">
                See help page for transcluding these entries
            </a>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <nav>
            <a href="/wiki/Main_Page">← Main Page</a>
            <span>•</span>
            <span>What links here</span>
        </nav>
    </div>

    <!-- Navigation -->
    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" 
               style="padding: 8px 16px; background: #f8f9fa; color: #0d6efd; text-decoration: none; border: 1px solid #dee2e6; border-radius: 4px;">
                ← <?php echo htmlspecialchars($article['title']); ?>
            </a>
            <small style="color: #6c757d;">
                Last updated: <?php echo date('M j, Y \a\t g:i A'); ?>
            </small>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>
