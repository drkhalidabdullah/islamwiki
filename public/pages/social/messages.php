<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Messages';
check_maintenance_mode();
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get user's conversations
$stmt = $pdo->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.recipient_id 
            ELSE m.sender_id 
        END as other_user_id,
        u.username,
        u.display_name,
        u.avatar,
        m.message,
        m.created_at,
        m.is_read,
        m.sender_id
    FROM messages m
    JOIN users u ON (
        CASE 
            WHEN m.sender_id = ? THEN m.recipient_id 
            ELSE m.sender_id 
        END = u.id
    )
    WHERE m.sender_id = ? OR m.recipient_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversations = $stmt->fetchAll();

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

include "../../includes/header.php";;
?>

<div class="messages-page">
    <div class="messages-container">
        <!-- Left Sidebar: Chat List -->
        <div class="messages-sidebar">
            <div class="messages-header">
                <h2>Chats</h2>
                <div class="messages-actions">
                    <a href="#" class="messages-options" id="messagesOptions">
                        <i class="fas fa-ellipsis-h"></i>
                    </a>
                    <a href="#" class="new-message-btn" id="newMessageBtn">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                </div>
            </div>
            
            <div class="messages-search">
                <input type="text" placeholder="Search Messenger" class="messages-search-input">
            </div>
            
            <div class="messages-tabs">
                <button class="message-tab active" data-tab="all">All</button>
                <button class="message-tab" data-tab="unread">Unread</button>
                <button class="message-tab" data-tab="groups">Groups</button>
                <button class="message-tab" data-tab="ummah">Ummah</button>
            </div>
            
            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item <?php echo ($active_conversation == $conv['other_user_id']) ? 'active' : ''; ?>" 
                         onclick="loadConversation(<?php echo $conv['other_user_id']; ?>)">
                        <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?>" 
                             class="conversation-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                        <div class="conversation-info">
                            <div class="conversation-name"><?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?></div>
                            <div class="conversation-preview"><?php echo htmlspecialchars($conv['message']); ?></div>
                        </div>
                        <div class="conversation-meta">
                            <div class="conversation-time"><?php echo date('M j', strtotime($conv['created_at'])); ?></div>
                            <?php if (!$conv['is_read'] && $conv['sender_id'] != $_SESSION['user_id']): ?>
                                <div class="unread-indicator"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="chat-action-btn" title="Video call">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="chat-action-btn" title="Chat info">
                            <i class="fas fa-info-circle"></i>
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
                        <i class="fas fa-microphone"></i>
                    </button>
                    <button class="chat-input-btn" title="Attach photo">
                        <i class="fas fa-image"></i>
                    </button>
                    <button class="chat-input-btn" title="GIF">
                        <i class="fas fa-smile"></i>
                    </button>
                    <input type="text" placeholder="Aa" class="chat-text-input" id="messageInput">
                    <button class="chat-input-btn" title="Emoji">
                        <i class="fas fa-smile"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="no-conversation">
                    <div class="no-conversation-content">
                        <i class="fas fa-comments"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose a conversation from the sidebar to start messaging</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar: Chat Info -->
        <?php if ($active_user): ?>
            <div class="messages-info">
                <div class="chat-info-header">
                    <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?>" 
                         class="info-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                    <div class="info-details">
                        <div class="info-name"><?php echo htmlspecialchars($active_user['display_name'] ?: $active_user['username']); ?></div>
                        <div class="info-status">
                            <i class="fas fa-lock"></i>
                            End-to-end encrypted
                        </div>
                    </div>
                </div>
                
                <div class="chat-info-actions">
                    <button class="info-action-btn" title="Profile">
                        <i class="fas fa-user"></i>
                    </button>
                    <button class="info-action-btn" title="Mute">
                        <i class="fas fa-bell"></i>
                    </button>
                    <button class="info-action-btn" title="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="chat-info-sections">
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Chat info</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Customize chat</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Media & files</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Privacy & support</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function loadConversation(userId) {
    window.location.href = '/messages?conversation=' + userId;
}

// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Message input functionality
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                sendMessage(this.value.trim());
                this.value = '';
            }
        });
    }
});

function sendMessage(message) {
    const conversationId = new URLSearchParams(window.location.search).get('conversation');
    if (!conversationId) return;
    
    fetch('/ajax/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipient_id: conversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending message');
    });
}
</script>

<?php include "../../includes/footer.php";; ?>
