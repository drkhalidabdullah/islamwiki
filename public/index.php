<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Home';

// Get recent wiki articles
$stmt = $pdo->query("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published'
    ORDER BY wa.published_at DESC 
    LIMIT 8
");
$recent_articles = $stmt->fetchAll();

// Get featured articles
$stmt = $pdo->query("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published' AND wa.is_featured = 1
    ORDER BY wa.published_at DESC 
    LIMIT 6
");
$featured_articles = $stmt->fetchAll();

// Get recent user posts (if logged in)
$recent_posts = [];
if (is_logged_in()) {
    $stmt = $pdo->query("
        SELECT up.*, u.username, u.display_name 
        FROM user_posts up 
        JOIN users u ON up.user_id = u.id 
        WHERE up.is_public = 1 
        ORDER BY up.created_at DESC 
        LIMIT 5
    ");
    $recent_posts = $stmt->fetchAll();
}

// Get community statistics
$total_articles = $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE status = 'published'")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $pdo->query("SELECT COUNT(*) FROM user_posts WHERE is_public = 1")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM content_categories WHERE is_active = 1")->fetchColumn();

include 'includes/header.php';
?>

<div class="homepage-container">
    <div class="homepage-layout">
        <!-- Left Sidebar -->
        <div class="homepage-sidebar">
            <!-- Quick Actions -->
            <div class="sidebar-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="/create_post" class="action-item">
                            <i class="fas fa-plus"></i>
                            <span>Create Post</span>
                        </a>
                        <a href="/create_article" class="action-item">
                            <i class="fas fa-edit"></i>
                            <span>Write Article</span>
                        </a>
                        <a href="/dashboard" class="action-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/messages" class="action-item">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                        </a>
                    <?php else: ?>
                        <a href="/register" class="action-item">
                            <i class="fas fa-user-plus"></i>
                            <span>Join Community</span>
                        </a>
                        <a href="/wiki" class="action-item">
                            <i class="fas fa-book"></i>
                            <span>Browse Articles</span>
                        </a>
                        <a href="/search" class="action-item">
                            <i class="fas fa-search"></i>
                            <span>Search Content</span>
                        </a>
                        <a href="/login" class="action-item">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Community Stats -->
            <div class="sidebar-section">
                <h3>Community Stats</h3>
                <div class="stats-list">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo number_format($total_articles); ?></span>
                            <span class="stat-label">Articles</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo number_format($total_users); ?></span>
                            <span class="stat-label">Members</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo number_format($total_posts); ?></span>
                            <span class="stat-label">Posts</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?php echo number_format($total_categories); ?></span>
                            <span class="stat-label">Categories</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Posts (if logged in) -->
            <?php if (is_logged_in() && !empty($recent_posts)): ?>
            <div class="sidebar-section">
                <h3>Recent Posts</h3>
                <div class="posts-list">
                    <?php foreach (array_slice($recent_posts, 0, 3) as $post): ?>
                    <div class="post-item">
                        <div class="post-content">
                            <h4 class="post-title">
                                <a href="/posts/<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars(truncate_text($post['content'], 50)); ?>
                                </a>
                            </h4>
                            <div class="post-meta">
                                <span class="post-author"><?php echo htmlspecialchars($post['display_name'] ?: $post['username']); ?></span>
                                <span class="post-time"><?php echo format_date($post['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="section-footer">
                    <a href="/posts" class="view-all-link">View All Posts</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <div class="homepage-main">
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-content">
                    <h1>Welcome to <?php echo SITE_NAME; ?></h1>
                    <p>Your comprehensive source for Islamic knowledge, community discussions, and educational resources.</p>
                    <?php if (!is_logged_in()): ?>
                        <div class="hero-actions">
                            <a href="/register" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i>
                                Get Started
                            </a>
                            <a href="/login" class="btn btn-secondary">
                                <i class="fas fa-sign-in-alt"></i>
                                Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Featured Articles -->
            <?php if (!empty($featured_articles)): ?>
            <section class="featured-section">
                <div class="section-header">
                    <h2>Featured Articles</h2>
                    <a href="/wiki" class="view-all-link">View All</a>
                </div>
                <div class="articles-grid">
                    <?php foreach ($featured_articles as $article): ?>
                    <div class="article-card">
                        <div class="article-header">
                            <div class="article-meta">
                                <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                <span class="article-date"><?php echo format_date($article['published_at']); ?></span>
                            </div>
                        </div>
                        <h3 class="article-title">
                            <a href="/wiki/<?php echo $article['slug']; ?>">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </a>
                        </h3>
                        <p class="article-excerpt">
                            <?php echo htmlspecialchars(truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120)); ?>
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
                    <a href="/wiki" class="view-all-link">View All</a>
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
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Community Highlights -->
            <section class="community-section">
                <div class="section-header">
                    <h2>Community Highlights</h2>
                </div>
                <div class="highlights-grid">
                    <div class="highlight-card">
                        <div class="highlight-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="highlight-content">
                            <h3>Knowledge Base</h3>
                            <p>Explore our comprehensive collection of Islamic articles and resources</p>
                            <a href="/wiki" class="highlight-link">Browse Articles</a>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <div class="highlight-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="highlight-content">
                            <h3>Community</h3>
                            <p>Connect with fellow Muslims and share knowledge through discussions</p>
                            <a href="/register" class="highlight-link">Join Community</a>
                        </div>
                    </div>
                    <div class="highlight-card">
                        <div class="highlight-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="highlight-content">
                            <h3>Search</h3>
                            <p>Find exactly what you're looking for with our powerful search engine</p>
                            <a href="/search" class="highlight-link">Start Searching</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
