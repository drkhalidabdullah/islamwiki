<?php
require_once __DIR__ . '/../config/config.php';

// Get user data if logged in
$current_user = null;
if (is_logged_in()) {
    $current_user = get_user($_SESSION['user_id']);
    $user_roles = get_user_roles($_SESSION['user_id']);
}

// Function to create sidebar dropdown menus
function createSidebarDropdown($id, $title, $icon, $items, $isActive = false, $customTrigger = null) {
    $activeClass = $isActive ? 'active' : '';
    $specificClass = "sidebar-{$id}";
    
    echo "<div class=\"sidebar-dropdown {$specificClass}\">";
    echo "<div class=\"user-icon-dropdown\">";
    echo "<a href=\"#\" class=\"sidebar-item user-icon-trigger {$activeClass}\" title=\"{$title}\">";
    
    if ($customTrigger) {
        echo $customTrigger;
    } else {
        echo "<i class=\"{$icon}\"></i>";
    }
    
    echo "</a>";
    echo "<div class=\"user-dropdown-menu\">";
    
    foreach ($items as $item) {
        if (isset($item['divider']) && $item['divider']) {
            echo "<hr class=\"dropdown-divider\">";
        } else {
            echo "<a href=\"{$item['url']}\" class=\"dropdown-item\">";
            echo "<i class=\"{$item['icon']}\"></i>";
            echo "<span>{$item['text']}</span>";
            echo "</a>";
        }
    }
    
    echo "</div>";
    echo "</div>";
    echo "</div>";
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
    <meta name="site-name" content="<?php echo htmlspecialchars(get_site_name()); ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . get_site_name() : get_site_name(); ?></title>
    <!-- Load skin CSS -->
    <?php $skins_manager->loadSkinAssets(); ?>
    
    <!-- Load core bismillah assets - loaded by skins_manager -->
    
    <!-- Load additional CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/iw-icons.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/skins/bismillah/assets/css/dashboard.css?v=<?php echo time(); ?>">
    
    <!-- Load admin CSS if needed -->
    <?php if (isset($admin_css) && $admin_css): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- Load extension assets -->
    <?php $extension_manager->loadExtensionAssets(); ?>
    <?php if (isset($is_search_page) && $is_search_page): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
    <?php endif; ?>
    <?php if (isset($_SERVER["REQUEST_URI"]) && ($_SERVER["REQUEST_URI"] === "/" || $_SERVER["REQUEST_URI"] === "")): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/homepage.css">
    <?php endif; ?>
    
    <!-- Load search overlay CSS for all pages -->
    <link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
    
    <!-- Load header dashboard CSS -->
    <link rel="stylesheet" href="/skins/bismillah/assets/css/header_dashboard.css">
</head>
<body>
    <?php if (!is_maintenance_mode() || is_logged_in()): ?>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="iw iw-bars"></i>
    </button>
    
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <!-- Left Sidebar Navigation -->
    <nav class="sidebar">
        <!-- Top Section -->
        <div class="sidebar-top">
            <!-- Logo at top -->
            <a href="/" class="sidebar-item" title="<?php echo get_site_name(); ?> Home">
                <i class="iw iw-book-open"></i>
            </a>
            
            <!-- Separator -->
            <div class="sidebar-separator"></div>
            
            <!-- Main Navigation -->
            <div class="sidebar-main-nav">
                <a href="<?php echo is_logged_in() ? '/dashboard' : '/'; ?>" class="sidebar-item <?php echo (is_logged_in() ? (strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') === 0) : (basename($_SERVER['PHP_SELF'] ?? '') == 'index.php' || ($_SERVER['REQUEST_URI'] ?? '') == '/')) ? 'active' : ''; ?>" title="Home">
                    <i class="iw iw-home"></i>
                </a>
                
                <?php if ($enable_wiki): ?>
                <a href="/wiki" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/wiki') === 0) ? 'active' : ''; ?>" title="Wiki">
                    <i class="iw iw-book"></i>
                </a>
                <?php endif; ?>
                
                <?php if (is_logged_in() && $enable_social): ?>
                <a href="/feed" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/feed') === 0) ? 'active' : ''; ?>" title="Feed">
                    <i class="iw iw-rss"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (is_logged_in()): ?>
        <?php if ($enable_social): ?>
        <!-- Messages Dropdown -->
        <?php
        $messagesItems = [
            ['url' => '/messages', 'icon' => 'iw iw-inbox', 'text' => 'Inbox'],
            ['url' => '/messages/sent', 'icon' => 'iw iw-paper-plane', 'text' => 'Sent'],
            ['url' => '/messages/compose', 'icon' => 'iw iw-edit', 'text' => 'Compose']
        ];
        $isMessagesActive = strpos($_SERVER['REQUEST_URI'] ?? '', '/messages') === 0;
        createSidebarDropdown('messages', 'Messages', 'iw iw-comments', $messagesItems, $isMessagesActive);
        ?>
        
        <!-- Friends Dropdown -->
        <?php
        $friendsItems = [
            ['url' => '/friends', 'icon' => 'iw iw-users', 'text' => 'All Friends'],
            ['url' => '/friends/requests', 'icon' => 'iw iw-user-plus', 'text' => 'Friend Requests'],
            ['url' => '/friends/suggestions', 'icon' => 'iw iw-user-friends', 'text' => 'Suggestions'],
            ['url' => '/friends/find', 'icon' => 'iw iw-search', 'text' => 'Find Friends']
        ];
        $isFriendsActive = strpos($_SERVER['REQUEST_URI'] ?? '', '/friends') === 0;
        createSidebarDropdown('friends', 'Friends', 'iw iw-users', $friendsItems, $isFriendsActive);
        ?>
        
        <?php endif; ?>
        
        
        <!-- User section at bottom -->
        <?php
        $userItems = [
            ['url' => '/user/' . $current_user['username'], 'icon' => 'iw iw-user', 'text' => 'Profile'],
            ['url' => '/dashboard', 'icon' => 'iw iw-tachometer-alt', 'text' => 'Dashboard'],
            ['url' => '/pages/user/watchlist.php', 'icon' => 'iw iw-eye', 'text' => 'My Watchlist'],
            ['url' => '/settings', 'icon' => 'iw iw-cog', 'text' => 'Settings']
        ];
        
        if (is_admin()) {
            $userItems[] = ['url' => '/admin', 'icon' => 'iw iw-shield-alt', 'text' => 'Admin Panel'];
        }
        
        $userItems[] = ['divider' => true];
        $userItems[] = ['url' => '/logout', 'icon' => 'iw iw-sign-out-alt', 'text' => 'Logout'];
        
        // Get user's current profile picture from database
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_avatar = $stmt->fetchColumn();
        
        // Update session with current avatar
        $_SESSION['avatar'] = $user_avatar;
        
        // Use avatar or default
        $avatar_url = $user_avatar ?: '/assets/images/default-avatar.svg';
        $userAvatar = '<img src="' . htmlspecialchars($avatar_url) . '" alt="Profile" class="user-avatar-img" onerror="this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K\';">';
        
        $isUserActive = strpos($_SERVER['REQUEST_URI'] ?? '', '/user/') === 0 || 
                        strpos($_SERVER['REQUEST_URI'] ?? '', '/dashboard') === 0 || 
                        strpos($_SERVER['REQUEST_URI'] ?? '', '/watchlist') !== false || 
                        strpos($_SERVER['REQUEST_URI'] ?? '', '/settings') === 0;
        createSidebarDropdown('user', 'User Menu', 'iw iw-user', $userItems, $isUserActive, $userAvatar);
        ?>
        
        <?php else: ?>
        <!-- Guest navigation -->
        <?php
        $guestItems = [
            ['url' => '/login', 'icon' => 'iw iw-sign-in-alt', 'text' => 'Login'],
            ['url' => '/register', 'icon' => 'iw iw-user-plus', 'text' => 'Create Account']
        ];
        createSidebarDropdown('guest', 'User Menu', 'iw iw-user', $guestItems, false);
        ?>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    
    <!-- Header Dashboard -->
    <?php if (!is_maintenance_mode() || is_logged_in()): ?>
    <?php include __DIR__ . '/header_dashboard.php'; ?>
    <?php endif; ?>
    
    <!-- Maintenance Mode Banner (for admins) -->
    <?php if (should_show_maintenance_banner()): ?>
    <div class="maintenance-banner">
        <div class="maintenance-banner-content">
            <div class="maintenance-banner-left">
                <i class="iw iw-tools"></i>
                <span>Maintenance Mode Active</span>
            </div>
            <div class="maintenance-banner-center">
                <span>Site is in maintenance mode. Regular users are redirected to maintenance page.</span>
            </div>
            <div class="maintenance-banner-right">
                <a href="/admin/system_settings" class="maintenance-banner-link">
                    <i class="iw iw-cog"></i>
                    Manage
                </a>
                <button class="maintenance-banner-close" onclick="closeMaintenanceBanner()" title="Hide Banner">
                    <i class="iw iw-times"></i>
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
                <i class="iw iw-search search-icon"></i>
                <input type="text" id="searchInput" class="search-overlay-input" placeholder="Search articles, users, content..." autocomplete="off">
                <button class="search-clear-btn" id="searchClearBtn" style="display: none;">
                    <i class="iw iw-times"></i>
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
    
    // User icon dropdown functionality is handled by header.js
    
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
            // Adjust newsbar position - keep it below header-dashboard
            const newsbar = document.querySelector('.newsbar');
            if (newsbar) {
                newsbar.style.top = '60px'; // Position below header-dashboard
            }
            // Adjust main content padding - account for header-dashboard + newsbar
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.style.paddingTop = '120px'; // 60px header-dashboard + 60px newsbar
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
            <i class="toast-icon iw iw-${getToastIcon(type)}"></i>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="iw iw-times"></i>
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
<script src="/skins/bismillah/assets/js/header.js"></script>
<script src="/skins/bismillah/assets/js/enhanced-search-overlay.js"></script>
<script src="/skins/bismillah/assets/js/avatar-updater.js"></script>
<script src="/skins/bismillah/assets/js/notifications.js"></script>
<script>
// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page with notifications
    if (document.getElementById('notificationBadge')) {
        window.notificationManager = new NotificationManager();
    }
});
</script>

