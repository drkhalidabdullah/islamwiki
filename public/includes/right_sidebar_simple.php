<?php
// Right Sidebar - Friends and Messages
if (!is_logged_in() || !$enable_social) return;
?>

<div class="right-sidebar" id="rightSidebar" style="background: #2c3e50 !important; z-index: 10001 !important;">
    <!-- Messages Icon -->
    <?php if (is_logged_in() && $enable_social): ?>
    <div class="right-sidebar-item">
        <button class="sidebar-icon-btn" title="Messages" onclick="toggleMessagesDropdown()">
            <i class="iw iw-comment"></i>
            <span class="notification-badge">3</span>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Notifications Icon -->
    <?php if (is_logged_in() && $enable_notifications): ?>
    <div class="right-sidebar-item">
        <button class="sidebar-icon-btn" title="Notifications" onclick="toggleNotificationsDropdown()">
            <i class="iw iw-bell"></i>
            <span class="notification-badge">5</span>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Friends Profile Pictures -->
    <div class="right-sidebar-section">
        <div class="friends-profiles" id="friendsProfiles">
            <div class="loading-friends">Loading friends...</div>
        </div>
    </div>
</div>

<!-- Messages Dropdown -->
<?php if (is_logged_in() && $enable_social): ?>
<div class="right-sidebar-dropdown" id="messagesDropdown">
    <div class="dropdown-header">
        <h4>Recent Messages</h4>
        <a href="/pages/social/messages.php" class="view-all">View All</a>
    </div>
    <div class="dropdown-content" id="messagesContent">
        <div class="loading-messages">Loading messages...</div>
        <div class="dropdown-footer">
            <a href="/pages/social/messages.php?action=compose" class="dropdown-link">New Message</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Notifications Dropdown -->
<?php if (is_logged_in() && $enable_notifications): ?>
<div class="right-sidebar-dropdown" id="notificationsDropdown">
    <div class="dropdown-header">
        <h4>Notifications</h4>
        <button class="mark-all-read" onclick="markAllNotificationsRead()">Mark All Read</button>
    </div>
    <div class="dropdown-content" id="notificationsContent">
        <div class="loading-notifications">Loading notifications...</div>
        <div class="dropdown-footer">
            <a href="/pages/notifications.php" class="dropdown-link">View All Notifications</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Dropdowns moved to header dashboard -->

<style>
.right-sidebar {
    position: fixed !important;
    top: 60px !important;
    right: 0 !important;
    width: 60px !important;
    height: calc(100vh - 60px) !important;
    background: #2c3e50 !important;
    border-left: 1px solid rgba(255, 255, 255, 0.1) !important;
    z-index: 10001 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    padding: 20px 0 !important;
    box-sizing: border-box !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure right sidebar is visible on desktop by default */
@media (min-width: 769px) {
    .right-sidebar {
        display: flex !important;
    }
}

.right-sidebar-item {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-icon-btn {
    width: 40px;
    height: 40px;
    background: #34495e;
    border: none;
    border-radius: 8px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    font-size: 16px;
}

.sidebar-icon-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.sidebar-icon-btn i {
    font-size: 16px;
}

.notification-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
    display: none;
}

.right-sidebar-section {
    position: relative;
    width: 100%;
    padding: 0 10px;
    margin-top: 10px;
}


.friends-profiles {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}

.friend-profile {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    border: 2px solid transparent;
}

.friend-profile:hover {
    transform: scale(1.1);
    border-color: #3498db;
    box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
}

.friend-profile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.friend-profile .default-avatar {
    width: 100%;
    height: 100%;
    background: #34495e;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ecf0f1;
    font-size: 16px;
}

.loading-friends {
    color: #bdc3c7;
    font-size: 11px;
    text-align: center;
    font-style: italic;
}

.no-friends {
    color: #bdc3c7;
    font-size: 11px;
    text-align: center;
    font-style: italic;
}

/* Dropdown Styles */
.right-sidebar-dropdown {
    position: fixed !important;
    right: 80px !important;
    top: 70px !important;
    width: 300px !important;
    background: #2c3e50 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    box-shadow: -4px 0 12px rgba(0, 0, 0, 0.3) !important;
    z-index: 9999999 !important;
    display: none !important;
    opacity: 0 !important;
    transform: translateX(20px) !important;
    transition: all 0.3s ease !important;
    visibility: hidden !important;
}

.right-sidebar-dropdown.show {
    display: block !important;
    opacity: 1 !important;
    transform: translateX(0) !important;
    visibility: visible !important;
}

.dropdown-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-header h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.view-all {
    color: #666;
    font-size: 12px;
    text-decoration: none;
    transition: color 0.2s;
}

.view-all:hover {
    color: #fff;
}

.mark-all-read {
    background: none;
    border: none;
    color: #666;
    font-size: 12px;
    cursor: pointer;
    transition: color 0.2s;
}

.mark-all-read:hover {
    color: #fff;
}

.dropdown-content {
    max-height: 300px;
    overflow-y: auto;
}

.message-item, .notification-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.message-item:hover, .notification-item:hover {
    background-color: #34495e;
}

.message-avatar {
    position: relative;
    margin-right: 12px;
}

.message-avatar img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.unread-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
}

.message-info, .notification-info {
    flex: 1;
    min-width: 0;
}

.sender-name {
    display: block;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-preview, .notification-text {
    display: block;
    color: #666;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-time, .notification-time {
    display: block;
    color: #999;
    font-size: 11px;
    margin-top: 2px;
}

.notification-item i {
    font-size: 16px;
    color: #666;
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

.dropdown-footer {
    padding: 15px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.dropdown-link {
    color: #6c5ce7;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
}

       .dropdown-link:hover {
           color: #5a4fcf;
       }

       /* Loading and empty states */
       .loading-messages, .loading-notifications {
           padding: 20px;
           text-align: center;
           color: #666;
           font-style: italic;
       }

       .no-messages, .no-notifications {
           padding: 20px;
           text-align: center;
           color: #666;
           font-style: italic;
       }

/* Responsive */
@media (max-width: 768px) {
    .right-sidebar {
        display: none !important;
    }
}
</style>

<script>
// Load friends profiles
async function loadFriendsProfiles() {
    try {
        const response = await fetch('/api/ajax/get_friends_profiles.php');
        const data = await response.json();
        
        const friendsProfiles = document.getElementById('friendsProfiles');
        
        if (data.success) {
            const friends = data.friends;
            
            if (friends.length === 0) {
                friendsProfiles.innerHTML = '<div class="no-friends">No friends yet</div>';
            } else {
                let friendsHtml = '';
                friends.forEach(friend => {
                    friendsHtml += `
                        <div class="friend-profile" title="${friend.display_name}" onclick="window.location.href='/user/${friend.username}'">
                            ${friend.avatar ? 
                                `<img src="${friend.avatar}" alt="${friend.display_name}" onerror="this.parentElement.innerHTML='<div class=&quot;default-avatar&quot;><i class=&quot;iw iw-user&quot;></i></div>'">` :
                                `<div class="default-avatar"><i class="iw iw-user"></i></div>`
                            }
                        </div>
                    `;
                });
                friendsProfiles.innerHTML = friendsHtml;
            }
        } else {
            friendsProfiles.innerHTML = '<div class="no-friends">Failed to load friends</div>';
        }
    } catch (error) {
        console.error('Error loading friends profiles:', error);
        document.getElementById('friendsProfiles').innerHTML = '<div class="no-friends">Error loading friends</div>';
    }
}

// Messages dropdown functionality
function toggleMessagesDropdown() {
    const dropdown = document.getElementById('messagesDropdown');
    const isOpen = dropdown.classList.contains('show');
    
    // Close all right sidebar dropdowns
    document.querySelectorAll('.right-sidebar-dropdown').forEach(d => {
        d.classList.remove('show');
    });
    
    // Toggle current dropdown
    if (!isOpen) {
        loadMessagesData();
        dropdown.classList.add('show');
    }
}

// Notifications dropdown functionality
function toggleNotificationsDropdown() {
    const dropdown = document.getElementById('notificationsDropdown');
    const isOpen = dropdown.classList.contains('show');
    
    // Close all right sidebar dropdowns
    document.querySelectorAll('.right-sidebar-dropdown').forEach(d => {
        d.classList.remove('show');
    });
    
    // Toggle current dropdown
    if (!isOpen) {
        loadNotificationsData();
        dropdown.classList.add('show');
    }
}

// Mark all notifications as read
function markAllNotificationsRead() {
    console.log('Marking all notifications as read');
    // This would typically make an AJAX call to mark all notifications as read
    document.getElementById('notificationsDropdown').classList.remove('show');
}

// Load real messages data
async function loadMessagesData() {
    try {
        const response = await fetch('/api/ajax/get_sidebar_messages.php');
        const data = await response.json();
        
        if (data.success) {
            const messagesContent = document.getElementById('messagesContent');
            const conversations = data.conversations;
            
            if (conversations.length === 0) {
                messagesContent.innerHTML = `
                    <div class="no-messages">No recent messages</div>
                    <div class="dropdown-footer">
                        <a href="/pages/social/messages.php?action=compose" class="dropdown-link">New Message</a>
                    </div>
                `;
            } else {
                let messagesHtml = '';
                conversations.forEach(conv => {
                    messagesHtml += `
                        <div class="message-item" onclick="window.location.href='/pages/social/messages.php?conversation=${conv.id}'">
                            <div class="message-avatar">
                                <img src="${conv.avatar}" alt="${conv.display_name}">
                                ${conv.unread_count > 0 ? `<div class="unread-badge">${conv.unread_count}</div>` : ''}
                            </div>
                            <div class="message-info">
                                <span class="sender-name">${conv.display_name}</span>
                                <span class="message-preview">${conv.last_message}</span>
                                <span class="message-time">${conv.time}</span>
                            </div>
                        </div>
                    `;
                });
                
                messagesContent.innerHTML = messagesHtml + `
                    <div class="dropdown-footer">
                        <a href="/pages/social/messages.php?action=compose" class="dropdown-link">New Message</a>
                    </div>
                `;
            }
            
            // Update badge count
            updateMessagesBadge(data.total_unread);
        } else {
            console.error('Failed to load messages:', data.message);
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Load real notifications data
async function loadNotificationsData() {
    try {
        const response = await fetch('/api/ajax/get_sidebar_notifications.php');
        const data = await response.json();
        
        if (data.success) {
            const notificationsContent = document.getElementById('notificationsContent');
            const notifications = data.notifications;
            
            if (notifications.length === 0) {
                notificationsContent.innerHTML = `
                    <div class="no-notifications">No recent notifications</div>
                    <div class="dropdown-footer">
                        <a href="/pages/notifications.php" class="dropdown-link">View All Notifications</a>
                    </div>
                `;
            } else {
                let notificationsHtml = '';
                notifications.forEach(notif => {
                    notificationsHtml += `
                        <div class="notification-item" onclick="window.location.href='${notif.url}'">
                            <i class="${notif.icon}"></i>
                            <div class="notification-info">
                                <span class="notification-text">${notif.text}</span>
                                <span class="notification-time">${notif.time}</span>
                            </div>
                        </div>
                    `;
                });
                
                notificationsContent.innerHTML = notificationsHtml + `
                    <div class="dropdown-footer">
                        <a href="/pages/notifications.php" class="dropdown-link">View All Notifications</a>
                    </div>
                `;
            }
            
            // Update badge count
            updateNotificationsBadge(data.unread_count);
        } else {
            console.error('Failed to load notifications:', data.message);
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// Update messages badge count
function updateMessagesBadge(count) {
    const badge = document.querySelector('.sidebar-icon-btn[title="Messages"] .notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Update notifications badge count
function updateNotificationsBadge(count) {
    const badge = document.querySelector('.sidebar-icon-btn[title="Notifications"] .notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.right-sidebar-dropdown') && !e.target.closest('.sidebar-icon-btn')) {
        document.querySelectorAll('.right-sidebar-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Load friends on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFriendsProfiles();
    
    // Load initial data for messages and notifications
    if (document.getElementById('messagesDropdown')) {
        loadMessagesData();
    }
    if (document.getElementById('notificationsDropdown')) {
        loadNotificationsData();
    }
});
</script>
