<?php
/**
 * Header Dashboard Component
 * Displays a modern header with search, create button, and user menu
 * Positioned above the news bar
 */

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
?>

<!-- Header Dashboard -->
<div class="header-dashboard" id="headerDashboard" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; z-index: 99999 !important; height: 60px !important; background: #2c3e50 !important; border-bottom: 1px solid #333 !important; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important; width: 100vw !important; box-sizing: border-box !important;">
    <div class="header-dashboard-container">
        <!-- Search Bar -->
        <div class="header-search-container">
                   <!-- Left Sidebar Toggle Button -->
                   <button class="sidebar-toggle-btn" id="leftSidebarToggleBtn" title="Toggle Left Sidebar">
                       <i class="iw iw-bars"></i>
                   </button>
            
            <!-- Site Logo -->
            <a href="<?php echo is_logged_in() ? '/dashboard' : '/'; ?>" class="header-logo" title="<?php echo get_site_name(); ?> <?php echo is_logged_in() ? 'Dashboard' : 'Home'; ?>">
                <i class="iw iw-moon"></i>
            </a>
            
            <!-- Search Input Wrapper -->
            <div class="search-input-wrapper">
                <i class="iw iw-search search-icon"></i>
                <input type="text" class="header-search-input" placeholder="Search" id="headerSearchInput">
                <button class="header-search-btn" onclick="performHeaderSearch()">
                    Search
                </button>
            </div>
        </div>

        <!-- News Toggle and Create Button -->
        <div class="header-center-container">
            <!-- News Toggle Button -->
            <button class="news-toggle-btn" id="newsToggleBtn" title="Toggle News Bar">
                <i class="iw iw-bullhorn"></i>
            </button>
            
            <!-- Create Button -->
            <?php if (is_logged_in()): ?>
            <div class="header-create-container">
            <div class="create-dropdown">
                <button class="create-btn" id="createBtn">
                    <i class="iw iw-plus"></i>
                    <span>Create</span>
                    <i class="iw iw-chevron-down"></i>
                </button>
                <div class="create-dropdown-menu" id="createDropdown">
                    <a href="/pages/social/create_post.php" class="create-item">
                        <i class="iw iw-edit"></i>
                        <span>Create Post</span>
                    </a>
                    <?php if ($enable_wiki): ?>
                    <a href="/pages/wiki/create_article.php" class="create-item">
                        <i class="iw iw-file-text"></i>
                        <span>Write Article</span>
                    </a>
                    <?php endif; ?>
                    <a href="/pages/social/create_post.php" class="create-item">
                        <i class="iw iw-image"></i>
                        <span>Upload Image</span>
                    </a>
                    <?php if ($enable_social): ?>
                    <a href="/pages/social/friends.php" class="create-item">
                        <i class="iw iw-users"></i>
                        <span>Friends</span>
                    </a>
                    <a href="/pages/social/messages.php" class="create-item">
                        <i class="iw iw-message"></i>
                        <span>Messages</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($enable_notifications): ?>
                    <a href="/pages/notifications.php" class="create-item">
                        <i class="iw iw-bell"></i>
                        <span>Notifications</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </div>

        <!-- Right Side Group -->
        <div class="header-right-group">

            <!-- User Menu -->
            <div class="header-user-menu">
            <?php if (is_logged_in()): ?>
                <div class="user-dropdown">
                    <button class="user-profile-btn" id="userProfileBtn">
                        <div class="user-avatar">
                            <?php
                            // Get user's current profile picture from database
                            $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user_avatar = $stmt->fetchColumn();
                            
                            // Use avatar or default
                            $avatar_url = $user_avatar ?: '/assets/images/default-avatar.svg';
                            ?>
                            <img src="<?php echo htmlspecialchars($avatar_url); ?>" 
                                 alt="Profile" 
                                 class="user-avatar-img" 
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDYgMTIgMTIgMTJaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTIgMTRDOC42OTExNyAxNCA2IDE2LjY5MTE3IDYgMjBIMjBDMjAgMTYuNjkxMTcgMTcuMzA4OCAxNCAxMiAxNFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                        </div>
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?></div>
                            <div class="user-status">Online</div>
                        </div>
                        <i class="iw iw-chevron-down"></i>
                    </button>
                    <div class="user-dropdown-menu" id="userDropdown">
                        <a href="/user/<?php echo htmlspecialchars($current_user['username']); ?>" class="dropdown-item">
                            <i class="iw iw-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="/dashboard" class="dropdown-item">
                            <i class="iw iw-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/pages/user/watchlist.php" class="dropdown-item">
                            <i class="iw iw-eye"></i>
                            <span>My Watchlist</span>
                        </a>
                        <a href="/settings" class="dropdown-item">
                            <i class="iw iw-cog"></i>
                            <span>Settings</span>
                        </a>
                        <?php if (is_admin()): ?>
                        <hr class="dropdown-divider">
                        <a href="/admin" class="dropdown-item">
                            <i class="iw iw-shield-alt"></i>
                            <span>Admin Panel</span>
                        </a>
                        <?php endif; ?>
                        <hr class="dropdown-divider">
                        <a href="/logout" class="dropdown-item">
                            <i class="iw iw-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="guest-actions">
                    <a href="/login" class="btn-login">Log In</a>
                    <a href="/register" class="btn-register">Sign Up</a>
                </div>
            <?php endif; ?>
            </div>

            <!-- Right Sidebar Toggle -->
            <?php if (is_logged_in() && $enable_social): ?>
            <div class="header-right-sidebar-toggle">
                <button class="right-sidebar-toggle-btn" id="rightSidebarToggleBtn" title="Toggle Right Sidebar">
                    <i class="iw iw-users"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Header Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Create dropdown toggle
    const createBtn = document.getElementById('createBtn');
    const createDropdown = document.getElementById('createDropdown');
    
    if (createBtn && createDropdown) {
        createBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            createDropdown.classList.toggle('show');
        });
    }

    // User dropdown toggle
    const userProfileBtn = document.getElementById('userProfileBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userProfileBtn && userDropdown) {
        userProfileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (createDropdown && !createBtn.contains(e.target)) {
            createDropdown.classList.remove('show');
        }
        if (userDropdown && !userProfileBtn.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
        if (!e.target.closest('.utility-dropdown')) {
            document.querySelectorAll('.utility-dropdown-menu').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });

    // Header search functionality
    const headerSearchInput = document.getElementById('headerSearchInput');
    if (headerSearchInput) {
        headerSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performHeaderSearch();
            }
        });
    }

    // Left sidebar toggle functionality
    const leftSidebarToggleBtn = document.getElementById('leftSidebarToggleBtn');
    if (leftSidebarToggleBtn) {
        console.log('Left sidebar toggle button found');
        leftSidebarToggleBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            console.log('Left sidebar toggle clicked - calling toggleLeftSidebarVisibility');
            toggleLeftSidebarVisibility();
        });
        
        // Initialize left sidebar state on page load with delay to ensure elements are loaded
        setTimeout(() => {
            initializeLeftSidebarState();
        }, 100);
    } else {
        console.log('Left sidebar toggle button NOT found');
    }

    // Right sidebar toggle functionality
    const rightSidebarToggleBtn = document.getElementById('rightSidebarToggleBtn');
    if (rightSidebarToggleBtn) {
        console.log('Right sidebar toggle button found');
        rightSidebarToggleBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            console.log('Right sidebar toggle clicked');
            toggleRightSidebarVisibility();
        });
        
        // Initialize right sidebar state on page load with delay to ensure elements are loaded
        setTimeout(() => {
            initializeRightSidebarState();
        }, 100);
    } else {
        console.log('Right sidebar toggle button NOT found');
    }

    // News toggle functionality
    const newsToggleBtn = document.getElementById('newsToggleBtn');
    if (newsToggleBtn) {
        newsToggleBtn.addEventListener('click', function() {
            toggleNewsbar();
        });
        
        // Initialize newsbar state on page load
        initializeNewsbarState();
    }
    
});

function initializeLeftSidebarState() {
    const leftSidebarHidden = localStorage.getItem('left-sidebar-hidden');
    const leftSidebar = document.querySelector('.left-sidebar');
    const leftSidebarToggleBtn = document.getElementById('leftSidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    const headerDashboard = document.querySelector('.header-dashboard');
    
    console.log('Initializing left sidebar state from localStorage:', leftSidebarHidden);
    console.log('Left sidebar element found:', !!leftSidebar);
    console.log('Left sidebar toggle button found:', !!leftSidebarToggleBtn);
    
    // Only hide sidebar if user explicitly set it to hidden (not on first visit)
    if (leftSidebarHidden === 'true') {
        // Left sidebar should be hidden
        if (leftSidebar) {
            // Check if we're on mobile (screen width <= 768px)
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                leftSidebar.style.transform = 'translateX(-100%)';
                leftSidebar.classList.remove('open');
            } else {
                leftSidebar.style.setProperty('display', 'none', 'important');
            }
            console.log('Left sidebar set to hidden on init (mobile:', isMobile, ')');
        }
        
        if (leftSidebarToggleBtn) {
            leftSidebarToggleBtn.classList.add('inactive');
            const icon = leftSidebarToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '0.5';
            }
            leftSidebarToggleBtn.title = 'Show Left Sidebar';
            console.log('Left sidebar toggle button set to inactive state');
        }
        
        // No need to adjust positioning - layout is now fixed
    } else {
        // Left sidebar should be visible (default state)
        if (leftSidebar) {
            leftSidebar.style.setProperty('display', 'flex', 'important');
            leftSidebar.style.transform = 'translateX(0)';
            console.log('Left sidebar set to visible on init');
        }
        
        if (leftSidebarToggleBtn) {
            leftSidebarToggleBtn.classList.remove('inactive');
            const icon = leftSidebarToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '1';
            }
            leftSidebarToggleBtn.title = 'Hide Left Sidebar';
            console.log('Left sidebar toggle button set to active state');
        }
        
        // No need to adjust positioning - layout is now fixed
    }
}

function toggleLeftSidebarVisibility() {
    console.log('=== toggleLeftSidebarVisibility START ===');
    const leftSidebar = document.querySelector('.left-sidebar');
    const leftSidebarToggleBtn = document.getElementById('leftSidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    const headerDashboard = document.querySelector('.header-dashboard');
    
    console.log('toggleLeftSidebarVisibility called from header dashboard');
    console.log('leftSidebar:', leftSidebar);
    console.log('leftSidebarToggleBtn:', leftSidebarToggleBtn);
    
    if (!leftSidebar || !leftSidebarToggleBtn) {
        console.log('Missing elements - leftSidebar:', !!leftSidebar, 'leftSidebarToggleBtn:', !!leftSidebarToggleBtn);
        return;
    }
    
    // Log current sidebar state before toggle
    console.log('Current sidebar styles:');
    console.log('- display:', leftSidebar.style.display);
    console.log('- transform:', leftSidebar.style.transform);
    console.log('- classList:', leftSidebar.classList.toString());
    console.log('- computed display:', window.getComputedStyle(leftSidebar).display);
    console.log('- computed transform:', window.getComputedStyle(leftSidebar).transform);
    
    // Toggle left sidebar visibility
    const isMobile = window.innerWidth <= 768;
    const isCurrentlyHidden = (isMobile ? leftSidebar.style.transform === 'translateX(-100%)' || !leftSidebar.classList.contains('open') : leftSidebar.style.display === 'none');
    
    console.log('Current sidebar state - isHidden:', isCurrentlyHidden, 'isMobile:', isMobile);
    
    if (isCurrentlyHidden) {
        // Show sidebar
        if (isMobile) {
            leftSidebar.style.transform = 'translateX(0)';
            leftSidebar.classList.add('open');
            console.log('Mobile: Showing sidebar with transform and open class');
        } else {
            leftSidebar.style.setProperty('display', 'flex', 'important');
            leftSidebar.style.transform = 'translateX(0)';
            console.log('Desktop: Showing sidebar with display flex');
        }
        console.log('Sidebar shown');
    } else {
        // Hide sidebar
        if (isMobile) {
            leftSidebar.style.transform = 'translateX(-100%)';
            leftSidebar.classList.remove('open');
            console.log('Mobile: Hiding sidebar with transform and removing open class');
        } else {
            leftSidebar.style.setProperty('display', 'none', 'important');
            console.log('Desktop: Hiding sidebar with display none');
        }
        console.log('Sidebar hidden');
    }
    
    // Log sidebar state after toggle
    console.log('After toggle sidebar styles:');
    console.log('- display:', leftSidebar.style.display);
    console.log('- transform:', leftSidebar.style.transform);
    console.log('- classList:', leftSidebar.classList.toString());
    console.log('- computed display:', window.getComputedStyle(leftSidebar).display);
    console.log('- computed transform:', window.getComputedStyle(leftSidebar).transform);
    
    // Update button appearance based on NEW state
    const icon = leftSidebarToggleBtn.querySelector('i');
    const newStateIsHidden = (isMobile ? leftSidebar.style.transform === 'translateX(-100%)' || !leftSidebar.classList.contains('open') : leftSidebar.style.display === 'none');
    
    console.log('Button state update - newStateIsHidden:', newStateIsHidden);
    
    if (newStateIsHidden) {
        // Left sidebar is hidden - show inactive state
        leftSidebarToggleBtn.classList.add('inactive');
        if (icon) {
            icon.style.opacity = '0.5';
        }
        leftSidebarToggleBtn.title = 'Show Left Sidebar';
        
        // Save state to localStorage
        localStorage.setItem('left-sidebar-hidden', 'true');
        console.log('Button set to inactive (hidden state)');
    } else {
        // Left sidebar is visible - show active state
        leftSidebarToggleBtn.classList.remove('inactive');
        if (icon) {
            icon.style.opacity = '1';
        }
        leftSidebarToggleBtn.title = 'Hide Left Sidebar';
        
        // Save state to localStorage
        localStorage.setItem('left-sidebar-hidden', 'false');
        console.log('Button set to active (visible state)');
    }
    
    console.log('=== toggleLeftSidebarVisibility END ===');
}

function initializeRightSidebarState() {
    const rightSidebarHidden = localStorage.getItem('right-sidebar-hidden');
    const rightSidebar = document.querySelector('.right-sidebar');
    const rightSidebarToggleBtn = document.getElementById('rightSidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    
    console.log('Initializing right sidebar state from localStorage:', rightSidebarHidden);
    console.log('Right sidebar element found:', !!rightSidebar);
    console.log('Right sidebar toggle button found:', !!rightSidebarToggleBtn);
    
    if (rightSidebarHidden === 'true') {
        // Right sidebar should be hidden
        if (rightSidebar) {
            rightSidebar.style.display = 'none';
            rightSidebar.style.setProperty('display', 'none', 'important');
            console.log('Right sidebar set to hidden on init');
        }
        
        if (rightSidebarToggleBtn) {
            rightSidebarToggleBtn.classList.add('inactive');
            const icon = rightSidebarToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '0.5';
            }
            rightSidebarToggleBtn.title = 'Show Right Sidebar';
            console.log('Right sidebar toggle button set to inactive state');
        }
        
        // Adjust main content width when right sidebar is hidden
        if (mainContent) {
            mainContent.style.marginRight = '0';
            mainContent.style.width = 'calc(100% - 60px)'; // Only left sidebar
            console.log('Main content adjusted for hidden right sidebar');
        }
    } else {
        // Right sidebar should be visible (default state)
        if (rightSidebar) {
            rightSidebar.style.display = 'flex';
            rightSidebar.style.setProperty('display', 'flex', 'important');
            console.log('Right sidebar set to visible on init');
        }
        
        if (rightSidebarToggleBtn) {
            rightSidebarToggleBtn.classList.remove('inactive');
            const icon = rightSidebarToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '1';
            }
            rightSidebarToggleBtn.title = 'Hide Right Sidebar';
            console.log('Right sidebar toggle button set to active state');
        }
        
        // Ensure main content width accounts for right sidebar
        if (mainContent) {
            mainContent.style.marginRight = '60px';
            mainContent.style.width = 'calc(100% - 120px)'; // Both sidebars
            console.log('Main content positioned for visible right sidebar');
        }
    }
}

function toggleRightSidebarVisibility() {
    const rightSidebar = document.querySelector('.right-sidebar');
    const rightSidebarToggleBtn = document.getElementById('rightSidebarToggleBtn');
    const mainContent = document.querySelector('.main-content');
    
    console.log('toggleRightSidebarVisibility called from header dashboard');
    console.log('rightSidebar:', rightSidebar);
    console.log('rightSidebarToggleBtn:', rightSidebarToggleBtn);
    console.log('rightSidebar current display:', rightSidebar ? rightSidebar.style.display : 'N/A');
    console.log('rightSidebar computed display:', rightSidebar ? window.getComputedStyle(rightSidebar).display : 'N/A');
    
    if (!rightSidebar || !rightSidebarToggleBtn) {
        console.log('Missing elements - rightSidebar:', !!rightSidebar, 'rightSidebarToggleBtn:', !!rightSidebarToggleBtn);
        return;
    }
    
    // Toggle right sidebar visibility
    const computedStyle = window.getComputedStyle(rightSidebar);
    const isHidden = rightSidebar.style.display === 'none' || computedStyle.display === 'none';
    
    if (isHidden) {
        rightSidebar.style.display = 'flex';
        rightSidebar.style.setProperty('display', 'flex', 'important');
    } else {
        rightSidebar.style.display = 'none';
        rightSidebar.style.setProperty('display', 'none', 'important');
    }
    console.log('Right sidebar visibility toggled, is now:', isHidden ? 'visible' : 'hidden');
    
    // Update button appearance
    const icon = rightSidebarToggleBtn.querySelector('i');
    const currentDisplay = rightSidebar.style.display || window.getComputedStyle(rightSidebar).display;
    if (currentDisplay === 'none') {
        // Right sidebar is hidden - show inactive state
        rightSidebarToggleBtn.classList.add('inactive');
        icon.style.opacity = '0.5';
        rightSidebarToggleBtn.title = 'Show Right Sidebar';
        
        // Adjust main content width when right sidebar is hidden
        if (mainContent) {
            mainContent.style.marginRight = '0';
            mainContent.style.width = 'calc(100% - 60px)'; // Only left sidebar
        }
        
        // Save state to localStorage
        localStorage.setItem('right-sidebar-hidden', 'true');
        console.log('Right sidebar hidden, saved to localStorage');
    } else {
        // Right sidebar is visible - show active state
        rightSidebarToggleBtn.classList.remove('inactive');
        icon.style.opacity = '1';
        rightSidebarToggleBtn.title = 'Hide Right Sidebar';
        
        // Adjust main content width when right sidebar is visible
        if (mainContent) {
            mainContent.style.marginRight = '60px';
            mainContent.style.width = 'calc(100% - 120px)'; // Both sidebars
        }
        
        // Save state to localStorage
        localStorage.setItem('right-sidebar-hidden', 'false');
        console.log('Right sidebar visible, saved to localStorage');
    }
}

function initializeNewsbarState() {
    const newsbarHidden = localStorage.getItem('newsbar-hidden');
    const newsbar = document.querySelector('.newsbar');
    const newsToggleBtn = document.getElementById('newsToggleBtn');
    const mainContent = document.querySelector('.main-content');
    
    console.log('Initializing newsbar state from localStorage:', newsbarHidden);
    
    if (newsbarHidden === 'true') {
        // Newsbar should be hidden
        if (newsbar) {
            newsbar.classList.add('hidden');
            console.log('Newsbar set to hidden on init');
        }
        
        if (newsToggleBtn) {
            newsToggleBtn.classList.add('inactive');
            const icon = newsToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '0.5';
            }
            newsToggleBtn.title = 'Show News Bar';
            console.log('News toggle button set to inactive state');
        }
        
        if (mainContent) {
            mainContent.style.paddingTop = '60px'; // Only header-dashboard
            console.log('Main content padding set to 60px (header only)');
        }
    } else {
        // Newsbar should be visible (default state)
        if (newsbar) {
            newsbar.classList.remove('hidden');
            console.log('Newsbar set to visible on init');
        }
        
        if (newsToggleBtn) {
            newsToggleBtn.classList.remove('inactive');
            const icon = newsToggleBtn.querySelector('i');
            if (icon) {
                icon.style.opacity = '1';
            }
            newsToggleBtn.title = 'Hide News Bar';
            console.log('News toggle button set to active state');
        }
        
        if (mainContent) {
            mainContent.style.paddingTop = '120px'; // Header-dashboard + newsbar
            console.log('Main content padding set to 120px (header + newsbar)');
        }
    }
}

function performHeaderSearch() {
    const searchInput = document.getElementById('headerSearchInput');
    const query = searchInput.value.trim();
    
    if (query) {
        window.location.href = '/search?q=' + encodeURIComponent(query);
    }
}


function toggleNewsbar() {
    const newsbar = document.querySelector('.newsbar');
    const newsToggleBtn = document.getElementById('newsToggleBtn');
    const mainContent = document.querySelector('.main-content');
    
    console.log('toggleNewsbar called from header dashboard');
    console.log('newsbar:', newsbar);
    console.log('newsToggleBtn:', newsToggleBtn);
    
    if (!newsbar || !newsToggleBtn) {
        console.log('Missing elements - newsbar:', !!newsbar, 'newsToggleBtn:', !!newsToggleBtn);
        return;
    }
    
    // Toggle newsbar visibility
    newsbar.classList.toggle('hidden');
    console.log('Newsbar classes after toggle:', newsbar.className);
    
    // Update button appearance
    const icon = newsToggleBtn.querySelector('i');
    if (newsbar.classList.contains('hidden')) {
        // Newsbar is hidden - show inactive state
        newsToggleBtn.classList.add('inactive');
        icon.style.opacity = '0.5';
        newsToggleBtn.title = 'Show News Bar';
        
        // Adjust main content padding
        if (mainContent) {
            mainContent.style.paddingTop = '60px'; // Only header-dashboard
        }
        
        // Save state to localStorage
        localStorage.setItem('newsbar-hidden', 'true');
        console.log('Newsbar hidden, saved to localStorage');
    } else {
        // Newsbar is visible - show active state
        newsToggleBtn.classList.remove('inactive');
        icon.style.opacity = '1';
        newsToggleBtn.title = 'Hide News Bar';
        
        // Adjust main content padding
        if (mainContent) {
            mainContent.style.paddingTop = '120px'; // Header-dashboard + newsbar
        }
        
        // Save state to localStorage
        localStorage.setItem('newsbar-hidden', 'false');
        console.log('Newsbar visible, saved to localStorage');
    }
    
    // Update floating close button if it exists
    const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-floating-close i');
    if (floatingCloseBtn) {
        if (newsbar.classList.contains('hidden')) {
            floatingCloseBtn.className = 'iw iw-arrow-down';
            floatingCloseBtn.parentElement.title = 'Show Newsbar';
        } else {
            floatingCloseBtn.className = 'iw iw-times';
            floatingCloseBtn.parentElement.title = 'Close Newsbar';
        }
    }
}
</script>
