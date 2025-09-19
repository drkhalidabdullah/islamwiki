<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';
require_once '../../includes/markdown/MarkdownParser.php';
require_once '../../includes/markdown/WikiParser.php';

// Make PDO available globally for WikiParser
$GLOBALS['pdo'] = $pdo;

$page_title = 'Feed';
check_maintenance_mode();
require_login();

$current_user = get_user($_SESSION['user_id']);

// Check if comments are enabled
$enable_comments = get_system_setting('enable_comments', true);

// Get user stats
$user_stats = [
    'articles_count' => 0,
    'posts_count' => 0,
    'followers_count' => 0
];

// Get user's article count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE author_id = ? AND status = 'published'");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['articles_count'] = $stmt->fetchColumn();

// Get user's post count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_posts WHERE user_id = ? AND is_public = 1");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['posts_count'] = $stmt->fetchColumn();

// Get user's followers count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['followers_count'] = $stmt->fetchColumn();

// Get user's following list for personalized feed
$stmt = $pdo->prepare("
    SELECT uf.following_id, u.username, u.display_name, u.avatar
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ?
    ORDER BY uf.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$following = $stmt->fetchAll();

// Get personalized feed content
$feed_items = [];

// Get recent posts from followed users
if (!empty($following)) {
    $following_ids = array_column($following, 'following_id');
    $placeholders = str_repeat('?,', count($following_ids) - 1) . '?';
    
    // Get posts from followed users only
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
               COALESCE(like_counts.likes_count, 0) as likes_count,
               COALESCE(comment_counts.comments_count, 0) as comments_count,
               COALESCE(share_counts.shares_count, 0) as shares_count
        FROM user_posts up
        JOIN users u ON up.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as likes_count 
            FROM post_interactions 
            WHERE interaction_type = 'like' 
            GROUP BY post_id
        ) like_counts ON up.id = like_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as comments_count 
            FROM post_comments 
            GROUP BY post_id
        ) comment_counts ON up.id = comment_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as shares_count 
            FROM post_interactions 
            WHERE interaction_type = 'share' 
            GROUP BY post_id
        ) share_counts ON up.id = share_counts.post_id
        WHERE up.user_id IN ($placeholders) AND up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 50
    ");
    $stmt->execute($following_ids);
    $following_posts = $stmt->fetchAll();
    
    // Get articles from followed users only
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name, u.avatar, 'article' as content_type
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        WHERE wa.author_id IN ($placeholders) AND wa.status = 'published'
        ORDER BY wa.published_at DESC
        LIMIT 50
    ");
    $stmt->execute($following_ids);
    $following_articles = $stmt->fetchAll();
    
    $feed_items = array_merge($following_posts, $following_articles);
}

// If no following content, get recent public posts
if (empty($feed_items)) {
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
               COALESCE(like_counts.likes_count, 0) as likes_count,
               COALESCE(comment_counts.comments_count, 0) as comments_count,
               COALESCE(share_counts.shares_count, 0) as shares_count
        FROM user_posts up
        JOIN users u ON up.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as likes_count 
            FROM post_interactions 
            WHERE interaction_type = 'like' 
            GROUP BY post_id
        ) like_counts ON up.id = like_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as comments_count 
            FROM post_comments 
            GROUP BY post_id
        ) comment_counts ON up.id = comment_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as shares_count 
            FROM post_interactions 
            WHERE interaction_type = 'share' 
            GROUP BY post_id
        ) share_counts ON up.id = share_counts.post_id
        WHERE up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $feed_items = $stmt->fetchAll();
}

// Filter out articles with unparsed template syntax
$feed_items = array_filter($feed_items, function($item) {
    if ($item['content_type'] === 'article') {
        // Check if article contains unparsed template syntax (both {{ and }} must be present)
        return !(strpos($item['content'], '{{') !== false && strpos($item['content'], '}}') !== false);
    }
    return true;
});

// Sort by creation date
usort($feed_items, function($a, $b) {
    $date_a = $a['content_type'] === 'post' ? $a['created_at'] : $a['published_at'];
    $date_b = $b['content_type'] === 'post' ? $b['created_at'] : $b['published_at'];
    return strtotime($date_b) - strtotime($date_a);
});

include_once '../../includes/header.php';
?>

<script src="/skins/bismillah/assets/js/dashboard.js"></script>
<script src="/skins/bismillah/assets/js/mentions.js"></script>
<link rel="stylesheet" href="/skins/bismillah/assets/css/dashboard.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/mentions.css">

<div class="dashboard-container">
    <div class="dashboard-layout">
        <!-- Left Sidebar -->
        <div class="dashboard-sidebar">
            <!-- User Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($current_user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="Profile">
                        <?php else: ?>
                            <div class="avatar-circle">
                                <?php echo strtoupper(substr($current_user['display_name'] ?: $current_user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?></h3>
                        <p>@<?php echo htmlspecialchars($current_user['username']); ?></p>
                    </div>
                </div>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['articles_count'] ?? 0); ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['posts_count'] ?? 0); ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['followers_count'] ?? 0); ?></span>
                        <span class="stat-label">Followers</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <h4>Quick Actions</h4>
                <div class="action-buttons">
                    <a href="/pages/social/create_post.php" class="action-btn">
                        <i class="iw iw-edit"></i>
                        <span>Create Post</span>
                    </a>
                    <?php if (is_editor()): ?>
                    <a href="/pages/wiki/create_article.php" class="action-btn">
                        <i class="iw iw-file-alt"></i>
                        <span>New Article</span>
                    </a>
                    <?php endif; ?>
                    <a href="/search" class="action-btn">
                        <i class="iw iw-search"></i>
                        <span>Search</span>
                    </a>
                    <a href="/pages/user/settings.php" class="action-btn">
                        <i class="iw iw-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
            
            <!-- Trending Topics -->
            <div class="trending-card">
                <h4>Trending</h4>
                <div class="trending-list">
                    <?php
                    // Get trending articles
                    $stmt = $pdo->prepare("
                        SELECT wa.*, u.username, u.display_name
                        FROM wiki_articles wa
                        JOIN users u ON wa.author_id = u.id
                        WHERE wa.status = 'published'
                        ORDER BY wa.view_count DESC
                        LIMIT 5
                    ");
                    $stmt->execute();
                    $trending_articles = $stmt->fetchAll();
                    ?>
                    <?php foreach ($trending_articles as $article): ?>
                    <div class="trending-item">
                        <a href="/wiki/<?php echo $article['slug']; ?>">
                            <h5><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p><?php echo number_format($article['view_count']); ?> views</p>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Feed -->
        <div class="dashboard-main">
            <!-- Feed Header -->
            <div class="feed-header">
                <h1>Your Feed</h1>
                <div class="feed-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="posts">Posts</button>
                    <button class="filter-btn" data-filter="articles">Articles</button>
                    <button class="filter-btn" data-filter="following">Following</button>
                </div>
            </div>

            <!-- Create Post Component -->
            <div class="create-post-card">
                <div class="create-post-header">
                    <div class="author-info">
                        <?php if (!empty($current_user['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="User" class="author-avatar">
                        <?php else: ?>
                            <div class="author-avatar avatar-circle">
                                <?php echo strtoupper(substr($current_user['display_name'] ?: $current_user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <div class="author-details">
                            <span class="author-name"><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="create-post-content">
                    <textarea id="post-content" placeholder="What's on your mind? Share your thoughts, knowledge, or ask a question..." rows="3"></textarea>
                    <div class="post-actions">
                        <div class="post-tools">
                            <button type="button" class="tool-btn" id="image-upload-btn" title="Add Image">
                                <i class="iw iw-image"></i>
                            </button>
                            <button type="button" class="tool-btn" id="markdown-btn" title="Markdown Help">
                                <i class="iw iw-code"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-primary" id="publish-post">Post</button>
                    </div>
                </div>
            </div>

            <!-- Feed Content -->
            <div class="feed-content">
                <div class="unified-feed">
                    <!-- All Content -->
                    <div class="feed-section" id="all-content">
                        <?php if (!empty($feed_items)): ?>
                            <?php foreach (array_slice($feed_items, 0, 20) as $item): ?>
                            <div class="feed-item" data-type="<?php echo $item['content_type']; ?>" data-filter="all" <?php echo $item['content_type'] === 'post' ? 'data-post-id="' . $item['id'] . '"' : ''; ?>>
                                <?php if ($item['content_type'] === 'post'): ?>
                                <div class="card-header">
                                    <div class="author-info">
                                        <?php if (!empty($item['avatar'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['avatar']); ?>" alt="User" class="author-avatar">
                                        <?php else: ?>
                                            <div class="author-avatar avatar-circle">
                                                <?php echo strtoupper(substr($item['display_name'] ?: $item['username'], 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="author-details">
                                            <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>" class="author-name">
                                                <?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?>
                                            </a>
                                            <span class="post-time">
                                                <?php echo format_date($item['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="content-type-badge">
                                        <?php echo ucfirst($item['content_type']); ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <div class="post-content"><?php 
                                        $parser = new MarkdownParser();
                                        $markdown_content = $parser->parse($item['content']);
                                        echo parse_mentions($markdown_content);
                                    ?></div>
                                    <?php if (!empty($item['image_url'])): ?>
                                    <div class="post-image">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Post Image" onclick="openImageModal('<?php echo htmlspecialchars($item['image_url']); ?>')">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <span><i class="iw iw-comment"></i> <?php echo $item['comments_count']; ?></span>
                                        <span><i class="iw iw-heart"></i> <?php echo $item['likes_count']; ?></span>
                                        <span><i class="iw iw-share"></i> <?php echo $item['shares_count']; ?></span>
                                    </div>
                                    <div class="post-actions">
                                        <button class="action-btn like-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-heart"></i></button>
                                        <?php if ($enable_comments): ?>
                                        <button class="action-btn comment-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-comment"></i></button>
                                        <?php endif; ?>
                                        <button class="action-btn share-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-share"></i></button>
                                    </div>
                                </div>
                                <?php else: // Article ?>
                                <div class="card-header">
                                    <div class="article-header-left">
                                        <!-- Empty space for alignment -->
                                    </div>
                                    <span class="content-type-badge">
                                        <?php echo ucfirst($item['content_type']); ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h4><a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                                    <?php
                                    // Parse the article content to remove templates and get clean excerpt
                                    $parser = new WikiParser();
                                    $parsed_content = $parser->parse($item['content']);
                                    $clean_content = strip_tags($parsed_content);
                                    
                                    // Filter out content that still contains unparsed templates
                                    if (strpos($clean_content, '{{') !== false || strpos($clean_content, '}}') !== false) {
                                        $clean_content = preg_replace('/\{\{[^}]*\}\}/', '', $clean_content);
                                        $clean_content = preg_replace('/\{\{[^}]*$/', '', $clean_content);
                                    }
                                    
                                    $excerpt = strlen($clean_content) > 150 ? substr($clean_content, 0, 150) . '...' : $clean_content;
                                    ?>
                                    <p><?php echo htmlspecialchars($excerpt); ?></p>
                                    <?php 
                                    // Get categories for this article
                                    $article_categories = get_article_categories($item['id']);
                                    if (!empty($article_categories)): 
                                    ?>
                                    <div class="category-tags">
                                        <?php foreach ($article_categories as $category): ?>
                                        <span class="category-tag">
                                            <a href="/wiki/category/<?php echo htmlspecialchars($category['slug']); ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </a>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <span><i class="iw iw-eye"></i> <?php echo number_format($item['view_count']); ?></span>
                                        <span><i class="iw iw-comment"></i> 0</span>
                                        <span><i class="iw iw-heart"></i> 0</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <h3>No posts yet</h3>
                                <p>Be the first to share something with the community!</p>
                                <a href="/create_post" class="btn btn-primary">Create Post</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="dashboard-rightbar">
            <!-- My Content -->
            <div class="my-content-card">
                <h4>My Content</h4>
                <div class="content-tabs">
                    <button class="tab-btn active" data-tab="articles">Articles</button>
                    <button class="tab-btn" data-tab="posts">Posts</button>
                </div>
                
                <div class="tab-content" id="articles-tab">
                    <?php
                    // Get user's recent articles
                    $stmt = $pdo->prepare("
                        SELECT wa.*, u.username, u.display_name
                        FROM wiki_articles wa
                        JOIN users u ON wa.author_id = u.id
                        WHERE wa.author_id = ? AND wa.status = 'published'
                        ORDER BY wa.published_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $my_articles = $stmt->fetchAll();
                    ?>
                    <?php if (!empty($my_articles)): ?>
                        <?php foreach ($my_articles as $article): ?>
                        <div class="content-item">
                            <h5><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h5>
                            <p class="content-meta">
                                <?php echo format_date($article['published_at']); ?> • 
                                <?php echo number_format($article['view_count']); ?> views
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-content">No articles yet</p>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="posts-tab" style="display: none;">
                    <?php
                    // Get user's recent posts
                    $stmt = $pdo->prepare("
                        SELECT up.*, u.username, u.display_name
                        FROM user_posts up
                        JOIN users u ON up.user_id = u.id
                        WHERE up.user_id = ? AND up.is_public = 1
                        ORDER BY up.created_at DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $my_posts = $stmt->fetchAll();
                    ?>
                    <?php if (!empty($my_posts)): ?>
                        <?php foreach ($my_posts as $post): ?>
                        <div class="content-item">
                            <h5><?php echo htmlspecialchars(truncate_text(strip_tags($post['content']), 50)); ?></h5>
                            <p class="content-meta">
                                <?php echo format_date($post['created_at']); ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-content">No posts yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Following -->
            <div class="following-card">
                <h4>Following</h4>
                <?php if (!empty($following)): ?>
                    <?php foreach (array_slice($following, 0, 5) as $followed_user): ?>
                    <div class="following-item">
                        <div class="following-avatar">
                            <?php if (!empty($followed_user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($followed_user['avatar']); ?>" alt="User">
                            <?php else: ?>
                                <div class="avatar-circle">
                                    <?php echo strtoupper(substr($followed_user['display_name'] ?: $followed_user['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="following-info">
                            <h5><a href="/pages/user/user_profile.php?username=<?php echo urlencode($followed_user['username']); ?>"><?php echo htmlspecialchars($followed_user['display_name'] ?: $followed_user['username']); ?></a></h5>
                            <p>@<?php echo htmlspecialchars($followed_user['username']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-content">Not following anyone yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="modalImage" src="" alt="Full size image">
    </div>
</div>

<script>
// Image modal
window.openImageModal = function(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modalImg.src = imageSrc;
};

// Close modal
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('imageModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('imageModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>
