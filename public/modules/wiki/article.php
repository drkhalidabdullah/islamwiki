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
                    <?php if ($article['slug'] === 'Main_Page'): ?>
                        <!-- Main Page with COMPLETELY EDITABLE content - everything comes from database -->
                        <div class="mp-editable-content">
                            <?php echo $parsed_content; ?>
                        </div>
                    <?php else: ?>
                        <?php echo $parsed_content; ?>
                    <?php endif; ?>
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

<style>
/* Main Page Section Header Styling */
.article-header-compact {
    position: relative;
}

.article-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 2px solid #f3f4f6;
    position: relative;
}

.article-header-top::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 50px;
    height: 3px;
    background: #3b82f6;
    border-radius: 2px;
}

.article-title-compact {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.article-actions-compact {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.article-header-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0 1.5rem 0;
}

.article-meta-compact {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.article-date-compact,
.article-views-compact {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.article-date-compact i,
.article-views-compact i {
    color: #9ca3af;
}

.no-category {
    font-size: 0.875rem;
    color: #9ca3af;
    font-style: italic;
}

.btn-icon-compact {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 6px;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-icon-compact:hover {
    background: #f3f4f6;
    color: #374151;
}

.btn-icon-compact.btn-danger:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Main Page Three-Column Layout - High Specificity */
.article-content .mp-topbanner {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
    color: white !important;
    padding: 2rem !important;
    border-radius: 8px !important;
    margin-bottom: 2rem !important;
    text-align: center !important;
}

.mp-welcomecount {
    max-width: 800px;
    margin: 0 auto;
}

.mp-welcome h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: white;
}

.mp-free {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
}

.articlecount ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.articlecount li {
    font-size: 1rem;
}

.articlecount a {
    color: white;
    text-decoration: none;
    font-weight: 600;
}

.articlecount a:hover {
    text-decoration: underline;
}

.article-content .mp-upper {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 2rem !important;
    margin-bottom: 2rem !important;
}

.mp-left, .mp-right {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.mp-lower {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.mp-tfp {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.mp-tfp img {
    width: 200px;
    height: 150px;
    object-fit: cover;
    border-radius: 6px;
    flex-shrink: 0;
}

.article-content .mp-bottom {
    display: grid !important;
    grid-template-columns: 1fr 1fr 1fr !important;
    gap: 2rem !important;
    margin-bottom: 2rem !important;
}

.mp-other-content,
.mp-sister-content,
.mp-lang {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.mp-other-content ul,
.mp-sister-content ul,
.mp-lang ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mp-other-content li,
.mp-sister-content li,
.mp-lang li {
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.mp-other-content li:last-child,
.mp-sister-content li:last-child,
.mp-lang li:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.mp-other-content a,
.mp-sister-content a,
.mp-lang a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.mp-other-content a:hover,
.mp-sister-content a:hover,
.mp-lang a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .article-content .mp-upper {
        grid-template-columns: 1fr !important;
    }
    
    .article-content .mp-bottom {
        grid-template-columns: 1fr !important;
    }
    
    .article-content .mp-tfp {
        flex-direction: column !important;
    }
    
    .article-content .mp-tfp img {
        width: 100% !important;
        height: 200px !important;
    }
}

@media (max-width: 768px) {
    .article-content .mp-topbanner {
        padding: 1.5rem 1rem !important;
    }
    
    .article-content .mp-welcome h1 {
        font-size: 2rem !important;
    }
    
    .article-content .articlecount ul {
        flex-direction: column !important;
        gap: 1rem !important;
    }
    
    .article-content .mp-left, 
    .article-content .mp-right,
    .article-content .mp-lower,
    .article-content .mp-other-content,
    .article-content .mp-sister-content,
    .article-content .mp-lang {
        padding: 1rem !important;
    }
}

.talk-indicator {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    border: 2px solid white;
}

.watchlist-btn {
    position: relative;
    transition: all 0.3s;
}

.watchlist-btn.watched {
    color: #ffc107;
}

.watchlist-btn:hover {
    transform: scale(1.1);
}

.article-actions-top {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    color: #6c757d;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s;
    position: relative;
}

.btn-icon:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-1px);
}

.btn-icon.btn-danger {
    color: #dc3545;
}

.btn-icon.btn-danger:hover {
    background: #f8d7da;
    color: #721c24;
}

.wiki-thumbnail {
    float: right;
    margin: 0 0 1rem 1rem;
    max-width: 200px;
    text-align: center;
}

.thumb-image {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.thumb-caption {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.missing-file {
    color: #dc3545;
    background: #f8d7da;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.9rem;
}

/* Enhanced article styling */
.article-content {
    line-height: 1.7;
    color: #2c3e50;
}

.article-content h2:first-child {
    margin-top: 0;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.article-content h1 {
    font-size: 2rem;
    border-bottom: 2px solid #007bff;
}

.article-content h2 {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #1f2937 !important;
    margin: 0 0 1rem 0 !important;
    padding-bottom: 0.75rem !important;
    border-bottom: 2px solid #f3f4f6 !important;
    position: relative !important;
}

.article-content h3 {
    font-size: 1.25rem;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.5rem;
}

.article-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1.5rem;
    margin: 2rem 0;
    color: #6c757d;
    font-style: italic;
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-radius: 0 4px 4px 0;
}

.article-content code {
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #e83e8c;
}

.article-content pre {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 6px;
    overflow-x: auto;
    margin: 2rem 0;
    border: 1px solid #e9ecef;
}

.article-content pre code {
    background: none;
    padding: 0;
    color: #2c3e50;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 6px;
    overflow: hidden;
}

.article-content th,
.article-content td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.article-content th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.article-content tr:hover {
    background: #f8f9fa;
}

/* Wiki link styling */
.article-content a {
    color: #007bff;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: all 0.3s;
}

.article-content a:hover {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

.article-content a.missing-link,
.article-content a.wiki-link.missing {
    color: #dc3545;
    border-bottom: 1px dashed #dc3545;
}

.article-content a.missing-link:hover,
.article-content a.wiki-link.missing:hover {
    color: #a71e2a;
    border-bottom-color: #a71e2a;
}

.article-content a.wiki-link {
    color: #007bff;
    border-bottom: 1px solid transparent;
    transition: all 0.3s;
}

.article-content a.wiki-link:hover {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

/* Category styling */
.article-categories {
    margin-top: 0.5rem;
}

.category-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s;
}

.category-tag:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}

/* ========================================
   WIKI LAYOUT STYLES
   ======================================== */

/* Three-column layout: 250px | 1fr | 250px */
.wiki-layout {
    display: grid !important; /* Restore proper grid layout */
    grid-template-columns: 250px 1fr 250px !important;
    gap: 2rem !important;
    margin-top: 1rem;
    max-width: 100%;
    min-width: 900px;
    position: static !important; /* Don't create stacking context that interferes with sticky */
    overflow: visible !important; /* Allow sticky elements to work properly */
}

/* Left Sidebar: Table of Contents */
.wiki-toc {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    height: fit-content;
    position: -webkit-sticky !important; /* Safari support */
    position: sticky !important; /* Use sticky positioning */
    top: 70px !important; /* Stick below newsbar (newsbar is ~64px tall) */
    max-height: calc(100vh - 90px) !important; /* Prevent overflow, account for newsbar + top position */
    overflow-y: auto !important; /* Allow scrolling if content is too tall */
    border: 1px solid #e9ecef;
    z-index: 100 !important; /* Much lower than search popup (10002) and newsbar (10000) */
    will-change: transform !important; /* Optimize for positioning */
    transform: translateZ(0) !important; /* Force hardware acceleration */
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; /* Add shadow for better visibility */
    align-self: start !important; /* Start at top of grid cell */
    /* Force sticky behavior */
    -webkit-transform: translateZ(0) !important;
    -moz-transform: translateZ(0) !important;
    -ms-transform: translateZ(0) !important;
    -o-transform: translateZ(0) !important;
    /* Prevent overlay - force containment */
    contain: layout style !important;
    isolation: isolate !important;
}

.toc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.toc-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.toc-toggle {
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.toc-toggle:hover {
    background: #e9ecef;
    color: #495057;
}

.toc-content {
    max-height: calc(100vh - 150px) !important; /* Account for header and newsbar */
    overflow-y: auto !important;
    padding: 0.5rem 0 !important;
}

.toc-content.collapsed {
    display: none;
}

.toc-list {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.toc-item {
    margin-bottom: 0.25rem !important;
}

.toc-link {
    display: block !important;
    padding: 0.75rem 1rem !important;
    color: #495057 !important;
    text-decoration: none !important;
    border-radius: 6px !important;
    transition: all 0.2s !important;
    font-size: 0.9rem !important;
    line-height: 1.4 !important;
}

.toc-link:hover {
    background: #e9ecef !important;
    color: #007bff !important;
    text-decoration: none !important;
    transform: translateX(2px) !important;
}

.toc-link.active {
    background: #007bff !important;
    color: white !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3) !important;
}

.toc-level-1 { font-weight: 600; }
.toc-level-2 { font-weight: 500; }
.toc-level-3 { font-weight: 400; }
.toc-level-4 { font-weight: 400; }
.toc-level-5 { font-weight: 400; }
.toc-level-6 { font-weight: 400; }

.toc-empty {
    color: #6c757d;
    font-style: italic;
    text-align: center;
    padding: 1rem;
}

.toc-loading {
    color: #6c757d;
    text-align: center;
    padding: 1rem;
}

/* Main Content Area */
.wiki-main-content {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    min-width: 0; /* Prevent grid overflow */
}

/* Right Sidebar: Tools */
.wiki-tools {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    height: fit-content;
    position: -webkit-sticky !important; /* Safari support */
    position: sticky !important; /* Use sticky positioning */
    top: 70px !important; /* Stick below newsbar (newsbar is ~64px tall) */
    max-height: calc(100vh - 90px) !important; /* Prevent overflow, account for newsbar + top position */
    overflow-y: auto !important; /* Allow scrolling if content is too tall */
    border: 1px solid #e9ecef;
    z-index: 100 !important; /* Much lower than search popup (10002) and newsbar (10000) */
    will-change: transform !important; /* Optimize for positioning */
    transform: translateZ(0) !important; /* Force hardware acceleration */
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; /* Add shadow for better visibility */
    align-self: start !important; /* Start at top of grid cell */
    /* Force sticky behavior */
    -webkit-transform: translateZ(0) !important;
    -moz-transform: translateZ(0) !important;
    -ms-transform: translateZ(0) !important;
    -o-transform: translateZ(0) !important;
    /* Prevent overlay - force containment */
    contain: layout style !important;
    isolation: isolate !important;
    min-width: 0;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.tools-section {
    margin-bottom: 2rem;
}

.tools-section:last-child {
    margin-bottom: 0;
}

.tools-section h3 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.tools-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tools-list li {
    margin-bottom: 0.5rem;
}

.tool-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    color: #495057;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
    font-size: 0.8rem;
    min-width: 0;
    overflow: hidden;
}

.tool-link:hover {
    background: #e9ecef;
    color: #007bff;
    text-decoration: none;
    transform: translateX(2px);
}

.tool-link i {
    width: 16px;
    text-align: center;
    color: #6c757d;
    flex-shrink: 0;
}

.tool-link span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

.tool-link:hover i {
    color: #007bff;
}

.stats-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stats-list .stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    font-size: 0.75rem;
    color: #6c757d;
    min-width: 0;
    overflow: hidden;
}

.stats-list .stat-item i {
    width: 16px;
    text-align: center;
    color: #007bff;
    flex-shrink: 0;
}

.stats-list .stat-item span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .wiki-layout {
        grid-template-columns: 220px 1fr 220px;
        gap: 1.5rem;
    }
}

@media (max-width: 992px) {
    .wiki-layout {
        grid-template-columns: 200px 1fr 200px;
        gap: 1rem;
    }
    
    .wiki-toc,
    .wiki-tools {
        padding: 0.75rem;
    }
    
    .toc-header h3,
    .tools-section h3 {
        font-size: 0.9rem;
    }
    
    .toc-link,
    .tool-link {
        font-size: 0.75rem;
        padding: 0.4rem;
    }
}

@media (max-width: 900px) {
    .wiki-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
        min-width: auto;
        overflow: visible !important; /* Allow sticky elements to work properly */
    }
    
    .wiki-toc,
    .wiki-tools {
        position: sticky !important;
        top: 70px !important; /* Match main rule */
        margin-bottom: 1rem;
        min-width: auto;
    }
    
    .wiki-main-content {
        padding: 1.5rem;
    }
    
    .toc-content {
        max-height: 200px;
    }
}

@media (max-width: 576px) {
    .wiki-main-content {
        padding: 1rem;
    }
    
    .wiki-toc,
    .wiki-tools {
        padding: 0.5rem;
    }
    
    .toc-header h3,
    .tools-section h3 {
        font-size: 0.85rem;
    }
    
    .toc-link,
    .tool-link {
        font-size: 0.75rem;
        padding: 0.375rem;
    }
}

/* Citation Modal */
.citation-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10002 !important; /* Higher than sidebars (9999) and newsbar (10000) */
    display: flex;
    align-items: center;
    justify-content: center;
}

.citation-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.citation-modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.citation-modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.citation-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6c757d;
    padding: 0.25rem;
}

.citation-modal-body {
    padding: 1.5rem;
}

.citation-format {
    margin-bottom: 1.5rem;
}

.citation-format label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.citation-format select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    background: white;
    cursor: pointer;
}

.citation-format select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.citation-format textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: 'Courier New', monospace;
    resize: vertical;
    min-height: 100px;
}

.citation-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

</style>

<script>
function toggleWatchlist(articleId, button) {
    const isWatched = button.classList.contains('watched');
    const action = isWatched ? 'remove' : 'add';
    
    fetch('/api/ajax/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            article_id: articleId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (response.status === 401) {
            showToast('Please log in to use the watchlist', 'error');
            return;
        }
        return response.json();
    })
    .then(data => {
        if (!data) return;
        
        console.log('Watchlist API response:', data);
        
        if (data.success) {
            if (action === 'add') {
                button.classList.add('watched');
                button.title = 'Remove from watchlist';
                showToast('Added to watchlist', 'success');
            } else {
                button.classList.remove('watched');
                button.title = 'Add to watchlist';
                showToast('Removed from watchlist', 'success');
            }
        } else {
            showToast(data.message || 'Error updating watchlist', 'error');
        }
    })
    .catch(error => {
        console.error('Watchlist API error:', error);
        showToast('Error updating watchlist', 'error');
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
        padding: 1rem 1.5rem;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    /* Article Actions Section */
    .article-actions-section {
        margin-top: 2rem;
        padding: 1rem 0;
        border-top: 1px solid #e9ecef;
    }
    
    .article-report {
        margin-bottom: 1rem;
    }
    
    /* Guest Engagement Banner */
    .guest-engagement-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 2rem;
        margin: 2rem 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .banner-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .banner-icon {
        font-size: 3rem;
        opacity: 0.9;
    }
    
    .banner-text {
        flex: 1;
    }
    
    .banner-text h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .banner-text p {
        margin: 0;
        opacity: 0.9;
        line-height: 1.5;
    }
    
    .banner-actions {
        display: flex;
        gap: 1rem;
        flex-shrink: 0;
    }
    
    .banner-actions .btn {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .banner-actions .btn-primary {
        background: white;
        color: #667eea;
        border: 2px solid white;
    }
    
    .banner-actions .btn-primary:hover {
        background: transparent;
        color: white;
        transform: translateY(-2px);
    }
    
    .banner-actions .btn-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
    }
    
    .banner-actions .btn-outline:hover {
        background: white;
        color: #667eea;
        transform: translateY(-2px);
    }
    
    /* Report Modal */
    .report-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .report-modal-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .report-modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .report-modal-header h3 {
        margin: 0;
        color: #2c3e50;
    }
    
    .report-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6c757d;
        padding: 0.25rem;
    }
    
    .report-modal-body {
        padding: 1.5rem;
    }
    
    .report-form-group {
        margin-bottom: 1.5rem;
    }
    
    .report-form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .report-form-group select,
    .report-form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: inherit;
    }
    
    .report-form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .report-form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }
    
    .report-submit-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .report-submit-btn:hover {
        background: #c82333;
    }
    
    .report-submit-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }
    
    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        .banner-content {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .banner-actions {
            flex-direction: column;
            width: 100%;
        }
        
        .banner-actions .btn {
            width: 100%;
        }
        
        .report-modal-content {
            width: 95%;
            margin: 1rem;
        }
        
        .report-form-actions {
            flex-direction: column;
        }
    }
`;
document.head.appendChild(style);

// Report modal functionality
function showReportModal(contentId, contentType) {
    const modal = document.createElement('div');
    modal.className = 'report-modal';
    modal.innerHTML = `
        <div class="report-modal-content">
            <div class="report-modal-header">
                <h3>Report Content</h3>
                <button class="report-modal-close" onclick="closeReportModal()">&times;</button>
            </div>
            <div class="report-modal-body">
                <form id="reportForm">
                    <input type="hidden" name="content_id" value="${contentId}">
                    <input type="hidden" name="content_type" value="${contentType}">
                    
                    <div class="report-form-group">
                        <label for="reportReason">Reason for reporting:</label>
                        <select name="reason" id="reportReason" required>
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or promotional content</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="harassment">Harassment or bullying</option>
                            <option value="copyright">Copyright violation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="report-form-group">
                        <label for="reportDescription">Additional details (optional):</label>
                        <textarea name="description" id="reportDescription" 
                                  placeholder="Please provide additional details about why you're reporting this content..."></textarea>
                    </div>
                    
                    <div class="report-form-actions">
                        <button type="button" class="btn btn-outline" onclick="closeReportModal()">Cancel</button>
                        <button type="submit" class="report-submit-btn">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add ESC key listener to close modal
    const escKeyHandler = function(e) {
        if (e.key === 'Escape') {
            closeReportModal();
            document.removeEventListener('keydown', escKeyHandler);
        }
    };
    document.addEventListener('keydown', escKeyHandler);
    
    // Handle form submission
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitReport(this);
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeReportModal();
        }
    });
}

function closeReportModal() {
    const modal = document.querySelector('.report-modal');
    if (modal) {
        modal.remove();
    }
}

function submitReport(form) {
    const submitBtn = form.querySelector('.report-submit-btn');
    const originalText = submitBtn.textContent;
    
    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    const formData = new FormData(form);
    
    fetch('/api/ajax/report_content.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeReportModal();
        } else {
            showToast(data.message || 'Failed to submit report', 'error');
        }
    })
    .catch(error => {
        console.error('Report submission error:', error);
        showToast('Error submitting report. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

// Close modal with escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReportModal();
    }
});

// Table of Contents functionality
function generateTOC() {
    const content = document.querySelector('.article-content');
    const headings = content.querySelectorAll('h1, h2, h3, h4, h5, h6');
    const tocContent = document.getElementById('toc-content');
    
    if (headings.length === 0) {
        tocContent.innerHTML = '<div class="toc-empty">No headings found in this article.</div>';
        return;
    }
    
    let tocHTML = '<ul class="toc-list">';
    let tocCounter = 1;
    
    headings.forEach((heading, index) => {
        // Skip the first h1 if it's the article title
        if (index === 0 && heading.tagName === 'H1') {
            return;
        }
        
        const id = `heading-${tocCounter}`;
        heading.id = id;
        
        const level = parseInt(heading.tagName.charAt(1));
        const text = heading.textContent.trim();
        const indent = (level - 2) * 20; // Indent based on heading level
        
        tocHTML += `
            <li class="toc-item toc-level-${level}" style="padding-left: ${indent}px">
                <a href="#${id}" class="toc-link" data-heading="${id}">
                    ${text}
                </a>
            </li>
        `;
        
        tocCounter++;
    });
    
    tocHTML += '</ul>';
    tocContent.innerHTML = tocHTML;
    
    // Add click handlers for smooth scrolling
    const tocLinks = tocContent.querySelectorAll('.toc-link');
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Set up scroll spy for active TOC highlighting
    setupScrollSpy();
}

function toggleTOC() {
    const tocContent = document.getElementById('toc-content');
    const toggleBtn = document.querySelector('.toc-toggle i');
    
    tocContent.classList.toggle('collapsed');
    
    if (tocContent.classList.contains('collapsed')) {
        toggleBtn.className = 'fas fa-chevron-right';
    } else {
        toggleBtn.className = 'fas fa-chevron-down';
    }
}

function updateActiveTOCItem(activeId) {
    const tocLinks = document.querySelectorAll('.toc-link');
    tocLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-heading') === activeId) {
            link.classList.add('active');
        }
    });
}

function setupScrollSpy() {
    const headings = document.querySelectorAll('.article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateActiveTOCItem(entry.target.id);
            }
        });
    }, {
        rootMargin: '-100px 0px -50% 0px'
    });
    
    headings.forEach(heading => {
        observer.observe(heading);
    });
}

// Tools functionality
function citePage() {
    try {
        const articleTitle = document.querySelector('.article-title-compact').textContent;
        const currentUrl = window.location.href;
        const currentDate = new Date();
        
        const citation = `${articleTitle}. (${currentDate.getFullYear()}, ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}). In *Islamic Wiki*. Retrieved ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}, ${currentDate.getFullYear()}, from ${currentUrl}`;
        
        // Keep sidebars visible but ensure modal appears above them

        // Create modal for citation
        const modal = document.createElement('div');
        modal.className = 'citation-modal';
        modal.innerHTML = `
            <div class="citation-modal-content">
                <div class="citation-modal-header">
                    <h3>Cite This Page</h3>
                    <button class="citation-modal-close" onclick="closeCitationModal()">&times;</button>
                </div>
                <div class="citation-modal-body">
                    <div class="citation-format">
                        <label for="citation-style">Citation Style:</label>
                        <select id="citation-style" onchange="updateCitation()">
                            <option value="mla">MLA 9th Edition</option>
                            <option value="apa" selected>APA 7th Edition</option>
                            <option value="chicago">Chicago 17th Edition</option>
                            <option value="harvard">Harvard</option>
                            <option value="ieee">IEEE</option>
                        </select>
                    </div>
                    <div class="citation-format">
                        <label id="citation-format-label">APA 7th Edition:</label>
                        <textarea id="citation-text" readonly>${citation}</textarea>
                    </div>
                    <div class="citation-actions">
                        <button onclick="copyCitation()" class="btn btn-primary">
                            <i class="fas fa-copy"></i> Copy Citation
                        </button>
                        <button onclick="closeCitationModal()" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.style.display = 'flex';
        
        // Add ESC key listener to close modal
        const escKeyHandler = function(e) {
            if (e.key === 'Escape') {
                closeCitationModal();
                document.removeEventListener('keydown', escKeyHandler);
            }
        };
        document.addEventListener('keydown', escKeyHandler);
    } catch (error) {
        console.error('Error in citePage:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

function closeCitationModal() {
    const modal = document.querySelector('.citation-modal');
    if (modal) {
        modal.remove();
    }
}

function updateCitation() {
    const style = document.getElementById('citation-style').value;
    const label = document.getElementById('citation-format-label');
    const textarea = document.getElementById('citation-text');
    
    if (!textarea) return;
    
    const articleTitle = document.querySelector('.article-title-compact').textContent;
    const currentUrl = window.location.href;
    const currentDate = new Date();
    
    let citation = '';
    let formatLabel = '';
    
    switch(style) {
        case 'mla':
            formatLabel = 'MLA 9th Edition:';
            citation = `"${articleTitle}." Islamic Wiki, ${currentDate.toLocaleDateString()}, ${currentUrl}.`;
            break;
        case 'apa':
            formatLabel = 'APA 7th Edition:';
            citation = `${articleTitle}. (${currentDate.getFullYear()}, ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}). In *Islamic Wiki*. Retrieved ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}, ${currentDate.getFullYear()}, from ${currentUrl}`;
            break;
        case 'chicago':
            formatLabel = 'Chicago 17th Edition:';
            citation = `"${articleTitle}." Islamic Wiki. Last modified ${currentDate.toLocaleDateString()}. ${currentUrl}.`;
            break;
        case 'harvard':
            formatLabel = 'Harvard Format:';
            citation = `Islamic Wiki ${currentDate.getFullYear()}, '${articleTitle}', viewed ${currentDate.toLocaleDateString()}, <${currentUrl}>.`;
            break;
        case 'ieee':
            formatLabel = 'IEEE Format:';
            citation = `Islamic Wiki, "${articleTitle}," Islamic Wiki, ${currentDate.getFullYear()}. [Online]. Available: ${currentUrl}. [Accessed: ${currentDate.toLocaleDateString()}].`;
            break;
        default:
            formatLabel = 'APA 7th Edition:';
            citation = `${articleTitle}. (${currentDate.getFullYear()}, ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}). In *Islamic Wiki*. Retrieved ${currentDate.toLocaleDateString('en-US', { month: 'long' })} ${currentDate.getDate()}, ${currentDate.getFullYear()}, from ${currentUrl}`;
    }
    
    label.textContent = formatLabel;
    textarea.value = citation;
}

function copyCitation() {
    const textarea = document.getElementById('citation-text');
    if (textarea) {
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            document.execCommand('copy');
            showToast('Citation copied to clipboard!', 'success');
        } catch (err) {
            // Fallback for modern browsers
            navigator.clipboard.writeText(textarea.value).then(() => {
                showToast('Citation copied to clipboard!', 'success');
            }).catch(() => {
                showToast('Failed to copy citation', 'error');
            });
        }
    }
}

function downloadPDF() {
    try {
        const articleTitle = document.querySelector('.article-title-compact').textContent;
        const articleContent = document.querySelector('.article-content').innerHTML;
        
        // Create a new window for PDF generation
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${articleTitle} - Islamic Wiki</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                        color: #333;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        color: #2c3e50;
                        margin-top: 30px;
                        margin-bottom: 15px;
                    }
                    h1 { font-size: 2.5em; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                    h2 { font-size: 2em; }
                    h3 { font-size: 1.5em; }
                    p { margin-bottom: 15px; }
                    ul, ol { margin-bottom: 15px; }
                    blockquote {
                        border-left: 4px solid #3498db;
                        margin: 20px 0;
                        padding-left: 20px;
                        font-style: italic;
                        color: #666;
                    }
                    code {
                        background: #f4f4f4;
                        padding: 2px 4px;
                        border-radius: 3px;
                        font-family: 'Courier New', monospace;
                    }
                    pre {
                        background: #f4f4f4;
                        padding: 15px;
                        border-radius: 5px;
                        overflow-x: auto;
                    }
                    .article-meta {
                        background: #f8f9fa;
                        padding: 15px;
                        border-radius: 5px;
                        margin-bottom: 30px;
                        font-size: 0.9em;
                        color: #666;
                    }
                    @media print {
                        body { margin: 0; padding: 15px; }
                        .no-print { display: none; }
}

/* Mobile Responsive - Override fixed positioning on small screens */
@media (max-width: 768px) {
    .wiki-toc,
    .wiki-tools {
        position: static !important; /* On mobile, use static positioning */
        left: auto !important;
        right: auto !important;
        width: auto !important;
        top: auto !important;
        max-height: none !important;
    }
    
    .wiki-main-content {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}
</style>
            </head>
            <body>
                <div class="article-meta">
                    <strong>Islamic Wiki</strong><br>
                    Article: ${articleTitle}<br>
                    Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}<br>
                    URL: ${window.location.href}
                </div>
                <h1>${articleTitle}</h1>
                <div class="article-content">
                    ${articleContent}
                </div>
                <script>
                    window.onload = function() {
                        // Remove any interactive elements
                        const interactiveElements = document.querySelectorAll('button, a, input, select, textarea');
                        interactiveElements.forEach(el => el.remove());
                        
                        // Trigger print dialog
                        setTimeout(() => {
                            window.print();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);
        printWindow.document.close();
        
        showToast('PDF generation window opened. Use Ctrl+P to print or save as PDF.', 'info');
    } catch (error) {
        console.error('Error in downloadPDF:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

// Close citation modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.querySelector('.citation-modal');
    if (e.target === modal) {
        closeCitationModal();
    }
});

// Initialize TOC when page loads
document.addEventListener('DOMContentLoaded', function() {
    generateTOC();
    
    // Force sticky positioning for sidebars
    function enforceStickyPositioning() {
        const toc = document.querySelector('.wiki-toc');
        const tools = document.querySelector('.wiki-tools');
        const newsbar = document.querySelector('.newsbar');
        
        if (toc) {
            // Force sticky positioning
            toc.style.position = 'sticky';
            toc.style.top = '70px';
            toc.style.zIndex = '100';
        }
        
        if (tools) {
            // Force sticky positioning
            tools.style.position = 'sticky';
            tools.style.top = '70px';
            tools.style.zIndex = '100';
            
            // Check if tools sidebar is at the bottom and should go under newsbar
            if (newsbar) {
                const toolsRect = tools.getBoundingClientRect();
                const newsbarRect = newsbar.getBoundingClientRect();
                
                // If tools sidebar bottom is near or below newsbar bottom, reduce z-index
                if (toolsRect.bottom >= newsbarRect.bottom - 10) {
                    tools.style.zIndex = '9998'; // Go under newsbar
                } else {
                    tools.style.zIndex = '100'; // Stay above other content
                }
            }
        }
    }
    
    // Apply on load and scroll
    enforceStickyPositioning();
    window.addEventListener('scroll', enforceStickyPositioning);
    window.addEventListener('resize', enforceStickyPositioning);
});
</script>

<?php include '../../includes/footer.php'; ?>
