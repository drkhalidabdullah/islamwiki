// Infinite scroll functionality for search results
class InfiniteScroll {
    constructor(container, loadMoreUrl, options = {}) {
        this.container = container;
        this.loadMoreUrl = loadMoreUrl;
        this.options = {
            threshold: 100, // Load more when 100px from bottom
            loadingText: 'Loading more results...',
            noMoreText: 'No more results',
            errorText: 'Error loading results',
            ...options
        };
        
        this.currentPage = 1;
        this.isLoading = false;
        this.hasMore = true;
        this.query = '';
        this.filters = {};
        
        this.init();
    }
    
    init() {
        this.setupIntersectionObserver();
        this.bindEvents();
    }
    
    setupIntersectionObserver() {
        // Create loading indicator
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'infinite-scroll-loading';
        this.loadingIndicator.innerHTML = `
            <div class="spinner"></div>
            ${this.options.loadingText}
        `;
        this.loadingIndicator.style.display = 'none';
        
        // Add loading indicator to container
        this.container.appendChild(this.loadingIndicator);
        
        // Create intersection observer
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.hasMore && !this.isLoading) {
                    this.loadMore();
                }
            });
        }, {
            rootMargin: `${this.options.threshold}px`
        });
        
        this.observer.observe(this.loadingIndicator);
    }
    
    bindEvents() {
        // Listen for search form submissions
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                this.reset();
            });
        }
        
        // Listen for filter changes
        const filterInputs = document.querySelectorAll('.search-filters input, .search-filters select');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.reset();
            });
        });
    }
    
    reset() {
        this.currentPage = 1;
        this.hasMore = true;
        this.isLoading = false;
        this.loadingIndicator.style.display = 'none';
        this.loadingIndicator.innerHTML = `
            <div class="spinner"></div>
            ${this.options.loadingText}
        `;
    }
    
    setQuery(query, filters = {}) {
        this.query = query;
        this.filters = filters;
        this.reset();
    }
    
    async loadMore() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        this.loadingIndicator.style.display = 'block';
        
        try {
            const nextPage = this.currentPage + 1;
            const params = new URLSearchParams({
                q: this.query,
                page: nextPage,
                ...this.filters
            });
            
            const response = await fetch(`${this.loadMoreUrl}?${params}`);
            const data = await response.json();
            
            if (data.success && data.results) {
                this.appendResults(data.results);
                this.currentPage = nextPage;
                
                // Check if there are more results
                if (data.results.length < (data.limit || 20)) {
                    this.hasMore = false;
                    this.loadingIndicator.innerHTML = this.options.noMoreText;
                }
            } else {
                this.hasMore = false;
                this.loadingIndicator.innerHTML = this.options.errorText;
            }
        } catch (error) {
            console.error('Error loading more results:', error);
            this.hasMore = false;
            this.loadingIndicator.innerHTML = this.options.errorText;
        } finally {
            this.isLoading = false;
        }
    }
    
    appendResults(results) {
        const resultsContainer = this.container.querySelector('.search-results');
        if (!resultsContainer) return;
        
        results.forEach(result => {
            const resultElement = this.createResultElement(result);
            resultsContainer.appendChild(resultElement);
        });
        
        // Re-highlight search terms
        if (this.query) {
            this.highlightResults(this.query);
        }
    }
    
    createResultElement(result) {
        const div = document.createElement('div');
        div.className = 'result-item';
        
        const icon = this.getResultIcon(result.type);
        const date = this.formatDate(result.published_at || result.created_at);
        
        div.innerHTML = `
            <div class="result-icon">
                <i class="${icon}"></i>
            </div>
            <div class="result-content">
                <h4><a href="${result.url}">${result.title || result.text}</a></h4>
                <div class="result-excerpt">${result.excerpt || result.content}</div>
                <div class="result-meta">
                    <span class="result-type">${result.type}</span>
                    ${result.author ? `<span class="result-author">by ${result.author}</span>` : ''}
                    <span class="result-date">${date}</span>
                    ${result.views ? `<span class="result-views">${result.views} views</span>` : ''}
                </div>
            </div>
        `;
        
        return div;
    }
    
    getResultIcon(type) {
        const icons = {
            'article': 'iw iw-book',
            'user': 'iw iw-user',
            'message': 'iw iw-comment',
            'category': 'iw iw-folder'
        };
        return icons[type] || 'iw iw-file';
    }
    
    formatDate(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return `${diffDays} days ago`;
        if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`;
        if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months ago`;
        return `${Math.ceil(diffDays / 365)} years ago`;
    }
    
    highlightResults(query) {
        if (!query) return;
        
        const terms = query.toLowerCase().split(/\s+/).filter(term => term.length > 1);
        if (terms.length === 0) return;
        
        // Highlight in result titles
        this.container.querySelectorAll('.result-item h4 a').forEach(link => {
            const originalText = link.textContent;
            link.innerHTML = this.highlightText(originalText, terms);
        });
        
        // Highlight in result excerpts
        this.container.querySelectorAll('.result-excerpt').forEach(excerpt => {
            const originalText = excerpt.textContent;
            excerpt.innerHTML = this.highlightText(originalText, terms);
        });
    }
    
    highlightText(text, terms) {
        let highlightedText = text;
        
        terms.forEach(term => {
            const regex = new RegExp(`(${this.escapeRegExp(term)})`, 'gi');
            highlightedText = highlightedText.replace(regex, '<mark class="search-highlight">$1</mark>');
        });
        
        return highlightedText;
    }
    
    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
}

// Initialize infinite scroll when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const resultsContainer = document.querySelector('.search-results-container');
    if (resultsContainer) {
        const infiniteScroll = new InfiniteScroll(
            resultsContainer,
            '/search/api/load-more',
            {
                threshold: 200,
                loadingText: 'Loading more results...',
                noMoreText: 'No more results to load',
                errorText: 'Error loading more results'
            }
        );
        
        // Set initial query and filters
        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('q') || '';
        const filters = {
            type: urlParams.get('type') || 'all',
            category: urlParams.get('category') || '',
            sort: urlParams.get('sort') || 'relevance',
            date_range: urlParams.get('date_range') || '',
            author: urlParams.get('author') || ''
        };
        
        infiniteScroll.setQuery(query, filters);
    }
});
