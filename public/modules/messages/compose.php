<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Compose Message';
check_maintenance_mode();
require_login();

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'error');
    redirect('/dashboard');
}

$current_user = get_user($_SESSION['user_id']);

// Get user's friends for recipient selection
$stmt = $pdo->prepare("
    SELECT u.*, uf.created_at as friendship_date
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ? AND uf.status = 'accepted'
    ORDER BY u.display_name ASC, u.username ASC
");
$stmt->execute([$_SESSION['user_id']]);
$friends = $stmt->fetchAll();

// Get recent conversations for quick access
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
        m.created_at
    FROM messages m
    JOIN users u ON (
        CASE 
            WHEN m.sender_id = ? THEN m.recipient_id 
            ELSE m.sender_id 
        END = u.id
    )
    WHERE m.sender_id = ? OR m.recipient_id = ?
    ORDER BY m.created_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$recent_conversations = $stmt->fetchAll();

// Handle form submission (fallback for non-JS users)
if ($_POST) {
    $recipient_id = $_POST['recipient_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    
    if ($recipient_id && $message) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages (sender_id, recipient_id, message, subject, created_at, is_read) 
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            $stmt->execute([$_SESSION['user_id'], $recipient_id, $message, $subject]);
            
            show_message('Message sent successfully!', 'success');
            redirect('/messages/sent');
        } catch (PDOException $e) {
            show_message('Error sending message. Please try again.', 'error');
        }
    } else {
        show_message('Please fill in all required fields.', 'error');
    }
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
        <!-- Left Sidebar: Recipients -->
        <div class="messages-sidebar">
            <div class="messages-header">
                <h2>Compose Message</h2>
                <div class="messages-actions">
                    <a href="/messages" class="messages-options" title="All Messages">
                        <i class="fas fa-inbox"></i>
                    </a>
                    <a href="/messages/sent" class="messages-options" title="Sent Messages">
                        <i class="fas fa-paper-plane"></i>
                    </a>
                </div>
            </div>
            
            <div class="messages-search">
                <input type="text" placeholder="Search friends..." class="messages-search-input" id="friendSearchInput">
            </div>
            
            <div class="messages-tabs">
                <button class="message-tab active" data-tab="friends">Friends</button>
                <button class="message-tab" data-tab="recent">Recent</button>
            </div>
            
            <div class="conversations-list" id="recipientsList">
                <!-- Friends Tab -->
                <div class="recipients-section" id="friendsSection">
                    <?php if (empty($friends)): ?>
                        <div class="no-friends">
                            <i class="fas fa-users"></i>
                            <h3>No friends yet</h3>
                            <p>Add some friends to start messaging.</p>
                            <a href="/friends" class="btn btn-primary">Find Friends</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($friends as $friend): ?>
                            <div class="recipient-item" data-user-id="<?php echo $friend['id']; ?>" data-user-name="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>">
                                <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?>" 
                                     class="recipient-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                <div class="recipient-info">
                                    <div class="recipient-name"><?php echo htmlspecialchars($friend['display_name'] ?: $friend['username']); ?></div>
                                    <div class="recipient-status">Friends since <?php echo date('M Y', strtotime($friend['friendship_date'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Tab -->
                <div class="recipients-section" id="recentSection" style="display: none;">
                    <?php if (empty($recent_conversations)): ?>
                        <div class="no-recent">
                            <i class="fas fa-history"></i>
                            <h3>No recent conversations</h3>
                            <p>Start a conversation to see it here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_conversations as $conv): ?>
                            <div class="recipient-item" data-user-id="<?php echo $conv['other_user_id']; ?>" data-user-name="<?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?>">
                                <img src="/assets/images/default-avatar.png" alt="<?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?>" 
                                     class="recipient-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                <div class="recipient-info">
                                    <div class="recipient-name"><?php echo htmlspecialchars($conv['display_name'] ?: $conv['username']); ?></div>
                                    <div class="recipient-preview"><?php echo htmlspecialchars(substr($conv['message'], 0, 50)) . (strlen($conv['message']) > 50 ? '...' : ''); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Center: Compose Area -->
        <div class="messages-main">
            <div class="compose-area">
                <div class="compose-header">
                    <h1>Compose New Message</h1>
                    <div class="compose-actions">
                        <button class="btn-save-draft" id="saveDraftBtn" title="Save as Draft">
                            <i class="fas fa-save"></i>
                            Save Draft
                        </button>
                        <button class="btn-clear" id="clearBtn" title="Clear Message">
                            <i class="fas fa-trash"></i>
                            Clear
                        </button>
                    </div>
                </div>

                <form method="POST" class="compose-form" id="composeForm" onsubmit="return false;">
                    <div class="compose-recipient">
                        <label for="recipient_id">To:</label>
                        <div class="recipient-selector">
                            <input type="hidden" name="recipient_id" id="recipient_id" required>
                            <div class="selected-recipient" id="selectedRecipient">
                                <span class="placeholder">Select a recipient...</span>
                            </div>
                            <button type="button" class="btn-select-recipient" id="selectRecipientBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="compose-subject">
                        <label for="subject">Subject (optional):</label>
                        <input type="text" name="subject" id="subject" placeholder="Enter subject..." maxlength="100">
                    </div>

                    <div class="compose-message">
                        <label for="message">Message:</label>
                        <textarea name="message" id="message" placeholder="Type your message here..." required rows="10"></textarea>
                        <div class="message-actions">
                            <div class="message-tools">
                                <button type="button" class="tool-btn" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="tool-btn" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="tool-btn" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button type="button" class="tool-btn" title="Emoji">
                                    <i class="fas fa-smile"></i>
                                </button>
                                <button type="button" class="tool-btn" title="Attach File">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                            </div>
                            <div class="message-counter">
                                <span id="charCount">0</span> characters
                            </div>
                        </div>
                    </div>

                    <div class="compose-footer">
                        <div class="compose-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="save_copy" checked>
                                <span class="checkmark"></span>
                                Save a copy in Sent
                            </label>
                        </div>
                        <div class="compose-buttons">
                            <button type="button" class="btn-cancel" onclick="window.location.href='/messages'">Cancel</button>
                            <button type="submit" class="btn-send">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Sidebar: Compose Info -->
        <div class="messages-info">
            <div class="compose-info-header">
                <h3>Compose Tips</h3>
            </div>
            
            <div class="compose-tips">
                <div class="tip-item">
                    <i class="fas fa-lightbulb"></i>
                    <div class="tip-content">
                        <h4>Use @mentions</h4>
                        <p>Type @username to mention someone in your message.</p>
                    </div>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-shield-alt"></i>
                    <div class="tip-content">
                        <h4>End-to-end encryption</h4>
                        <p>Your messages are encrypted and secure.</p>
                    </div>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-clock"></i>
                    <div class="tip-content">
                        <h4>Draft auto-save</h4>
                        <p>Your drafts are automatically saved every 30 seconds.</p>
                    </div>
                </div>
                
                <div class="tip-item">
                    <i class="fas fa-edit"></i>
                    <div class="tip-content">
                        <h4>Rich formatting</h4>
                        <p>Use the toolbar to format your text with bold, italic, and more.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const friendSearchInput = document.getElementById('friendSearchInput');
    const recipientsList = document.getElementById('recipientsList');
    const selectedRecipient = document.getElementById('selectedRecipient');
    const recipientId = document.getElementById('recipient_id');
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const composeForm = document.getElementById('composeForm');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const clearBtn = document.getElementById('clearBtn');
    
    // Tab switching
    const tabs = document.querySelectorAll('.message-tab');
    const sections = document.querySelectorAll('.recipients-section');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding section
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            if (tabName === 'friends') {
                document.getElementById('friendsSection').style.display = 'block';
            } else if (tabName === 'recent') {
                document.getElementById('recentSection').style.display = 'block';
            }
        });
    });
    
    // Friend search
    friendSearchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const recipientItems = document.querySelectorAll('.recipient-item');
        
        recipientItems.forEach(item => {
            const name = item.querySelector('.recipient-name').textContent.toLowerCase();
            const status = item.querySelector('.recipient-status, .recipient-preview')?.textContent.toLowerCase() || '';
            
            if (name.includes(searchTerm) || status.includes(searchTerm)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Recipient selection
    const recipientItems = document.querySelectorAll('.recipient-item');
    recipientItems.forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            // Update selected recipient
            selectedRecipient.innerHTML = `<span class="selected-name">${userName}</span>`;
            recipientId.value = userId;
            
            // Update visual state
            recipientItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Character counter
    messageTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
    
    // Auto-save draft
    let draftTimer;
    messageTextarea.addEventListener('input', function() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(() => {
            saveDraft();
        }, 30000); // Save every 30 seconds
    });
    
    // Load draft on page load
    loadDraft();
    
    // Save draft function
    function saveDraft() {
        const draft = {
            recipient_id: recipientId.value,
            subject: document.getElementById('subject').value,
            message: messageTextarea.value,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('message_draft', JSON.stringify(draft));
    }
    
    // Load draft function
    function loadDraft() {
        const draft = localStorage.getItem('message_draft');
        if (draft) {
            try {
                const draftData = JSON.parse(draft);
                if (draftData.recipient_id) {
                    recipientId.value = draftData.recipient_id;
                    selectedRecipient.innerHTML = `<span class="selected-name">Selected recipient</span>`;
                }
                if (draftData.subject) {
                    document.getElementById('subject').value = draftData.subject;
                }
                if (draftData.message) {
                    messageTextarea.value = draftData.message;
                    charCount.textContent = draftData.message.length;
                }
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }
    }
    
    // Manual save draft
    saveDraftBtn.addEventListener('click', function() {
        saveDraft();
        this.innerHTML = '<i class="fas fa-check"></i> Saved!';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-save"></i> Save Draft';
        }, 2000);
    });
    
    // Clear form
    clearBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the message?')) {
            composeForm.reset();
            selectedRecipient.innerHTML = '<span class="placeholder">Select a recipient...</span>';
            recipientItems.forEach(item => item.classList.remove('selected'));
            charCount.textContent = '0';
            localStorage.removeItem('message_draft');
        }
    });
    
    // Form submission
    composeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!recipientId.value) {
            alert('Please select a recipient.');
            return;
        }
        
        if (!messageTextarea.value.trim()) {
            alert('Please enter a message.');
            return;
        }
        
        // Send message via AJAX
        sendComposeMessage();
    });
    
    // Send compose message function
    async function sendComposeMessage() {
        const recipientId = document.getElementById('recipient_id').value;
        const message = document.getElementById('message').value.trim();
        const subject = document.getElementById('subject').value.trim();
        
        try {
            const response = await fetch('/api/ajax/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    recipient_id: recipientId,
                    message: message,
                    subject: subject
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Clear form
                composeForm.reset();
                selectedRecipient.innerHTML = '<span class="placeholder">Select a recipient...</span>';
                document.querySelectorAll('.recipient-item').forEach(item => item.classList.remove('selected'));
                charCount.textContent = '0';
                localStorage.removeItem('message_draft');
                
                // Show success message
                alert('Message sent successfully!');
                
                // Redirect to sent messages
                window.location.href = '/messages/sent';
            } else {
                alert('Failed to send message: ' + data.message);
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message');
        }
    }
});
</script>

<?php include "../../includes/footer.php"; ?>
