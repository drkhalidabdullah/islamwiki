<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'Wiki';

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $stmt->fetchAll();

// Get featured articles with proper permissions
$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = is_logged_in();
$is_editor = is_editor();

$where_conditions = ["wa.is_featured = 1"];
$params = [];

if (!$is_logged_in) {
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.published_at DESC 
    LIMIT 6
");
$stmt->execute($params);
$featured_articles = $stmt->fetchAll();

// Get recent articles with proper permissions
$where_conditions = [];
$params = [];

if (!$is_logged_in) {
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.published_at DESC 
    LIMIT 8
");
$stmt->execute($params);
$recent_articles = $stmt->fetchAll();

// Get popular articles
$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.view_count DESC 
    LIMIT 5
");
$stmt->execute($params);
$popular_articles = $stmt->fetchAll();

// Get all published articles for the comprehensive list
$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published'
    ORDER BY wa.title ASC
");
$stmt->execute();
$all_articles = $stmt->fetchAll();

// Get article statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_articles FROM wiki_articles WHERE status = 'published'");
$total_articles = $stmt->fetch()['total_articles'];

$stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM content_categories WHERE is_active = 1");
$total_categories = $stmt->fetch()['total_categories'];

include "../../includes/header.php";
?>

<div class="wiki-page-container">
    <div class="wiki-layout">
        <!-- Left Sidebar -->
        <div class="wiki-sidebar">
            <div class="sidebar-section">
                <h3>Browse Categories</h3>
                <div class="category-list">
                    <?php foreach ($categories as $category): ?>
                    <a href="category.php?slug=<?php echo $category['slug']; ?>" class="category-item">
                        <i class="fas fa-folder"></i>
                        <span><?php echo htmlspecialchars($category['name']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Popular Articles -->
            <?php if (!empty($popular_articles)): ?>
            <div class="sidebar-section">
                <h3>Popular Articles</h3>
                <div class="popular-list">
                    <?php foreach ($popular_articles as $article): ?>
                    <a href="/wiki/<?php echo $article['slug']; ?>" class="popular-item">
                        <span class="popular-title"><?php echo htmlspecialchars($article['title']); ?></span>
                        <span class="popular-views"><?php echo number_format($article['view_count']); ?> views</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="sidebar-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="/search?type=articles" class="action-item">
                        <i class="fas fa-search"></i>
                        <span>Search Articles</span>
                    </a>
                    <?php if ($is_logged_in && $is_editor): ?>
                    <a href="/create_article" class="action-item">
                        <i class="fas fa-plus"></i>
                        <span>Create Article</span>
                    </a>
                    <?php endif; ?>
                    <a href="#all-articles" class="action-item" onclick="document.querySelector('.all-articles-section').scrollIntoView({behavior: 'smooth'});">
                        <i class="fas fa-list"></i>
                        <span>Browse All Articles</span>
                    </a>
                    <a href="/wiki" class="action-item">
                        <i class="fas fa-random"></i>
                        <span>Random Article</span>
                    </a>
                </div>
            </div>

            <!-- Alphabet Navigation -->
            <?php if (!empty($all_articles)): ?>
            <div class="sidebar-section">
                <h3>Browse by Letter</h3>
                <div class="alphabet-nav">
                    <?php 
                    $letters = [];
                    foreach ($all_articles as $article) {
                        $letter = strtoupper(substr($article['title'], 0, 1));
                        if (!in_array($letter, $letters)) {
                            $letters[] = $letter;
                        }
                    }
                    sort($letters);
                    foreach ($letters as $letter): ?>
                        <a href="#letter-<?php echo $letter; ?>" class="alphabet-link" onclick="document.querySelector('#letter-<?php echo $letter; ?>').scrollIntoView({behavior: 'smooth'});">
                            <?php echo $letter; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Wiki Content -->
        <div class="wiki-main">
            <!-- Wiki Header -->
            <div class="wiki-header">
                <h1>Islamic Knowledge Wiki</h1>
                <p>Explore comprehensive articles about Islam, Islamic history, and Islamic teachings</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_articles); ?></h3>
                        <p>Articles</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($total_categories); ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format(array_sum(array_column($recent_articles, 'view_count'))); ?></h3>
                        <p>Total Views</p>
                    </div>
                </div>
            </div>

            <!-- Featured Articles -->
            <?php if (!empty($featured_articles)): ?>
            <section class="featured-section">
                <div class="section-header">
                    <h2>Featured Articles</h2>
                    <a href="/search?type=articles&featured=1" class="view-all-link">View All</a>
                </div>
                <div class="articles-grid">
                    <?php foreach ($featured_articles as $article): ?>
                    <div class="article-card">
                        <div class="article-header">
                            <div class="article-meta">
                                <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <span class="article-date"><?php echo format_date($article['published_at']); ?></span>
                            </div>
                            <?php if ($article['status'] === 'draft'): ?>
                                <span class="draft-badge">Draft</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="article-title">
                            <a href="/wiki/<?php echo $article['slug']; ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="article-excerpt">
                            <?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120); ?>
                        </p>
                        <div class="article-footer">
                            <div class="article-stats">
                                <span class="views">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($article['view_count']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Recent Articles -->
            <?php if (!empty($recent_articles)): ?>
            <section class="recent-section">
                <div class="section-header">
                    <h2>Recent Articles</h2>
                    <a href="/search?type=articles&sort=date" class="view-all-link">View All</a>
                </div>
                <div class="recent-articles-list">
                    <?php foreach ($recent_articles as $article): ?>
                    <div class="recent-article-item">
                        <div class="recent-article-content">
                            <h4 class="recent-title">
                                <a href="/wiki/<?php echo $article['slug']; ?>">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </h4>
                            <div class="recent-meta">
                                <span class="recent-category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <span class="recent-date"><?php echo format_date($article['published_at']); ?></span>
                                <span class="recent-views"><?php echo number_format($article['view_count']); ?> views</span>
                            </div>
                        </div>
                        <?php if ($article['status'] === 'draft'): ?>
                            <span class="draft-indicator">Draft</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- All Articles Section -->
            <?php if (!empty($all_articles)): ?>
            <section class="all-articles-section" id="all-articles">
                <div class="section-header">
                    <h2>All Wiki Articles</h2>
                    <div class="articles-count"><?php echo count($all_articles); ?> articles</div>
                </div>
                <div class="articles-alphabetical">
                    <?php 
                    $current_letter = '';
                    $letter_count = 0;
                    foreach ($all_articles as $article): 
                        $first_letter = strtoupper(substr($article['title'], 0, 1));
                        if ($first_letter !== $current_letter):
                            if ($current_letter !== ''): ?>
                                </div>
                            <?php endif; ?>
                            <div class="alphabet-section" id="letter-<?php echo $first_letter; ?>">
                                <h3 class="alphabet-header"><?php echo $first_letter; ?></h3>
                                <div class="alphabet-articles">
                            <?php 
                            $current_letter = $first_letter;
                            $letter_count = 0;
                        endif;
                        $letter_count++;
                    ?>
                        <div class="article-link-item">
                            <a href="/wiki/<?php echo $article['slug']; ?>" class="article-link">
                                <span class="article-title"><?php echo htmlspecialchars($article['title']); ?></span>
                                <?php if ($article['category_name']): ?>
                                    <span class="article-category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <?php endif; ?>
                                <span class="article-views"><?php echo number_format($article['view_count']); ?> views</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($current_letter !== ''): ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
