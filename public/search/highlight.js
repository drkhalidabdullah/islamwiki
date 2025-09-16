// Search term highlighting functionality
function highlightSearchTerms(text, query) {
    if (!query || !text) return text;
    
    // Split query into individual terms
    const terms = query.toLowerCase().split(/\s+/).filter(term => term.length > 1);
    
    if (terms.length === 0) return text;
    
    let highlightedText = text;
    
    terms.forEach(term => {
        // Create regex for case-insensitive matching
        const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
        highlightedText = highlightedText.replace(regex, '<mark class="search-highlight">$1</mark>');
    });
    
    return highlightedText;
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightResults(query) {
    if (!query) return;
    
    // Highlight in result titles
    document.querySelectorAll('.result-item h4 a').forEach(link => {
        const originalText = link.textContent;
        link.innerHTML = highlightSearchTerms(originalText, query);
    });
    
    // Highlight in result excerpts
    document.querySelectorAll('.result-excerpt').forEach(excerpt => {
        const originalText = excerpt.textContent;
        excerpt.innerHTML = highlightSearchTerms(originalText, query);
    });
    
    // Highlight in user bios
    document.querySelectorAll('.user-bio').forEach(bio => {
        const originalText = bio.textContent;
        bio.innerHTML = highlightSearchTerms(originalText, query);
    });
    
    // Highlight in message content
    document.querySelectorAll('.message-content').forEach(content => {
        const originalText = content.textContent;
        content.innerHTML = highlightSearchTerms(originalText, query);
    });
}

// Auto-complete functionality
class SearchAutocomplete {
    constructor(input, suggestionsContainer) {
        this.input = input;
        this.suggestionsContainer = suggestionsContainer;
        this.currentQuery = '';
        this.debounceTimer = null;
        this.selectedIndex = -1;
        
        this.init();
    }
    
    init() {
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        this.input.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        this.input.addEventListener('blur', () => {
            // Delay hiding suggestions to allow clicking
            setTimeout(() => {
                this.hideSuggestions();
            }, 200);
        });
        
        this.input.addEventListener('focus', () => {
            if (this.input.value.length >= 2) {
                this.showSuggestions();
            }
        });
    }
    
    handleInput(query) {
        this.currentQuery = query;
        
        // Clear previous timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        // Debounce the search
        this.debounceTimer = setTimeout(() => {
            if (query.length >= 2) {
                this.fetchSuggestions(query);
            } else {
                this.hideSuggestions();
            }
        }, 300);
    }
    
    handleKeydown(e) {
        const suggestions = this.suggestionsContainer.querySelectorAll('.suggestion-item');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, suggestions.length - 1);
                this.updateSelection();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && suggestions[this.selectedIndex]) {
                    this.selectSuggestion(suggestions[this.selectedIndex]);
                } else {
                    this.input.form.submit();
                }
                break;
                
            case 'Escape':
                this.hideSuggestions();
                this.input.blur();
                break;
        }
    }
    
    async fetchSuggestions(query) {
        try {
            const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}&limit=8`);
            const data = await response.json();
            
            if (data.suggestions && data.suggestions.length > 0) {
                this.displaySuggestions(data.suggestions);
            } else {
                this.hideSuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            this.hideSuggestions();
        }
    }
    
    displaySuggestions(suggestions) {
        this.suggestionsContainer.innerHTML = '';
        
        suggestions.forEach((suggestion, index) => {
            const suggestionElement = this.createSuggestionElement(suggestion, index);
            this.suggestionsContainer.appendChild(suggestionElement);
        });
        
        this.showSuggestions();
        this.selectedIndex = -1;
    }
    
    createSuggestionElement(suggestion, index) {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.dataset.index = index;
        
        const icon = this.getSuggestionIcon(suggestion.type);
        const category = suggestion.category || suggestion.type;
        
        div.innerHTML = `
            <div class="suggestion-icon">
                <i class="${icon}"></i>
            </div>
            <div class="suggestion-content">
                <div class="suggestion-title">${this.highlightText(suggestion.text, this.currentQuery)}</div>
                ${suggestion.subtext ? `<div class="suggestion-subtitle">${suggestion.subtext}</div>` : ''}
                ${suggestion.excerpt ? `<div class="suggestion-excerpt">${suggestion.excerpt}</div>` : ''}
                <div class="suggestion-category">${category}</div>
            </div>
        `;
        
        div.addEventListener('click', () => {
            this.selectSuggestion(div);
        });
        
        return div;
    }
    
    getSuggestionIcon(type) {
        const icons = {
            'article': 'iw iw-book',
            'user': 'iw iw-user',
            'category': 'iw iw-folder',
            'popular': 'iw iw-fire'
        };
        return icons[type] || 'iw iw-search';
    }
    
    highlightText(text, query) {
        if (!query) return text;
        
        const terms = query.toLowerCase().split(/\s+/).filter(term => term.length > 1);
        let highlightedText = text;
        
        terms.forEach(term => {
            const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
            highlightedText = highlightedText.replace(regex, '<mark class="suggestion-highlight">$1</mark>');
        });
        
        return highlightedText;
    }
    
    updateSelection() {
        const suggestions = this.suggestionsContainer.querySelectorAll('.suggestion-item');
        
        suggestions.forEach((suggestion, index) => {
            if (index === this.selectedIndex) {
                suggestion.classList.add('selected');
            } else {
                suggestion.classList.remove('selected');
            }
        });
    }
    
    selectSuggestion(suggestionElement) {
        const suggestion = suggestionElement.dataset;
        const url = suggestionElement.querySelector('a')?.href || `/search?q=${encodeURIComponent(this.currentQuery)}`;
        
        window.location.href = url;
    }
    
    showSuggestions() {
        this.suggestionsContainer.style.display = 'block';
    }
    
    hideSuggestions() {
        this.suggestionsContainer.style.display = 'none';
        this.selectedIndex = -1;
    }
}

// Initialize search functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize auto-complete for search inputs
    const searchInputs = document.querySelectorAll('.search-input, input[name="q"]');
    searchInputs.forEach(input => {
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        input.parentNode.appendChild(suggestionsContainer);
        
        new SearchAutocomplete(input, suggestionsContainer);
    });
    
    // Highlight search terms in results
    const urlParams = new URLSearchParams(window.location.search);
    const query = urlParams.get('q');
    if (query) {
        highlightResults(query);
    }
    
    // Clear filters functionality
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const form = this.closest('form');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name === 'q') {
                    input.value = '';
                } else {
                    input.value = '';
                }
            });
            form.submit();
        });
    }
    
    // Save search functionality
    const saveSearchBtn = document.getElementById('saveSearch');
    if (saveSearchBtn) {
        saveSearchBtn.addEventListener('click', function() {
            const form = this.closest('form');
            const formData = new FormData(form);
            const searchParams = new URLSearchParams(formData);
            
            // Show save search modal or prompt
            const searchName = prompt('Enter a name for this search:');
            if (searchName) {
                saveSearchQuery(searchName, searchParams.toString());
            }
        });
    }
});

// Save search query function
async function saveSearchQuery(name, queryString) {
    try {
        const response = await fetch('/api/search/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                query: queryString
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Search saved successfully!', 'success');
        } else {
            showNotification('Failed to save search', 'error');
        }
    } catch (error) {
        console.error('Error saving search:', error);
        showNotification('Failed to save search', 'error');
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
