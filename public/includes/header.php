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
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Mobile Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <!-- Left Sidebar Navigation -->
    <nav class="sidebar">
        <!-- Logo at top -->
        <a href="/" class="sidebar-item" title="IslamWiki Home">
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
                        <h3>Search IslamWiki</h3>
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
        
        <a href="/wiki" class="sidebar-item <?php echo (strpos($_SERVER['REQUEST_URI'] ?? '', '/wiki') === 0) ? 'active' : ''; ?>" title="Wiki">
            <i class="fas fa-book"></i>
        </a>
        
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
                <a href="/pages/wiki/create_article.php" class="dropdown-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Create Article</span>
                </a>
                <a href="/wiki/upload" class="dropdown-item">
                    <i class="fas fa-upload"></i>
                    <span>Upload File</span>
                </a>
            </div>
        </div>
        
        <!-- Separator -->
        <div class="sidebar-separator"></div>
        
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
    
    <main class="main-content">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?>">
            <?php echo htmlspecialchars($message['message']); ?>
        </div>
        <?php endif; ?>

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
});
</script>
