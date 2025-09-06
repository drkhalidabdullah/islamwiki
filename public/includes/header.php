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
                    <a href="/" class="nav-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' || ($_SERVER['REQUEST_URI'] ?? '') == '/') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                    </a>
                    <a href="/wiki" class="nav-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') != 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i>
                    </a>
                    <a href="/dashboard" class="nav-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>
                    </a>
                    <a href="/feed" class="nav-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'feed.php') ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i>
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
                        <a href="/feed" class="action-btn" title="Feed">
                            <i class="fas fa-newspaper"></i>
                        </a>
                        <div class="notifications">
                            <a href="#" class="action-btn" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge">3</span>
                            </a>
                        </div>
                        <div class="user-menu">
                            <a href="#" class="user-profile" id="userProfileBtn">
                                <img src="/assets/images/default-avatar.png" alt="Profile" class="profile-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-menu" id="userDropdown">
                                <a href="/user/<?php echo $current_user['username']; ?>">Profile</a>
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
    console.log("Header JavaScript loaded");
    const userProfileBtn = document.getElementById("userProfileBtn");
    const userDropdown = document.getElementById("userDropdown");
    const userMenu = document.querySelector(".user-menu");
    
    console.log("userProfileBtn:", userProfileBtn);
    console.log("userDropdown:", userDropdown);
    console.log("userMenu:", userMenu);
    
    if (userProfileBtn && userDropdown && userMenu) {
        console.log("Setting up dropdown");
        
        // Toggle dropdown on click
        userProfileBtn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log("Dropdown clicked");
            
            // Toggle the 'show' class instead of style.display
            const isVisible = userDropdown.classList.contains('show');
            if (isVisible) {
                userDropdown.classList.remove('show');
                console.log("Dropdown hidden");
            } else {
                userDropdown.classList.add('show');
                console.log("Dropdown shown");
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener("click", function(e) {
            if (!userMenu.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        
        // Close dropdown when pressing Escape
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                userDropdown.classList.remove('show');
            }
        });
    } else {
        console.log("Dropdown elements not found");
    }
});
</script>
