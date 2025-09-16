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
            articles: { title: 'Wiki Pages', icon: 'iw iw-book', color: '#3498db' },
            users: { title: 'People', icon: 'iw iw-users', color: '#e74c3c' },
            posts: { title: 'Posts', icon: 'iw iw-comment', color: '#2ecc71' },
            groups: { title: 'Groups', icon: 'iw iw-layer-group', color: '#9b59b6' },
            events: { title: 'Events', icon: 'iw iw-calendar', color: '#f39c12' },
            ummah: { title: 'Ummah', icon: 'iw iw-mosque', color: '#1abc9c' }
        };

        const config = typeConfig[contentType] || { title: contentType, icon: 'iw iw-file', color: '#95a5a6' };

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
                    <i class="iw iw-book"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${article.title}
                    </h5>
                    <p class="result-excerpt" >${excerpt}</p>
                    <div class="result-meta" >
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
                    <div class="result-meta" >
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
                    <i class="iw iw-comment"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        Post by ${post.display_name}
                    </h5>
                    <p class="result-excerpt" >${content}</p>
                    <div class="result-meta" >
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
                    <i class="iw iw-layer-group"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${group.name}
                    </h5>
                    <p class="result-excerpt" >${group.description || 'No description available'}</p>
                    <div class="result-meta" >
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
                    <i class="iw iw-calendar"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${event.title}
                    </h5>
                    <p class="result-excerpt" >${event.description || 'No description available'}</p>
                    <div class="result-meta" >
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
            featured_article: { icon: 'iw iw-star', title: 'Featured Article' },
            discussion: { icon: 'iw iw-comments', title: 'Community Discussion' },
            announcement: { icon: 'iw iw-bullhorn', title: 'Community Announcement' }
        };

        const config = typeConfig[item.content_type] || { icon: 'iw iw-file', title: 'Community Content' };

        return `
            <a href="/${item.content_type === 'featured_article' ? 'wiki/' + item.slug : 'posts/' + item.id}" class="result-item ummah-result">
                <div class="result-icon">
                    <i class="${config.icon}"></i>
                </div>
                <div class="result-content">
                    <h5 class="result-title">
                        ${item.title || item.content.substring(0, 50) + '...'}
                    </h5>
                    <p class="result-excerpt" >${item.excerpt || item.description || item.content.substring(0, 150) + '...'}</p>
                    <div class="result-meta" >
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
                    <i class="iw iw-search"></i>
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
                    <i class="iw iw-exclamation-triangle"></i>
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
