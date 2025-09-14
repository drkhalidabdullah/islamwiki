<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

// Enforce rate limiting for wiki article views (disabled in development)
if (!defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
    enforce_rate_limit('wiki_views');
}

$page_title = 'Article';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Check for redirects first
$redirect = get_redirect_target($slug);
if ($redirect) {
    // Redirect to the target article
    header("Location: /wiki/" . $redirect['target_slug'], true, 301);
    exit;
}

// Get article
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name, cc.slug as category_slug 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND (wa.status = 'published' OR (wa.status = 'draft' AND (wa.author_id = ? OR ?)))
");
$stmt->execute([$slug, ucfirst($slug), $_SESSION['user_id'] ?? 0, is_editor() ? 1 : 0]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Increment view count
$stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$article['id']]);

$page_title = $article['title'];

// Parse markdown content with enhanced wiki features
$parser = new EnhancedMarkdownParser('');
$parsed_content = $parser->parse($article['content']);

// Get talk page status
$talk_page = get_talk_page($article['id']);

// Check if article is in user's watchlist
$is_watched = false;
if (is_logged_in()) {
    $is_watched = is_in_watchlist($_SESSION['user_id'], $article['id']);
}

include '../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/wiki_article.js"></script>
<script src="/skins/bismillah/assets/js/citation.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_article.css">
<?php

// Check if this is the Main_Page (for potential future use)
$is_main_page = ($article['slug'] === 'Main_Page');
?>

<article class="card">
        <header class="article-header">
            <!-- Compact Header Layout -->
            <div class="article-header-compact">
                <!-- Top Row: Title on left, Tools on right -->
                <div class="article-header-top">
                    <h1 class="article-title-compact"><?php echo htmlspecialchars($article['title']); ?></h1>
                    <div class="article-actions-compact">
                        <a href="/wiki/<?php echo $article['slug']; ?>/history" class="btn-icon-compact" title="View History">
                            <i class="fas fa-history"></i>
                        </a>
                        <a href="/wiki/<?php echo $article['slug']; ?>/talk" class="btn-icon-compact" title="Discussion">
                            <i class="fas fa-comments"></i>
                            <?php if ($talk_page): ?>
                                <span class="talk-indicator" title="Has discussion"></span>
                            <?php endif; ?>
                        </a>
                        <?php if (is_logged_in()): ?>
                            <a href="#" class="btn-icon-compact watchlist-btn <?php echo $is_watched ? 'watched' : ''; ?>" 
                               title="<?php echo $is_watched ? 'Remove from watchlist' : 'Add to watchlist'; ?>"
                               onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                                <i class="fas fa-eye"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (is_logged_in() && is_editor()): ?>
                            <a href="/wiki/<?php echo $article['slug']; ?>/edit" class="btn-icon-compact" title="Edit Article">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../delete_article.php?id=<?php echo $article['id']; ?>" class="btn-icon-compact btn-danger" title="Delete Article" onclick="return confirm('Are you sure you want to delete this article?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Bottom Row: Category on left, Date and Views on right -->
                <div class="article-header-bottom">
                    <div class="article-category-compact">
                        <?php if ($article['category_name']): ?>
                            <a href="/wiki/category/<?php echo $article['category_slug']; ?>" class="category-tag-compact">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($article['category_name']); ?>
                            </a>
                        <?php else: ?>
                            <span class="no-category">No category</span>
                        <?php endif; ?>
                    </div>
                    <div class="article-meta-compact">
                        <span class="article-date-compact">
                            <i class="fas fa-calendar"></i>
                            <?php echo format_date($article['published_at']); ?>
                        </span>
                        <span class="article-views-compact">
                            <i class="fas fa-eye"></i>
                            <?php echo number_format($article['view_count']); ?> views
                        </span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Three-column layout: 15% | 70% | 15% -->
        <div class="article-page">
            <div class="article-container">
                <div class="wiki-layout">
            <!-- Left Sidebar: Table of Contents -->
            <aside class="wiki-toc">
                <div class="toc-header">
                    <h3>Contents</h3>
                    <button class="toc-toggle" onclick="toggleTOC()">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="toc-content" id="toc-content">
                    <div class="toc-loading">
                        <i class="fas fa-spinner fa-spin"></i> Generating table of contents...
                    </div>
                </div>
            </aside>
            
            <!-- Main Content Area -->
            <main class="wiki-main-content">
                <div class="article-content">
                    <?php echo $parsed_content; ?>
                </div>
            </main>
            
            <!-- Right Sidebar: Tools -->
            <aside class="wiki-tools">
                <div class="tools-section">
                    <h3>Tools</h3>
                    <ul class="tools-list">
                        <li>
                            <a href="/wiki/special/what-links-here?slug=<?php echo urlencode($article['slug']); ?>" class="tool-link">
                                <i class="fas fa-link"></i>
                                <span>What links here</span>
                            </a>
                        </li>
                        <li>
                            <a href="/wiki/special/page-info?slug=<?php echo urlencode($article['slug']); ?>" class="tool-link">
                                <i class="fas fa-info-circle"></i>
                                <span>Page information</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="tool-link" onclick="citePage()">
                                <i class="fas fa-quote-left"></i>
                                <span>Cite this page</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="tool-link" onclick="downloadPDF()">
                                <i class="fas fa-download"></i>
                                <span>Download as PDF</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="tools-section">
                    <h3>Page Statistics</h3>
                    <div class="stats-list">
                        <div class="stat-item">
                            <i class="fas fa-eye"></i>
                            <span><?php echo number_format($article['view_count']); ?> views</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-calendar"></i>
                            <span>Created <?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-edit"></i>
                            <span>Last edited <?php echo date('M j, Y', strtotime($article['updated_at'])); ?></span>
                        </div>
                        <?php if ($article['category_name']): ?>
                        <div class="stat-item">
                            <i class="fas fa-folder"></i>
                            <span>Category: <?php echo htmlspecialchars($article['category_name']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="tools-section">
                    <h3>Quick Actions</h3>
                    <ul class="tools-list">
                        <?php if (is_logged_in() && is_editor()): ?>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/edit" class="tool-link">
                                <i class="fas fa-edit"></i>
                                <span>Edit this page</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/history" class="tool-link">
                                <i class="fas fa-history"></i>
                                <span>View history</span>
                            </a>
                        </li>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/talk" class="tool-link">
                                <i class="fas fa-comments"></i>
                                <span>Discussion</span>
                            </a>
                        </li>
                        <?php if (is_logged_in()): ?>
                        <li>
                            <a href="#" class="tool-link" onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                                <i class="fas fa-eye"></i>
                                <span><?php echo $is_watched ? 'Remove from watchlist' : 'Add to watchlist'; ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </aside>
                </div>
            </div>
        </div>
        
        <!-- Article Actions and Engagement -->
        <div class="article-actions-section">
            <!-- Report Button -->
            <div class="article-report">
                <button class="btn btn-outline btn-sm" onclick="showReportModal(<?php echo $article['id']; ?>, 'wiki_article')">
                    <i class="fas fa-flag"></i> Report Content
                </button>
            </div>
            
            <!-- Guest Engagement Banner -->
            <?php if (!is_logged_in()): ?>
            <div class="guest-engagement-banner">
                <div class="banner-content">
                    <div class="banner-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="banner-text">
                        <h4>Join the Community</h4>
                        <p>Sign up to contribute to this article, edit content, and connect with other members</p>
                    </div>
                    <div class="banner-actions">
                        <a href="/register" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                        <a href="/login" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
    </article>
    
    <!-- Related Articles -->
    <?php if ($article['category_id']): ?>
    <?php
    // Get related articles (same category, excluding current)
    $stmt = $pdo->prepare("
        SELECT wa.*, u.display_name, u.username 
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        WHERE wa.category_id = ? AND wa.id != ? AND wa.status = 'published' 
        ORDER BY wa.published_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$article['category_id'], $article['id']]);
    $related_articles = $stmt->fetchAll();
    
    if (!empty($related_articles)):
    ?>
    <div class="related-articles">
        <h3>Related Articles</h3>
        <div class="related-grid">
            <?php foreach ($related_articles as $related): ?>
            <div class="related-item">
                <h4><a href="/wiki/<?php echo $related['slug']; ?>"><?php echo htmlspecialchars($related['title']); ?></a></h4>
                <p class="related-meta">
                    By <?php echo htmlspecialchars($related['display_name'] ?: $related['username']); ?>
                    | <?php echo format_date($related['published_at']); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

