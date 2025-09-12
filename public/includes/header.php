<?php
// Get user data if logged in
$current_user = null;
if (is_logged_in()) {
    $current_user = get_user($_SESSION['user_id']);
    $user_roles = get_user_roles($_SESSION['user_id']);
}

// Get feature settings
$enable_wiki = get_system_setting('enable_wiki', true);
$enable_social = get_system_setting('enable_social', true);
$enable_comments = get_system_setting('enable_comments', true);
$enable_notifications = get_system_setting('enable_notifications', true);

// Get any toast messages
$toast_message = $_SESSION['toast_message'] ?? null;
$toast_type = $_SESSION['toast_type'] ?? 'info';
if ($toast_message) {
    unset($_SESSION['toast_message'], $_SESSION['toast_type']);
}

// Check if we're on the search page to conditionally hide header search
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . get_site_name() : get_site_name(); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Override body padding to ensure newsbar touches top */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Reset any potential spacing from parent elements */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Add top padding to main content to account for fixed newsbar and maintenance banner */
    .main-content {
        padding-top: 60px !important;
        margin-top: 0 !important;
    }
    
    /* When sidebar and newsbar are hidden during maintenance mode */
    .main-content.maintenance-mode {
        padding-top: 0 !important;
        margin-left: 0 !important;
    }
    
    /* Maintenance Banner Styles */
    .maintenance-banner {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 0.75rem 0;
        margin: 0;
        border-radius: 0;
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        position: fixed;
        top: 0;
        left: 60px;
        z-index: 10000;
        width: calc(100vw - 60px);
        max-width: calc(100vw - 60px);
        box-sizing: border-box;
        border-bottom: 2px solid #a93226;
    }
    
    .maintenance-banner-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        position: relative;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .maintenance-banner-left {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    
    .maintenance-banner-center {
        flex: 1;
        margin: 0 1rem;
        min-width: 0;
        overflow: hidden;
        text-align: center;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .maintenance-banner-right {
        flex-shrink: 0;
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .maintenance-banner-link {
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .maintenance-banner-link:hover {
        background: rgba(255,255,255,0.3);
        transform: translateY(-1px);
        color: white;
        text-decoration: none;
    }
    
    .maintenance-banner-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 0.5rem;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .maintenance-banner-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }
    
    /* Adjust newsbar position when maintenance banner is present */
    .maintenance-banner + .newsbar {
        top: 60px;
    }
    
    .maintenance-banner + .newsbar + * .main-content {
        padding-top: 120px !important;
    }
    
    /* Newsbar Styles - positioned at absolute top, accounting for sidebar */
    .newsbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.75rem 0;
        margin: 0;
        border-radius: 0;
        overflow: hidden;
        position: fixed;
        top: 0;
        left: 60px;
        z-index: 10000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: calc(100vw - 60px);
        max-width: calc(100vw - 60px);
        box-sizing: border-box;
        margin-top: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .newsbar-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        width: 100%;
        padding: 0 1rem;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
        min-width: 0;
    }

    .newsbar-left {
        flex-shrink: 0;
    }

    .newsbar-center {
        flex: 1;
        margin: 0 1rem;
        min-width: 0;
        overflow: hidden;
    }

    .newsbar-right {
        flex-shrink: 0;
        min-width: 0;
    }

    .newsbar-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .newsbar-ticker {
        overflow: hidden;
        position: relative;
        height: 2rem;
        max-width: 100%;
        flex: 1;
        min-width: 0;
    }

    .newsbar-items {
        display: flex;
        animation: newsbar-scroll 30s linear infinite;
        gap: 3rem;
        align-items: center;
        height: 100%;
        width: 200%;
        overflow: hidden;
    }

    .newsbar-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        white-space: nowrap;
        flex-shrink: 0;
        padding: 0 1rem;
    }

    .newsbar-time {
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
        flex-shrink: 0;
    }

    .newsbar-text {
        font-size: 0.9rem;
        font-weight: 400;
        line-height: 1.4;
    }

    .newsbar-controls {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
        min-width: 0;
        white-space: nowrap;
    }

    .newsbar-pause,
    .newsbar-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 2rem;
        height: 2rem;
        min-width: 2rem;
        min-height: 2rem;
        max-width: 2rem;
        max-height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .newsbar-pause:hover,
    .newsbar-close:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.1);
    }

    .newsbar.paused .newsbar-items {
        animation-play-state: paused;
    }

    .newsbar.hidden {
        background: none;
        box-shadow: none;
    }
    
    .newsbar.hidden .newsbar-content {
        transform: translateY(-100%);
        opacity: 0;
        pointer-events: none;
    }
    
    .newsbar-floating-controls {
        display: none;
        position: fixed;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 10001;
        background: none;
        padding: 0;
        border-radius: 0;
        box-shadow: none;
    }
    
    .newsbar.hidden .newsbar-floating-controls {
        display: block;
    }
    
    .newsbar-floating-controls .newsbar-close {
        background: rgba(0,0,0,0.7);
        border: 2px solid rgba(255,255,255,0.8);
        color: white;
        padding: 0.5rem;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 2.5rem;
        height: 2.5rem;
        min-width: 2.5rem;
        min-height: 2.5rem;
        max-width: 2.5rem;
        max-height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    .newsbar-floating-controls .newsbar-close:hover {
        background: rgba(0,0,0,0.9);
        border-color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }

    @keyframes newsbar-scroll {
        0% {
            transform: translateX(100%);
        }
        100% {
            transform: translateX(-100%);
        }
    }

    /* Responsive Maintenance Banner */
    @media (max-width: 992px) {
        .maintenance-banner {
            left: 50px;
            width: calc(100vw - 50px);
            max-width: calc(100vw - 50px);
        }
        
        .maintenance-banner + .newsbar {
            left: 50px;
            width: calc(100vw - 50px);
            max-width: calc(100vw - 50px);
        }
    }
    
    @media (max-width: 768px) {
        .maintenance-banner {
            left: 0;
            width: 100vw;
            max-width: 100vw;
        }
        
        .maintenance-banner-content {
            flex-direction: column;
            gap: 0.5rem;
            align-items: stretch;
            padding: 0.5rem;
        }
        
        .maintenance-banner-left,
        .maintenance-banner-center,
        .maintenance-banner-right {
            flex: none;
            margin: 0;
            justify-content: center;
        }
        
        .maintenance-banner-left {
            order: 1;
            text-align: center;
        }
        
        .maintenance-banner-center {
            order: 2;
            font-size: 0.8rem;
        }
        
        .maintenance-banner-right {
            order: 3;
            text-align: center;
        }
        
        .maintenance-banner + .newsbar {
            left: 0;
            width: 100vw;
            max-width: 100vw;
        }
    }

    /* Responsive Newsbar */
    @media (max-width: 992px) {
        .newsbar {
            left: 50px;
            width: calc(100vw - 50px);
            max-width: calc(100vw - 50px);
        }
    }
    
    @media (max-width: 768px) {
        .newsbar {
            left: 0;
            width: 100vw;
            max-width: 100vw;
        }
        
        .newsbar-floating-controls {
            right: 0;
            left: auto;
            border-radius: 0 0 0 8px;
        }
        
        .newsbar-content {
            flex-direction: column;
            gap: 0.5rem;
            align-items: stretch;
        }
        
        .newsbar-left,
        .newsbar-center,
        .newsbar-right {
            flex: none;
            margin: 0;
        }
        
        .newsbar-left {
            order: 1;
            text-align: center;
        }
        
        .newsbar-center {
            order: 2;
        }
        
        .newsbar-right {
            order: 3;
            text-align: center;
        }
        
        .newsbar-label {
            justify-content: center;
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
        
        .newsbar-ticker {
            height: 1.5rem;
        }
        
        .newsbar-item {
            gap: 0.5rem;
            padding: 0 0.5rem;
        }
        
        .newsbar-time {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        
        .newsbar-text {
            font-size: 0.8rem;
        }
        
        .newsbar-controls {
            justify-content: center;
        }
        
        .newsbar-pause,
        .newsbar-close {
            width: 1.8rem;
            height: 1.8rem;
            padding: 0.4rem;
        }
    }

    @media (max-width: 480px) {
        .newsbar {
            padding: 0.5rem 0;
        }
        
        .newsbar-items {
            animation-duration: 20s;
        }
        
        .newsbar-item {
            flex-direction: column;
            gap: 0.25rem;
            text-align: center;
        }
        
        .main-content {
            padding-top: 50px !important;
        }
    }
    </style>
    <?php if (isset($is_search_page) && $is_search_page): ?>
    <link rel="stylesheet" href="/assets/css/search.css">
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
    <?php if (!is_maintenance_mode() || is_logged_in()): ?>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <!-- Left Sidebar Navigation -->
    <nav class="sidebar">
        <!-- Logo at top -->
        <a href="/" class="sidebar-item" title="<?php echo get_site_name(); ?> Home">
            <i class="fas fa-book-open"></i>
        </a>
        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <!-- Main Navigation -->
        <div class="search-container">
            <a href="#" class="sidebar-item search-trigger <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/search') === 0) ? 'active' : ''; ?>" title="Search" onclick="toggleSearchPopup(event)">
                <i class="fas fa-search"></i>
            </a>
            
            <!-- Search Popup -->
            <div class="search-popup" id="searchPopup">
                <div class="search-popup-content">
                    <div class="search-popup-header">
                        <h3>Search <?php echo get_site_name(); ?></h3>
                        <button class="search-popup-close" onclick="closeSearchPopup()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form class="search-popup-form" onsubmit="performSearch(event)">
                        <div class="search-input-group">
                            <input type="text" id="searchInput" name="q" placeholder="Search articles, users, content..." autocomplete="off" required>
                            <button type="submit" class="search-submit-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="search-suggestions" id="searchSuggestions">
                            <!-- Suggestions will be loaded here -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <a href="/" class="sidebar-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' || ($_SERVER['REQUEST_URI'] ?? '') == '/') ? 'active' : ''; ?>" title="Home">
            <i class="fas fa-home"></i>
        </a>
        
        <?php if ($enable_wiki): ?>
        <a href="/wiki" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/wiki') === 0) ? 'active' : ''; ?>" title="Wiki">
            <i class="fas fa-book"></i>
        </a>
        <?php endif; ?>
        
        <?php if (is_logged_in()): ?>
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <!-- Create Dropdown -->
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item dropdown-trigger" title="Create" data-target="createMenu">
                <i class="fas fa-plus"></i>
            </a>
            <div class="dropdown-menu" id="createMenu">
                <a href="/create_post" class="dropdown-item">
                    <i class="fas fa-edit"></i>
                    <span>Create Post</span>
                </a>
                <?php if ($enable_wiki): ?>
                <a href="/pages/wiki/create_article.php" class="dropdown-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Create Article</span>
                </a>
                <a href="/wiki/upload" class="dropdown-item">
                    <i class="fas fa-upload"></i>
                    <span>Upload File</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
        <?php if ($enable_social): ?>
        <!-- Messages Dropdown -->
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item dropdown-trigger" title="Messages" data-target="messagesMenu">
                <i class="fas fa-comments"></i>
            </a>
            <div class="dropdown-menu" id="messagesMenu">
                <a href="/messages" class="dropdown-item">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                </a>
            </div>
        </div>
        
        <a href="/friends" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/friends') === 0) ? 'active' : ''; ?>" title="Friends">
            <i class="fas fa-users"></i>
        </a>
        <?php endif; ?>
        
        <a href="/pages/user/watchlist.php" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/watchlist') !== false) ? 'active' : ''; ?>" title="My Watchlist">
            <i class="fas fa-eye"></i>
        </a>
        
        <a href="/dashboard" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') === 0) ? 'active' : ''; ?>" title="Dashboard">
            <i class="fas fa-tachometer-alt"></i>
        </a>
        
        <a href="/settings" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/settings') === 0) ? 'active' : ''; ?>" title="Settings">
            <i class="fas fa-cog"></i>
        </a>
        
        <?php if (is_admin()): ?>
        <a href="/admin" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0) ? 'active' : ''; ?>" title="Admin Panel">
            <i class="fas fa-shield-alt"></i>
        </a>
        <?php endif; ?>
        
        <!-- User section at bottom -->
        <div class="sidebar-user">
            <a href="/user/<?php echo $current_user['username']; ?>" class="sidebar-item" title="Profile">
                <img src="/assets/images/default-avatar.png" alt="Profile" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
            </a>
            <a href="/logout" class="sidebar-item" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        
        <?php else: ?>
        <!-- Guest navigation -->
        <div class="sidebar-user">
            <a href="/login" class="sidebar-item" title="Login">
                <i class="fas fa-sign-in-alt"></i>
            </a>
            <a href="/register" class="sidebar-item" title="Register">
                <i class="fas fa-user-plus"></i>
            </a>
        </div>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    
    <!-- Maintenance Mode Banner (for admins) -->
    <?php if (should_show_maintenance_banner()): ?>
    <div class="maintenance-banner">
        <div class="maintenance-banner-content">
            <div class="maintenance-banner-left">
                <i class="fas fa-tools"></i>
                <span>Maintenance Mode Active</span>
            </div>
            <div class="maintenance-banner-center">
                <span>Site is in maintenance mode. Regular users are redirected to maintenance page.</span>
            </div>
            <div class="maintenance-banner-right">
                <a href="/admin/system_settings" class="maintenance-banner-link">
                    <i class="fas fa-cog"></i>
                    Manage
                </a>
                <button class="maintenance-banner-close" onclick="closeMaintenanceBanner()" title="Hide Banner">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!is_maintenance_mode() || is_logged_in()): ?>
    <!-- Newsbar -->
    <div class="newsbar">
        <div class="newsbar-content">
            <div class="newsbar-left">
                <div class="newsbar-label">
                    <i class="fas fa-bullhorn"></i>
                    <span>Latest News</span>
                </div>
            </div>
            <div class="newsbar-center">
                <div class="newsbar-ticker">
                    <div class="newsbar-items">
                        <div class="newsbar-item">
                            <span class="newsbar-time">2 hours ago</span>
                            <span class="newsbar-text">New Islamic Wiki feature: Enhanced search with AI-powered suggestions</span>
                        </div>
                        <div class="newsbar-item">
                            <span class="newsbar-time">5 hours ago</span>
                            <span class="newsbar-text">Community milestone: 1,000+ articles published on Islamic topics</span>
                        </div>
                        <div class="newsbar-item">
                            <span class="newsbar-time">1 day ago</span>
                            <span class="newsbar-text">Ramadan 2024: Special collection of fasting and prayer articles now available</span>
                        </div>
                        <div class="newsbar-item">
                            <span class="newsbar-time">2 days ago</span>
                            <span class="newsbar-text">New editor tools: Improved article creation and editing experience</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="newsbar-right">
                <div class="newsbar-controls">
                    <button class="newsbar-pause" onclick="toggleNewsbar()" title="Pause/Resume">
                        <i class="fas fa-pause"></i>
                    </button>
                    <button class="newsbar-close" onclick="closeNewsbar()" title="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Floating controls when hidden -->
        <div class="newsbar-floating-controls">
            <button class="newsbar-close" onclick="closeNewsbar()" title="Show Newsbar">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content<?php echo (is_maintenance_mode() && !is_logged_in()) ? ' maintenance-mode' : ''; ?>">

<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("Dropdown script loaded");
    
    // Get all dropdown triggers
    const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');
    
    console.log("Found dropdown triggers:", dropdownTriggers.length);
    console.log("Found dropdown menus:", dropdownMenus.length);
    
    // Close all dropdowns
    function closeAllDropdowns() {
        dropdownMenus.forEach(menu => {
            menu.classList.remove('show');
        });
    }
    
    // Add click event to each trigger
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const targetId = this.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            
            console.log("Clicked trigger:", this.title, "Target:", targetId);
            
            if (targetMenu) {
                // Close all other dropdowns first
                closeAllDropdowns();
                
                // Toggle this dropdown
                targetMenu.classList.toggle('show');
                console.log("Menu show state:", targetMenu.classList.contains('show'));
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sidebar-dropdown')) {
            closeAllDropdowns();
        }
    });
    
    // Close dropdowns when clicking on dropdown items
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            closeAllDropdowns();
        });
    });
    
    // Mobile sidebar functionality
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }
    
    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    }
    
    // Close sidebar when clicking on sidebar items (mobile)
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
    
    // Close sidebar on window resize if desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
    
    // Make functions globally available
    window.toggleSidebar = toggleSidebar;
    window.closeSidebar = closeSidebar;
    
    // Search popup functionality
    let searchTimeout;
    
    function toggleSearchPopup(event) {
        event.preventDefault();
        const popup = document.getElementById('searchPopup');
        popup.classList.toggle('show');
        
        if (popup.classList.contains('show')) {
            // Keep sidebars visible but ensure search popup appears above them (like citation modal)
            
            // Add ESC key listener to close popup
            const escKeyHandler = function(e) {
                if (e.key === 'Escape') {
                    closeSearchPopup();
                    document.removeEventListener('keydown', escKeyHandler);
                }
            };
            document.addEventListener('keydown', escKeyHandler);
            
            // Focus on search input when popup opens
            setTimeout(() => {
                document.getElementById('searchInput').focus();
            }, 100);
        }
    }
    
    function closeSearchPopup() {
        const popup = document.getElementById('searchPopup');
        popup.classList.remove('show');
        document.getElementById('searchInput').value = '';
        document.getElementById('searchSuggestions').innerHTML = '';
    }
    
    function performSearch(event) {
        event.preventDefault();
        const query = document.getElementById('searchInput').value.trim();
        
        if (query) {
            // Redirect to search page with query
            window.location.href = `/search?q=${encodeURIComponent(query)}`;
        }
    }
    
    // Add / key to open search popup
    document.addEventListener('keydown', function(e) {
        // Only trigger if not typing in an input field
        if (e.key === '/' && !e.target.matches('input, textarea, [contenteditable]')) {
            e.preventDefault();
            const searchTrigger = document.querySelector('.search-trigger');
            if (searchTrigger) {
                searchTrigger.click();
            }
        }
    });
    
    // Load search suggestions as user types
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const suggestionsContainer = document.getElementById('searchSuggestions');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                if (query.length >= 2) {
                    // Debounce search suggestions
                    searchTimeout = setTimeout(() => {
                        loadSearchSuggestions(query);
                    }, 300);
                } else {
                    suggestionsContainer.innerHTML = '';
                }
            });
            
            // Handle suggestion clicks
            suggestionsContainer.addEventListener('click', function(e) {
                const suggestionItem = e.target.closest('.search-suggestion-item');
                if (suggestionItem) {
                    const query = suggestionItem.dataset.query;
                    if (query) {
                        document.getElementById('searchInput').value = query;
                        performSearch(e);
                    }
                }
            });
        }
    });
    
    function loadSearchSuggestions(query) {
        const suggestionsContainer = document.getElementById('searchSuggestions');
        
        // Show loading state
        suggestionsContainer.innerHTML = `
            <div class="search-loading">
                <i class="fas fa-spinner"></i> Loading suggestions...
            </div>
        `;
        
        // Fetch suggestions via AJAX
        fetch(`/api/ajax/search_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displaySuggestions(data);
            })
            .catch(error => {
                console.error('Error loading suggestions:', error);
                suggestionsContainer.innerHTML = `
                    <div class="no-suggestions">
                        <i class="fas fa-search"></i><br>
                        No suggestions available
                    </div>
                `;
            });
    }
    
    function displaySuggestions(suggestions) {
        const suggestionsContainer = document.getElementById('searchSuggestions');
        
        if (!suggestions || suggestions.length === 0) {
            suggestionsContainer.innerHTML = `
                <div class="no-suggestions">
                    <i class="fas fa-search"></i><br>
                    No suggestions found
                </div>
            `;
            return;
        }
        
        let html = '';
        suggestions.forEach(suggestion => {
            const icon = getSuggestionIcon(suggestion.suggestion_type || suggestion.content_type);
            html += `
                <div class="search-suggestion-item" data-query="${suggestion.suggestion}">
                    <i class="${icon}"></i>
                    <div class="suggestion-text">
                        <div class="suggestion-title">${suggestion.suggestion}</div>
                        <div class="suggestion-meta">${suggestion.suggestion_type || suggestion.content_type || 'Search term'}</div>
                    </div>
                    <div class="suggestion-count">${suggestion.search_count || 0} searches</div>
                </div>
            `;
        });
        
        suggestionsContainer.innerHTML = html;
    }
    
    function getSuggestionIcon(type) {
        switch (type) {
            case 'article':
                return 'fas fa-book';
            case 'user':
                return 'fas fa-user';
            case 'category':
                return 'fas fa-folder';
            case 'trending':
                return 'fas fa-fire';
            default:
                return 'fas fa-search';
        }
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        const popup = document.getElementById('searchPopup');
        const searchContainer = document.querySelector('.search-container');
        
        if (popup && popup.classList.contains('show') && !searchContainer.contains(e.target)) {
            closeSearchPopup();
        }
    });
    
    // Close popup with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSearchPopup();
        }
    });
    
    // Make functions globally available
    window.toggleSearchPopup = toggleSearchPopup;
    window.closeSearchPopup = closeSearchPopup;
    window.performSearch = performSearch;
    
    // Newsbar functionality
    function toggleNewsbar() {
        const newsbar = document.querySelector('.newsbar');
        const pauseBtn = document.querySelector('.newsbar-pause i');
        
        newsbar.classList.toggle('paused');
        
        if (newsbar.classList.contains('paused')) {
            pauseBtn.className = 'fas fa-play';
        } else {
            pauseBtn.className = 'fas fa-pause';
        }
    }

    function closeNewsbar() {
        const newsbar = document.querySelector('.newsbar');
        const closeBtn = document.querySelector('.newsbar-controls .newsbar-close i');
        const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-close i');
        
        newsbar.classList.toggle('hidden');
        
        // Update button icon and title
        if (newsbar.classList.contains('hidden')) {
            closeBtn.className = 'fas fa-eye';
            closeBtn.parentElement.title = 'Show Newsbar';
            floatingCloseBtn.className = 'fas fa-eye';
            floatingCloseBtn.parentElement.title = 'Show Newsbar';
            localStorage.setItem('newsbar-hidden', 'true');
        } else {
            closeBtn.className = 'fas fa-times';
            closeBtn.parentElement.title = 'Hide Newsbar';
            floatingCloseBtn.className = 'fas fa-eye';
            floatingCloseBtn.parentElement.title = 'Show Newsbar';
            localStorage.setItem('newsbar-hidden', 'false');
        }
    }

    // Check if newsbar should be hidden on page load
    const newsbarHidden = localStorage.getItem('newsbar-hidden');
    if (newsbarHidden === 'true') {
        const newsbar = document.querySelector('.newsbar');
        const closeBtn = document.querySelector('.newsbar-controls .newsbar-close i');
        const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-close i');
        if (newsbar) {
            newsbar.classList.add('hidden');
            // Update button icon and title
            closeBtn.className = 'fas fa-eye';
            closeBtn.parentElement.title = 'Show Newsbar';
            floatingCloseBtn.className = 'fas fa-eye';
            floatingCloseBtn.parentElement.title = 'Show Newsbar';
        }
    }
    
    // Maintenance banner functionality
    function closeMaintenanceBanner() {
        const banner = document.querySelector('.maintenance-banner');
        if (banner) {
            banner.style.display = 'none';
            // Adjust newsbar position
            const newsbar = document.querySelector('.newsbar');
            if (newsbar) {
                newsbar.style.top = '0';
            }
            // Adjust main content padding
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.style.paddingTop = '60px';
            }
        }
    }
    
    // Make newsbar functions globally available
    window.toggleNewsbar = toggleNewsbar;
    window.closeNewsbar = closeNewsbar;
    window.closeMaintenanceBanner = closeMaintenanceBanner;
    
    // Toast notification system
    <?php if ($toast_message): ?>
    showToast('<?php echo addslashes($toast_message); ?>', '<?php echo $toast_type; ?>');
    <?php endif; ?>
});

// Global toast notification function
function showToast(message, type = 'info') {
    // Check if notifications are enabled
    <?php if (!$enable_notifications): ?>
    return;
    <?php endif; ?>
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="toast-icon fas fa-${getToastIcon(type)}"></i>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Style the toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '16px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease',
        maxWidth: '400px',
        minWidth: '300px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: '12px'
    });
    
    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    toast.style.backgroundColor = colors[type] || colors.info;
    
    // Add toast content styles
    const toastContent = toast.querySelector('.toast-content');
    Object.assign(toastContent.style, {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
        flex: '1'
    });
    
    const toastIcon = toast.querySelector('.toast-icon');
    Object.assign(toastIcon.style, {
        fontSize: '16px',
        opacity: '0.9'
    });
    
    const toastMessage = toast.querySelector('.toast-message');
    Object.assign(toastMessage.style, {
        flex: '1',
        wordWrap: 'break-word'
    });
    
    const toastClose = toast.querySelector('.toast-close');
    Object.assign(toastClose.style, {
        background: 'none',
        border: 'none',
        color: 'white',
        cursor: 'pointer',
        padding: '4px',
        borderRadius: '4px',
        opacity: '0.7',
        transition: 'opacity 0.2s ease'
    });
    
    toastClose.addEventListener('mouseenter', () => {
        toastClose.style.opacity = '1';
    });
    
    toastClose.addEventListener('mouseleave', () => {
        toastClose.style.opacity = '0.7';
    });
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

function getToastIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || icons.info;
}

// Make showToast globally available
window.showToast = showToast;
</script>
