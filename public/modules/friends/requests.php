<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Friend Requests';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get pending friend requests
$stmt = $pdo->prepare("
    SELECT u.*, fr.created_at as request_date
    FROM user_follows fr
    JOIN users u ON fr.follower_id = u.id
    WHERE fr.following_id = ? AND fr.status = 'pending'
    ORDER BY fr.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$friend_requests = $stmt->fetchAll();

include "../../includes/header.php";;

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<?php

?>
<script src="/skins/bismillah/assets/js/friends_requests.js"></script>
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
                    <a href="/friends/requests" class="friends-nav-item active">
                        <i class="fas fa-user-plus"></i>
                        <span>Friend Requests</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/suggestions" class="friends-nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Suggestions</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/all" class="friends-nav-item">
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
            <div class="friend-requests">
                <div class="section-header">
                    <h1>Friend Requests</h1>
                    <span class="request-count"><?php echo count($friend_requests); ?> requests</span>
                </div>
                
                <?php if (empty($friend_requests)): ?>
                    <div class="no-requests">
                        <i class="fas fa-user-plus"></i>
                        <h3>No friend requests</h3>
                        <p>You don't have any pending friend requests at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="requests-list">
                        <?php foreach ($friend_requests as $request): ?>
                            <div class="request-item">
                                <div class="request-user">
                                    <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($request['display_name'] ?: $request['username']); ?>" 
                                         class="request-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwNiAxMiAxMiAxMloiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xMiAxNEM4LjY5MTE3IDE0IDYgMTYuNjkxMTcgNiAyMEgyMEMyMCAxNi42OTExNyAxNy4zMDg4IDE0IDEyIDE0WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo=';">
                                    <div class="request-info">
                                        <h3 class="request-name"><?php echo htmlspecialchars($request['display_name'] ?: $request['username']); ?></h3>
                                        <p class="request-date"><?php echo date('M j, Y', strtotime($request['request_date'])); ?></p>
                                    </div>
                                </div>
                                <div class="request-actions">
                                    <button class="btn-confirm" onclick="respondToRequest(<?php echo $request['id']; ?>, 'accept')">
                                        <i class="fas fa-check"></i>
                                        Confirm
                                    </button>
                                    <button class="btn-delete" onclick="respondToRequest(<?php echo $request['id']; ?>, 'decline')">
                                        <i class="fas fa-times"></i>
                                        Delete
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
