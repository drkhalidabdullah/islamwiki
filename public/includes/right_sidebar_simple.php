<?php
// Right Sidebar - Friends and Messages
if (!is_logged_in() || !$enable_social) return;
?>

<div class="right-sidebar" id="rightSidebar" style="background: #2c3e50 !important; z-index: 10001 !important;">
    <!-- Friends Icon -->
    <div class="right-sidebar-section">
        <div class="right-sidebar-item" title="Friends" onclick="window.location.href='/pages/social/friends.php'">
            <i class="iw iw-users"></i>
            <span class="notification-badge">4</span>
        </div>
    </div>
    
    <!-- Messages Icon -->
    <div class="right-sidebar-section">
        <div class="right-sidebar-item" title="Messages" onclick="toggleMessagesDropdown(); console.log('Messages icon clicked');">
            <i class="iw iw-comment"></i>
            <span class="notification-badge">3</span>
        </div>
        
    </div>
    
    <!-- Notifications Icon -->
    <div class="right-sidebar-section">
        <div class="right-sidebar-item" title="Notifications" onclick="toggleNotificationsDropdown()">
            <i class="iw iw-bell"></i>
            <span class="notification-badge">5</span>
        </div>
        
    </div>
</div>

<!-- Dropdowns outside sidebar container -->
<div class="right-sidebar-dropdown" id="messagesDropdown" style="display: none; background: #2c3e50; border: 1px solid rgba(255, 255, 255, 0.1); position: fixed; right: 80px; top: 70px; z-index: 9999999; width: 300px; visibility: hidden; opacity: 0;">
    <div class="dropdown-header">
        <h4>Recent Messages</h4>
        <a href="/pages/social/messages.php" class="view-all">View All</a>
    </div>
    <div class="dropdown-content">
        <div class="message-item" onclick="window.location.href='/pages/social/messages.php?conversation=1'">
            <div class="message-avatar">
                <img src="/assets/images/default-avatar.svg" alt="Sender">
                <div class="unread-badge">2</div>
            </div>
            <div class="message-info">
                <span class="sender-name">John Doe</span>
                <span class="message-preview">Hey, how are you?</span>
                <span class="message-time">2m ago</span>
            </div>
        </div>
        <div class="message-item" onclick="window.location.href='/pages/social/messages.php?conversation=2'">
            <div class="message-avatar">
                <img src="/assets/images/default-avatar.svg" alt="Sender">
            </div>
            <div class="message-info">
                <span class="sender-name">Jane Smith</span>
                <span class="message-preview">Thanks for the help!</span>
                <span class="message-time">1h ago</span>
            </div>
        </div>
        <div class="dropdown-footer">
            <a href="/pages/social/messages.php?action=compose" class="dropdown-link">New Message</a>
        </div>
    </div>
</div>

<div class="right-sidebar-dropdown" id="notificationsDropdown" style="display: none; background: #2c3e50; border: 1px solid rgba(255, 255, 255, 0.1); position: fixed; right: 80px; top: 130px; z-index: 9999999; width: 300px; visibility: hidden; opacity: 0;">
    <div class="dropdown-header">
        <h4>Notifications</h4>
        <button class="mark-all-read" onclick="markAllNotificationsRead()">Mark All Read</button>
    </div>
    <div class="dropdown-content">
        <div class="notification-item" onclick="window.location.href='/pages/notifications.php'">
            <i class="iw iw-heart"></i>
            <div class="notification-info">
                <span class="notification-text">John liked your post</span>
                <span class="notification-time">5m ago</span>
            </div>
        </div>
        <div class="notification-item" onclick="window.location.href='/pages/notifications.php'">
            <i class="iw iw-comment"></i>
            <div class="notification-info">
                <span class="notification-text">Jane commented on your article</span>
                <span class="notification-time">1h ago</span>
            </div>
        </div>
        <div class="dropdown-footer">
            <a href="/pages/notifications.php" class="dropdown-link">View All Notifications</a>
        </div>
    </div>
</div>

<style>
.right-sidebar {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    width: 60px !important;
    height: 100vh !important;
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

.right-sidebar-section {
    position: relative;
    margin-bottom: 15px;
}

.right-sidebar-item {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #34495e;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.right-sidebar-item:hover {
    background: #3498db;
    transform: scale(1.05);
}

.right-sidebar-item i {
    font-size: 18px;
    color: #fff;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
    line-height: 1.2;
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
    background: red !important;
    border: 2px solid yellow !important;
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

/* Responsive */
@media (max-width: 768px) {
    .right-sidebar {
        display: none !important;
    }
}
</style>

<script>
function toggleMessagesDropdown() {
    console.log('toggleMessagesDropdown called');
    const dropdown = document.getElementById('messagesDropdown');
    console.log('Messages dropdown element:', dropdown);
    
    if (!dropdown) {
        console.error('Messages dropdown not found!');
        return;
    }
    
    const isOpen = dropdown.style.display === 'block';
    console.log('Is open:', isOpen);
    console.log('Current display style:', dropdown.style.display);
    
    // Close all dropdowns
    document.querySelectorAll('.right-sidebar-dropdown').forEach(d => {
        d.style.setProperty('display', 'none', 'important');
        d.style.setProperty('visibility', 'hidden', 'important');
        d.style.setProperty('opacity', '0', 'important');
    });
    
    // Toggle current dropdown
    if (!isOpen) {
        // Position dropdown next to the messages button
        dropdown.style.setProperty('right', '80px', 'important');
        dropdown.style.setProperty('top', '70px', 'important');
        dropdown.style.setProperty('display', 'block', 'important');
        dropdown.style.setProperty('visibility', 'visible', 'important');
        dropdown.style.setProperty('opacity', '1', 'important');
        console.log('Showing messages dropdown');
        console.log('New display style:', dropdown.style.display);
    }
}

function toggleNotificationsDropdown() {
    console.log('toggleNotificationsDropdown called');
    const dropdown = document.getElementById('notificationsDropdown');
    const isOpen = dropdown.style.display === 'block';
    console.log('Is open:', isOpen);
    
    // Close all dropdowns
    document.querySelectorAll('.right-sidebar-dropdown').forEach(d => {
        d.style.setProperty('display', 'none', 'important');
        d.style.setProperty('visibility', 'hidden', 'important');
        d.style.setProperty('opacity', '0', 'important');
    });
    
    // Toggle current dropdown
    if (!isOpen) {
        // Position dropdown next to the notifications button
        dropdown.style.setProperty('right', '80px', 'important');
        dropdown.style.setProperty('top', '130px', 'important');
        dropdown.style.setProperty('display', 'block', 'important');
        dropdown.style.setProperty('visibility', 'visible', 'important');
        dropdown.style.setProperty('opacity', '1', 'important');
        console.log('Showing notifications dropdown');
    }
}

function markAllNotificationsRead() {
    // This would typically make an AJAX call to mark all notifications as read
    console.log('Marking all notifications as read');
    // For now, just close the dropdown
    document.getElementById('notificationsDropdown').classList.remove('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.right-sidebar')) {
        document.querySelectorAll('.right-sidebar-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});
</script>
