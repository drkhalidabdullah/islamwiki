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

// Load extension manager
require_once __DIR__ . '/extension_manager.php';
$extension_manager = new ExtensionManager();

// Load skins manager
require_once __DIR__ . '/../skins/skins_manager.php';

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
    <!-- Load skin CSS -->
    <?php $skins_manager->loadSkinAssets(); ?>
    <style>
    /* Force section header styles to override any conflicting styles */
    .featured-section .section-header,
    .recent-section .section-header,
    .community-section .section-header,
    div.section-header,
    .section-header {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        margin-top: 3rem !important;
        margin-bottom: 1.5rem !important;
        padding-bottom: 0.75rem !important;
        border-bottom: 2px solid #f3f4f6 !important;
        position: relative !important;
    }
    
    /* First section header should have less top margin */
    .homepage-main > section:first-child .section-header {
        margin-top: 2rem !important;
    }
    
    /* General content spacing for better readability */
    .article-content h2,
    .article-content h3,
    .article-content h4 {
        margin-top: 2rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    /* First heading in content should have less top margin */
    .article-content h2:first-child,
    .article-content h3:first-child,
    .article-content h4:first-child {
        margin-top: 0.25rem !important;
    }
    
    /* Extra spacing for h2 headers specifically */
    .article-content h2 {
        margin-top: 2rem !important;
    }
    
    .article-content h2:first-child {
        margin-top: 0.5rem !important;
    }
    
    .featured-section .section-header::after,
    .recent-section .section-header::after,
    .community-section .section-header::after,
    div.section-header::after,
    .section-header::after {
        content: '' !important;
        position: absolute !important;
        bottom: -2px !important;
        left: 0 !important;
        width: 60px !important;
        height: 2px !important;
        background: #2563eb !important;
        border-radius: 1px !important;
    }
    
    
    .featured-section .section-header h1,
    .featured-section .section-header h2,
    .featured-section .section-header h3,
    .recent-section .section-header h1,
    .recent-section .section-header h2,
    .recent-section .section-header h3,
    .community-section .section-header h1,
    .community-section .section-header h2,
    .community-section .section-header h3,
    div.section-header h1,
    div.section-header h2,
    div.section-header h3,
    .section-header h1,
    .section-header h2,
    .section-header h3 {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937 !important;
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
    }
    
    .featured-section .section-header h1::before,
    .featured-section .section-header h2::before,
    .featured-section .section-header h3::before,
    .recent-section .section-header h1::before,
    .recent-section .section-header h2::before,
    .recent-section .section-header h3::before,
    .community-section .section-header h1::before,
    .community-section .section-header h2::before,
    .community-section .section-header h3::before,
    div.section-header h1::before,
    div.section-header h2::before,
    div.section-header h3::before,
    .section-header h1::before,
    .section-header h2::before,
    .section-header h3::before {
        content: '' !important;
        width: 4px !important;
        height: 1.5rem !important;
        background: #2563eb !important;
        border-radius: 2px !important;
        flex-shrink: 0 !important;
    }
    
    .featured-section .section-header .view-all-link,
    .recent-section .section-header .view-all-link,
    .community-section .section-header .view-all-link,
    div.section-header .view-all-link,
    .section-header .view-all-link {
        color: #2563eb !important;
        text-decoration: none !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
        padding: 0.5rem 1rem !important;
        border: 1px solid #2563eb !important;
        border-radius: 0.5rem !important;
        transition: all 0.2s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
    
    .featured-section .section-header .view-all-link:hover,
    .recent-section .section-header .view-all-link:hover,
    .community-section .section-header .view-all-link:hover,
    div.section-header .view-all-link:hover,
    .section-header .view-all-link:hover {
        background: #2563eb !important;
        color: white !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    
    /* Horizontal rule styling for proper section spacing */
    .article-content hr {
        border: none !important;
        border-top: 2px solid #e5e7eb !important;
        margin: 3rem 0 !important;
        background: none !important;
    }
    </style>
    
    <!-- Load additional CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Load admin CSS if needed -->
    <?php if (isset($admin_css) && $admin_css): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- Load extension assets -->
    <?php $extension_manager->loadExtensionAssets(); ?>
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
    
    
    /* Newsbar styles moved to extension */

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
    <link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
    <?php endif; ?>
    <?php if ($_SERVER["REQUEST_URI"] === "/" || $_SERVER["REQUEST_URI"] === ""): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/homepage.css">
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
        <!-- Top Section -->
        <div class="sidebar-top">
            <!-- Logo at top -->
            <a href="/" class="sidebar-item" title="<?php echo get_site_name(); ?> Home">
                <i class="fas fa-book-open"></i>
            </a>
            
            <!-- Separator -->
            <div class="sidebar-separator"></div>
            
            <!-- Main Navigation -->
            <div class="search-container">
                <a href="#" class="sidebar-item search-trigger <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/search') === 0) ? 'active' : ''; ?>" title="Search" onclick="openSearch(); return false;">
                    <i class="fas fa-search"></i>
                </a>
            </div>
            
            <!-- Separator -->
            <div class="sidebar-separator"></div>
            
            <!-- Main Navigation -->
            <div class="sidebar-main-nav">
                <a href="/" class="sidebar-item <?php echo (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' || ($_SERVER['REQUEST_URI'] ?? '') == '/') ? 'active' : ''; ?>" title="Home">
                    <i class="fas fa-home"></i>
                </a>
                
                <?php if ($enable_wiki): ?>
                <a href="/wiki" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/wiki') === 0) ? 'active' : ''; ?>" title="Wiki">
                    <i class="fas fa-book"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
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
        <!-- Messages Link -->
        <div class="sidebar-item">
            <a href="/messages" class="sidebar-item" title="Messages">
                <i class="fas fa-comments"></i>
            </a>
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
    <!-- Extensions -->
    <?php $extension_manager->renderExtensions(); ?>
    <?php endif; ?>

    <main class="main-content<?php echo (is_maintenance_mode() && !is_logged_in()) ? ' maintenance-mode' : ''; ?>">

<!-- Enhanced Search Overlay HTML -->
<div id="searchOverlay" class="enhanced-search-overlay">
    <div class="search-overlay-backdrop"></div>
    <div class="search-overlay-content">
        <div class="search-overlay-header">
            <div class="search-input-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" class="search-overlay-input" placeholder="Search articles, users, content..." autocomplete="off">
                <button class="search-clear-btn" id="searchClearBtn" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="search-overlay-body">
            <div class="search-suggestions-container" id="searchSuggestionsContainer">
                <div class="search-welcome">
                    <div class="welcome-content">
                        <h3>Welcome to MuslimWiki</h3>
                        <p>Start typing to search for articles, users, and content</p>
                        <div class="welcome-suggestions">
                            <div class="suggestion-category">
                                <h4>Popular Searches</h4>
                                <div class="suggestion-tags">
                                    <span class="suggestion-tag" onclick="searchFor('Allah')">Allah</span>
                                    <span class="suggestion-tag" onclick="searchFor('Quran')">Quran</span>
                                    <span class="suggestion-tag" onclick="searchFor('Muhammad')">Muhammad</span>
                                    <span class="suggestion-tag" onclick="searchFor('Islam')">Islam</span>
                                    <span class="suggestion-tag" onclick="searchFor('Prayer')">Prayer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Enhanced Search Overlay Styles */
.enhanced-search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    background: rgba(0, 0, 0, 0.7);
    animation: fadeIn 0.3s ease-out;
}

.enhanced-search-overlay.show {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.search-overlay-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: transparent;
    cursor: pointer;
}

.search-overlay-content {
    position: relative;
    width: 100%;
    max-width: 900px;
    max-height: 80vh;
    margin: auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.search-overlay-header {
    padding: 2rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.search-input-container {
    position: relative;
    display: flex;
    align-items: center;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: border-color 0.2s ease;
}

.search-input-container:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
    color: #6b7280;
    font-size: 1.1rem;
    margin-right: 0.75rem;
}

.search-overlay-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1.1rem;
    color: #1f2937;
    background: transparent;
}

.search-overlay-input::placeholder {
    color: #9ca3af;
}

.search-clear-btn {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: color 0.2s ease;
}

.search-clear-btn:hover {
    color: #374151;
}

.search-overlay-body {
    max-height: 60vh;
    overflow-y: auto;
}

.search-suggestions-container {
    padding: 1.5rem;
}

.search-welcome {
    text-align: center;
    padding: 2rem 1rem;
}

.welcome-content h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.welcome-content p {
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.welcome-suggestions {
    margin-top: 1.5rem;
}

.suggestion-category h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
}

.suggestion-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
}

.suggestion-tag {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 20px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.suggestion-tag:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.search-results {
    margin-top: 1rem;
}

.search-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
}

.search-tab {
    flex: 1;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 2px solid transparent;
}

.search-tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.search-tab:hover {
    color: #374151;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.top-suggestions {
    margin-bottom: 1.5rem;
}

.suggestion-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.suggestion-item:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.suggestion-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: #f3f4f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    color: #6b7280;
}

.suggestion-content {
    flex: 1;
}

.suggestion-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.suggestion-meta {
    font-size: 0.875rem;
    color: #6b7280;
}

.suggestion-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-outline {
    background: transparent;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
    color: #374151;
}

.articles-list, .facts-list, .actions-list {
    space-y: 0.5rem;
}

.article-item, .fact-item, .action-item {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 0.5rem;
}

.article-item:hover, .action-item:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.article-icon {
    width: 2rem;
    height: 2rem;
    background: #f3f4f6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    color: #6b7280;
}

.article-content {
    flex: 1;
}

.article-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.article-meta {
    font-size: 0.875rem;
    color: #6b7280;
}

.search-loading {
    text-align: center;
    padding: 2rem;
}

.loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid #e5e7eb;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.search-no-results, .search-error {
    text-align: center;
    padding: 2rem;
}

.search-no-results i, .search-error i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.search-no-results h3, .search-error h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.search-no-results p, .search-error p {
    color: #6b7280;
}

.no-results {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-overlay-content {
        margin: 1rem;
        max-height: 90vh;
    }
    
    .search-overlay-header {
        padding: 1rem;
    }
    
    .search-suggestions-container {
        padding: 1rem;
    }
    
    .suggestion-item {
        padding: 0.75rem;
    }
    
    .suggestion-actions {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
let searchTimeout = null;
let currentQuery = '';

function openSearch() {
    console.log('Opening enhanced search overlay...');
    const overlay = document.getElementById('searchOverlay');
    if (overlay) {
        overlay.classList.add('show');
        const input = document.getElementById('searchInput');
        if (input) {
            input.focus();
        }
        loadInitialSuggestions();
    }
}

function closeSearch() {
    console.log('Closing enhanced search overlay...');
    const overlay = document.getElementById('searchOverlay');
    if (overlay) {
        overlay.classList.remove('show');
        const input = document.getElementById('searchInput');
        if (input) {
            input.value = '';
        }
        const clearBtn = document.getElementById('searchClearBtn');
        if (clearBtn) {
            clearBtn.style.display = 'none';
        }
        showWelcome();
    }
}

function searchFor(query) {
    const input = document.getElementById('searchInput');
    if (input) {
        input.value = query;
        handleSearchInput(query);
    }
}

function handleSearchInput(query) {
    currentQuery = query.trim();
    const clearBtn = document.getElementById('searchClearBtn');
    
    // Show/hide clear button
    if (clearBtn) {
        clearBtn.style.display = query.length > 0 ? 'block' : 'none';
    }

    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (query.length === 0) {
        showWelcome();
    } else if (query.length >= 2) {
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    }
}

async function performSearch(query) {
    try {
        showLoading();
        
        // Fetch search suggestions
        const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        displayResults(data);
    } catch (error) {
        console.error('Search error:', error);
        showError('Failed to load search suggestions');
    }
}

function displayResults(data) {
    const container = document.getElementById('searchSuggestionsContainer');
    
    if (!data || (!data.topArticles && !data.newestArticles && !data.didYouKnow)) {
        showNoResults();
        return;
    }

    container.innerHTML = `
        <div class="search-results">
            ${renderTopSuggestions(data.topSuggestions || [])}
            <div class="search-tabs">
                <button class="search-tab active" data-tab="top">Top Articles</button>
                <button class="search-tab" data-tab="newest">Newest Articles</button>
                <button class="search-tab" data-tab="facts">Did You Know?</button>
                <button class="search-tab" data-tab="actions">Actions</button>
            </div>
            <div class="search-tab-content">
                <div class="tab-pane active" id="top-articles">
                    ${renderArticles(data.topArticles || [])}
                </div>
                <div class="tab-pane" id="newest-articles">
                    ${renderArticles(data.newestArticles || [])}
                </div>
                <div class="tab-pane" id="did-you-know">
                    ${renderFacts(data.didYouKnow || [])}
                </div>
                <div class="tab-pane" id="actions">
                    ${renderActions()}
                </div>
            </div>
        </div>
    `;

    // Bind tab events
    bindTabEvents();
}

function renderTopSuggestions(suggestions) {
    if (!suggestions.length) return '';

    return `
        <div class="top-suggestions">
            ${suggestions.map(item => `
                <div class="suggestion-item" onclick="window.location.href='${item.url}'">
                    <div class="suggestion-icon">
                        <i class="${item.icon || 'fas fa-file-alt'}"></i>
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-title">${item.title}</div>
                        <div class="suggestion-meta">${item.meta || ''}</div>
                    </div>
                    <div class="suggestion-actions">
                        <button class="btn btn-sm btn-primary" onclick="window.location.href='${item.url}'">
                            <i class="fas fa-external-link-alt"></i> ${item.action || 'View'}
                        </button>
                        ${item.editable ? `<button class="btn btn-sm btn-outline" onclick="window.location.href='${item.editUrl}'">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function renderArticles(articles) {
    if (!articles.length) {
        return '<div class="no-results">No articles found</div>';
    }

    return `
        <div class="articles-list">
            ${articles.map(article => `
                <div class="article-item" onclick="window.location.href='${article.url}'">
                    <div class="article-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="article-content">
                        <div class="article-title">${article.title}</div>
                        <div class="article-meta">
                            <span>${article.category || 'General'}</span> • 
                            <span>${article.date || ''}</span>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function renderFacts(facts) {
    if (!facts.length) {
        return '<div class="no-results">No facts available</div>';
    }

    return `
        <div class="facts-list">
            ${facts.map(fact => `
                <div class="fact-item">
                    <div class="fact-content">${fact.content}</div>
                    ${fact.source ? `<div class="fact-source" style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">Source: ${fact.source}</div>` : ''}
                </div>
            `).join('')}
        </div>
    `;
}

function renderActions() {
    return `
        <div class="actions-list">
            <div class="action-item" onclick="window.location.href='/search?q=${encodeURIComponent(currentQuery)}'">
                <i class="fas fa-search"></i>
                <span>Search for "${currentQuery}"</span>
            </div>
            <div class="action-item" onclick="window.location.href='/wiki/create_article'">
                <i class="fas fa-plus"></i>
                <span>Create new article</span>
            </div>
            <div class="action-item" onclick="window.location.href='/wiki'">
                <i class="fas fa-book"></i>
                <span>Browse all articles</span>
            </div>
        </div>
    `;
}

function bindTabEvents() {
    const tabs = document.querySelectorAll('.search-tab');
    const panes = document.querySelectorAll('.tab-pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetTab = tab.dataset.tab;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Update active pane
            panes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(`${targetTab}-articles`).classList.add('active');
        });
    });
}

function showWelcome() {
    const container = document.getElementById('searchSuggestionsContainer');
    container.innerHTML = `
        <div class="search-welcome">
            <div class="welcome-content">
                <h3>Welcome to MuslimWiki</h3>
                <p>Start typing to search for articles, users, and content</p>
                <div class="welcome-suggestions">
                    <div class="suggestion-category">
                        <h4>Popular Searches</h4>
                        <div class="suggestion-tags">
                            <span class="suggestion-tag" onclick="searchFor('Allah')">Allah</span>
                            <span class="suggestion-tag" onclick="searchFor('Quran')">Quran</span>
                            <span class="suggestion-tag" onclick="searchFor('Muhammad')">Muhammad</span>
                            <span class="suggestion-tag" onclick="searchFor('Islam')">Islam</span>
                            <span class="suggestion-tag" onclick="searchFor('Prayer')">Prayer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function showLoading() {
    const container = document.getElementById('searchSuggestionsContainer');
    container.innerHTML = `
        <div class="search-loading">
            <div class="loading-spinner"></div>
            <p>Searching...</p>
        </div>
    `;
}

function showNoResults() {
    const container = document.getElementById('searchSuggestionsContainer');
    container.innerHTML = `
        <div class="search-no-results">
            <i class="fas fa-search"></i>
            <h3>No results found</h3>
            <p>Try different keywords or check your spelling</p>
        </div>
    `;
}

function showError(message) {
    const container = document.getElementById('searchSuggestionsContainer');
    container.innerHTML = `
        <div class="search-error">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Search Error</h3>
            <p>${message}</p>
        </div>
    `;
}

async function loadInitialSuggestions() {
    try {
        const response = await fetch('/api/search/initial-suggestions');
        const data = await response.json();
        
        if (data.suggestions) {
            displayResults(data);
        }
    } catch (error) {
        console.error('Failed to load initial suggestions:', error);
    }
}

// Add event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('searchInput');
    const clearBtn = document.getElementById('searchClearBtn');
    
    if (input) {
        input.addEventListener('input', function(e) {
            handleSearchInput(e.target.value);
        });
    }
    
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            input.value = '';
            clearBtn.style.display = 'none';
            showWelcome();
        });
    }
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSearch();
        }
    });
    
    // Open search on / key
    document.addEventListener('keydown', function(e) {
        if (e.key === '/' && !e.target.matches('input, textarea, [contenteditable]')) {
            e.preventDefault();
            openSearch();
        }
    });
    
    // Close on backdrop click
    const overlay = document.getElementById('searchOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target.classList.contains('search-overlay-backdrop')) {
                closeSearch();
            }
        });
    }
});

// Make functions globally available
window.openSearch = openSearch;
window.closeSearch = closeSearch;
window.searchFor = searchFor;
</script>

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
    // Enhanced search overlay is handled by enhanced-search-overlay.js
    // Old search code removed - using enhanced search overlay
    
    // Old search functions removed - using enhanced search overlay
    
    // Old popup click handler removed - using enhanced search overlay
    
    // Old escape key handler removed - using enhanced search overlay
    
    // Old search functions removed - using enhanced search overlay
    
    // Newsbar functionality moved to extension
    
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
    
    // Make maintenance banner function globally available
    window.closeMaintenanceBanner = closeMaintenanceBanner;
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

// Toast notification system
<?php if ($toast_message): ?>
showToast('<?php echo addslashes($toast_message); ?>', '<?php echo $toast_type; ?>');
<?php endif; ?>
</script>

