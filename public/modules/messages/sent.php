<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Sent Messages';
check_maintenance_mode();
require_login();

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'error');
    redirect('/dashboard');
}

$current_user = get_user($_SESSION['user_id']);

// Get user's sent messages
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        m.recipient_id as other_user_id,
        u.username,
        u.display_name,
        u.avatar,
        m.message,
        m.created_at,
        m.is_read,
        m.id as message_id
    FROM messages m
    JOIN users u ON m.recipient_id = u.id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$sent_messages = $stmt->fetchAll();

// Get active conversation (from URL parameter)
$active_conversation = $_GET['conversation'] ?? null;
$active_messages = [];
$active_user = null;

if ($active_conversation) {
    // Get messages for active conversation
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.display_name, u.avatar
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.recipient_id = ?) 
           OR (m.sender_id = ? AND m.recipient_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$_SESSION['user_id'], $active_conversation, $active_conversation, $_SESSION['user_id']]);
    $active_messages = $stmt->fetchAll();
    
    // Get active user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$active_conversation]);
    $active_user = $stmt->fetch();
}

include "../../includes/header.php";

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/social.css">
<?php

?>
<script src="/skins/bismillah/assets/js/social_messages.js"></script>
<script src="/skins/bismillah/assets/js/messaging.js"></script>
<script>
// Set current user ID for messaging system
window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
</script>
<?php
?>

<div class="messages-page">
    <div class="messages-container">
        <!-- Left Sidebar: Sent Messages List -->
        <div class="messages-sidebar">
            <div class="messages-header">
                <h2>Sent Messages</h2>
                <div class="messages-actions">
                    <a href="/messages/compose" class="new-message-btn" title="Compose New Message">
                        <i class="iw iw-pencil-alt"></i>
                    </a>
                    <a href="/messages" class="messages-options" title="All Messages">
                        <i class="iw iw-inbox"></i>
                    </a>
                </div>
            </div>
            
            <div class="messages-search">
                <input type="text" placeholder="Search sent messages" class="messages-search-input" id="sentSearchInput">
            </div>
            
            <div class="messages-tabs">
                <button class="message-tab" data-tab="all" onclick="window.location.href='/messages'">All</button>
                <button class="message-tab active" data-tab="sent">Sent</button>
                <button class="message-tab" data-tab="unread" onclick="window.location.href='/messages?tab=unread'">Unread</button>
                <button class="message-tab" data-tab="groups" onclick="window.location.href='/messages?tab=groups'">Groups</button>
            </div>
            
            <div class="conversations-list">
                <?php if (empty($sent_messages)): ?>
                    <div class="no-messages">
                        <i class="iw iw-paper-plane"></i>
                        <h3>No sent messages</h3>
                        <p>You haven't sent any messages yet.</p>
                        <a href="/messages/compose" class="btn btn-primary">Compose Message</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($sent_messages as $message): ?>
                        <div class="conversation-item <?php echo ($active_conversation == $message['other_user_id']) ? 'active' : ''; ?>" 
                             onclick="loadConversation(<?php echo $message['other_user_id']; ?>)">
                            <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($message['display_name'] ?: $message['username']); ?>" 
                                 class="conversation-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                            <div class="conversation-info">
                                <div class="conversation-name"><?php echo htmlspecialchars($message['display_name'] ?: $message['username']); ?></div>
                                <div class="conversation-preview"><?php echo htmlspecialchars($message['message']); ?></div>
                            </div>
                            <div class="conversation-meta">
                                <div class="conversation-time"><?php echo date('M j', strtotime($message['created_at'])); ?></div>
                                <div class="sent-indicator">
                                    <i class="iw iw-paper-plane"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Center: Chat Area -->
        <div class="messages-main">
            <?php if ($active_user): ?>
                <div class="chat-header">
                    <div class="chat-user-info">
                        <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?>" 
                             class="chat-user-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                        <div class="chat-user-details">
                            <div class="chat-user-name"><?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?></div>
                            <div class="chat-status">Active now</div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn" title="Voice call">
                            <i class="iw iw-phone"></i>
                        </button>
                        <button class="chat-action-btn" title="Video call">
                            <i class="iw iw-video"></i>
                        </button>
                        <button class="chat-action-btn" title="Chat info">
                            <i class="iw iw-info-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($active_messages as $message): ?>
                        <div class="message <?php echo ($message['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received'; ?>">
                            <div class="message-content">
                                <?php echo htmlspecialchars($message['message']); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('M j, Y, g:i A', strtotime($message['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-input">
                    <button class="chat-input-btn" title="Voice message">
                        <i class="iw iw-microphone"></i>
                    </button>
                    <button class="chat-input-btn" title="Attach photo">
                        <i class="iw iw-image"></i>
                    </button>
                    <button class="chat-input-btn" title="GIF">
                        <i class="iw iw-smile"></i>
                    </button>
                    <input type="text" placeholder="Aa" class="chat-text-input" id="messageInput">
                    <button class="chat-input-btn" title="Emoji">
                        <i class="iw iw-smile"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="no-conversation">
                    <div class="no-conversation-content">
                        <i class="iw iw-paper-plane"></i>
                        <h3>Select a sent message</h3>
                        <p>Choose a conversation from the sidebar to view your sent messages</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar: Message Info -->
        <?php if ($active_user): ?>
            <div class="messages-info">
                <div class="chat-info-header">
                    <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?>" 
                         class="info-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                    <div class="info-details">
                        <div class="info-name"><?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?></div>
                        <div class="info-status">
                            <i class="iw iw-lock"></i>
                            End-to-end encrypted
                        </div>
                    </div>
                </div>
                
                <div class="chat-info-actions">
                    <button class="info-action-btn" title="Profile">
                        <i class="iw iw-user"></i>
                    </button>
                    <button class="info-action-btn" title="Mute">
                        <i class="iw iw-bell"></i>
                    </button>
                    <button class="info-action-btn" title="Search">
                        <i class="iw iw-search"></i>
                    </button>
                </div>
                
                <div class="chat-info-sections">
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Message info</span>
                            <i class="iw iw-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Customize chat</span>
                            <i class="iw iw-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Media & files</span>
                            <i class="iw iw-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Privacy & support</span>
                            <i class="iw iw-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Search functionality for sent messages
document.getElementById('sentSearchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const conversationItems = document.querySelectorAll('.conversation-item');
    
    conversationItems.forEach(item => {
        const name = item.querySelector('.conversation-name').textContent.toLowerCase();
        const preview = item.querySelector('.conversation-preview').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || preview.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>

<?php include "../../includes/footer.php"; ?>
