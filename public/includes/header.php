<?php
// Get user data if logged in
$current_user = null;
if (is_logged_in()) {
    $current_user = get_user($_SESSION['user_id']);
    $user_roles = get_user_roles($_SESSION['user_id']);
}

// Get any flash messages
$message = $_SESSION['flash_message'] ?? null;
if ($message) {
    unset($_SESSION['flash_message']);
}

// Get current page info for navigation highlighting
$current_uri = $_SERVER["REQUEST_URI"] ?? "";
$current_script = basename($_SERVER["PHP_SELF"] ?? "");

// Determine which navigation item should be active
$is_home = ($current_uri == "/" || $current_uri == "");
$is_wiki = (strpos($current_uri, "/wiki") === 0);
$is_friends = ($current_script == "friends.php" || strpos($current_uri, "/friends") === 0);
$is_dashboard = ($current_script == "dashboard.php" || strpos($current_uri, "/dashboard") === 0);
$is_messages = ($current_script == "messages.php" || strpos($current_uri, "/messages") === 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <!-- Left Section: Logo and Search -->
            <div class="header-left">
                <a href="/" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>IslamWiki</span>
                </a>
                <div class="search-container">
                    <form action="/wiki/search" method="GET" class="search-form">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" placeholder="Search IslamWiki" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="search-input">
                    </form>
                </div>
            </div>
            
            <!-- Center Section: Navigation Icons -->
            <div class="header-center">
                <nav class="main-nav">
                    <a href="/" class="nav-item <?php echo $is_home ? "active" : ""; ?>">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="/wiki" class="nav-item <?php echo $is_wiki ? "active" : ""; ?>">
                        <i class="fas fa-book"></i>
                    </a>
                    <a href="/friends" class="nav-item <?php echo $is_friends ? "active" : ""; ?>" <?php if (!is_logged_in()) echo 'style="display: none;"'; ?>>
                        <i class="fas fa-users"></i>
                    </a>
                    <a href="/dashboard" class="nav-item <?php echo $is_dashboard ? "active" : ""; ?>" <?php if (!is_logged_in()) echo 'style="display: none;"'; ?>>
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                </nav>
            </div>
            
            <!-- Right Section: User Actions -->
            <div class="header-right">
                <?php if (is_logged_in()): ?>
                    <div class="user-actions">
                        <a href="/create_post" class="action-btn" title="Create Post">
                            <i class="fas fa-plus"></i>
                        </a>
                        <div class="chats-dropdown">
                            <a href="#" class="action-btn" title="Chats" id="chatsBtn">
                                <i class="fas fa-comments"></i>
                                <span class="chat-badge" id="chatBadge">0</span>
                            </a>
                            <div class="chats-panel" id="chatsPanel">
                                <div class="chats-header">
                                    <h3>Chats</h3>
                                    <div class="chats-actions">
                                        <a href="#" class="chats-options" id="chatsOptions">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="chats-content">
                                    <div class="chats-links">
                                        <a href="/messages" class="chats-link">See all in Messenger</a>
                                        <a href="#" class="chats-link" id="newMessageBtn">New message</a>
                                    </div>
                                    <div class="chats-search">
                                        <input type="text" placeholder="Search Messenger" class="chats-search-input">
                                    </div>
                                    <div class="chats-tabs">
                                        <button class="chat-tab active" data-tab="all">All</button>
                                        <button class="chat-tab" data-tab="unread">Unread</button>
                                        <button class="chat-tab" data-tab="groups">Groups</button>
                                        <button class="chat-tab" data-tab="ummah">Ummah</button>
                                    </div>
                                    <div class="chats-list" id="chatsList">
                                        <!-- Dynamic chat items will be loaded here -->
                                    </div>
                                    <div class="chats-footer">
                                        <a href="/messages" class="chats-link">See all in Messenger</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="notifications">
                            <a href="#" class="action-btn" title="Notifications" id="notificationsBtn">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge" id="notificationBadge">0</span>
                            </a>
                            <div class="notifications-panel" id="notificationsPanel">
                                <div class="notifications-header">
                                    <h3>Notifications</h3>
                                    <div class="notifications-actions">
                                        <a href="#" class="notifications-options" id="notificationsOptions">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="notifications-content">
                                    <div class="notifications-tabs">
                                        <button class="notification-tab active" data-tab="all">All</button>
                                        <button class="notification-tab" data-tab="unread">Unread</button>
                                    </div>
                                    <div class="notifications-list" id="notificationsList">
                                        <!-- Dynamic notifications will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="user-menu">
                            <a href="#" class="user-profile" id="userProfileBtn">
                                <img src="/assets/images/default-avatar.png" alt="Profile" class="profile-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-menu" id="userDropdown">
                                <a href="/user/<?php echo $current_user['username']; ?>">Profile</a>
                                <a href="/settings">Settings</a>
                                <a href="/dashboard">Dashboard</a>
                                <?php if (is_admin()): ?>
                                    <a href="/admin">Admin Panel</a>
                                <?php endif; ?>
                                <hr class="dropdown-divider">
                                <a href="/logout">Logout</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="guest-actions">
                        <a href="/login" class="btn-login">Log In</a>
                        <a href="/register" class="btn-register">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const userProfileBtn = document.getElementById('userProfileBtn');
    const userDropdown = document.getElementById('userDropdown');
    const userMenu = document.querySelector('.user-menu');
    const chatsBtn = document.getElementById('chatsBtn');
    const chatsPanel = document.getElementById('chatsPanel');
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsPanel = document.getElementById('notificationsPanel');
    const newMessageBtn = document.getElementById('newMessageBtn');
    
    // Create new message modal
    const newMessageModal = document.createElement('div');
    newMessageModal.className = 'new-message-modal';
    newMessageModal.innerHTML = `
        <div class="new-message-content">
            <div class="new-message-header">
                <h3>New Message</h3>
                <button class="new-message-close">&times;</button>
            </div>
            <div class="new-message-body">
                <input type="text" placeholder="Search for a person" class="new-message-search">
                <div class="new-message-list">
                    <!-- Message recipients will be loaded here -->
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(newMessageModal);
    
    // User dropdown functionality
    if (userProfileBtn && userDropdown && userMenu) {
        userProfileBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
    }
    
    // Chats dropdown functionality
    if (chatsBtn && chatsPanel) {
        chatsBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            chatsPanel.classList.toggle('show');
        });
    }
    
    // Notifications dropdown functionality
    if (notificationsBtn && notificationsPanel) {
        notificationsBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationsPanel.classList.toggle('show');
        });
    }
    
    // New message functionality
    if (newMessageBtn) {
        newMessageBtn.addEventListener("click", function(e) {
            e.preventDefault();
            newMessageModal.classList.add('show');
        });
    }
    
    // Close new message modal
    const newMessageClose = newMessageModal.querySelector('.new-message-close');
    if (newMessageClose) {
        newMessageClose.addEventListener("click", function() {
            newMessageModal.classList.remove('show');
        });
    }
    
    // Chat tabs functionality
    const chatTabs = document.querySelectorAll('.chat-tab');
    chatTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            chatTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Notification tabs functionality
    const notificationTabs = document.querySelectorAll('.notification-tab');
    notificationTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            notificationTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener("click", function(e) {
        if (!e.target.closest('.chats-dropdown')) {
            chatsPanel.classList.remove('show');
        }
        if (!e.target.closest('.notifications')) {
            notificationsPanel.classList.remove('show');
        }
        if (!e.target.closest('.user-menu')) {
            userDropdown.classList.remove('show');
        }
        if (!e.target.closest('.new-message-modal')) {
            newMessageModal.classList.remove('show');
        }
    });
    
    // Close dropdowns when pressing Escape
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
            chatsPanel.classList.remove('show');
            notificationsPanel.classList.remove('show');
            userDropdown.classList.remove('show');
            newMessageModal.classList.remove('show');
        }
    });
    
    // Load real-time data
    loadNotifications();
    loadMessages();
    
    // Auto-refresh data every 30 seconds
    setInterval(function() {
        loadNotifications();
        loadMessages();
    }, 30000);
    
    // Function to load notifications
    function loadNotifications() {
        fetch("/ajax/get_notifications.php")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(data.unread_count);
                    renderNotifications(data.notifications);
                }
            })
            .catch(error => console.error("Error loading notifications:", error));
    }
    
    // Function to load messages
    function loadMessages() {
        fetch("/ajax/get_messages.php")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateMessageBadge(data.unread_count);
                    renderMessages(data.messages);
                }
            })
            .catch(error => console.error("Error loading messages:", error));
    }
    
    // Function to update notification badge
    function updateNotificationBadge(count) {
        const badge = document.getElementById("notificationBadge");
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? "flex" : "none";
        }
    }
    
    // Function to update message badge
    function updateMessageBadge(count) {
        const badge = document.getElementById("chatBadge");
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? "flex" : "none";
        }
    }
    
    // Function to render notifications
    function renderNotifications(notifications) {
        const container = document.getElementById("notificationsList");
        if (!container) return;
        
        if (notifications.length === 0) {
            container.innerHTML = "<div class=\"no-notifications\">No notifications</div>";
            return;
        }
        
        container.innerHTML = notifications.map(notification => {
            const timeAgo = getTimeAgo(notification.created_at);
            const unreadClass = notification.is_read ? "" : "unread";
            return `
                <div class="notification-item ${unreadClass}" data-id="${notification.id}">
                    <img src="/assets/images/default-avatar.png" alt="User" class="notification-avatar">
                    <div class="notification-info">
                        <div class="notification-text">${notification.title}</div>
                        <div class="notification-time">${timeAgo}</div>
                    </div>
                </div>
            `;
        }).join("");
    }
    
    // Function to render messages
    function renderMessages(messages) {
        const container = document.getElementById("chatsList");
        if (!container) return;
        
        if (messages.length === 0) {
            container.innerHTML = "<div class=\"no-messages\">No recent messages</div>";
            return;
        }
        
        // Group messages by conversation
        const conversations = {};
        messages.forEach(message => {
            const otherUserId = message.sender_id == <?php echo $_SESSION["user_id"] ?? 0; ?> ? message.recipient_id : message.sender_id;
            const otherUsername = message.sender_id == <?php echo $_SESSION["user_id"] ?? 0; ?> ? message.recipient_username : message.sender_username;
            
            if (!conversations[otherUserId]) {
                conversations[otherUserId] = {
                    username: otherUsername,
                    lastMessage: message.content,
                    time: message.created_at,
                    unread: message.recipient_id == <?php echo $_SESSION["user_id"] ?? 0; ?> && !message.is_read
                };
            }
        });
        
        container.innerHTML = Object.values(conversations).map(conv => {
            const timeAgo = getTimeAgo(conv.time);
            return `
                <div class="chat-item">
                    <img src="/assets/images/default-avatar.png" alt="User" class="chat-avatar">
                    <div class="chat-info">
                        <div class="chat-name">${conv.username}</div>
                        <div class="chat-message">${conv.lastMessage}</div>
                    </div>
                    <div class="chat-time">${timeAgo}</div>
                </div>
            `;
        }).join("");
    }
    
    // Function to get time ago
    function getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return "now";
        if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + "m";
        if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + "h";
        return Math.floor(diffInSeconds / 86400) + "d";
    }
});
</script>
