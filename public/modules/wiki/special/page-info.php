<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'Page Information';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get the article with detailed information
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.email, u.created_at as user_created_at, cc.name as category_name, cc.slug as category_slug 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Get comprehensive article statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT wa.id) as total_articles,
        COUNT(DISTINCT u.id) as total_authors,
        AVG(wa.view_count) as avg_views,
        MAX(wa.created_at) as latest_article,
        MIN(wa.created_at) as first_article
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.status = 'published'
");
$stmt->execute();
$stats = $stmt->fetch();

// Get edit history for this article
$edit_history = [];
$total_edits = 0;
$recent_edits = [];
$page_creator = null;
$latest_editor = null;

try {
    // Get total edit count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_edits
        FROM wiki_edit_history 
        WHERE article_id = ?
    ");
    $stmt->execute([$article['id']]);
    $total_edits = $stmt->fetch()['total_edits'] ?: 0;

    // Get recent edits (last 30 days)
    $stmt = $pdo->prepare("
        SELECT we.*, u.username, u.display_name
        FROM wiki_edit_history we
        JOIN users u ON we.editor_id = u.id
        WHERE we.article_id = ? 
        AND we.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY we.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$article['id']]);
    $recent_edits = $stmt->fetchAll();

    // Get page creator (first edit)
    $stmt = $pdo->prepare("
        SELECT we.*, u.username, u.display_name
        FROM wiki_edit_history we
        JOIN users u ON we.editor_id = u.id
        WHERE we.article_id = ?
        ORDER BY we.created_at ASC
        LIMIT 1
    ");
    $stmt->execute([$article['id']]);
    $page_creator = $stmt->fetch();

    // Get latest editor
    $stmt = $pdo->prepare("
        SELECT we.*, u.username, u.display_name
        FROM wiki_edit_history we
        JOIN users u ON we.editor_id = u.id
        WHERE we.article_id = ?
        ORDER BY we.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$article['id']]);
    $latest_editor = $stmt->fetch();

} catch (PDOException $e) {
    // Tables don't exist, use fallback data
    $total_edits = 1; // At least the creation counts as one edit
    $page_creator = [
        'username' => $article['username'],
        'display_name' => $article['display_name'],
        'created_at' => $article['created_at']
    ];
    $latest_editor = [
        'username' => $article['username'],
        'display_name' => $article['display_name'],
        'created_at' => $article['updated_at']
    ];
}

// Get redirects to this page
$redirects_count = 0;
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as redirect_count
        FROM wiki_articles 
        WHERE content LIKE ? 
        AND status = 'published'
        AND id != ?
    ");
    $search_term = '%[[' . $article['title'] . ']]%';
    $stmt->execute([$search_term, $article['id']]);
    $redirects_count = $stmt->fetch()['redirect_count'] ?: 0;
} catch (PDOException $e) {
    $redirects_count = 0;
}

// Get page watchers (users who have this page in their watchlist)
$watchers_count = 0;
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as watchers_count
        FROM user_watchlist 
        WHERE article_id = ?
    ");
    $stmt->execute([$article['id']]);
    $watchers_count = $stmt->fetch()['watchers_count'] ?: 0;
} catch (PDOException $e) {
    $watchers_count = 0;
}

// Get recent page views (last 30 days)
$recent_views = 0;
try {
    $stmt = $pdo->prepare("
        SELECT SUM(view_count) as recent_views
        FROM wiki_article_views 
        WHERE article_id = ? 
        AND view_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$article['id']]);
    $recent_views = $stmt->fetch()['recent_views'] ?: 0;
} catch (PDOException $e) {
    $recent_views = $article['view_count']; // Fallback to total view count
}

// Calculate article metrics
$word_count = str_word_count(strip_tags($article['content']));
$char_count = strlen(strip_tags($article['content']));
$byte_count = strlen($article['content']);
$reading_time = ceil($word_count / 200); // Assuming 200 words per minute

// Get page protection status (simplified)
$page_protection = [
    'edit' => 'No protection',
    'move' => 'No protection'
];

// Get lint errors (simplified)
$lint_errors = [
    'duplicate_ids' => 0,
    'background_color_without_text_color' => 0
];

// Get page properties
$page_properties = [
    'namespace_id' => 0,
    'page_id' => $article['id'],
    'content_language' => 'en - English',
    'content_model' => 'wikitext',
    'indexing_by_robots' => 'Allowed',
    'counted_as_content_page' => 'Yes',
    'local_description' => substr(strip_tags($article['content']), 0, 100) . '...',
    'central_description' => $article['title']
];

include '../../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_special_page-info.css">
<?php
?>


<div class="page-info-container">
    <!-- Header Section -->
    <div class="page-info-header">
        <h1 >
            <i class="fas fa-info-circle"></i> Information for "<?php echo htmlspecialchars($article['title']); ?>"
        </h1>
        <p >
            Comprehensive page information and statistics
        </p>
    </div>

    <!-- Basic Information Section -->
    <div class="page-info-section">
        <h3>Basic information</h3>
        <table class="info-table">
            <tr>
                <td>Display title</td>
                <td><?php echo htmlspecialchars($article['title']); ?></td>
            </tr>
            <tr>
                <td>Default sort key</td>
                <td><?php echo htmlspecialchars($article['title']); ?></td>
            </tr>
            <tr>
                <td>Page length (in bytes)</td>
                <td><?php echo number_format($byte_count); ?></td>
            </tr>
            <tr>
                <td>Namespace ID</td>
                <td><?php echo $page_properties['namespace_id']; ?></td>
            </tr>
            <tr>
                <td>Page ID</td>
                <td><?php echo $page_properties['page_id']; ?></td>
            </tr>
            <tr>
                <td>Page content language</td>
                <td><?php echo $page_properties['content_language']; ?></td>
            </tr>
            <tr>
                <td>Page content model</td>
                <td><?php echo $page_properties['content_model']; ?></td>
            </tr>
            <tr>
                <td>Indexing by robots</td>
                <td><?php echo $page_properties['indexing_by_robots']; ?></td>
            </tr>
            <tr>
                <td>Number of page watchers</td>
                <td><?php echo number_format($watchers_count); ?></td>
            </tr>
            <tr>
                <td>Number of page watchers who visited in the last 30 days</td>
                <td><?php echo number_format($watchers_count); ?></td>
            </tr>
            <tr>
                <td>Number of redirects to this page</td>
                <td>
                    <a href="/wiki/special/what-links-here?slug=<?php echo urlencode($article['slug']); ?>&hidelinks=1&hidetrans=1">
                        <?php echo number_format($redirects_count); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>Counted as a content page</td>
                <td><?php echo $page_properties['counted_as_content_page']; ?></td>
            </tr>
            <tr>
                <td>Local description</td>
                <td><?php echo htmlspecialchars($page_properties['local_description']); ?></td>
            </tr>
            <tr>
                <td>Central description</td>
                <td><?php echo htmlspecialchars($page_properties['central_description']); ?></td>
            </tr>
            <tr>
                <td>Page views in the past 30 days</td>
                <td>
                    <a href="#" title="View detailed page view statistics">
                        <?php echo number_format($recent_views); ?>
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <!-- Page Protection Section -->
    <div class="page-info-section">
        <h3>Page protection</h3>
        <table class="info-table">
            <tr>
                <td>Edit</td>
                <td>
                    <span class="protection-badge"><?php echo $page_protection['edit']; ?></span>
                </td>
            </tr>
            <tr>
                <td>Move</td>
                <td>
                    <span class="protection-badge"><?php echo $page_protection['move']; ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Edit History Section -->
    <div class="page-info-section">
        <h3>Edit history</h3>
        <table class="info-table">
            <tr>
                <td>Page creator</td>
                <td>
                    <a href="/user/<?php echo htmlspecialchars($page_creator['username']); ?>">
                        <?php echo htmlspecialchars($page_creator['display_name'] ?: $page_creator['username']); ?>
                    </a>
                    <small class="text-muted">
                        (<?php echo date('H:i, j F Y', strtotime($page_creator['created_at'])); ?>)
                    </small>
                </td>
            </tr>
            <tr>
                <td>Date of page creation</td>
                <td>
                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history">
                        <?php echo date('H:i, j F Y', strtotime($article['created_at'])); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>Latest editor</td>
                <td>
                    <a href="/user/<?php echo htmlspecialchars($latest_editor['username']); ?>">
                        <?php echo htmlspecialchars($latest_editor['display_name'] ?: $latest_editor['username']); ?>
                    </a>
                    <small class="text-muted">
                        (<?php echo date('H:i, j F Y', strtotime($latest_editor['created_at'])); ?>)
                    </small>
                </td>
            </tr>
            <tr>
                <td>Date of latest edit</td>
                <td>
                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history">
                        <?php echo date('H:i, j F Y', strtotime($article['updated_at'])); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <td>Total number of edits</td>
                <td><?php echo number_format($total_edits); ?></td>
            </tr>
            <tr>
                <td>Recent number of edits (within past 30 days)</td>
                <td><?php echo count($recent_edits); ?></td>
            </tr>
            <tr>
                <td>Recent number of distinct authors</td>
                <td><?php echo count(array_unique(array_column($recent_edits, 'username'))); ?></td>
            </tr>
        </table>
    </div>

    <!-- Page Properties Section -->
    <div class="page-info-section">
        <h3>Page properties</h3>
        <table class="info-table">
            <tr>
                <td>Word count</td>
                <td><?php echo number_format($word_count); ?> words</td>
            </tr>
            <tr>
                <td>Character count</td>
                <td><?php echo number_format($char_count); ?> characters</td>
            </tr>
            <tr>
                <td>Reading time</td>
                <td><?php echo $reading_time; ?> minute<?php echo $reading_time !== 1 ? 's' : ''; ?></td>
            </tr>
            <tr>
                <td>Total view count</td>
                <td><?php echo number_format($article['view_count']); ?> views</td>
            </tr>
            <?php if ($article['category_name']): ?>
            <tr>
                <td>Category</td>
                <td>
                    <a href="/wiki/category/<?php echo htmlspecialchars($article['category_slug']); ?>">
                        <?php echo htmlspecialchars($article['category_name']); ?>
                    </a>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Lint Errors Section -->
    <div class="page-info-section">
        <h3>Lint errors</h3>
        <table class="info-table">
            <tr>
                <td>Duplicate IDs</td>
                <td>
                    <span class="lint-error-count"><?php echo $lint_errors['duplicate_ids']; ?></span>
                </td>
            </tr>
            <tr>
                <td>Background color inline style rule exists without a corresponding text color</td>
                <td>
                    <span class="lint-error-count"><?php echo $lint_errors['background_color_without_text_color']; ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- External Tools Section -->
    <div class="external-tools">
        <h4>External tools</h4>
        <div >
            <a href="#">Revision history search</a>
            <span style="color: #6c757d;">•</span>
            <a href="#">Revision history statistics</a>
            <span style="color: #6c757d;">•</span>
            <a href="#">Edits by user</a>
            <span style="color: #6c757d;">•</span>
            <a href="#">Page view statistics</a>
            <span style="color: #6c757d;">•</span>
            <a href="#">WikiChecker</a>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <nav>
            <a href="/wiki/Main_Page">← Main Page</a>
            <span>•</span>
            <span>Page information</span>
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
