<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

// Check maintenance mode
check_maintenance_mode();

// Enforce rate limiting for search queries
enforce_rate_limit('search_queries');

// Include analytics
require_once '../includes/analytics.php';

$page_title = "Comprehensive Search";
$is_search_page = true; // Hide header search

// Get search parameters
$query = $_GET['q'] ?? '';
$content_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';
$page = max(1, (int)($_GET['page'] ?? 1));

// Get categories for filter
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM content_categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get search suggestions for empty query
$suggestions = [];
$trending = [];
if (empty($query)) {
    try {
        $stmt = $pdo->prepare("SELECT suggestion, suggestion_type, content_type, search_count 
                               FROM search_suggestions 
                               WHERE is_active = 1 
                               ORDER BY search_count DESC 
                               LIMIT 10");
        $stmt->execute();
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT suggestion, search_count, content_type
                               FROM search_suggestions 
                               WHERE is_active = 1 AND suggestion_type = 'trending'
                               ORDER BY search_count DESC 
                               LIMIT 5");
        $stmt->execute();
        $trending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching suggestions: " . $e->getMessage());
    }
}

include "../includes/header.php";
?>

<div class="search-page-container">
    <div class="search-layout">
        <!-- Left Sidebar Filters -->
        <div class="search-sidebar">
            <div class="sidebar-section">
                <h3>Search Filters</h3>
                
                <!-- Content Type Filter -->
                <div class="filter-group">
                    <h4>Content Type</h4>
                    <div class="filter-options">
                        <a href="?q=<?php echo urlencode($query); ?>&type=all" class="filter-link <?php echo ($content_type === 'all') ? 'active' : ''; ?>">
                            <i class="fas fa-search"></i>
                            All Content
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=articles" class="filter-link <?php echo ($content_type === 'articles') ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i>
                            Wiki Pages
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=posts" class="filter-link <?php echo ($content_type === 'posts') ? 'active' : ''; ?>">
                            <i class="fas fa-comment"></i>
                            Posts
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=people" class="filter-link <?php echo ($content_type === 'people') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            People
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=groups" class="filter-link <?php echo ($content_type === 'groups') ? 'active' : ''; ?>">
                            <i class="fas fa-layer-group"></i>
                            Groups
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=events" class="filter-link <?php echo ($content_type === 'events') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar"></i>
                            Events
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=ummah" class="filter-link <?php echo ($content_type === 'ummah') ? 'active' : ''; ?>">
                            <i class="fas fa-mosque"></i>
                            Ummah
                        </a>
                    </div>
                </div>

                <!-- Category Filter (for articles) -->
                <?php if ($content_type === 'all' || $content_type === 'articles'): ?>
                <div class="filter-group">
                    <h4>Category</h4>
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Sort Options -->
                <div class="filter-group">
                    <h4>Sort By</h4>
                    <select name="sort" class="filter-select">
                        <option value="relevance" <?php echo ($sort === 'relevance') ? 'selected' : ''; ?>>Relevance</option>
                        <option value="date" <?php echo ($sort === 'date') ? 'selected' : ''; ?>>Date</option>
                        <option value="title" <?php echo ($sort === 'title') ? 'selected' : ''; ?>>Title</option>
                        <option value="popularity" <?php echo ($sort === 'popularity') ? 'selected' : ''; ?>>Popularity</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="filter-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>

            <!-- Search Suggestions -->
            <?php if (!empty($suggestions)): ?>
            <div class="sidebar-section">
                <h3>Popular Searches</h3>
                <div class="suggestions-list">
                    <?php foreach ($suggestions as $suggestion): ?>
                    <a href="?q=<?php echo urlencode($suggestion['suggestion']); ?>&type=<?php echo $suggestion['content_type'] ?? 'all'; ?>" 
                       class="suggestion-item">
                        <span class="suggestion-text"><?php echo htmlspecialchars($suggestion['suggestion']); ?></span>
                        <span class="suggestion-count"><?php echo $suggestion['search_count']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Trending Topics -->
            <?php if (!empty($trending)): ?>
            <div class="sidebar-section">
                <h3>Trending</h3>
                <div class="trending-list">
                    <?php foreach ($trending as $topic): ?>
                    <a href="?q=<?php echo urlencode($topic['suggestion']); ?>&type=<?php echo $topic['content_type'] ?? 'all'; ?>" 
                       class="trending-item">
                        <span class="trending-text"><?php echo htmlspecialchars($topic['suggestion']); ?></span>
                        <span class="trending-badge">Trending</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Search Content -->
        <div class="search-main">
            <!-- Search Header -->
            <div class="search-header">
                <h1>Comprehensive Search</h1>
                <p>Discover knowledge across articles, users, groups, events, and community content</p>
            </div>

            <!-- Search Form -->
            <div class="search-form-container">
                <form method="GET" class="search-form" id="searchForm">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($content_type); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                                   placeholder="Search everything..." class="search-input" id="searchInput" required>
                            <button type="button" class="clear-search" id="clearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="search-options">
                        <a href="/search/advanced" class="btn btn-secondary">
                            <i class="fas fa-cog"></i> Advanced Search
                        </a>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <div class="search-results-container" id="searchResults">
                <?php if (!empty($query)): ?>
                    <div class="search-loading" id="searchLoading">
                        <div class="spinner"></div>
                        <span>Searching...</span>
                    </div>
                    <div class="search-results" id="searchResultsContent" style="display: none;">
                        <!-- Results will be loaded via AJAX -->
                    </div>
                <?php else: ?>
                    <div class="search-welcome">
                        <div class="welcome-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Start Your Search</h3>
                        <p>Enter a search term above to discover content across our platform</p>
                        
                        <?php if (!empty($suggestions)): ?>
                        <div class="quick-suggestions">
                            <h4>Try searching for:</h4>
                            <div class="suggestion-tags">
                                <?php foreach (array_slice($suggestions, 0, 6) as $suggestion): ?>
                                <a href="?q=<?php echo urlencode($suggestion['suggestion']); ?>&type=<?php echo $suggestion['content_type'] ?? 'all'; ?>" 
                                   class="suggestion-tag">
                                    <?php echo htmlspecialchars($suggestion['suggestion']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Search JavaScript -->
<script>
class EnhancedSearch {
    constructor() {
        this.currentQuery = '';
        this.currentPage = 1;
        this.isLoading = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialResults();
    }

    bindEvents() {
        // Search form submission
        document.getElementById('searchForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Real-time search input
        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });

        // Clear search button
        document.getElementById('clearSearch').addEventListener('click', () => {
            this.clearSearch();
        });

        // Filter changes (no longer needed for content type as they're now links)

        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', () => {
                this.updateFilters();
            });
        });
    }

    handleSearchInput(query) {
        this.currentQuery = query;
        
        // Show/hide clear button
        const clearBtn = document.getElementById('clearSearch');
        clearBtn.style.display = query.length > 0 ? 'block' : 'none';

        // Debounce search
        clearTimeout(this.searchTimeout);
        if (query.length >= 2) {
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 500);
        } else if (query.length === 0) {
            this.showWelcome();
        }
    }

    async performSearch() {
        if (this.isLoading) return;

        const query = document.getElementById('searchInput').value.trim();
        if (!query) {
            this.showWelcome();
            return;
        }

        this.currentQuery = query;
        this.currentPage = 1;
        this.showLoading();

        try {
            const results = await this.fetchSearchResults(query, 1);
            this.displayResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Search failed. Please try again.');
        }
    }

    async fetchSearchResults(query, page = 1) {
        const formData = new FormData();
        formData.append('q', query);
        formData.append('type', this.getCurrentContentType());
        formData.append('category', document.querySelector('select[name="category"]')?.value || '');
        formData.append('sort', document.querySelector('select[name="sort"]').value);
        formData.append('page', page);
        formData.append('limit', 20);

        const response = await fetch('/search/enhanced-search-api.php?' + new URLSearchParams(formData));
        if (!response.ok) {
            throw new Error('Search request failed');
        }

        return await response.json();
    }

    displayResults(data) {
        const resultsContainer = document.getElementById('searchResultsContent');
        
        if (!data.success) {
            this.showError(data.error || 'Search failed');
            return;
        }

        // Track search analytics
        this.trackSearch(data.query, data.total_results);

        if (data.total_results === 0) {
            this.showNoResults(data.query, data.suggestions);
            return;
        }

        let html = `
            <div class="search-results-header">
                <h3>Search Results</h3>
                <div class="results-count">${data.total_results} results for "${data.query}"</div>
            </div>
        `;

        // Display results by content type
        Object.keys(data.results).forEach(contentType => {
            const results = data.results[contentType];
            if (results.length > 0) {
                html += this.renderContentTypeResults(contentType, results);
            }
        });

        resultsContainer.innerHTML = html;
        this.hideLoading();
        resultsContainer.style.display = 'block';
    }

    renderContentTypeResults(contentType, results) {
        const typeConfig = {
            articles: { title: 'Wiki Pages', icon: 'fas fa-book', color: '#3498db' },
            users: { title: 'People', icon: 'fas fa-users', color: '#e74c3c' },
            posts: { title: 'Posts', icon: 'fas fa-comment', color: '#2ecc71' },
            groups: { title: 'Groups', icon: 'fas fa-layer-group', color: '#9b59b6' },
            events: { title: 'Events', icon: 'fas fa-calendar', color: '#f39c12' },
            ummah: { title: 'Ummah', icon: 'fas fa-mosque', color: '#1abc9c' }
        };

        const config = typeConfig[contentType] || { title: contentType, icon: 'fas fa-file', color: '#95a5a6' };

        let html = `
            <div class="results-section">
                <h4 class="section-title">
                    <i class="${config.icon}" style="color: ${config.color}"></i>
                    ${config.title} (${results.length})
                </h4>
                <div class="results-list">
        `;

        results.forEach(result => {
            html += this.renderResultItem(contentType, result);
        });

        html += `
                </div>
            </div>
        `;

        return html;
    }

    renderResultItem(contentType, item) {
        switch (contentType) {
            case 'articles':
                return this.renderArticleResult(item);
            case 'users':
                return this.renderUserResult(item);
            case 'posts':
                return this.renderPostResult(item);
            case 'groups':
                return this.renderGroupResult(item);
            case 'events':
                return this.renderEventResult(item);
            case 'ummah':
                return this.renderUmmahResult(item);
            default:
                return '';
        }
    }

    renderArticleResult(article) {
        const excerpt = article.excerpt || article.content ? 
            article.content.substring(0, 200) + '...' : 'No description available';
        
        return `
            <a href="/wiki/${article.slug}" class="result-item article-result">
                <div class="result-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${article.title}
                    </h5>
                    <p class="result-excerpt" style="margin-bottom: 1.5rem; line-height: 1.7; font-size: 1rem;">${excerpt}</p>
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-category">${article.category_name || 'Uncategorized'}</span>
                        <span class="result-date">${new Date(article.published_at).toLocaleDateString()}</span>
                        <span class="result-views">${article.view_count} views</span>
                    </div>
                </div>
            </a>
        `;
    }

    renderUserResult(user) {
        return `
            <a href="/user/${user.username}" class="result-item user-result">
                <div class="result-avatar">
                    ${user.avatar ? 
                        `<img src="${user.avatar}" alt="${user.display_name}">` : 
                        `<div class="avatar-placeholder">${user.display_name.charAt(0).toUpperCase()}</div>`
                    }
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${user.display_name}
                    </h5>
                    <p class="result-username">@${user.username}</p>
                    ${user.bio ? `<p class="result-bio">${user.bio}</p>` : ''}
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-joined">Joined ${new Date(user.created_at).toLocaleDateString()}</span>
                    </div>
                </div>
            </a>
        `;
    }

    renderPostResult(post) {
        const content = post.content.length > 150 ? 
            post.content.substring(0, 150) + '...' : post.content;
        
        return `
            <a href="/posts/${post.id}" class="result-item post-result">
                <div class="result-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        Post by ${post.display_name}
                    </h5>
                    <p class="result-excerpt" style="margin-bottom: 1.5rem; line-height: 1.7; font-size: 1rem;">${content}</p>
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-date">${new Date(post.created_at).toLocaleDateString()}</span>
                        <span class="result-likes">${post.likes_count} likes</span>
                        ${post.group_name ? `<span class="result-group">in ${post.group_name}</span>` : ''}
                    </div>
                </div>
            </a>
        `;
    }

    renderGroupResult(group) {
        return `
            <a href="/groups/${group.slug}" class="result-item group-result">
                <div class="result-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${group.name}
                    </h5>
                    <p class="result-excerpt" style="margin-bottom: 1.5rem; line-height: 1.7; font-size: 1rem;">${group.description || 'No description available'}</p>
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-members">${group.members_count} members</span>
                        <span class="result-posts">${group.posts_count} posts</span>
                        <span class="result-type">${group.group_type}</span>
                    </div>
                </div>
            </a>
        `;
    }

    renderEventResult(event) {
        const startDate = new Date(event.start_date);
        const isOnline = event.event_type === 'online';
        
        return `
            <a href="/events/${event.slug}" class="result-item event-result">
                <div class="result-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${event.title}
                    </h5>
                    <p class="result-excerpt" style="margin-bottom: 1.5rem; line-height: 1.7; font-size: 1rem;">${event.description || 'No description available'}</p>
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-date">${startDate.toLocaleDateString()}</span>
                        <span class="result-time">${startDate.toLocaleTimeString()}</span>
                        <span class="result-location">${isOnline ? 'Online' : event.location}</span>
                        <span class="result-attendees">${event.current_attendees} attending</span>
                    </div>
                </div>
            </a>
        `;
    }

    renderUmmahResult(item) {
        const typeConfig = {
            featured_article: { icon: 'fas fa-star', title: 'Featured Article' },
            discussion: { icon: 'fas fa-comments', title: 'Community Discussion' },
            announcement: { icon: 'fas fa-bullhorn', title: 'Community Announcement' }
        };

        const config = typeConfig[item.content_type] || { icon: 'fas fa-file', title: 'Community Content' };

        return `
            <a href="/${item.content_type === 'featured_article' ? 'wiki/' + item.slug : 'posts/' + item.id}" class="result-item ummah-result">
                <div class="result-icon">
                    <i class="${config.icon}"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${item.title || item.content.substring(0, 50) + '...'}
                    </h5>
                    <p class="result-excerpt" style="margin-bottom: 1.5rem; line-height: 1.7; font-size: 1rem;">${item.excerpt || item.description || item.content.substring(0, 150) + '...'}</p>
                    <div class="result-meta" style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
                        <span class="result-type">${config.title}</span>
                        <span class="result-date">${new Date(item.created_at || item.published_at).toLocaleDateString()}</span>
                    </div>
                </div>
            </a>
        `;
    }

    showNoResults(query, suggestions) {
        const resultsContainer = document.getElementById('searchResultsContent');
        
        let html = `
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>No results found</h4>
                <p>No results found for "${query}"</p>
                <div class="no-results-suggestions">
                    <h5>Try these suggestions:</h5>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Try different keywords</li>
                        <li>Use more general terms</li>
                        <li>Try searching in different content types</li>
                    </ul>
                </div>
        `;

        if (suggestions && suggestions.length > 0) {
            html += `
                <div class="suggested-searches">
                    <h5>Popular searches:</h5>
                    <div class="suggestion-tags">
                        ${suggestions.slice(0, 5).map(s => 
                            `<a href="?q=${encodeURIComponent(s.suggestion)}&type=${s.content_type || 'all'}" class="suggestion-tag">${s.suggestion}</a>`
                        ).join('')}
                    </div>
                </div>
            `;
        }

        html += `</div>`;

        resultsContainer.innerHTML = html;
        this.hideLoading();
        resultsContainer.style.display = 'block';
    }

    showLoading() {
        this.isLoading = true;
        document.getElementById('searchLoading').style.display = 'block';
        document.getElementById('searchResultsContent').style.display = 'none';
    }

    hideLoading() {
        this.isLoading = false;
        document.getElementById('searchLoading').style.display = 'none';
    }

    showError(message) {
        const resultsContainer = document.getElementById('searchResultsContent');
        resultsContainer.innerHTML = `
            <div class="search-error">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Search Error</h4>
                <p>${message}</p>
            </div>
        `;
        this.hideLoading();
        resultsContainer.style.display = 'block';
    }

    showWelcome() {
        document.getElementById('searchResultsContent').style.display = 'none';
        document.querySelector('.search-welcome').style.display = 'block';
    }

    clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('clearSearch').style.display = 'none';
        this.showWelcome();
    }

    trackSearch(query, resultsCount) {
        // Track search analytics
        fetch('/api/ajax/track_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                query: query,
                results_count: resultsCount
            })
        }).catch(error => {
            console.error('Search tracking error:', error);
        });
    }

    getCurrentContentType() {
        // Get content type from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('type') || 'all';
    }

    updateFilters() {
        const form = document.getElementById('searchForm');
        const formData = new FormData(form);
        
        // Update hidden inputs
        form.querySelector('input[name="type"]').value = this.getCurrentContentType();
        form.querySelector('input[name="category"]').value = document.querySelector('select[name="category"]')?.value || '';
        form.querySelector('input[name="sort"]').value = document.querySelector('select[name="sort"]').value;

        // Perform search if there's a query
        if (this.currentQuery) {
            this.performSearch();
        }
    }

    loadInitialResults() {
        // Check if there's a query parameter in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const urlQuery = urlParams.get('q');
        
        if (urlQuery) {
            // Set the search input value from URL parameter
            document.getElementById('searchInput').value = urlQuery;
            this.performSearch();
        } else {
            // Check if there's a value in the search input
            const query = document.getElementById('searchInput').value.trim();
            if (query) {
                this.performSearch();
            }
        }
    }
}

// Initialize search when page loads
document.addEventListener('DOMContentLoaded', () => {
    new EnhancedSearch();
});

// Clear filters function
function clearFilters() {
    // Reset category and sort filters
    document.querySelectorAll('.filter-select').forEach(select => {
        select.selectedIndex = 0;
    });
    
    // Redirect to all content type
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('q') || '';
    window.location.href = `?q=${encodeURIComponent(query)}&type=all`;
}
</script>

<style>
.search-options {
    margin-top: 1rem;
    text-align: center;
}

.search-options .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: background 0.3s;
}

.search-options .btn:hover {
    background: #545b62;
    color: white;
    text-decoration: none;
}
</style>

<?php include "../includes/footer.php"; ?>
