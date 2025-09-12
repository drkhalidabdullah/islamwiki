/**
 * Enhanced Search Overlay with Dynamic Suggestions
 * Provides real-time search suggestions with categorized results
 */

class EnhancedSearchOverlay {
    constructor() {
        this.searchInput = null;
        this.suggestionsContainer = null;
        this.currentQuery = '';
        this.searchTimeout = null;
        this.isOpen = false;
        this.cache = new Map();
        this.init();
    }

    init() {
        console.log('Enhanced Search Overlay initializing...');
        this.bindEvents();
        this.createSearchOverlay();
        console.log('Enhanced Search Overlay initialized');
    }

    bindEvents() {
        // Listen for search trigger clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.search-trigger')) {
                console.log('Search trigger clicked');
                e.preventDefault();
                this.openSearchOverlay();
            }
        });

        // Listen for ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeSearchOverlay();
            }
        });

        // Listen for / key to open search
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !e.target.matches('input, textarea, [contenteditable]') && !this.isOpen) {
                e.preventDefault();
                this.openSearchOverlay();
            }
        });
    }

    createSearchOverlay() {
        // Create the enhanced search overlay
        const overlay = document.createElement('div');
        overlay.className = 'enhanced-search-overlay';
        overlay.innerHTML = `
            <div class="search-overlay-backdrop"></div>
            <div class="search-overlay-content">
                <div class="search-overlay-header">
                    <div class="search-input-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               id="enhancedSearchInput" 
                               class="search-overlay-input" 
                               placeholder="Search articles, users, content..."
                               autocomplete="off">
                        <button class="search-clear-btn" id="searchClearBtn" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="search-overlay-body">
                    <div class="search-suggestions-container" id="searchSuggestionsContainer">
                        <div class="search-welcome">
                            <div class="welcome-content">
                                <h3>Welcome to ${document.title.split(' - ')[0] || 'MuslimWiki'}</h3>
                                <p>Start typing to search for articles, users, and content</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        console.log('Search overlay created and added to DOM');
        console.log('Overlay element:', overlay);
        console.log('Overlay parent:', overlay.parentNode);
        
        // Debug mode - make overlay visible for testing
        if (window.location.search.includes('debug=1')) {
            overlay.classList.add('debug');
            console.log('Debug mode enabled - overlay should be visible');
        }

        // Get references to elements
        this.searchInput = document.getElementById('enhancedSearchInput');
        this.suggestionsContainer = document.getElementById('searchSuggestionsContainer');
        this.clearBtn = document.getElementById('searchClearBtn');
        
        console.log('Search elements found:', {
            searchInput: !!this.searchInput,
            suggestionsContainer: !!this.suggestionsContainer,
            clearBtn: !!this.clearBtn
        });

        // Bind input events
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearchInput(e.target.value);
        });

        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeyNavigation(e);
        });

        this.clearBtn.addEventListener('click', () => {
            this.clearSearch();
        });

        // Close on backdrop click
        overlay.querySelector('.search-overlay-backdrop').addEventListener('click', () => {
            this.closeSearchOverlay();
        });
    }

    openSearchOverlay() {
        console.log('Opening search overlay...');
        const overlay = document.querySelector('.enhanced-search-overlay');
        if (!overlay) {
            console.error('Search overlay not found!');
            return;
        }
        
        console.log('Overlay found, adding show class');
        overlay.classList.add('show');
        this.isOpen = true;
        console.log('Search overlay opened, isOpen:', this.isOpen);
        console.log('Overlay classes:', overlay.className);
        
        // Focus on input
        setTimeout(() => {
            if (this.searchInput) {
                this.searchInput.focus();
                console.log('Search input focused');
            }
        }, 100);

        // Load initial suggestions
        this.loadInitialSuggestions();
    }

    closeSearchOverlay() {
        const overlay = document.querySelector('.enhanced-search-overlay');
        overlay.classList.remove('show');
        this.isOpen = false;
        this.searchInput.value = '';
        this.clearBtn.style.display = 'none';
        this.showWelcome();
    }

    handleSearchInput(query) {
        this.currentQuery = query.trim();
        
        // Show/hide clear button
        this.clearBtn.style.display = query.length > 0 ? 'block' : 'none';

        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        if (query.length === 0) {
            this.showWelcome();
        } else if (query.length >= 2) {
            // Debounce search
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        }
    }

    async performSearch(query) {
        try {
            this.showLoading();
            
            // Check cache first
            if (this.cache.has(query)) {
                this.displayResults(this.cache.get(query));
                return;
            }

            // Fetch search suggestions
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            // Cache results
            this.cache.set(query, data);
            
            this.displayResults(data);
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Failed to load search suggestions');
        }
    }

    displayResults(data) {
        const container = this.suggestionsContainer;
        
        if (!data || (!data.topArticles && !data.newestArticles && !data.didYouKnow)) {
            this.showNoResults();
            return;
        }

        container.innerHTML = `
            <div class="search-results">
                ${this.renderTopSuggestions(data.topSuggestions || [])}
                <div class="search-tabs">
                    <button class="search-tab active" data-tab="top">Top Articles</button>
                    <button class="search-tab" data-tab="newest">Newest Articles</button>
                    <button class="search-tab" data-tab="facts">Did You Know?</button>
                    <button class="search-tab" data-tab="actions">Actions</button>
                </div>
                <div class="search-tab-content">
                    <div class="tab-pane active" id="top-articles">
                        ${this.renderArticles(data.topArticles || [])}
                    </div>
                    <div class="tab-pane" id="newest-articles">
                        ${this.renderArticles(data.newestArticles || [])}
                    </div>
                    <div class="tab-pane" id="did-you-know">
                        ${this.renderFacts(data.didYouKnow || [])}
                    </div>
                    <div class="tab-pane" id="actions">
                        ${this.renderActions()}
                    </div>
                </div>
            </div>
        `;

        // Bind tab events
        this.bindTabEvents();
    }

    renderTopSuggestions(suggestions) {
        if (!suggestions.length) return '';

        return `
            <div class="top-suggestions">
                ${suggestions.map(item => `
                    <div class="suggestion-item">
                        <div class="suggestion-icon">
                            ${item.icon ? `<i class="${item.icon}"></i>` : '<i class="fas fa-file-alt"></i>'}
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

    renderArticles(articles) {
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
                                <span class="article-category">${article.category || 'General'}</span>
                                <span class="article-date">${article.date || ''}</span>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderFacts(facts) {
        if (!facts.length) {
            return '<div class="no-results">No facts available</div>';
        }

        return `
            <div class="facts-list">
                ${facts.map(fact => `
                    <div class="fact-item">
                        <div class="fact-content">${fact.content}</div>
                        ${fact.source ? `<div class="fact-source">Source: ${fact.source}</div>` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderActions() {
        return `
            <div class="actions-list">
                <div class="action-item" onclick="window.location.href='/search?q=${encodeURIComponent(this.currentQuery)}'">
                    <i class="fas fa-search"></i>
                    <span>Search for "${this.currentQuery}"</span>
                </div>
                <div class="action-item" onclick="window.location.href='/create_article'">
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

    bindTabEvents() {
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

    showWelcome() {
        this.suggestionsContainer.innerHTML = `
            <div class="search-welcome">
                <div class="welcome-content">
                    <h3>Welcome to ${document.title.split(' - ')[0] || 'MuslimWiki'}</h3>
                    <p>Start typing to search for articles, users, and content</p>
                    <div class="welcome-suggestions">
                        <div class="suggestion-category">
                            <h4>Popular Searches</h4>
                            <div class="suggestion-tags">
                                <span class="suggestion-tag" onclick="this.searchFor('Allah')">Allah</span>
                                <span class="suggestion-tag" onclick="this.searchFor('Quran')">Quran</span>
                                <span class="suggestion-tag" onclick="this.searchFor('Muhammad')">Muhammad</span>
                                <span class="suggestion-tag" onclick="this.searchFor('Islam')">Islam</span>
                                <span class="suggestion-tag" onclick="this.searchFor('Prayer')">Prayer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    showLoading() {
        this.suggestionsContainer.innerHTML = `
            <div class="search-loading">
                <div class="loading-spinner"></div>
                <p>Searching...</p>
            </div>
        `;
    }

    showNoResults() {
        this.suggestionsContainer.innerHTML = `
            <div class="search-no-results">
                <i class="fas fa-search"></i>
                <h3>No results found</h3>
                <p>Try different keywords or check your spelling</p>
            </div>
        `;
    }

    showError(message) {
        this.suggestionsContainer.innerHTML = `
            <div class="search-error">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Search Error</h3>
                <p>${message}</p>
            </div>
        `;
    }

    clearSearch() {
        this.searchInput.value = '';
        this.clearBtn.style.display = 'none';
        this.showWelcome();
    }

    handleKeyNavigation(e) {
        // Handle arrow keys for navigation
        // This can be enhanced for keyboard navigation
    }

    async loadInitialSuggestions() {
        try {
            const response = await fetch('/api/search/initial-suggestions');
            const data = await response.json();
            
            if (data.suggestions) {
                this.displayResults(data);
            }
        } catch (error) {
            console.error('Failed to load initial suggestions:', error);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing EnhancedSearchOverlay...');
    window.searchOverlay = new EnhancedSearchOverlay();
    console.log('EnhancedSearchOverlay initialized:', window.searchOverlay);
});

// Global test function
window.testSearchOverlay = function() {
    console.log('Testing search overlay...');
    if (window.searchOverlay) {
        window.searchOverlay.openSearchOverlay();
    } else {
        console.error('Search overlay not initialized');
    }
};
