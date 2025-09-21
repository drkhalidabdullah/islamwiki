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

// Check if we're on the search page to conditionally hide header search
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($is_search_page) && $is_search_page): ?>
    <link rel="stylesheet" href="/assets/css/enhanced-search-v2.css">
    <link rel="stylesheet" href="/assets/css/search-results-fix.css">
    <?php endif; ?>
    <?php if (strpos($_SERVER["REQUEST_URI"] ?? "", "/wiki/") === 0 && basename($_SERVER["REQUEST_URI"] ?? "") !== "wiki"): ?>
    <link rel="stylesheet" href="/assets/css/wiki-article-styling.css">
    <?php endif; ?>
    <?php if ($_SERVER["REQUEST_URI"] === "/" || $_SERVER["REQUEST_URI"] === ""): ?>
    <link rel="stylesheet" href="/assets/css/homepage-redesign.css">
    <?php endif; ?>
    <?php if (strpos($_SERVER["REQUEST_URI"] ?? "", "/wiki") === 0 && basename($_SERVER["REQUEST_URI"] ?? "") === "wiki"): ?>
    <link rel="stylesheet" href="/assets/css/wiki-index-redesign.css">
    <?php endif; ?>
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
                <?php if (!$is_search_page): ?>
                <div class="search-container">
                    <form action="/search" method="GET" class="search-form">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="q" placeholder="Search IslamWiki" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="search-input">
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Center Section: Navigation Icons -->
            <div class="header-center">
                <nav class="main-nav">
                    <a href="/" class="nav-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' || ($_SERVER['REQUEST_URI'] ?? '') == '/') ? 'active' : ''; ?>" title="Home">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="/wiki" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/wiki') === 0) ? 'active' : ''; ?>" title="Wiki">
                        <i class="fas fa-book"></i>
                    </a>
                    <?php if (is_logged_in()): ?>
                    <a href="/dashboard" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') === 0) ? 'active' : ''; ?>" title="Dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                    <a href="/friends" class="nav-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/friends') === 0) ? 'active' : ''; ?>" title="Friends">
                        <i class="fas fa-users"></i>
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Right Section: User Actions -->
            <div class="header-right">
                <?php if (is_logged_in()): ?>
                    <div class="user-actions">
                        <a href="/create_post" class="action-btn" title="Create Post">
                            <i class="fas fa-plus"></i>
                        </a>
                        <div class="messages-dropdown">
                            <a href="#" class="action-btn" title="Messages" id="messagesBtn">
                                <i class="fas fa-comments"></i>
                                <span class="chat-badge" id="chatBadge" style="display: none;">0</span>
                            </a>
                            <div class="dropdown-menu" id="messagesDropdown">
                                <div class="dropdown-header">
                                    <h4>Messages</h4>
                                    <a href="/messages">See All</a>
                                </div>
                                <div class="messages-list" id="messagesList">
                                    <div class="no-messages">No new messages</div>
                                </div>
                            </div>
                        </div>
                        <div class="notifications">
                            <div class="notifications-dropdown">
                                <a href="#" class="action-btn" title="Notifications" id="notificationsBtn">
                                    <i class="fas fa-bell"></i>
                                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                                </a>
                                <div class="dropdown-menu" id="notificationsDropdown">
                                    <div class="dropdown-header">
                                        <h4>Notifications</h4>
                                        <a href="#" id="markAllRead">Mark All Read</a>
                                    </div>
                                    <div class="notifications-list" id="notificationsList">
                                        <div class="no-notifications">No new notifications</div>
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
                                <a href="/dashboard">Dashboard</a>
                                <a href="/settings">Settings</a>
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
            <?php echo htmlspecialchars($message['message']); ?>
        </div>
        <?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get all dropdown elements
    const userProfileBtn = document.getElementById('userProfileBtn');
    const userDropdown = document.getElementById('userDropdown');
    const messagesBtn = document.getElementById('messagesBtn');
    const messagesDropdown = document.getElementById('messagesDropdown');
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    
    // User dropdown functionality
    if (userProfileBtn && userDropdown) {
        userProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            userDropdown.classList.toggle('show');
            // Close other dropdowns
            messagesDropdown.classList.remove('show');
            notificationsDropdown.classList.remove('show');
        });
    }
    
    // Messages dropdown functionality
    if (messagesBtn && messagesDropdown) {
        messagesBtn.addEventListener('click', function(e) {
            e.preventDefault();
            messagesDropdown.classList.toggle('show');
            // Close other dropdowns
            userDropdown.classList.remove('show');
            notificationsDropdown.classList.remove('show');
            // Load messages
            loadMessages();
        });
    }
    
    // Notifications dropdown functionality
    if (notificationsBtn && notificationsDropdown) {
        notificationsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            notificationsDropdown.classList.toggle('show');
            // Close other dropdowns
            userDropdown.classList.remove('show');
            messagesDropdown.classList.remove('show');
            // Load notifications
            loadNotifications();
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!userProfileBtn.contains(e.target) && !userDropdown.contains(e.target) && 
            !messagesBtn.contains(e.target) && !messagesDropdown.contains(e.target) && 
            !notificationsBtn.contains(e.target) && !notificationsDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
            messagesDropdown.classList.remove('show');
            notificationsDropdown.classList.remove('show');
        }
    });
    
    // Load initial notification and message counts
    loadNotificationCount();
    loadMessageCount();
    
    // Auto-refresh counts every 30 seconds
    setInterval(function() {
        loadNotificationCount();
        loadMessageCount();
    }, 30000);
});

function loadMessages() {
    fetch('/api/ajax/get_messages.php')
        .then(response => response.json())
        .then(data => {
            const messagesList = document.getElementById('messagesList');
            if (data.messages && data.messages.length > 0) {
                messagesList.innerHTML = data.messages.map(msg => `
                    <div class="message-item">
                        <div class="message-avatar">
                            <img src="/assets/images/default-avatar.png" alt="${msg.display_name || msg.username}">
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender">${msg.display_name || msg.username}</span>
                                <span class="message-time">${new Date(msg.created_at).toLocaleTimeString()}</span>
                            </div>
                            <div class="message-preview">${msg.message.substring(0, 50)}${msg.message.length > 50 ? '...' : ''}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                messagesList.innerHTML = '<div class="no-messages">No new messages</div>';
            }
        })
        .catch(error => console.log('Error loading messages:', error));
}

function loadNotifications() {
    fetch('/api/ajax/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationsList = document.getElementById('notificationsList');
            if (data.notifications && data.notifications.length > 0) {
                notificationsList.innerHTML = data.notifications.map(notif => `
                    <div class="notification-item">
                        <div class="notification-content">
                            <div class="notification-text">${notif.message}</div>
                            <div class="notification-time">${new Date(notif.created_at).toLocaleTimeString()}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                notificationsList.innerHTML = '<div class="no-notifications">No new notifications</div>';
            }
        })
        .catch(error => console.log('Error loading notifications:', error));
}

function loadNotificationCount() {
    fetch('/api/ajax/get_notifications.php?count_only=1')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.log('Error loading notification count:', error));
}

function loadMessageCount() {
    fetch('/api/ajax/get_messages.php?count_only=1')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('chatBadge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.log('Error loading message count:', error));
}
</script>
