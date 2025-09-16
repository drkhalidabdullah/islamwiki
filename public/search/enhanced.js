// Enhanced Search Interface with AI and Advanced Features
class EnhancedSearchInterface {
    constructor() {
        this.currentQuery = '';
        this.searchResults = [];
        this.clusters = [];
        this.insights = [];
        this.recommendations = [];
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeComponents();
    }
    
    setupEventListeners() {
        // Search form submission
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.performEnhancedSearch();
            });
        }
        
        // Real-time search suggestions
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });
        }
        
        // Filter changes
        const filterInputs = document.querySelectorAll('.search-filters input, .search-filters select');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.performEnhancedSearch();
            });
        });
    }
    
    initializeComponents() {
        // Initialize auto-complete
        this.initializeAutocomplete();
        
        // Initialize semantic search
        this.initializeSemanticSearch();
        
        // Initialize clustering
        this.initializeClustering();
        
        // Initialize insights
        this.initializeInsights();
    }
    
    async handleSearchInput(query) {
        if (query.length < 2) {
            this.hideAdvancedFeatures();
            return;
        }
        
        this.currentQuery = query;
        
        // Debounce the search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.performSemanticSearch(query);
        }, 300);
    }
    
    async performEnhancedSearch() {
        const form = document.querySelector('.search-form');
        const formData = new FormData(form);
        const query = formData.get('q');
        
        if (!query || query.length < 2) {
            return;
        }
        
        this.currentQuery = query;
        this.showLoadingState();
        
        try {
            // Perform multiple searches in parallel
            const [searchResults, clusters, insights, recommendations] = await Promise.all([
                this.performBasicSearch(formData),
                this.performClusteringSearch(query),
                this.performInsightsSearch(query),
                this.performRecommendationsSearch(query)
            ]);
            
            this.searchResults = searchResults;
            this.clusters = clusters;
            this.insights = insights;
            this.recommendations = recommendations;
            
            this.displayEnhancedResults();
            
        } catch (error) {
            console.error('Enhanced search error:', error);
            this.showErrorState();
        } finally {
            this.hideLoadingState();
        }
    }
    
    async performBasicSearch(formData) {
        const params = new URLSearchParams(formData);
        const response = await fetch(`/search?${params}`);
        const data = await response.json();
        return data.results || [];
    }
    
    async performSemanticSearch(query) {
        try {
            const response = await fetch(`/search/semantic?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                this.displaySemanticSuggestions(data);
            }
        } catch (error) {
            console.error('Semantic search error:', error);
        }
    }
    
    async performClusteringSearch(query) {
        try {
            const response = await fetch(`/search/clustering?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.clusters || [];
            }
        } catch (error) {
            console.error('Clustering search error:', error);
        }
        
        return [];
    }
    
    async performInsightsSearch(query) {
        try {
            const response = await fetch(`/search/insights?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.insights || [];
            }
        } catch (error) {
            console.error('Insights search error:', error);
        }
        
        return [];
    }
    
    async performRecommendationsSearch(query) {
        try {
            const response = await fetch(`/search/insights?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                return data.recommendations || [];
            }
        } catch (error) {
            console.error('Recommendations search error:', error);
        }
        
        return [];
    }
    
    displaySemanticSuggestions(data) {
        const suggestionsContainer = document.querySelector('.semantic-suggestions');
        if (!suggestionsContainer) return;
        
        let html = '';
        
        if (data.semantic_suggestions && data.semantic_suggestions.length > 0) {
            html += '<div class="semantic-section">';
            html += '<h4>Semantic Suggestions</h4>';
            html += '<div class="semantic-suggestions-list">';
            
            data.semantic_suggestions.forEach(suggestion => {
                html += `
                    <div class="semantic-suggestion" onclick="window.location.href='${suggestion.url}'">
                        <i class="${suggestion.icon || 'iw iw-search'}"></i>
                        <div class="suggestion-content">
                            <div class="suggestion-title">${suggestion.title}</div>
                            <div class="suggestion-excerpt">${suggestion.excerpt}</div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
        }
        
        if (data.related_topics && data.related_topics.length > 0) {
            html += '<div class="related-topics-section">';
            html += '<h4>Related Topics</h4>';
            html += '<div class="related-topics-list">';
            
            data.related_topics.forEach(topic => {
                html += `
                    <div class="related-topic" onclick="window.location.href='${topic.url}'">
                        <i class="${topic.icon || 'iw iw-folder'}"></i>
                        <div class="topic-content">
                            <div class="topic-title">${topic.title}</div>
                            <div class="topic-description">${topic.description}</div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
        }
        
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = html ? 'block' : 'none';
    }
    
    displayEnhancedResults() {
        this.displaySearchResults();
        this.displayClusters();
        this.displayInsights();
        this.displayRecommendations();
    }
    
    displaySearchResults() {
        const resultsContainer = document.querySelector('.search-results');
        if (!resultsContainer) return;
        
        if (this.searchResults.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found for your search.</div>';
            return;
        }
        
        let html = '<div class="search-results-header">';
        html += `<h3>Search Results (${this.searchResults.length})</h3>`;
        html += '</div>';
        html += '<div class="results-list">';
        
        this.searchResults.forEach(result => {
            html += this.createResultHTML(result);
        });
        
        html += '</div>';
        
        resultsContainer.innerHTML = html;
    }
    
    displayClusters() {
        const clustersContainer = document.querySelector('.search-clusters');
        if (!clustersContainer || this.clusters.length === 0) return;
        
        let html = '<div class="clusters-header">';
        html += `<h3>Organized Results (${this.clusters.length} clusters)</h3>`;
        html += '</div>';
        html += '<div class="clusters-list">';
        
        this.clusters.forEach(cluster => {
            html += `
                <div class="cluster-item">
                    <div class="cluster-header">
                        <i class="${cluster.icon}"></i>
                        <h4>${cluster.title}</h4>
                        <span class="cluster-size">${cluster.cluster_size} items</span>
                    </div>
                    <div class="cluster-description">${cluster.description}</div>
                    <div class="cluster-results">
                        ${cluster.results.slice(0, 3).map(result => this.createResultHTML(result)).join('')}
                        ${cluster.results.length > 3 ? `<div class="show-more" onclick="this.showAllClusterResults('${cluster.title}')">Show ${cluster.results.length - 3} more...</div>` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        clustersContainer.innerHTML = html;
        clustersContainer.style.display = 'block';
    }
    
    displayInsights() {
        const insightsContainer = document.querySelector('.search-insights');
        if (!insightsContainer || this.insights.length === 0) return;
        
        let html = '<div class="insights-header">';
        html += `<h3>Search Insights</h3>`;
        html += '</div>';
        html += '<div class="insights-list">';
        
        this.insights.forEach(insight => {
            html += `
                <div class="insight-item priority-${insight.priority}">
                    <div class="insight-icon">
                        <i class="${insight.icon}"></i>
                    </div>
                    <div class="insight-content">
                        <h4>${insight.title}</h4>
                        <p>${insight.description}</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        insightsContainer.innerHTML = html;
        insightsContainer.style.display = 'block';
    }
    
    displayRecommendations() {
        const recommendationsContainer = document.querySelector('.search-recommendations');
        if (!recommendationsContainer || this.recommendations.length === 0) return;
        
        let html = '<div class="recommendations-header">';
        html += `<h3>Recommendations</h3>`;
        html += '</div>';
        html += '<div class="recommendations-list">';
        
        this.recommendations.forEach(recommendation => {
            html += `
                <div class="recommendation-item" onclick="window.location.href='${recommendation.url}'">
                    <div class="recommendation-icon">
                        <i class="${recommendation.icon}"></i>
                    </div>
                    <div class="recommendation-content">
                        <h4>${recommendation.title}</h4>
                        <p>${recommendation.description}</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        recommendationsContainer.innerHTML = html;
        recommendationsContainer.style.display = 'block';
    }
    
    createResultHTML(result) {
        const date = this.formatDate(result.published_at || result.created_at);
        const icon = this.getResultIcon(result.type);
        
        return `
            <div class="result-item">
                <div class="result-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="result-content">
                    <h4><a href="${result.url}">${result.title}</a></h4>
                    <div class="result-excerpt">${result.excerpt || result.content}</div>
                    <div class="result-meta">
                        <span class="result-type">${result.type}</span>
                        ${result.author ? `<span class="result-author">by ${result.author}</span>` : ''}
                        <span class="result-date">${date}</span>
                        ${result.views ? `<span class="result-views">${result.views} views</span>` : ''}
                    </div>
                </div>
            </div>
        `;
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
    
    showLoadingState() {
        this.isLoading = true;
        const loadingElement = document.querySelector('.search-loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
    }
    
    hideLoadingState() {
        this.isLoading = false;
        const loadingElement = document.querySelector('.search-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }
    
    showErrorState() {
        const errorElement = document.querySelector('.search-error');
        if (errorElement) {
            errorElement.style.display = 'block';
        }
    }
    
    hideAdvancedFeatures() {
        const containers = [
            '.semantic-suggestions',
            '.search-clusters',
            '.search-insights',
            '.search-recommendations'
        ];
        
        containers.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                element.style.display = 'none';
            }
        });
    }
    
    initializeAutocomplete() {
        // Initialize the existing autocomplete functionality
        const searchInputs = document.querySelectorAll('.search-input, input[name="q"]');
        searchInputs.forEach(input => {
            const suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions';
            input.parentNode.appendChild(suggestionsContainer);
            
            new SearchAutocomplete(input, suggestionsContainer);
        });
    }
    
    initializeSemanticSearch() {
        // Create semantic suggestions container
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            const semanticContainer = document.createElement('div');
            semanticContainer.className = 'semantic-suggestions';
            semanticContainer.style.display = 'none';
            searchForm.appendChild(semanticContainer);
        }
    }
    
    initializeClustering() {
        // Create clusters container
        const searchPage = document.querySelector('.search-page');
        if (searchPage) {
            const clustersContainer = document.createElement('div');
            clustersContainer.className = 'search-clusters';
            clustersContainer.style.display = 'none';
            searchPage.appendChild(clustersContainer);
        }
    }
    
    initializeInsights() {
        // Create insights container
        const searchPage = document.querySelector('.search-page');
        if (searchPage) {
            const insightsContainer = document.createElement('div');
            insightsContainer.className = 'search-insights';
            insightsContainer.style.display = 'none';
            searchPage.appendChild(insightsContainer);
            
            const recommendationsContainer = document.createElement('div');
            recommendationsContainer.className = 'search-recommendations';
            recommendationsContainer.style.display = 'none';
            searchPage.appendChild(recommendationsContainer);
        }
    }
}

// Initialize enhanced search interface when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new EnhancedSearchInterface();
});
