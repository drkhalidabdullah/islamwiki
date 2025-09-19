                                                                                                                                <?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';
require_once __DIR__ . '/../../includes/markdown/WikiParser.php';

// Ensure createSlug function is available
if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

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
$title = $_GET['title'] ?? '';

// Handle namespace titles (e.g., Template:Colored_box)
if ($title) {
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $article_title = $parsed_title['title'];
    
    // Create slug from namespace and title (preserve case for namespace)
    $slug = $namespace['name'] . ':' . createSlug($article_title);
} elseif (!$slug) {
    redirect('index.php');
}

// Check for redirects first
$redirect = get_redirect_target($slug);
if ($redirect) {
    // Redirect to the target article
    header("Location: /wiki/" . $redirect['target_slug'], true, 301);
    exit;
}

// Get article (handle both regular articles and namespace articles)
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name,
           wn.name as namespace_name, wn.display_name as namespace_display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND (wa.status = 'published' OR (wa.status = 'draft' AND (wa.author_id = ? OR ?)))
");
$stmt->execute([$slug, ucfirst($slug), $_SESSION['user_id'] ?? 0, is_editor() ? 1 : 0]);
$article = $stmt->fetch();

if (!$article) {
    // Check if this is a template namespace request
    if ($title && strpos($title, 'Template:') === 0) {
        $template_name = substr($title, 9); // Remove "Template:" prefix
        redirect("/pages/wiki/create_template.php?name=" . urlencode($template_name));
    }
    
    // Check if this is a category namespace request
    if ($title && strpos($title, 'Category:') === 0) {
        $category_name = substr($title, 9); // Remove "Category:" prefix
        $category_slug = createSlug($category_name);
        redirect("/wiki/category/" . urlencode($category_slug));
    }
    
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Increment view count
$stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$article['id']]);

$page_title = $article['title'];

// Parse markdown content with enhanced wiki features
$parser = new WikiParser('');
$parsed_content = $parser->parse($article['content']);

// Check if article has good article template
$has_good_article = strpos($article['content'], '{{good article}}') !== false;

// Check if article has semi-protection template
$has_semi_protection = strpos($article['content'], '{{pp-semi-indef}}') !== false;

// Check if article has move-protection template
$has_move_protection = strpos($article['content'], '{{pp-move}}') !== false;

// Update article categories in database
$categories = $parser->getCategories();
if (!empty($categories)) {
    update_article_categories($article['id'], $categories);
}

// Get current article categories for display
$article_categories = get_article_categories($article['id']);

// Check if NOTITLE is enabled
$notitle_enabled = $parser->isNotitleEnabled();

// Check if NOCAT is enabled
$nocat_enabled = $parser->isNocatEnabled();

// Set global variables for magic words
$GLOBALS['current_page_name'] = $article['title'];
$GLOBALS['site_name'] = get_site_name();

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
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki.css">
<?php

// Check if this is the Main_Page (for potential future use)
$is_main_page = ($article['slug'] === 'Main_Page');
?>

<article class="card">
        <header class="article-header">
            <!-- Compact Header Layout -->
            <div class="article-header-compact<?php echo $nocat_enabled ? ' no-categories' : ''; ?>">
                <!-- Top Row: Title on left, Tools on right -->
                <div class="article-header-top<?php echo !$notitle_enabled ? ' with-title' : ''; ?>">
                    <?php if (!$notitle_enabled): ?>
                        <h1 class="article-title-compact">
                            <?php echo htmlspecialchars($article['title']); ?>
                            <?php if ($has_good_article): ?>
                                <span class="good-article-icon" title="This is a good article. It meets the quality standards for featured content.">★</span>
                            <?php endif; ?>
                            <?php if ($has_semi_protection): ?>
                                <span class="semi-protection-icon" title="This page is semi-protected. Only registered users can edit it.">🔒</span>
                            <?php endif; ?>
                            <?php if ($has_move_protection): ?>
                                <span class="move-protection-icon" title="This page is move-protected. Only administrators can move it.">↔️</span>
                            <?php endif; ?>
                        </h1>
                    <?php endif; ?>
                    <div class="article-actions-compact">
                        <a href="/wiki/<?php echo $article['slug']; ?>/history" class="btn-icon-compact" title="View History">
                            <i class="iw iw-history"></i>
                        </a>
                        <a href="/wiki/<?php echo $article['slug']; ?>/talk" class="btn-icon-compact" title="Discussion">
                            <i class="iw iw-comments"></i>
                            <?php if ($talk_page): ?>
                                <span class="talk-indicator" title="Has discussion"></span>
                            <?php endif; ?>
                        </a>
                        <?php if (is_logged_in()): ?>
                            <a href="#" class="btn-icon-compact watchlist-btn <?php echo $is_watched ? 'watched' : ''; ?>" 
                               title="<?php echo $is_watched ? 'Remove from watchlist' : 'Add to watchlist'; ?>"
                               onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                                <i class="iw iw-eye"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (is_logged_in() && is_editor()): ?>
                            <a href="/wiki/<?php echo $article['slug']; ?>/edit" class="btn-icon-compact" title="Edit Article">
                                <i class="iw iw-edit"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- Dropdown for testing - show for all users temporarily -->
                        <div class="article-actions-dropdown">
                            <button class="dropdown-toggle" onclick="toggleDropdown(this, event)" title="More actions" onmouseover="this.querySelector('.three-dots').textContent='⋮'" onmouseout="this.querySelector('.three-dots').textContent='⋯'">
                                <span class="three-dots">⋯</span>
                            </button>
                                <div class="dropdown-menu" id="articleDropdown">
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">More</div>
                                    <?php if (is_logged_in() && is_editor()): ?>
                                        <a href="#" class="dropdown-item danger" onclick="deleteArticle(<?php echo $article['id']; ?>)" title="Delete article (Alt + Shift + D)">
                                            <i class="iw iw-trash"></i>
                                            Delete
                                        </a>
                                        <a href="#" class="dropdown-item" onclick="moveArticle()" title="Move article (Alt + Shift + M)">
                                            <i class="iw iw-arrows-alt"></i>
                                            Move
                                        </a>
                                        <a href="#" class="dropdown-item" onclick="changeProtection()" title="Change protection (Alt + Shift + =)">
                                            <i class="iw iw-lock-open"></i>
                                            Change protection
                                        </a>
                                    <?php endif; ?>
                                    <a href="#" class="dropdown-item" onclick="unwatchArticle()" title="Unwatch (Alt + Shift + U)">
                                        <i class="iw iw-star"></i>
                                        Unwatch
                                    </a>
                                    <a href="#" class="dropdown-item" onclick="purgeCache()" title="Purge cache (Alt + Shift + P)">
                                        <i class="iw iw-sync-alt"></i>
                                        Purge cache
                                    </a>
                                </div>
                                <div class="dropdown-section">
                                    <div class="dropdown-section-title">Tools</div>
                                    <a href="/wiki/special/what-links-here?target=<?php echo urlencode($article['title']); ?>" class="dropdown-item" title="What links here (Alt + Shift + J)">
                                        <i class="iw iw-external-link-alt"></i>
                                        What links here
                                    </a>
                                    <a href="/wiki/special/recent-changes?target=<?php echo urlencode($article['title']); ?>" class="dropdown-item" title="Related changes (Alt + Shift + K)">
                                        <i class="iw iw-history"></i>
                                        Related changes
                                    </a>
                                    <a href="/wiki/special/page-info?title=<?php echo urlencode($article['title']); ?>" class="dropdown-item" title="Page information (Alt + Shift + I)">
                                        <i class="iw iw-info-circle"></i>
                                        Page information
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Categories Section -->
                <?php if (!empty($article_categories) && !$nocat_enabled): ?>
                <div class="article-categories-top">
                    <div class="categories-list">
                        <?php foreach ($article_categories as $category): ?>
                        <a href="/wiki/category/<?php echo htmlspecialchars($category['slug']); ?>" 
                           class="category-button" 
                           title="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Bottom Row: Date and Views on right -->
                <div class="article-header-bottom">
                    <div class="article-category-compact">
                        <!-- Categories are now displayed at the top as buttons -->
                    </div>
                    <div class="article-meta-compact">
                        <span class="article-date-compact">
                            <i class="iw iw-calendar"></i>
                            <?php echo format_date($article['published_at']); ?>
                        </span>
                        <span class="article-views-compact">
                            <i class="iw iw-eye"></i>
                            <?php echo number_format($article['view_count']); ?> views
                        </span>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Three-column layout: 15% | 70% | 15% -->
        <div class="article-page">
            <div class="article-container">
                <div class="wiki-layout<?php echo (!$parser->isTocEnabled() || (count($parser->getHeadings()) < 3 && !$parser->isTocForced())) ? ' no-toc' : ''; ?>">
            <!-- Left Sidebar: Table of Contents -->
            <?php if ($parser->isTocEnabled() && (count($parser->getHeadings()) >= 3 || $parser->isTocForced())): ?>
            <aside class="wiki-toc">
                <div class="toc-header">
                    <h3>Contents</h3>
                    <button class="toc-toggle" onclick="toggleTOC()">
                        <i class="iw iw-chevron-down"></i>
                    </button>
                </div>
                <div class="toc-content" id="toc-content">
                    <div class="toc-loading">
                        <i class="iw iw-spinner iw-spin"></i> Generating table of contents...
                    </div>
                </div>
            </aside>
            <?php endif; ?>
            
            <!-- Main Content Area -->
            <main class="wiki-main-content<?php echo (!$parser->isTocEnabled() || (count($parser->getHeadings()) < 3 && !$parser->isTocForced())) ? ' no-toc' : ''; ?>">
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
                                <i class="iw iw-link"></i>
                                <span>What links here</span>
                            </a>
                        </li>
                        <li>
                            <a href="/wiki/special/page-info?slug=<?php echo urlencode($article['slug']); ?>" class="tool-link">
                                <i class="iw iw-info-circle"></i>
                                <span>Page information</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="tool-link" onclick="citePage()">
                                <i class="iw iw-quote-left"></i>
                                <span>Cite this page</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="tool-link" onclick="downloadPDF()">
                                <i class="iw iw-download"></i>
                                <span>Download as PDF</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="tools-section">
                    <h3>Page Statistics</h3>
                    <div class="stats-list">
                        <div class="stat-item">
                            <i class="iw iw-eye"></i>
                            <span><?php echo number_format($article['view_count']); ?> views</span>
                        </div>
                        <div class="stat-item">
                            <i class="iw iw-calendar"></i>
                            <span>Created <?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="iw iw-edit"></i>
                            <span>Last edited <?php echo date('M j, Y', strtotime($article['updated_at'])); ?></span>
                        </div>
                        <!-- Categories are now displayed at the top as buttons -->
                    </div>
                </div>
                
                <div class="tools-section">
                    <h3>Quick Actions</h3>
                    <ul class="tools-list">
                        <?php if (is_logged_in() && is_editor()): ?>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/edit" class="tool-link">
                                <i class="iw iw-edit"></i>
                                <span>Edit this page</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/history" class="tool-link">
                                <i class="iw iw-history"></i>
                                <span>View history</span>
                            </a>
                        </li>
                        <li>
                            <a href="/wiki/<?php echo $article['slug']; ?>/talk" class="tool-link">
                                <i class="iw iw-comments"></i>
                                <span>Discussion</span>
                            </a>
                        </li>
                        <?php if (is_logged_in()): ?>
                        <li>
                            <a href="#" class="tool-link" onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                                <i class="iw iw-eye"></i>
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
                    <i class="iw iw-flag"></i> Report Content
                </button>
            </div>
            
            <!-- Guest Engagement Banner -->
            <?php if (!is_logged_in()): ?>
            <div class="guest-engagement-banner">
                <div class="banner-content">
                    <div class="banner-icon">
                        <i class="iw iw-users"></i>
                    </div>
                    <div class="banner-text">
                        <h4>Join the Community</h4>
                        <p>Sign up to contribute to this article, edit content, and connect with other members</p>
                    </div>
                    <div class="banner-actions">
                        <a href="/register" class="btn btn-primary">
                            <i class="iw iw-user-plus"></i> Get Started
                        </a>
                        <a href="/login" class="btn btn-outline">
                            <i class="iw iw-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
    </article>
    
    <!-- Related Articles -->
    <?php
    // Get related articles based on shared categories
    $article_categories = get_article_categories($article['id']);
    if (!empty($article_categories)) {
        $category_ids = array_column($article_categories, 'id');
        $placeholders = str_repeat('?,', count($category_ids) - 1) . '?';
        
        $stmt = $pdo->prepare("
            SELECT DISTINCT wa.*, u.display_name, u.username 
            FROM wiki_articles wa 
            JOIN users u ON wa.author_id = u.id 
            JOIN wiki_article_categories wac ON wa.id = wac.article_id
            WHERE wac.category_id IN ($placeholders) AND wa.id != ? AND wa.status = 'published' 
            ORDER BY wa.published_at DESC 
            LIMIT 3
        ");
        $params = array_merge($category_ids, [$article['id']]);
        $stmt->execute($params);
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
    <?php 
        endif;
    }
    ?>

    <!-- Footer -->
    <?php include_once '/var/www/html/public/includes/footer.php'; ?>

<script>
// Dropdown functionality
function toggleDropdown(button, event) {
    // Prevent event propagation to avoid immediate closing
    if (event) {
        event.stopPropagation();
    }
    
    console.log('Dropdown button clicked!');
    const dropdown = button.nextElementSibling;
    const isOpen = dropdown.classList.contains('show');
    
    console.log('Dropdown element:', dropdown);
    console.log('Is open:', isOpen);
    console.log('Dropdown classes before:', dropdown.className);
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== dropdown) {
            menu.classList.remove('show');
            menu.style.opacity = '0';
            menu.style.visibility = 'hidden';
            menu.style.transform = 'translateY(-8px) scale(0.95)';
        }
    });
    
    // Toggle current dropdown
    if (isOpen) {
        dropdown.classList.remove('show');
        dropdown.style.opacity = '0';
        dropdown.style.visibility = 'hidden';
        dropdown.style.transform = 'translateY(-8px) scale(0.95)';
        console.log('Closing dropdown');
    } else {
        dropdown.classList.add('show');
        dropdown.style.opacity = '1';
        dropdown.style.visibility = 'visible';
        dropdown.style.transform = 'translateY(0) scale(1)';
        console.log('Opening dropdown');
    }
    
    console.log('Dropdown classes after:', dropdown.className);
    console.log('Dropdown computed style:', window.getComputedStyle(dropdown).opacity);
}

// Close dropdown when clicking outside or pressing ESC
function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        menu.classList.remove('show');
        menu.style.opacity = '0';
        menu.style.visibility = 'hidden';
        menu.style.transform = 'translateY(-8px) scale(0.95)';
    });
}

// Click outside to close
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.article-actions-dropdown');
    const isOpen = dropdown && dropdown.querySelector('.dropdown-menu.show');
    
    console.log('Click detected on:', event.target);
    console.log('Dropdown element:', dropdown);
    console.log('Is dropdown open:', !!isOpen);
    console.log('Dropdown contains target:', dropdown && dropdown.contains(event.target));
    
    if (dropdown && isOpen && !dropdown.contains(event.target)) {
        console.log('Closing dropdown - clicked outside');
        closeAllDropdowns();
    }
});

// Delete article function
function deleteArticle(articleId) {
    if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
        window.location.href = '../delete_article.php?id=' + articleId;
    }
}

// Placeholder functions for other actions
function moveArticle() {
    alert('Move article functionality coming soon!');
}

function changeProtection() {
    alert('Change protection functionality coming soon!');
}

function unwatchArticle() {
    alert('Unwatch functionality coming soon!');
}

function purgeCache() {
    alert('Purge cache functionality coming soon!');
}

// Combined keyboard shortcuts and ESC key handler
document.addEventListener('keydown', function(event) {
    // ESC key to close dropdown
    if (event.key === 'Escape') {
        console.log('ESC key pressed - closing dropdown');
        closeAllDropdowns();
        return;
    }
    
    // Only trigger shortcuts if no input/textarea is focused
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.tagName === 'SELECT') {
        return;
    }
    
    // Debug: Log key combinations
    if (event.altKey && event.shiftKey) {
        console.log('Alt + Shift + ' + event.code + ' pressed');
    }
    
    // Alt + Shift + D for delete
    if (event.altKey && event.shiftKey && event.code === 'KeyD') {
        event.preventDefault();
        console.log('Delete shortcut triggered');
        const deleteButton = document.querySelector('.dropdown-item.danger');
        if (deleteButton) {
            deleteButton.click();
        } else {
            console.log('Delete button not found');
        }
    }
    
    // Alt + Shift + M for move
    if (event.altKey && event.shiftKey && event.code === 'KeyM') {
        event.preventDefault();
        const moveButton = document.querySelector('.dropdown-item[onclick="moveArticle()"]');
        if (moveButton) {
            moveButton.click();
        }
    }
    
    // Alt + Shift + = for change protection
    if (event.altKey && event.shiftKey && event.code === 'Equal') {
        event.preventDefault();
        const protectionButton = document.querySelector('.dropdown-item[onclick="changeProtection()"]');
        if (protectionButton) {
            protectionButton.click();
        }
    }
    
    // Alt + Shift + U for unwatch
    if (event.altKey && event.shiftKey && event.code === 'KeyU') {
        event.preventDefault();
        const unwatchButton = document.querySelector('.dropdown-item[onclick="unwatchArticle()"]');
        if (unwatchButton) {
            unwatchButton.click();
        }
    }
    
    // Alt + Shift + P for purge cache
    if (event.altKey && event.shiftKey && event.code === 'KeyP') {
        event.preventDefault();
        const purgeButton = document.querySelector('.dropdown-item[onclick="purgeCache()"]');
        if (purgeButton) {
            purgeButton.click();
        }
    }
    
    // Alt + Shift + J for what links here
    if (event.altKey && event.shiftKey && event.code === 'KeyJ') {
        event.preventDefault();
        const whatLinksButton = document.querySelector('a[href*="what-links-here"]');
        if (whatLinksButton) {
            whatLinksButton.click();
        }
    }
    
    // Alt + Shift + K for related changes
    if (event.altKey && event.shiftKey && event.code === 'KeyK') {
        event.preventDefault();
        const relatedChangesButton = document.querySelector('a[href*="recent-changes"]');
        if (relatedChangesButton) {
            relatedChangesButton.click();
        }
    }
    
    // Alt + Shift + I for page information
    if (event.altKey && event.shiftKey && event.code === 'KeyI') {
        event.preventDefault();
        const pageInfoButton = document.querySelector('a[href*="page-info"]');
        if (pageInfoButton) {
            pageInfoButton.click();
        }
    }
});
</script>

