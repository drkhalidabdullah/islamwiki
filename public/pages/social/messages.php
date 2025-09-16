<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$page_title = 'Messages';
check_maintenance_mode();
require_login();

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'error');
    redirect('/dashboard');
}

$current_user = get_user($_SESSION['user_id']);

// Get user's conversations (both sent and received)
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
        m.sender_id,
        m.id as message_id
    FROM messages m
    JOIN users u ON (
        CASE 
            WHEN m.sender_id = ? THEN m.recipient_id 
            ELSE m.sender_id 
        END = u.id
    )
    WHERE (m.sender_id = ? OR m.recipient_id = ?)
    AND m.id IN (
        SELECT MAX(id) 
        FROM messages 
        WHERE (sender_id = ? AND recipient_id = u.id) 
           OR (sender_id = u.id AND recipient_id = ?)
        GROUP BY LEAST(sender_id, recipient_id), GREATEST(sender_id, recipient_id)
    )
    ORDER BY m.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversations = $stmt->fetchAll();

// Get user's friends for compose
$stmt = $pdo->prepare("
    SELECT u.*, uf.created_at as friendship_date
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ? AND uf.status = 'accepted'
    ORDER BY u.display_name ASC, u.username ASC
");
$stmt->execute([$_SESSION['user_id']]);
$friends = $stmt->fetchAll();

// Get sent messages for sent view
$stmt = $pdo->prepare("
    SELECT m.*, u.username, u.display_name, u.avatar
    FROM messages m
    JOIN users u ON m.recipient_id = u.id
    WHERE m.sender_id = ?
    ORDER BY m.created_at DESC
    LIMIT 50
");
$stmt->execute([$_SESSION['user_id']]);
$sent_messages = $stmt->fetchAll();

include "../../includes/header.php";

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/social.css">
<script src="/skins/bismillah/assets/js/social_messages.js"></script>
<script src="/skins/bismillah/assets/js/messaging.js"></script>
<script>
// Set current user ID for messaging system
window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
</script>

<div class="messages-page">
    <div class="messages-container">
        <!-- Sidebar with conversations and compose -->
        <div class="messages-sidebar">
            <div class="messages-header">
                <h2>Messages</h2>
                <div class="messages-actions">
                    <button class="new-message-btn" id="newMessageBtn" title="New Message">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="messages-options">
                        <button class="messages-options-btn" title="Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="messages-options-menu">
                            <a href="#" class="messages-options" id="viewSentBtn" title="View Sent Messages">
                                <i class="fas fa-paper-plane"></i>
                                Sent
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="messages-search">
                <input type="text" placeholder="Search conversations..." class="messages-search-input" id="conversationSearch">
            </div>
            
            <div class="messages-tabs">
                <button class="message-tab active" data-tab="conversations">Conversations</button>
                <button class="message-tab" data-tab="sent">Sent</button>
            </div>
            
            <!-- Conversations List -->
            <div class="conversations-list" id="conversationsList">
                <?php if (empty($conversations)): ?>
                    <div class="no-conversations">
                        <i class="fas fa-comments"></i>
                        <p>No conversations yet</p>
                        <p>Start a new conversation!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item" 
                             data-conversation-id="<?php echo $conv['other_user_id']; ?>"
                             data-other-user-id="<?php echo $conv['other_user_id']; ?>">
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
                <?php endif; ?>
            </div>
            
            <!-- Sent Messages List (hidden by default) -->
            <div class="sent-messages-list" id="sentMessagesList" style="display: none;">
                <?php if (empty($sent_messages)): ?>
                    <div class="no-messages">
                        <i class="fas fa-paper-plane"></i>
                        <p>No sent messages yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($sent_messages as $msg): ?>
                        <div class="sent-message-item" data-message-id="<?php echo $msg['id']; ?>">
                            <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($msg['display_name'] ?: $msg['username']); ?>" 
                                 class="sent-message-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                            <div class="sent-message-info">
                                <div class="sent-message-recipient">To: <?php echo htmlspecialchars($msg['display_name'] ?: $msg['username']); ?></div>
                                <div class="sent-message-preview"><?php echo htmlspecialchars($msg['message']); ?></div>
                            </div>
                            <div class="sent-message-meta">
                                <div class="sent-message-time"><?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></div>
                                <div class="sent-message-status">
                                    <?php if ($msg['is_read']): ?>
                                        <i class="fas fa-check-double" title="Read"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check" title="Sent"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Chat Area -->
        <div class="messages-main">
            <!-- Default state when no conversation is selected -->
            <div class="no-conversation" id="noConversation">
                <div class="no-conversation-content">
                    <i class="fas fa-comments"></i>
                    <h3>Your Messages</h3>
                    <p>Select a conversation to start messaging</p>
                    <button class="btn btn-primary" id="startNewConversationBtn">
                        <i class="fas fa-plus"></i>
                        Start New Conversation
                    </button>
                </div>
            </div>
            
            <!-- Chat interface (hidden by default) -->
            <div class="chat-interface" id="chatInterface" style="display: none;">
                <div class="chat-header">
                    <div class="chat-user-info">
                        <img src="/assets/images/default-avatar.png" alt="User" class="chat-user-avatar" id="chatUserAvatar">
                        <div class="chat-user-details">
                            <div class="chat-user-name" id="chatUserName">User Name</div>
                            <div class="chat-status" id="chatStatus">Online</div>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn" title="Video Call">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="chat-action-btn" title="Voice Call">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="chat-action-btn" title="More Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be loaded here -->
                </div>
                
                <div class="chat-input">
                    <button class="chat-input-btn" title="Attach File">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="text" placeholder="Type a message..." class="chat-text-input" id="messageInput">
                    <button class="chat-input-btn" title="Send Message" id="sendMessageBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compose Modal -->
<div class="compose-modal" id="composeModal">
    <div class="compose-modal-content">
        <div class="compose-modal-header">
            <h3>New Message</h3>
            <button class="compose-modal-close" id="composeModalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="compose-modal-body">
            <div class="compose-recipient">
                <label for="composeRecipient">To:</label>
                <div class="recipient-selector">
                    <input type="hidden" name="recipient_id" id="composeRecipientId">
                    <div class="selected-recipient" id="composeSelectedRecipient">
                        <span class="placeholder">Select a recipient...</span>
                    </div>
                    <div class="recipient-dropdown" id="recipientDropdown">
                        <?php foreach ($friends as $friend): ?>
                            <div class="recipient-item" data-user-id="<?php echo $friend['id']; ?>" data-user-name="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>">
                                <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>" 
                                     class="recipient-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                <div class="recipient-info">
                                    <div class="recipient-name"><?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?></div>
                                    <div class="recipient-status">Online</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="compose-subject">
                <input type="text" name="subject" id="composeSubject" placeholder="Subject (optional)" maxlength="100">
            </div>
            
            <div class="compose-message">
                <textarea name="message" id="composeMessage" placeholder="Type your message here..." required rows="8"></textarea>
            </div>
        </div>
        
        <div class="compose-modal-footer">
            <button class="btn btn-secondary" id="composeCancelBtn">Cancel</button>
            <button class="btn btn-primary" id="composeSendBtn">Send</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationsList = document.getElementById('conversationsList');
    const sentMessagesList = document.getElementById('sentMessagesList');
    const noConversation = document.getElementById('noConversation');
    const chatInterface = document.getElementById('chatInterface');
    const composeModal = document.getElementById('composeModal');
    const newMessageBtn = document.getElementById('newMessageBtn');
    const composeModalClose = document.getElementById('composeModalClose');
    const composeCancelBtn = document.getElementById('composeCancelBtn');
    const composeSendBtn = document.getElementById('composeSendBtn');
    const messageTabs = document.querySelectorAll('.message-tab');
    const conversationSearch = document.getElementById('conversationSearch');
    const startNewConversationBtn = document.getElementById('startNewConversationBtn');
    
    let currentConversation = null;
    let currentView = 'conversations';
    
    // Tab switching
    messageTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabType = this.dataset.tab;
            
            // Update active tab
            messageTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide appropriate lists
            if (tabType === 'conversations') {
                conversationsList.style.display = 'block';
                sentMessagesList.style.display = 'none';
                currentView = 'conversations';
            } else if (tabType === 'sent') {
                conversationsList.style.display = 'none';
                sentMessagesList.style.display = 'block';
                currentView = 'sent';
            }
        });
    });
    
    // New message button
    newMessageBtn.addEventListener('click', function() {
        composeModal.style.display = 'flex';
    });
    
    startNewConversationBtn.addEventListener('click', function() {
        composeModal.style.display = 'flex';
    });
    
    // Close compose modal
    composeModalClose.addEventListener('click', function() {
        composeModal.style.display = 'none';
        resetComposeForm();
    });
    
    composeCancelBtn.addEventListener('click', function() {
        composeModal.style.display = 'none';
        resetComposeForm();
    });
    
    // Send message from compose modal
    composeSendBtn.addEventListener('click', function() {
        sendComposeMessage();
    });
    
    // Conversation search
    conversationSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const items = currentView === 'conversations' ? 
            conversationsList.querySelectorAll('.conversation-item') : 
            sentMessagesList.querySelectorAll('.sent-message-item');
        
        items.forEach(item => {
            const name = item.querySelector('.conversation-name, .sent-message-recipient').textContent.toLowerCase();
            if (name.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Conversation item clicks
    conversationsList.addEventListener('click', function(e) {
        const conversationItem = e.target.closest('.conversation-item');
        if (conversationItem) {
            const conversationId = conversationItem.dataset.conversationId;
            loadConversation(conversationId);
        }
    });
    
    // Sent message item clicks
    sentMessagesList.addEventListener('click', function(e) {
        const sentItem = e.target.closest('.sent-message-item');
        if (sentItem) {
            const messageId = sentItem.dataset.messageId;
            // Could implement viewing sent message details
            console.log('View sent message:', messageId);
        }
    });
    
    function loadConversation(conversationId) {
        currentConversation = conversationId;
        
        // Update active conversation
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const activeItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (activeItem) {
            activeItem.classList.add('active');
        }
        
        // Show chat interface
        noConversation.style.display = 'none';
        chatInterface.style.display = 'block';
        
        // Load messages
        if (window.messagingSystem) {
            window.messagingSystem.loadConversation(conversationId);
        }
    }
    
    function sendComposeMessage() {
        const recipientId = document.getElementById('composeRecipientId').value;
        const message = document.getElementById('composeMessage').value.trim();
        const subject = document.getElementById('composeSubject').value.trim();
        
        if (!recipientId) {
            alert('Please select a recipient.');
            return;
        }
        
        if (!message) {
            alert('Please enter a message.');
            return;
        }
        
        // Send message via AJAX
        fetch('/api/ajax/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                recipient_id: recipientId,
                message: message,
                subject: subject
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reset form
                composeModal.style.display = 'none';
                resetComposeForm();
                
                // Show success message
                alert('Message sent successfully!');
                
                // Refresh conversations list
                location.reload();
            } else {
                alert('Failed to send message: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message');
        });
    }
    
    function resetComposeForm() {
        document.getElementById('composeRecipientId').value = '';
        document.getElementById('composeSelectedRecipient').innerHTML = '<span class="placeholder">Select a recipient...</span>';
        document.getElementById('composeSubject').value = '';
        document.getElementById('composeMessage').value = '';
        
        // Reset recipient selection
        document.querySelectorAll('.recipient-item').forEach(item => {
            item.classList.remove('selected');
        });
    }
    
    // Recipient selection
    document.addEventListener('click', function(e) {
        if (e.target.closest('.recipient-item')) {
            const recipientItem = e.target.closest('.recipient-item');
            const userId = recipientItem.dataset.userId;
            const userName = recipientItem.dataset.userName;
            
            if (userId) {
                // Update selection
                document.querySelectorAll('.recipient-item').forEach(item => {
                    item.classList.remove('selected');
                });
                recipientItem.classList.add('selected');
                
                // Update display
                document.getElementById('composeRecipientId').value = userId;
                document.getElementById('composeSelectedRecipient').innerHTML = `<span class="selected-name">${userName}</span>`;
            }
        }
    });
});
</script>

<?php include "../../includes/footer.php"; ?>