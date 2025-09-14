<?php
require_once __DIR__ . '/../config/config.php';

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
    
    <!-- Load core bismillah assets -->
    <link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
    <script src="/skins/bismillah/assets/js/bismillah.js"></script>
    
    <!-- Load additional CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Load admin CSS if needed -->
    <?php if (isset($admin_css) && $admin_css): ?>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- Load extension assets -->
    <?php $extension_manager->loadExtensionAssets(); ?>
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
            <div class="user-icon-dropdown">
                <a href="#" class="sidebar-item user-icon-trigger" title="User Menu">
                    <img src="/assets/images/default-avatar.png" alt="Profile" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                </a>
                <div class="user-dropdown-menu">
                    <a href="/user/<?php echo $current_user['username']; ?>" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="/dashboard" class="dropdown-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/settings" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <?php if (is_admin()): ?>
                    <a href="/admin" class="dropdown-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Admin Panel</span>
                    </a>
                    <?php endif; ?>
                    <hr class="dropdown-divider">
                    <a href="/logout" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Guest navigation -->
        <div class="sidebar-user">
            <div class="user-icon-dropdown">
                <a href="#" class="sidebar-item user-icon-trigger" title="User Menu">
                    <i class="fas fa-user"></i>
                </a>
                <div class="user-dropdown-menu">
                    <a href="/login" class="dropdown-item">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="/register" class="dropdown-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                </div>
            </div>
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
    
    // User icon dropdown functionality - Simplified approach
    document.addEventListener('DOMContentLoaded', function() {
        const userIconDropdowns = document.querySelectorAll('.user-icon-dropdown');
        
        userIconDropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.user-icon-trigger');
            const menu = dropdown.querySelector('.user-dropdown-menu');
            
            if (!trigger || !menu) return;
            
            // Prevent default link behavior
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
            });
            
            // Use CSS-only hover approach with JavaScript fallback
            // The CSS should handle the hover, but we'll add a small delay for better UX
            let hoverTimeout;
            
            dropdown.addEventListener('mouseenter', function() {
                clearTimeout(hoverTimeout);
                // Let CSS handle the display
            });
            
            dropdown.addEventListener('mouseleave', function() {
                // Let CSS handle the hiding
            });
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
<script src="/skins/bismillah/assets/js/header.js"></script>

