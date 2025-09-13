/**
 * Simple Search Overlay - Minimal Working Version
 */

console.log('Simple Search Overlay script loaded');

// Create overlay immediately when script loads
function createSimpleSearchOverlay() {
    console.log('Creating simple search overlay...');
    
    // Check if overlay already exists
    if (document.querySelector('.simple-search-overlay')) {
        console.log('Overlay already exists');
        return;
    }
    
    const overlay = document.createElement('div');
    overlay.className = 'simple-search-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
    `;
    
    overlay.innerHTML = `
        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; width: 90%;">
            <h2>Search</h2>
            <input type="text" id="simpleSearchInput" placeholder="Search articles..." style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px;">
            <div id="simpleSearchResults" style="margin-top: 10px;">
                <p>Start typing to search...</p>
            </div>
            <button onclick="closeSimpleSearch()" style="margin-top: 10px; padding: 8px 16px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
    `;
    
    document.body.appendChild(overlay);
    console.log('Simple search overlay created and added to DOM');
    
    // Add event listeners
    const input = overlay.querySelector('#simpleSearchInput');
    input.addEventListener('input', function(e) {
        const query = e.target.value;
        if (query.length >= 2) {
            performSimpleSearch(query);
        } else {
            document.getElementById('simpleSearchResults').innerHTML = '<p>Start typing to search...</p>';
        }
    });
    
    // Close on backdrop click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeSimpleSearch();
        }
    });
    
    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.style.display !== 'none') {
            closeSimpleSearch();
        }
    });
}

function openSimpleSearch() {
    console.log('Opening simple search...');
    const overlay = document.querySelector('.simple-search-overlay');
    if (overlay) {
        overlay.style.display = 'flex';
        const input = overlay.querySelector('#simpleSearchInput');
        if (input) {
            input.focus();
        }
    } else {
        console.log('Overlay not found, creating it...');
        createSimpleSearchOverlay();
        openSimpleSearch();
    }
}

function closeSimpleSearch() {
    console.log('Closing simple search...');
    const overlay = document.querySelector('.simple-search-overlay');
    if (overlay) {
        overlay.style.display = 'none';
        const input = overlay.querySelector('#simpleSearchInput');
        if (input) {
            input.value = '';
        }
        document.getElementById('simpleSearchResults').innerHTML = '<p>Start typing to search...</p>';
    }
}

async function performSimpleSearch(query) {
    console.log('Performing search for:', query);
    const resultsDiv = document.getElementById('simpleSearchResults');
    resultsDiv.innerHTML = '<p>Searching...</p>';
    
    try {
        const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.topArticles) {
            let html = '<h3>Results:</h3>';
            data.topArticles.forEach(article => {
                html += `<div style="padding: 8px; border-bottom: 1px solid #eee; cursor: pointer;" onclick="window.location.href='${article.url}'">
                    <strong>${article.title}</strong><br>
                    <small>${article.category} - ${article.date}</small>
                </div>`;
            });
            resultsDiv.innerHTML = html;
        } else {
            resultsDiv.innerHTML = '<p>No results found</p>';
        }
    } catch (error) {
        console.error('Search error:', error);
        resultsDiv.innerHTML = '<p>Search failed</p>';
    }
}

// Make functions globally available
window.openSimpleSearch = openSimpleSearch;
window.closeSimpleSearch = closeSimpleSearch;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createSimpleSearchOverlay);
} else {
    createSimpleSearchOverlay();
}

console.log('Simple Search Overlay initialized');
