<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get username from URL parameter
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: /');
    exit();
}

// Get user by username
$profile_user = get_user_by_username($username);
if (!$profile_user) {
    header('Location: /404.php');
    exit();
}

// Check if current user can view this profile
$current_user_id = $_SESSION['user_id'] ?? null;
if (!can_view_profile($current_user_id, $profile_user['id'])) {
    header('Location: /login.php');
    exit();
}

// Get complete profile data for header
$profile_data = get_user_profile_complete($profile_user['id']);
$user_stats = get_user_stats($profile_user['id']);

// Check if current user is following this profile
$is_following = false;
if ($current_user_id && $current_user_id != $profile_user['id']) {
    $is_following = is_following($current_user_id, $profile_user['id']);
}

// Set active tab for navigation
$active_tab = 'activity';

// Get user activity
$activity = get_user_activity($profile_user['id'], 50, 0);

$page_title = $profile_user['display_name'] ?: $profile_user['username'] . "'s Activity";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - IslamWiki</title>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/main.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/activity.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php include '../../includes/profile_template.php'; ?>
    
    <!-- Activity Section - Full Width -->
    <div class="activity-section">
        <div class="activity-header">
            <h2>Activity</h2>
            <div class="activity-filters">
                <button class="filter-btn active" data-filter="all">All Activity</button>
                <button class="filter-btn" data-filter="posts">Posts</button>
                <button class="filter-btn" data-filter="comments">Comments</button>
                <button class="filter-btn" data-filter="likes">Likes</button>
                <button class="filter-btn" data-filter="follows">Follows</button>
            </div>
        </div>
        
        <div class="activity-content">
            <div class="activity-container">
                <?php if (!empty($activity)): ?>
                    <div class="activity-timeline">
                        <?php
                        $current_date = '';
                        foreach ($activity as $item):
                            $item_date = date('Y-m-d', strtotime($item['created_at']));
                            if ($item_date != $current_date):
                                $current_date = $item_date;
                        ?>
                            <div class="timeline-date">
                                <h3><?php echo format_activity_date($item_date); ?></h3>
                            </div>
                        <?php endif; ?>
                        
                        <div class="activity-item" data-type="<?php echo $item['type']; ?>">
                            <div class="activity-icon">
                                <?php echo get_activity_icon($item['type']); ?>
                            </div>
                            
                            <div class="activity-content">
                                <div class="activity-text">
                                    <?php echo format_activity_text($item, $profile_user); ?>
                                </div>
                                
                                <div class="activity-time">
                                    <?php echo time_ago($item['created_at']); ?>
                                </div>
                                
                                <?php if (!empty($item['content'])): ?>
                                <div class="activity-preview">
                                    <?php echo format_activity_preview($item); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($item['type'] == 'post' && !empty($item['post_id'])): ?>
                                <div class="activity-actions">
                                    <button class="action-btn like-btn" data-post-id="<?php echo $item['post_id']; ?>">
                                        <i class="fas fa-heart"></i>
                                        <span><?php echo $item['likes_count'] ?? 0; ?></span>
                                    </button>
                                    <button class="action-btn comment-btn" data-post-id="<?php echo $item['post_id']; ?>">
                                        <i class="fas fa-comment"></i>
                                        <span><?php echo $item['comments_count'] ?? 0; ?></span>
                                    </button>
                                    <button class="action-btn share-btn" data-post-id="<?php echo $item['post_id']; ?>">
                                        <i class="fas fa-share"></i>
                                        <span><?php echo $item['shares_count'] ?? 0; ?></span>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-activity">
                        <div class="no-activity-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3>No Activity Yet</h3>
                        <p>
                            <?php if ($current_user_id == $profile_user['id']): ?>
                                You haven't been active yet. Start by creating a post or interacting with the community!
                            <?php else: ?>
                                <?php echo htmlspecialchars($profile_user['display_name'] ?: $profile_user['username']); ?> hasn't been active yet.
                            <?php endif; ?>
                        </p>
                        <?php if ($current_user_id == $profile_user['id']): ?>
                            <a href="/dashboard" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Your First Post
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="/skins/bismillah/assets/js/activity.js"></script>
    <script src="/skins/bismillah/assets/js/user_profile.js"></script>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
