<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';
require_once '../../includes/markdown/MarkdownParser.php';
require_once '../../includes/markdown/WikiParser.php';

$page_title = 'Dashboard';
check_maintenance_mode();
require_login();

$current_user = get_user($_SESSION['user_id']);

// Check if comments are enabled
$enable_comments = get_system_setting('enable_comments', true);

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
        LIMIT 10
    ");
    $stmt->execute($following_ids);
    $followed_posts = $stmt->fetchAll();
    
    foreach ($followed_posts as $post) {
        $feed_items[] = $post;
    }
}

// Get recent public posts from all users (including current user)
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
    LIMIT 15
");
$stmt->execute();
$recent_posts = $stmt->fetchAll();

// Get recent published articles
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, 'article' as content_type
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
    -- Categories now handled via wiki_categories table
    WHERE wa.status = 'published'
    ORDER BY wa.published_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_articles = $stmt->fetchAll();

// Get featured articles
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, 'featured_article' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    -- Categories now handled via wiki_categories table
    WHERE wa.status = 'published' AND wa.is_featured = 1
    ORDER BY wa.published_at DESC
    LIMIT 5
");
$stmt->execute();
$featured_articles = $stmt->fetchAll();

// Get user's own content
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, 'my_article' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    -- Categories now handled via wiki_categories table
    WHERE wa.author_id = ?
        ORDER BY wa.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$my_articles = $stmt->fetchAll();

// Get user's posts
$stmt = $pdo->prepare("
    SELECT up.*, u.username, u.display_name, u.avatar, 'my_post' as content_type
    FROM user_posts up
    JOIN users u ON up.user_id = u.id
    WHERE up.user_id = ?
    ORDER BY up.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$my_posts = $stmt->fetchAll();

// Get user's watchlist
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, 'watchlist' as content_type
    FROM user_watchlists uw
    JOIN wiki_articles wa ON uw.article_id = wa.id
    JOIN users u ON wa.author_id = u.id
    -- Categories now handled via wiki_categories table
    WHERE uw.user_id = ? AND wa.status = 'published'
    ORDER BY uw.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$watchlist_articles = $stmt->fetchAll();

// Get trending content
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, 'trending' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    -- Categories now handled via wiki_categories table
    WHERE wa.status = 'published'
    ORDER BY wa.view_count DESC, wa.published_at DESC
    LIMIT 5
    ");
    $stmt->execute();
$trending_articles = $stmt->fetchAll();

// Get user statistics
$user_stats = [
    'articles_count' => count($my_articles),
    'posts_count' => count($my_posts),
    'following_count' => count($following),
    'watchlist_count' => count($watchlist_articles)
];

// Get follower count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['followers_count'] = $stmt->fetchColumn();

// Get total views on user's articles
$stmt = $pdo->prepare("SELECT SUM(view_count) FROM wiki_articles WHERE author_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['total_views'] = $stmt->fetchColumn() ?: 0;

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/dashboard.js"></script>
<script src="/skins/bismillah/assets/js/mentions.js"></script>
<script src="/skins/bismillah/assets/js/user_profile.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/dashboard.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/mentions.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
<?php
?>

<div class="dashboard-container">
    <div class="dashboard-layout">
        <!-- Left Sidebar -->
        <div class="dashboard-sidebar">
            <!-- User Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar-container">
                        <div class="profile-picture-wrapper" onclick="openProfilePictureModal()">
                            <?php if (!empty($current_user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="Profile" class="profile-image">
                            <?php else: ?>
                                <div class="profile-initials">
                                    <?php echo strtoupper(substr($current_user['display_name'] ?: $current_user['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="camera-button">
                                <i class="iw iw-camera"></i>
                            </div>
                            <div class="online-indicator"></div>
                        </div>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?></h3>
                        <p>@<?php echo htmlspecialchars($current_user['username']); ?></p>
                    </div>
                </div>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['articles_count']); ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['posts_count']); ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['followers_count']); ?></span>
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
                <h1><?php echo get_personalized_greeting($current_user); ?></h1>
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
                    <?php if (!empty($current_user['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($current_user['avatar']); ?>" alt="User" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar avatar-circle">
                            <?php echo strtoupper(substr($current_user['display_name'] ?: $current_user['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="post-input-container">
                        <!-- Markdown Toolbar -->
                        <div class="markdown-toolbar" id="postToolbar" style="display: none;">
                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                                    <i class="iw iw-bold"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                                    <i class="iw iw-italic"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="strikethrough" title="Strikethrough">
                                    <i class="iw iw-strikethrough"></i>
                                </button>
    </div>
    
                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" data-action="heading" title="Heading">
                                    <i class="iw iw-heading"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="quote" title="Quote">
                                    <i class="iw iw-quote-left"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="code" title="Code">
                                    <i class="iw iw-code"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" data-action="link" title="Link">
                                    <i class="iw iw-link"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="list" title="List">
                                    <i class="iw iw-list"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="image" title="Image">
                                    <i class="iw iw-image"></i>
                                </button>
                            </div>
                            
                            <div class="toolbar-group">
                                <button type="button" class="toolbar-btn" data-action="toggle-preview" title="Toggle Preview">
                                    <i class="iw iw-eye"></i>
                                </button>
                                <button type="button" class="toolbar-btn" data-action="help" title="Markdown Help">
                                    <i class="iw iw-question-circle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Editor and Preview Container -->
                        <div class="post-editor-container" id="postEditorContainer" style="display: none;">
                            <div class="post-editor-main">
                                <textarea id="postContent" placeholder="What's on your mind, <?php echo htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']); ?>?&#10;&#10;You can use Markdown formatting:&#10;**bold text**&#10;*italic text*&#10;# Heading&#10;> Quote&#10;`code`&#10;[link](url)&#10;- list item" class="post-input" rows="3"></textarea>
                            </div>
                            <div id="postPreviewContainer" class="post-preview-container" style="display: none;">
                                <div class="preview-header">
                                    <h4>Preview</h4>
                                </div>
                                <div id="postPreviewContent" class="preview-content"></div>
                            </div>
                        </div>
                        
                        <!-- Simple input (shown by default) -->
                        <textarea id="postContentSimple" placeholder="What's on your mind, <?php echo htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']); ?>?" class="post-input post-input-simple" rows="3"></textarea>
                        
                        <!-- Image preview area for simple mode -->
                        <div id="simpleImagePreview" class="simple-image-preview" style="display: none;"></div>
                        
                        <div class="post-input-footer">
                            <div class="post-options">
                                <button type="button" class="formatting-btn" id="toggleFormatting" title="Show formatting tools">
                                    <i class="iw iw-edit"></i>
                                    <span>Format</span>
                                </button>
                                <label class="privacy-option">
                                    <input type="checkbox" id="isPublic" checked>
                                    <span class="checkmark"></span>
                                    <span class="privacy-text">Public</span>
                                </label>
                            </div>
                            <div class="post-actions">
                                <button id="cancelPost" class="btn-cancel" style="display: none;">Cancel</button>
                                <button id="submitPost" class="btn-submit" disabled>Post</button>
                            </div>
                        </div>
                    </div>
                    <button id="fullscreenBtn" class="fullscreen-btn" title="Toggle Fullscreen Editor">
                        <i class="iw iw-expand"></i>
                    </button>
                </div>
                <div class="create-post-divider"></div>
                <div class="create-post-actions">
                    <div class="add-to-post-text">Add to your post:</div>
                    <div class="post-action-buttons">
                        <button class="post-action-btn" onclick="handlePhotoVideo()" title="Add Photo/Video">
                            <i class="iw iw-images" style="color: #27ae60;"></i>
                            <span>Photo/Video</span>
                        </button>
                        <button class="post-action-btn" onclick="handleTagPeople()" title="Tag People">
                            <i class="iw iw-user-tag" style="color: #3b82f6;"></i>
                            <span>Tag People</span>
                        </button>
                        <button class="post-action-btn" onclick="handleFeeling()" title="Add Feeling/Activity">
                            <i class="iw iw-smile" style="color: #f59e0b;"></i>
                            <span>Feeling/Activity</span>
                        </button>
                        <button class="post-action-btn" onclick="handleGIF()" title="Add GIF">
                            <i class="iw iw-gift" style="color: #8b5cf6;"></i>
                            <span>GIF</span>
                        </button>
                    </div>
                </div>
            </div>
    
            <!-- Feed Content -->
            <div class="feed-content">
                <?php
                // Get following content (posts and articles from followed users only)
                $following_content = [];
                $following_ids = array_column($following, 'following_id');
                
                // Only show content from users we're actually following (not including current user)
                if (!empty($following_ids)) {
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
                        LIMIT 20
                    ");
                    $stmt->execute($following_ids);
                    $following_posts = $stmt->fetchAll();
                    
                    // Get articles from followed users only
                    $stmt = $pdo->prepare("
                        SELECT wa.*, u.username, u.display_name, u.avatar, 'article' as content_type
                        FROM wiki_articles wa
                        JOIN users u ON wa.author_id = u.id
                        -- Categories now handled via wiki_categories table
                        WHERE wa.author_id IN ($placeholders) AND wa.status = 'published'
                        ORDER BY wa.published_at DESC
                        LIMIT 20
                    ");
                    $stmt->execute($following_ids);
                    $following_articles = $stmt->fetchAll();
                    
                    $following_content = array_merge($following_posts, $following_articles);
                }
                
                // Combine all content into one feed, removing duplicates
                $all_content = [];
                
                // Add featured articles
                foreach ($featured_articles as $article) {
                    $article['is_featured'] = true;
                    $all_content[] = $article;
                }
                
                // Add recent posts
                foreach ($recent_posts as $post) {
                    $all_content[] = $post;
                }
                
                // Add recent articles (excluding featured ones to avoid duplicates)
                $featured_ids = array_column($featured_articles, 'id');
                foreach ($recent_articles as $article) {
                    if (!in_array($article['id'], $featured_ids)) {
                        $all_content[] = $article;
                    }
                }
                
                // Sort by creation/publish date
                usort($all_content, function($a, $b) {
                    $date_a = $a['created_at'] ?? $a['published_at'] ?? '1970-01-01';
                    $date_b = $b['created_at'] ?? $b['published_at'] ?? '1970-01-01';
                    return strtotime($date_b) - strtotime($date_a);
                });
                
                usort($following_content, function($a, $b) {
                    $date_a = $a['created_at'] ?? $a['published_at'] ?? '1970-01-01';
                    $date_b = $b['created_at'] ?? $b['published_at'] ?? '1970-01-01';
                    return strtotime($date_b) - strtotime($date_a);
                });
                
                // Debug: Check what content we have
                // echo "<!-- Debug: Following content count: " . count($following_content) . " -->";
                // echo "<!-- Debug: All content count: " . count($all_content) . " -->";
                // echo "<!-- Debug: Recent posts count: " . count($recent_posts) . " -->";
                // echo "<!-- Debug: Recent articles count: " . count($recent_articles) . " -->";
                // echo "<!-- Debug: Featured articles count: " . count($featured_articles) . " -->";
                ?>
                
                <div class="unified-feed">
                    <!-- Following Content -->
                    <div class="feed-section" id="following-content" style="display: none;">
                        <?php if (!empty($following_content)): ?>
                            <?php foreach (array_slice($following_content, 0, 20) as $item): ?>
                            <div class="feed-item" data-type="<?php echo $item['content_type']; ?>" data-filter="following" <?php echo $item['content_type'] === 'post' ? 'data-post-id="' . $item['id'] . '"' : ''; ?>>
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
                                </div>
                                <?php else: ?>
                                <div class="card-header">
                                    <div class="article-header-left">
                                        <!-- Empty space for alignment -->
                                    </div>
                                    <span class="content-type-badge">
                                        <?php 
                                        if (isset($item['is_featured']) && $item['is_featured']) {
                                            echo 'Featured Article';
                                        } else {
                                            echo ucfirst($item['content_type']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h4><a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                                    <p><?php echo htmlspecialchars(truncate_text($item['excerpt'] ?: strip_tags($item['content']), 150)); ?></p>
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
                                    <div class="article-meta">
                                        <span class="updated-by">Updated by: <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>"><?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?></a> on <?php echo format_date($item['updated_at'] ?? $item['published_at']); ?></span>
    </div>
                                </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <?php if ($item['content_type'] === 'post'): ?>
                                            <span><i class="iw iw-comment"></i> <?php echo $item['comments_count']; ?></span>
                                            <span><i class="iw iw-heart"></i> <?php echo $item['likes_count']; ?></span>
                                            <span><i class="iw iw-share"></i> <?php echo $item['shares_count']; ?></span>
                                        <?php else: ?>
                                            <span><i class="iw iw-eye"></i> <?php echo number_format($item['view_count']); ?></span>
                                            <span><i class="iw iw-comment"></i> 0</span>
                                            <span><i class="iw iw-heart"></i> 0</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($item['content_type'] === 'post'): ?>
                                    <div class="post-actions">
                                        <button class="action-btn like-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-heart"></i></button>
                                        <?php if ($enable_comments): ?>
                                        <button class="action-btn comment-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-comment"></i></button>
                                        <?php endif; ?>
                                        <button class="action-btn share-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-share"></i></button>
                                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>You're not following anyone yet. <a href="/search">Find people to follow</a></p>
                            </div>
                        <?php endif; ?>
    </div>
                    
                    <!-- All Content -->
                    <div class="feed-section" id="all-content">
                        <?php if (!empty($all_content)): ?>
                            <?php foreach (array_slice($all_content, 0, 20) as $item): ?>
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
                                </div>
                                <?php else: ?>
                                <div class="card-header">
                                    <div class="article-header-left">
                                        <!-- Empty space for alignment -->
                                    </div>
                                    <span class="content-type-badge">
                                        <?php 
                                        if (isset($item['is_featured']) && $item['is_featured']) {
                                            echo 'Featured Article';
                                        } else {
                                            echo ucfirst($item['content_type']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h4><a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                                    <p><?php 
                                        // Use WikiParser for articles to properly parse templates
                                        if ($item['content_type'] === 'article' || $item['content_type'] === 'featured_article') {
                                            $parser = new WikiParser('');
                                            $parsed_content = $parser->parse($item['content']);
                                            echo htmlspecialchars(truncate_text($item['excerpt'] ?: strip_tags($parsed_content), 150));
                                        } else {
                                            echo htmlspecialchars(truncate_text($item['excerpt'] ?: strip_tags($item['content']), 150));
                                        }
                                    ?></p>
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
                                    <div class="article-meta">
                                        <span class="updated-by">Updated by: <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>"><?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?></a> on <?php echo format_date($item['updated_at'] ?? $item['published_at']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <?php if ($item['content_type'] === 'post'): ?>
                                            <span><i class="iw iw-comment"></i> <?php echo $item['comments_count']; ?></span>
                                            <span><i class="iw iw-heart"></i> <?php echo $item['likes_count']; ?></span>
                                            <span><i class="iw iw-share"></i> <?php echo $item['shares_count']; ?></span>
                                        <?php else: ?>
                                            <span><i class="iw iw-eye"></i> <?php echo number_format($item['view_count']); ?></span>
                                            <span><i class="iw iw-comment"></i> 0</span>
                                            <span><i class="iw iw-heart"></i> 0</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($item['content_type'] === 'post'): ?>
                                    <div class="post-actions">
                                        <button class="action-btn like-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-heart"></i></button>
                                        <?php if ($enable_comments): ?>
                                        <button class="action-btn comment-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-comment"></i></button>
                                        <?php endif; ?>
                                        <button class="action-btn share-btn" data-post-id="<?php echo $item['id']; ?>"><i class="iw iw-share"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No content available. <a href="/pages/wiki/create_article.php">Create the first article</a></p>
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
                    <?php if (!empty($my_articles)): ?>
                    <div class="content-list">
                        <?php foreach ($my_articles as $article): ?>
                        <div class="content-item">
                            <h5><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h5>
                            <p class="item-meta">
                                <span class="status-<?php echo $article['status']; ?>"><?php echo ucfirst($article['status']); ?></span>
                                • <?php echo format_date($article['created_at']); ?>
                            </p>
                </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No articles yet. <a href="/pages/wiki/create_article.php">Create your first article</a></p>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="posts-tab" style="display: none;">
                    <?php if (!empty($my_posts)): ?>
                    <div class="content-list">
                        <?php foreach ($my_posts as $post): ?>
                        <div class="content-item">
                            <div class="post-content preview"><?php 
                                $parser = new MarkdownParser();
                                echo $parser->parse($post['content']);
                            ?></div>
                            <p class="item-meta"><?php echo format_date($post['created_at']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
                    <?php else: ?>
                    <p class="empty-state">No posts yet. <a href="/create_post">Create your first post</a></p>
                    <?php endif; ?>
    </div>
            </div>

            <!-- Watchlist -->
            <div class="watchlist-card">
                <div class="card-header">
                    <h4>My Watchlist</h4>
                    <a href="/pages/user/watchlist.php" class="view-all-link">View All</a>
                </div>
                <?php if (!empty($watchlist_articles)): ?>
                <div class="watchlist-list">
                    <?php foreach ($watchlist_articles as $article): ?>
                    <div class="watchlist-item">
                        <div class="item-content">
                            <h5><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h5>
                            <p class="item-meta"><?php echo format_date($article['updated_at']); ?></p>
                        </div>
                        <button class="unwatch-btn" onclick="unwatchArticle(<?php echo $article['id']; ?>)">
                            <i class="iw iw-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-state">No articles in watchlist. <a href="/search">Find articles to watch</a></p>
    <?php endif; ?>
            </div>

            <!-- Following -->
            <div class="following-card">
                <div class="card-header">
                    <h4>Following</h4>
                    <a href="/pages/social/friends.php" class="view-all-link">View All</a>
                </div>
                <?php if (!empty($following)): ?>
                <div class="following-list">
                    <?php foreach (array_slice($following, 0, 5) as $user): ?>
                    <div class="following-item">
                        <img src="/assets/images/default-avatar.svg" alt="User" class="user-avatar">
                        <div class="user-info">
                            <a href="/pages/user/user_profile.php?username=<?php echo urlencode($user['username']); ?>" class="user-name">
                                <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?>
                            </a>
                            <span class="user-handle">@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <button class="unfollow-btn" onclick="unfollowUser(<?php echo $user['following_id']; ?>)">
                            <i class="iw iw-user-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-state">Not following anyone yet. <a href="/search">Find people to follow</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div id="helpModal" class="help-modal">
    <div class="help-modal-content">
        <div class="help-modal-header">
            <h3>Markdown Help</h3>
            <button type="button" class="help-modal-close">&times;</button>
        </div>
        <div class="help-modal-body">
            <div class="help-section">
                <h4>Text Formatting</h4>
                <ul>
                    <li><code>**bold text**</code> → <strong>bold text</strong></li>
                    <li><code>*italic text*</code> → <em>italic text</em></li>
                    <li><code>~~strikethrough~~</code> → <del>strikethrough</del></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h4>Headings</h4>
                <ul>
                    <li><code># Heading 1</code></li>
                    <li><code>## Heading 2</code></li>
                    <li><code>### Heading 3</code></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h4>Lists</h4>
                <ul>
                    <li><code>- Item 1</code></li>
                    <li><code>- Item 2</code></li>
                    <li><code>1. Numbered item</code></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h4>Links & Images</h4>
                <ul>
                    <li><code>[Link text](URL)</code></li>
                    <li><code>![Alt text](image URL)</code></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h4>Code</h4>
                <ul>
                    <li><code>`inline code`</code></li>
                    <li><code>```block code```</code></li>
                </ul>
            </div>
            
            <div class="help-section">
                <h4>Quotes</h4>
                <ul>
                    <li><code>> This is a quote</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Profile Picture Selection Modal -->
<div id="profilePictureModal" class="profile-picture-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Choose profile picture</h3>
            <button class="close-btn" onclick="closeProfilePictureModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <!-- Initial Options -->
            <div id="initialOptions" class="options-container">
                <div class="option-buttons">
                    <button class="option-btn primary" onclick="showProfilePictureViewer()">
                        <i class="iw iw-eye"></i>
                        See profile picture
                    </button>
                    <button class="option-btn secondary" onclick="showPictureSelection()">
                        <i class="iw iw-camera"></i>
                        Choose profile picture
                    </button>
                </div>
            </div>
            
            <!-- Picture Selection Options -->
            <div id="pictureSelection" class="selection-container" style="display: none;">
                <div class="selection-actions">
                    <button class="action-btn primary" onclick="triggerFileUpload()">
                        <i class="iw iw-plus"></i>
                        Upload photo
                    </button>
                    <div class="upload-hint">
                        <small>💡 Hold Shift while clicking to upload directly without adjustment</small>
                    </div>
                    <button class="action-btn secondary" onclick="showFrames()">
                        <i class="iw iw-square"></i>
                        Add Frame
                    </button>
                </div>
                
                <div class="photo-sections">
                    <div class="photo-section">
                        <h4>Suggested photos</h4>
                        <div class="photo-grid" id="suggestedPhotos">
                            <!-- Suggested photos will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreSuggested()">See more</button>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Uploads</h4>
                        <div class="photo-grid" id="userUploads">
                            <!-- User uploads will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreUploads()">See more</button>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Profile pictures</h4>
                        <div class="photo-grid" id="profilePictures">
                            <!-- Profile pictures will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Cover photos</h4>
                        <div class="photo-grid" id="coverPhotos">
                            <!-- Cover photos will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreCoverPhotos()">See more</button>
                    </div>
                </div>
            </div>
            
            <!-- Thumbnail Adjustment -->
            <div id="thumbnailAdjustment" class="adjustment-container" style="display: none;">
                <div class="adjustment-preview">
                    <div class="profile-preview">
                        <img id="adjustmentImage" src="" alt="Profile preview">
                        <div class="drag-overlay">
                            <i class="iw iw-arrows-alt"></i>
                            <span>Drag to Reposition</span>
                        </div>
                    </div>
                    <div class="zoom-controls">
                        <span class="zoom-label">Zoom</span>
                        <input type="range" id="zoomSlider" min="0.5" max="2" step="0.1" value="1" oninput="adjustZoom(this.value)">
                        <div class="zoom-buttons">
                            <button onclick="adjustZoom(0.5)">-</button>
                            <button onclick="adjustZoom(2)">+</button>
                        </div>
                    </div>
                    <div class="privacy-info">
                        <i class="iw iw-globe"></i>
                        <span>Your profile picture is public.</span>
                    </div>
                </div>
                <div class="adjustment-actions">
                    <button class="btn-cancel" onclick="cancelThumbnailAdjustment()">Cancel</button>
                    <button class="btn-save" onclick="saveProfilePicture()">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden file input for uploads -->
<input type="file" id="profilePictureUpload" accept="image/*" style="display: none;" onchange="handleFileUpload(this)">

<!-- Full Screen Profile Picture Viewer -->
<div id="profilePictureViewer" class="profile-picture-viewer">
    <div class="viewer-container">
        <div class="viewer-image-section">
            <img id="viewerImage" class="viewer-image" src="" alt="Profile Picture">
        </div>
        <div class="viewer-comments-section">
            <div class="viewer-header">
                <h3>Profile Picture</h3>
                <div class="viewer-actions">
                    <div class="options-dropdown">
                        <button class="options-btn" onclick="toggleOptionsDropdown()">
                            <i class="iw iw-ellipsis-v"></i>
                        </button>
                        <div class="options-menu" id="optionsMenu">
                            <button class="option-item delete-btn" onclick="deleteCurrentPhoto()">
                                <i class="iw iw-trash"></i>
                                Delete Photo
                            </button>
                        </div>
                    </div>
                    <button class="viewer-close" onclick="closeProfilePictureViewer()">&times;</button>
                </div>
            </div>
            <div class="viewer-comments" id="viewerComments">
                <!-- Comments will be loaded here -->
            </div>
            <div class="comment-form">
                <textarea class="comment-input" placeholder="Add a comment..." id="commentInput"></textarea>
                <button class="comment-submit" onclick="submitComment()">Post Comment</button>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>
