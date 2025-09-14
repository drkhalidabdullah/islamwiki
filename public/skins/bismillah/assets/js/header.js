let searchTimeout = null;
let currentQuery = '';

function openSearch() {
    console.log('Opening enhanced search overlay...');
    const overlay = document.getElementById('searchOverlay');
    console.log('Overlay found:', overlay);
    if (overlay) {
        console.log('Adding show class...');
        overlay.classList.add('show');
        console.log('Overlay classes after adding show:', overlay.className);
        const input = document.getElementById('searchInput');
        if (input) {
            input.focus();
        }
        // Temporarily comment out loadInitialSuggestions to test
        // loadInitialSuggestions();
    } else {
        console.error('Search overlay element not found!');
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
                    ${fact.source ? `<div class="fact-source" >Source: ${fact.source}</div>` : ''}
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
    console.log("Main DOMContentLoaded event fired");
    
    // Search functionality
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
    
    // General dropdown functionality removed - using specific user icon dropdown only
    
    // User icon dropdown functionality - Clean rewrite
    console.log("Setting up user icon dropdown functionality");
    
    const userIconDropdowns = document.querySelectorAll('.user-icon-dropdown');
    console.log("Found user icon dropdowns:", userIconDropdowns.length);
    
    userIconDropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.user-icon-trigger');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        
        if (!trigger || !menu) {
            console.log("Missing trigger or menu, skipping dropdown");
            return;
        }
        
        // Click functionality for user menu
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close all other dropdowns first
            userIconDropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    const otherMenu = otherDropdown.querySelector('.user-dropdown-menu');
                    if (otherMenu) {
                        otherMenu.classList.remove('show');
                        otherDropdown.classList.remove('active');
                    }
                }
            });
            
            // Toggle current dropdown
            const isOpen = menu.classList.contains('show');
            if (isOpen) {
                menu.classList.remove('show');
                dropdown.classList.remove('active');
                console.log('User menu closed');
                console.log('Classes after close:', menu.className, dropdown.className);
            } else {
                menu.classList.add('show');
                dropdown.classList.add('active');
                console.log('User menu opened');
                console.log('Classes after open:', menu.className, dropdown.className);
                console.log('Menu element:', menu);
                console.log('Dropdown element:', dropdown);
            }
        });
    });
    
    // Close all user dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-icon-dropdown')) {
            userIconDropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.user-dropdown-menu');
                if (menu) {
                    menu.classList.remove('show');
                    dropdown.classList.remove('active');
                }
            });
        }
    });
    
    // Close dropdowns when clicking on dropdown items
    document.querySelectorAll('.user-dropdown-menu .dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            userIconDropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.user-dropdown-menu');
                if (menu) {
                    menu.classList.remove('show');
                    dropdown.classList.remove('active');
                }
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
    
}); // End of main DOMContentLoaded function

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

// Global toast notification function
function showToast(message, type = 'info') {
    // Check if notifications are enabled
    // For now, just log to console
    console.log(`Toast: ${type} - ${message}`);
    
    // TODO: Implement proper toast notifications
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
