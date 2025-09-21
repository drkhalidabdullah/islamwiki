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
<div class="header-dashboard">
    <div class="header-dashboard-container">
        <!-- News Toggle & Search Bar -->
        <div class="header-search-container">
            <!-- News Toggle Button -->
            <button class="news-toggle-btn" id="newsToggleBtn" title="Toggle News Bar">
                <i class="iw iw-bullhorn"></i>
            </button>
            
            <!-- Search Input Wrapper -->
            <div class="search-input-wrapper">
                <i class="iw iw-search search-icon"></i>
                <input type="text" class="header-search-input" placeholder="Search" id="headerSearchInput">
                <button class="header-search-btn" onclick="performHeaderSearch()">
                    Search
                </button>
            </div>
        </div>

        <!-- Create Button -->
        <?php if (is_logged_in()): ?>
        <div class="header-create-container">
            <div class="create-dropdown">
                <button class="create-btn" id="createBtn">
                    <i class="iw iw-video"></i>
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

        <!-- Utility Icons -->
        <div class="header-utilities">
            <div class="utility-separator"></div>
            <button class="utility-btn" title="Settings" onclick="window.location.href='/settings'">
                <i class="iw iw-cog"></i>
            </button>
            <?php if ($enable_notifications): ?>
            <button class="utility-btn" title="Notifications" onclick="window.location.href='/pages/notifications.php'">
                <i class="iw iw-bell"></i>
            </button>
            <?php endif; ?>
        </div>

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

    // News toggle functionality
    const newsToggleBtn = document.getElementById('newsToggleBtn');
    if (newsToggleBtn) {
        newsToggleBtn.addEventListener('click', function() {
            toggleNewsbar();
        });
    }
});

function performHeaderSearch() {
    const searchInput = document.getElementById('headerSearchInput');
    const query = searchInput.value.trim();
    
    if (query) {
        window.location.href = '/search?q=' + encodeURIComponent(query);
    }
}
</script>
