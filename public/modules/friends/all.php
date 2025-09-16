<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'All Friends';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get all friends (people you follow)
$stmt = $pdo->prepare("
    SELECT u.*, uf.created_at as friendship_date
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ? AND uf.status = 'accepted'
    ORDER BY uf.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$friends = $stmt->fetchAll();

include "../../includes/header.php";;

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/social.css">
<?php

?>
<script src="/skins/bismillah/assets/js/friends_all.js"></script>
<?php
?>

<div class="friends-page">
    <div class="friends-container">
        <!-- Left Sidebar -->
        <div class="friends-sidebar">
            <div class="friends-nav">
                <h2><i class="fas fa-users"></i> Friends</h2>
                <nav class="friends-menu">
                    <a href="/friends" class="friends-nav-item">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="/friends/requests" class="friends-nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Friend Requests</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/suggestions" class="friends-nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Suggestions</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/all" class="friends-nav-item active">
                        <i class="fas fa-users"></i>
                        <span>All friends</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/lists" class="friends-nav-item">
                        <i class="fas fa-list"></i>
                        <span>Custom Lists</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="friends-main">
            <div class="all-friends">
                <div class="section-header">
                    <h1>All Friends</h1>
                    <span class="friend-count"><?php echo count($friends); ?> friends</span>
                </div>
                
                <?php if (empty($friends)): ?>
                    <div class="no-friends">
                        <i class="fas fa-users"></i>
                        <h3>No friends yet</h3>
                        <p>Start connecting with people by adding friends from suggestions or accepting friend requests.</p>
                        <a href="/friends/suggestions" class="btn btn-primary">Find Friends</a>
                    </div>
                <?php else: ?>
                    <div class="friends-grid">
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend-card">
                                <div class="friend-image">
                                    <img src="<?php echo htmlspecialchars($friend['avatar'] ?? '/assets/images/default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjYwIiBjeT0iNjAiIHI9IjYwIiBmaWxsPSIjNDI4NUY0Ii8+CjxzdmcgeD0iMjQiIHk9IjI0IiB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                </div>
                                <div class="friend-info">
                                    <h3 class="friend-name"><?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?></h3>
                                    <p class="friend-date">Friends since <?php echo date('M j, Y', strtotime($friend['friendship_date'])); ?></p>
                                </div>
                                <div class="friend-actions">
                                    <a href="/user/<?php echo $friend['username']; ?>" class="btn-view-profile">
                                        <i class="fas fa-user"></i>
                                        View Profile
                                    </a>
                                    <button class="btn-unfriend" onclick="unfriend(<?php echo $friend['id']; ?>)">
                                        <i class="fas fa-user-times"></i>
                                        Unfriend
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<?php include "../../includes/footer.php";; ?>
