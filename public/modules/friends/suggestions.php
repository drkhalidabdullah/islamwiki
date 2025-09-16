<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Friend Suggestions';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get suggested friends (people you may know)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.*, 
           (SELECT COUNT(*) FROM user_follows uf1 
            WHERE uf1.follower_id = ? AND uf1.following_id = u.id) as is_following,
           (SELECT COUNT(*) FROM user_follows uf2 
            WHERE uf2.follower_id = ? AND uf2.following_id IN (
                SELECT uf3.following_id FROM user_follows uf3 WHERE uf3.follower_id = u.id
            )) as mutual_friends
    FROM users u 
    WHERE u.id != ? 
    AND u.id NOT IN (
        SELECT following_id FROM user_follows WHERE follower_id = ?
    )
    ORDER BY mutual_friends DESC, u.created_at DESC 
    LIMIT 100
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$suggested_friends = $stmt->fetchAll();

include "../../includes/header.php";;

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/social.css">
<?php

?>
<script src="/skins/bismillah/assets/js/friends_suggestions.js"></script>
<?php
?>

<div class="friends-page">
    <div class="friends-container">
        <!-- Left Sidebar -->
        <div class="friends-sidebar">
            <div class="friends-nav">
                <h2><i class="iw iw-users"></i> Friends</h2>
                <nav class="friends-menu">
                    <a href="/friends" class="friends-nav-item">
                        <i class="iw iw-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="/friends/requests" class="friends-nav-item">
                        <i class="iw iw-user-plus"></i>
                        <span>Friend Requests</span>
                        <i class="iw iw-chevron-right"></i>
                    </a>
                    <a href="/friends/suggestions" class="friends-nav-item active">
                        <i class="iw iw-user-plus"></i>
                        <span>Suggestions</span>
                        <i class="iw iw-chevron-right"></i>
                    </a>
                    <a href="/friends/all" class="friends-nav-item">
                        <i class="iw iw-users"></i>
                        <span>All friends</span>
                        <i class="iw iw-chevron-right"></i>
                    </a>
                    <a href="/friends/lists" class="friends-nav-item">
                        <i class="iw iw-list"></i>
                        <span>Custom Lists</span>
                        <i class="iw iw-chevron-right"></i>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="friends-main">
            <div class="friend-suggestions">
                <div class="section-header">
                    <h1>People you may know</h1>
                    <span class="suggestion-count"><?php echo count($suggested_friends); ?> suggestions</span>
                </div>
                
                <?php if (empty($suggested_friends)): ?>
                    <div class="no-suggestions">
                        <i class="iw iw-users"></i>
                        <h3>No suggestions available</h3>
                        <p>We couldn't find any friend suggestions for you at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="suggestions-grid">
                        <?php foreach ($suggested_friends as $person): ?>
                            <div class="suggestion-card">
                                <div class="suggestion-image">
                                    <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($person['display_name'] ?: $person['username']); ?>" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjYwIiBjeT0iNjAiIHI9IjYwIiBmaWxsPSIjNDI4NUY0Ii8+CjxzdmcgeD0iMjQiIHk9IjI0IiB3aWR0aD0iNzIiIGhlaWdodD0iNzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                </div>
                                <div class="suggestion-info">
                                    <h3 class="suggestion-name"><?php echo htmlspecialchars($person['display_name'] ?: $person['username']); ?></h3>
                                    <?php if ($person['mutual_friends'] > 0): ?>
                                        <p class="mutual-friends">
                                            <i class="iw iw-users"></i>
                                            <?php echo $person['mutual_friends']; ?> mutual friend<?php echo $person['mutual_friends'] > 1 ? 's' : ''; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="suggestion-actions">
                                    <button class="btn-add-friend" onclick="addFriend(<?php echo $person['id']; ?>)">
                                        <i class="iw iw-user-plus"></i>
                                        Add friend
                                    </button>
                                    <button class="btn-remove" onclick="removeSuggestion(<?php echo $person['id']; ?>)">
                                        <i class="iw iw-times"></i>
                                        Remove
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
