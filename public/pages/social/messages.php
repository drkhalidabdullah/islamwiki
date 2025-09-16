<?php
require_once '/var/www/html/public/config/config.php';
require_once '/var/www/html/public/includes/functions.php';

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

// Get user's friends for new message
$stmt = $pdo->prepare("
    SELECT u.*, uf.created_at as friendship_date
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ? AND uf.status = 'accepted'
    ORDER BY u.display_name ASC, u.username ASC
");
$stmt->execute([$_SESSION['user_id']]);
$friends = $stmt->fetchAll();

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

<div class="messenger-container">
    <!-- Left Sidebar -->
    <div class="messenger-sidebar">
        <div class="messenger-header">
            <h2>Chats</h2>
            <div class="messenger-actions">
                <div class="messenger-options">
                    <button class="options-btn" id="optionsBtn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="options-menu" id="optionsMenu">
                        <a href="#" class="options-item">
                            <i class="fas fa-cog"></i>
                            Preferences
                        </a>
                        <a href="#" class="options-item">
                            <i class="fas fa-comment"></i>
                            Message requests
                        </a>
                        <a href="#" class="options-item">
                            <i class="fas fa-archive"></i>
                            Archived chats
                        </a>
                        <a href="#" class="options-item">
                            <i class="fas fa-ban"></i>
                            Restricted accounts
                        </a>
                        <a href="#" class="options-item">
                            <i class="fas fa-shield-alt"></i>
                            Privacy & safety
                        </a>
                        <a href="#" class="options-item">
                            <i class="fas fa-question-circle"></i>
                            Help
                        </a>
                    </div>
                </div>
                <button class="new-message-btn" id="newMessageBtn">
                    <i class="fas fa-pencil-alt"></i>
                </button>
            </div>
        </div>
        
        <div class="messenger-search">
            <input type="text" placeholder="Search Messenger" class="search-input" id="searchInput">
        </div>
        
        <div class="messenger-tabs">
            <button class="tab active" data-tab="all">All</button>
            <button class="tab" data-tab="unread">Unread</button>
            <button class="tab" data-tab="groups">Groups</button>
            <button class="tab" data-tab="communities">Communities</button>
        </div>
        
        <!-- New Message Section -->
        <div class="new-message-section" id="newMessageSection" style="display: none;">
            <div class="new-message-header">
                <button class="back-btn" id="backToChats">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h3>New message</h3>
            </div>
            <div class="recipient-search">
                <input type="text" placeholder="To:" class="recipient-input" id="recipientInput">
            </div>
            <div class="recipients-list" id="recipientsList">
                <div class="recipients-header">Your contacts</div>
                <?php foreach ($friends as $friend): ?>
                    <div class="recipient-item" data-user-id="<?php echo $friend['id']; ?>" data-user-name="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>">
                        <img src="<?php echo htmlspecialchars($friend['avatar'] ?? '/assets/images/default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>" 
                             class="recipient-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                        <div class="recipient-info">
                            <div class="recipient-name"><?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
                        <img src="<?php echo htmlspecialchars($conv['avatar'] ?? '/assets/images/default-avatar.png'); ?>" alt="<?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?>" 
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
    </div>
    
    <!-- Main Chat Area -->
    <div class="messenger-main">
        <!-- Default state when no conversation is selected -->
        <div class="no-conversation" id="noConversation">
            <div class="no-conversation-content">
                <i class="fas fa-comments"></i>
                <h3>Your Messages</h3>
                <p>Select a conversation to start messaging</p>
            </div>
        </div>
        
        <!-- Chat Wrapper -->
        <div class="chat-wrapper">
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
                    <button class="chat-action-btn" title="Voice Call">
                        <i class="fas fa-phone"></i>
                    </button>
                    <button class="chat-action-btn" title="Video Call">
                        <i class="fas fa-video"></i>
                    </button>
                    <button class="chat-action-btn" title="More Options" id="infoBtn">
                        <i class="fas fa-info-circle"></i>
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
                <button class="chat-input-btn" title="Emoji">
                    <i class="far fa-smile"></i>
                </button>
                <button class="chat-input-btn" title="Sticker">
                    <i class="fas fa-sticky-note"></i>
                </button>
                <button class="chat-input-btn" title="GIF">
                    <i class="fas fa-image"></i>
                </button>
                <input type="text" placeholder="Aa" class="chat-text-input" id="messageInput">
                <button class="chat-send-btn" title="Send Message" id="sendMessageBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            </div>

            <!-- Info Box -->
            <div class="info-box" id="infoBox" style="display: none;">
                <div class="info-header">
                    <div class="info-tabs">
                        <button class="info-tab active" data-tab="profile">Profile</button>
                        <button class="info-tab" data-tab="mute">Mute</button>
                        <button class="info-tab" data-tab="search">Search</button>
                    </div>
                    <button class="info-close" id="infoClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Profile Tab -->
                <div class="info-content" id="profileTab">
                    <div class="info-user-profile">
                        <img src="/assets/images/default-avatar.png" alt="User" class="info-user-avatar" id="infoUserAvatar">
                        <div class="info-user-name" id="infoUserName">User Name</div>
                        <div class="info-encryption">
                            <i class="fas fa-lock"></i>
                            End-to-end encrypted
                        </div>
                        <div class="info-profile-actions">
                            <button class="info-profile-btn" id="viewProfileBtn">
                                <i class="fas fa-user"></i>
                                Profile
                            </button>
                            <button class="info-profile-btn" id="muteBtn">
                                <i class="fas fa-bell-slash"></i>
                                Mute
                            </button>
                            <button class="info-profile-btn" id="searchBtn">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Chat info</span>
                            <i class="fas fa-chevron-up"></i>
                        </div>
                        <div class="info-section-content">
                            <div class="info-item">
                                <i class="fas fa-thumbtack"></i>
                                <span>View pinned messages</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Customize chat</span>
                            <i class="fas fa-chevron-up"></i>
                        </div>
                        <div class="info-section-content">
                            <div class="info-item">
                                <i class="fas fa-palette"></i>
                                <span>Change theme</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-smile"></i>
                                <span>Change emoji</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-edit"></i>
                                <span>Edit nicknames</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Media & files</span>
                            <i class="fas fa-chevron-up"></i>
                        </div>
                        <div class="info-section-content">
                            <div class="info-item">
                                <i class="fas fa-images"></i>
                                <span>Media</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-file"></i>
                                <span>Files</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-header">
                            <span>Privacy & support</span>
                            <i class="fas fa-chevron-up"></i>
                        </div>
                        <div class="info-section-content">
                            <div class="info-item">
                                <i class="fas fa-bell-slash"></i>
                                <span>Mute notifications</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Message permissions</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Disappearing messages</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-eye"></i>
                                <span>Read receipts</span>
                                <span class="info-status">On</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-lock"></i>
                                <span>Verify end-to-end encryption</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-ban"></i>
                                <span>Restrict</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-minus-circle"></i>
                                <span>Block</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Report</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mute Tab -->
                <div class="info-content" id="muteTab" style="display: none;">
                    <div class="mute-options">
                        <div class="mute-option">
                            <input type="radio" name="muteDuration" value="15" id="mute15">
                            <label for="mute15">For 15 minutes</label>
                        </div>
                        <div class="mute-option">
                            <input type="radio" name="muteDuration" value="60" id="mute60">
                            <label for="mute60">For 1 Hour</label>
                        </div>
                        <div class="mute-option">
                            <input type="radio" name="muteDuration" value="480" id="mute480">
                            <label for="mute480">For 8 Hours</label>
                        </div>
                        <div class="mute-option">
                            <input type="radio" name="muteDuration" value="1440" id="mute1440">
                            <label for="mute1440">For 24 Hours</label>
                        </div>
                        <div class="mute-option">
                            <input type="radio" name="muteDuration" value="permanent" id="mutePermanent">
                            <label for="mutePermanent">Until I turn it back on</label>
                        </div>
                    </div>
                    <div class="mute-description">
                        Chat windows will stay closed, and you won't get push notifications on your devices.
                    </div>
                    <div class="mute-actions">
                        <button class="mute-cancel" id="muteCancel">Cancel</button>
                        <button class="mute-confirm" id="muteConfirm">Mute</button>
                    </div>
                </div>

                <!-- Search Tab -->
                <div class="info-content" id="searchTab" style="display: none;">
                    <div class="search-header">
                        <span>Search in conversation</span>
                        <button class="search-close" id="searchClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="search-input-container">
                        <input type="text" class="search-input" id="conversationSearch" placeholder="Search messages...">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="search-results" id="searchResults">
                        <!-- Search results will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Mute Overlay -->
<div class="mute-overlay" id="muteOverlay" style="display: none;">
    <div class="mute-dialog">
        <div class="mute-dialog-header">
            <h3>Mute conversation</h3>
            <button class="mute-dialog-close" id="muteDialogClose">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="mute-dialog-content">
            <div class="mute-option">
                <input type="radio" name="muteDurationOverlay" value="15" id="mute15Overlay" checked>
                <label for="mute15Overlay">For 15 minutes</label>
            </div>
            <div class="mute-option">
                <input type="radio" name="muteDurationOverlay" value="60" id="mute60Overlay">
                <label for="mute60Overlay">For 1 Hour</label>
            </div>
            <div class="mute-option">
                <input type="radio" name="muteDurationOverlay" value="480" id="mute480Overlay">
                <label for="mute480Overlay">For 8 Hours</label>
            </div>
            <div class="mute-option">
                <input type="radio" name="muteDurationOverlay" value="1440" id="mute1440Overlay">
                <label for="mute1440Overlay">For 24 Hours</label>
            </div>
            <div class="mute-option">
                <input type="radio" name="muteDurationOverlay" value="permanent" id="mutePermanentOverlay">
                <label for="mutePermanentOverlay">Until I turn it back on</label>
            </div>
            <div class="mute-description">
                Chat windows will stay closed, and you won't get push notifications on your devices.
            </div>
        </div>
        <div class="mute-dialog-actions">
            <button class="mute-cancel" id="muteOverlayCancel">Cancel</button>
            <button class="mute-confirm" id="muteOverlayConfirm">Mute</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationsList = document.getElementById('conversationsList');
    const newMessageSection = document.getElementById('newMessageSection');
    const noConversation = document.getElementById('noConversation');
    const chatInterface = document.getElementById('chatInterface');
    const newMessageBtn = document.getElementById('newMessageBtn');
    const backToChats = document.getElementById('backToChats');
    const optionsBtn = document.getElementById('optionsBtn');
    const optionsMenu = document.getElementById('optionsMenu');
    const recipientInput = document.getElementById('recipientInput');
    const recipientsList = document.getElementById('recipientsList');
    const infoBtn = document.getElementById('infoBtn');
    const infoBox = document.getElementById('infoBox');
    const infoClose = document.getElementById('infoClose');
    const infoTabs = document.querySelectorAll('.info-tab');
    const muteOverlay = document.getElementById('muteOverlay');
    const muteDialogClose = document.getElementById('muteDialogClose');
    const muteOverlayCancel = document.getElementById('muteOverlayCancel');
    const muteOverlayConfirm = document.getElementById('muteOverlayConfirm');
    const conversationSearch = document.getElementById('conversationSearch');
    const searchResults = document.getElementById('searchResults');
    
    let currentConversation = null;
    let currentSearchResults = [];
    
    // New message button
    newMessageBtn.addEventListener('click', function() {
        conversationsList.style.display = 'none';
        newMessageSection.style.display = 'block';
    });
    
    // Back to chats
    backToChats.addEventListener('click', function() {
        newMessageSection.style.display = 'none';
        conversationsList.style.display = 'block';
    });
    
    // Options menu
    optionsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        optionsMenu.style.display = optionsMenu.style.display === 'block' ? 'none' : 'block';
    });
    
    // Close options menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!optionsBtn.contains(e.target) && !optionsMenu.contains(e.target)) {
            optionsMenu.style.display = 'none';
        }
    });

    // Info button functionality
    infoBtn.addEventListener('click', function() {
        if (infoBox.style.display === 'none' || infoBox.style.display === '') {
            // Show info box as third column
            infoBox.style.display = 'flex';
            // Update user info in info box
            const chatUserName = document.getElementById('chatUserName').textContent;
            const chatUserAvatar = document.getElementById('chatUserAvatar').src;
            document.getElementById('infoUserName').textContent = chatUserName;
            document.getElementById('infoUserAvatar').src = chatUserAvatar;
        } else {
            // Hide info box, return to two-column layout
            infoBox.style.display = 'none';
        }
    });

    // Close info box
    infoClose.addEventListener('click', function() {
        infoBox.style.display = 'none';
    });

    // Info tab switching
    infoTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all tabs
            infoTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all content
            document.querySelectorAll('.info-content').forEach(content => {
                content.style.display = 'none';
            });
            
            // Show selected content
            if (tabName === 'mute') {
                document.getElementById('muteTab').style.display = 'block';
            } else if (tabName === 'search') {
                document.getElementById('searchTab').style.display = 'block';
                conversationSearch.focus();
            } else {
                document.getElementById('profileTab').style.display = 'block';
            }
        });
    });

    // Mute functionality
    document.getElementById('muteCancel').addEventListener('click', function() {
        // Switch to profile tab
        infoTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('[data-tab="profile"]').classList.add('active');
        document.querySelectorAll('.info-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById('profileTab').style.display = 'block';
    });

    document.getElementById('muteConfirm').addEventListener('click', function() {
        const selectedDuration = document.querySelector('input[name="muteDuration"]:checked').value;
        console.log('Muting conversation for:', selectedDuration);
        // Here you would implement the actual muting logic
        alert('Conversation muted for ' + selectedDuration + ' minutes');
        // Switch back to profile tab
        infoTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('[data-tab="profile"]').classList.add('active');
        document.querySelectorAll('.info-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById('profileTab').style.display = 'block';
    });

    // Mute overlay functionality
    document.querySelector('[data-tab="mute"]').addEventListener('click', function() {
        muteOverlay.style.display = 'flex';
    });

    muteDialogClose.addEventListener('click', function() {
        muteOverlay.style.display = 'none';
    });

    muteOverlayCancel.addEventListener('click', function() {
        muteOverlay.style.display = 'none';
    });

    muteOverlayConfirm.addEventListener('click', function() {
        const selectedDuration = document.querySelector('input[name="muteDurationOverlay"]:checked').value;
        console.log('Muting conversation for:', selectedDuration);
        // Here you would implement the actual muting logic
        alert('Conversation muted for ' + selectedDuration + ' minutes');
        muteOverlay.style.display = 'none';
    });

    // Search functionality
    conversationSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        if (searchTerm.length < 2) {
            searchResults.innerHTML = '';
            return;
        }
        
        // Search through current conversation messages
        searchInConversation(searchTerm);
    });

    function searchInConversation(searchTerm) {
        // This would typically make an AJAX call to search messages
        // For now, we'll simulate with the current messages
        const messages = document.querySelectorAll('.message');
        const results = [];
        
        messages.forEach((message, index) => {
            const messageText = message.querySelector('.message-content').textContent.toLowerCase();
            if (messageText.includes(searchTerm)) {
                results.push({
                    id: index,
                    text: message.querySelector('.message-content').textContent,
                    time: message.querySelector('.message-time').textContent,
                    sender: message.classList.contains('sent') ? 'You' : document.getElementById('chatUserName').textContent
                });
            }
        });
        
        displaySearchResults(results, searchTerm);
    }

    function displaySearchResults(results, searchTerm) {
        currentSearchResults = results;
        searchResults.innerHTML = '';
        
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="no-results">No messages found</div>';
            return;
        }
        
        results.forEach(result => {
            const resultDiv = document.createElement('div');
            resultDiv.className = 'search-result';
            resultDiv.innerHTML = `
                <div class="search-result-text">${highlightSearchTerm(result.text, searchTerm)}</div>
                <div class="search-result-meta">
                    <span class="search-result-sender">${result.sender}</span>
                    <span class="search-result-time">${result.time}</span>
                </div>
            `;
            searchResults.appendChild(resultDiv);
        });
    }

    function highlightSearchTerm(text, searchTerm) {
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    // Profile action buttons
    document.getElementById('viewProfileBtn').addEventListener('click', function() {
        // Get current conversation user ID and redirect to profile
        if (currentConversation) {
            window.location.href = `/user/profile/${currentConversation}`;
        } else {
            alert('No conversation selected');
        }
    });

    document.getElementById('muteBtn').addEventListener('click', function() {
        // Switch to mute tab
        infoTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('[data-tab="mute"]').classList.add('active');
        document.querySelectorAll('.info-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById('muteTab').style.display = 'block';
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        // Switch to search tab
        infoTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('[data-tab="search"]').classList.add('active');
        document.querySelectorAll('.info-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById('searchTab').style.display = 'block';
        conversationSearch.focus();
    });
    
    // Recipient search
    recipientInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const recipients = recipientsList.querySelectorAll('.recipient-item');
        
        recipients.forEach(recipient => {
            const name = recipient.querySelector('.recipient-name').textContent.toLowerCase();
            if (name.includes(searchTerm)) {
                recipient.style.display = 'flex';
            } else {
                recipient.style.display = 'none';
            }
        });
    });
    
    // Recipient selection
    recipientsList.addEventListener('click', function(e) {
        const recipientItem = e.target.closest('.recipient-item');
        if (recipientItem) {
            const userId = recipientItem.dataset.userId;
            const userName = recipientItem.dataset.userName;
            
            // Start conversation with this user
            startConversation(userId, userName);
        }
    });
    
    // Conversation item clicks are handled by messaging.js
    
    function startConversation(userId, userName) {
        // Hide new message section and show conversations
        newMessageSection.style.display = 'none';
        conversationsList.style.display = 'block';
        
        // Load the conversation using messaging system
        if (window.messagingSystem) {
            window.messagingSystem.loadConversation(userId);
        }
    }
    
    // loadConversation is handled by messaging.js
});
</script>

<?php include "../../includes/footer.php"; ?>