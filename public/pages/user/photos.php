<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get username from URL parameter
$username = $_GET['username'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

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

// Get user photos with metadata
$photos = get_user_photos($profile_user['id'], $limit, $offset, true);
$total_photos = get_user_photo_count($profile_user['id']);
$total_pages = ceil($total_photos / $limit);

// Check if current user is following this profile
$is_following = false;
if ($current_user_id && $current_user_id != $profile_user['id']) {
    $is_following = is_following($current_user_id, $profile_user['id']);
}

// Set active tab for navigation
$active_tab = 'photos';

$page_title = $profile_user['display_name'] ?: $profile_user['username'] . "'s Photos";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - IslamWiki</title>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/main.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/photos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="main-container">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="content-area">
            <div class="photos-page">
                <?php include '../../includes/profile_header.php'; ?>
                
                <!-- Photos Gallery -->
                <div class="photos-section">
                    <div class="photos-header">
                        <h2>Photos</h2>
                        <div class="photos-count"><?php echo number_format($total_photos); ?> photos</div>
                    </div>
                    
                    <?php if (!empty($photos)): ?>
                        <div class="photos-grid" id="photosGrid">
                            <?php foreach ($photos as $photo): ?>
                                <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>" data-index="<?php echo array_search($photo, $photos); ?>">
                                    <div class="photo-container">
                                        <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($photo['caption'] ?: 'Photo'); ?>"
                                             loading="lazy"
                                             data-src="<?php echo htmlspecialchars($photo['file_path']); ?>">
                                        
                                        <div class="photo-overlay">
                                            <div class="photo-actions">
                                                <button class="action-btn like-btn" data-photo-id="<?php echo $photo['id']; ?>">
                                                    <i class="fas fa-heart"></i>
                                                    <span><?php echo number_format($photo['likes_count']); ?></span>
                                                </button>
                                                <button class="action-btn comment-btn" data-photo-id="<?php echo $photo['id']; ?>">
                                                    <i class="fas fa-comment"></i>
                                                    <span><?php echo number_format($photo['comments_count']); ?></span>
                                                </button>
                                                <button class="action-btn share-btn" data-photo-id="<?php echo $photo['id']; ?>">
                                                    <i class="fas fa-share"></i>
                                                    <span><?php echo number_format($photo['shares_count']); ?></span>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($photo['caption'])): ?>
                                            <div class="photo-caption">
                                                <?php echo htmlspecialchars($photo['caption']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="photo-date">
                                            <?php echo $photo['formatted_date']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?username=<?php echo urlencode($profile_user['username']); ?>&page=<?php echo $page - 1; ?>" class="page-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <div class="page-numbers">
                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <a href="?username=<?php echo urlencode($profile_user['username']); ?>&page=<?php echo $i; ?>" 
                                           class="page-number <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?username=<?php echo urlencode($profile_user['username']); ?>&page=<?php echo $page + 1; ?>" class="page-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-images"></i>
                            </div>
                            <h3>No Photos Yet</h3>
                            <p><?php echo htmlspecialchars($profile_user['display_name'] ?: $profile_user['username']); ?> hasn't shared any photos yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Photo Modal Viewer -->
    <div id="photoModal" class="photo-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Photo</h3>
                <button class="close-btn" id="closeModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="photo-viewer">
                    <button class="nav-btn prev-btn" id="prevPhoto">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="photo-container">
                        <img id="modalPhoto" src="" alt="">
                    </div>
                    <button class="nav-btn next-btn" id="nextPhoto">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <div class="photo-info">
                    <div class="photo-caption" id="modalCaption"></div>
                    <div class="photo-meta">
                        <span class="photo-date" id="modalDate"></span>
                        <div class="photo-stats">
                            <span class="likes"><i class="fas fa-heart"></i> <span id="modalLikes">0</span></span>
                            <span class="comments"><i class="fas fa-comment"></i> <span id="modalComments">0</span></span>
                            <span class="shares"><i class="fas fa-share"></i> <span id="modalShares">0</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="/skins/bismillah/assets/js/photos.js"></script>
    <script src="/skins/bismillah/assets/js/user_profile.js"></script>
    
    <?php include '../../includes/footer.php'; ?>
