<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

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
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_special_what-links-here.css">
<?php
?>


<div class="what-links-here-container">
    <!-- Header Section -->
    <div class="what-links-here-header">
        <h1 >
            <i class="iw iw-link"></i> What links here
        </h1>
        <p >
            Pages that link to: 
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" >
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
            <div >
                <i class="iw iw-unlink" ></i>
                <h3 >No pages link here</h3>
                <p >
                    No other pages currently link to 
                    <strong><?php echo htmlspecialchars($article['title']); ?></strong>.
                </p>
            </div>
        <?php else: ?>
            <!-- Results Header -->
            <div >
                <p >
                    The following pages link to <strong><?php echo htmlspecialchars($article['title']); ?></strong>:
                </p>
                <p >
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
                            <i class="iw iw-eye"></i> 
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
                    
                    <div >
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
        <div >
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
    <div >
        <div >
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" 
               >
                ← <?php echo htmlspecialchars($article['title']); ?>
            </a>
            <small style="color: #6c757d;">
                Last updated: <?php echo date('M j, Y \a\t g:i A'); ?>
            </small>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>
