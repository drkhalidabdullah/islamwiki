/**
 * SEO Extension JavaScript
 * 
 * This file contains JavaScript functionality for the SEO extension
 * including debug tools and template helpers.
 */

class SEOExtension {
    constructor() {
        this.debugMode = false;
        this.init();
    }
    
    init() {
        // Check if debug mode is enabled
        this.debugMode = this.getUrlParameter('seo_debug') === '1';
        
        if (this.debugMode) {
            this.showDebugPanel();
        }
        
        // Initialize other SEO features
        this.initCanonicalURLs();
        this.initStructuredData();
    }
    
    /**
     * Show SEO debug panel
     */
    showDebugPanel() {
        const debugPanel = document.createElement('div');
        debugPanel.className = 'seo-debug-panel show';
        debugPanel.innerHTML = this.generateDebugHTML();
        
        document.body.appendChild(debugPanel);
        
        // Add close button functionality
        const closeBtn = debugPanel.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                debugPanel.remove();
            });
        }
    }
    
    /**
     * Generate debug panel HTML
     */
    generateDebugHTML() {
        const metaTags = this.extractMetaTags();
        const structuredData = this.extractStructuredData();
        
        return `
            <h4>SEO Debug Panel</h4>
            <button class="close-btn" style="float: right; background: none; border: none; font-size: 16px; cursor: pointer;">&times;</button>
            <div class="debug-item">
                <span class="debug-label">Title:</span>
                <div class="debug-value">${metaTags.title || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">Description:</span>
                <div class="debug-value">${metaTags.description || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">Keywords:</span>
                <div class="debug-value">${metaTags.keywords || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">OG Title:</span>
                <div class="debug-value">${metaTags.ogTitle || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">OG Description:</span>
                <div class="debug-value">${metaTags.ogDescription || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">Twitter Title:</span>
                <div class="debug-value">${metaTags.twitterTitle || 'Not set'}</div>
            </div>
            <div class="debug-item">
                <span class="debug-label">Structured Data:</span>
                <div class="debug-value">${structuredData ? 'Present' : 'Not found'}</div>
            </div>
        `;
    }
    
    /**
     * Extract meta tags from the page
     */
    extractMetaTags() {
        const tags = {};
        
        // Get title
        const title = document.querySelector('title');
        if (title) {
            tags.title = title.textContent;
        }
        
        // Get meta tags
        const metaTags = document.querySelectorAll('meta');
        metaTags.forEach(meta => {
            const name = meta.getAttribute('name') || meta.getAttribute('property');
            const content = meta.getAttribute('content');
            
            if (name && content) {
                switch (name) {
                    case 'description':
                        tags.description = content;
                        break;
                    case 'keywords':
                        tags.keywords = content;
                        break;
                    case 'og:title':
                        tags.ogTitle = content;
                        break;
                    case 'og:description':
                        tags.ogDescription = content;
                        break;
                    case 'twitter:title':
                        tags.twitterTitle = content;
                        break;
                    case 'twitter:description':
                        tags.twitterDescription = content;
                        break;
                }
            }
        });
        
        return tags;
    }
    
    /**
     * Extract structured data from the page
     */
    extractStructuredData() {
        const scripts = document.querySelectorAll('script[type="application/ld+json"]');
        return scripts.length > 0;
    }
    
    /**
     * Initialize canonical URLs
     */
    initCanonicalURLs() {
        // Ensure canonical URL is set
        if (!document.querySelector('link[rel="canonical"]')) {
            const canonical = document.createElement('link');
            canonical.rel = 'canonical';
            canonical.href = window.location.href;
            document.head.appendChild(canonical);
        }
    }
    
    /**
     * Initialize structured data
     */
    initStructuredData() {
        // This could be expanded to validate or enhance structured data
        const structuredDataScripts = document.querySelectorAll('script[type="application/ld+json"]');
        structuredDataScripts.forEach(script => {
            try {
                const data = JSON.parse(script.textContent);
                console.log('Structured data found:', data);
            } catch (e) {
                console.warn('Invalid structured data:', e);
            }
        });
    }
    
    /**
     * Get URL parameter
     */
    getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
    
    /**
     * Generate SEO template from form data
     */
    generateSEOTemplate(formData) {
        const params = [];
        
        Object.keys(formData).forEach(key => {
            if (formData[key]) {
                params.push(`${key}=${formData[key]}`);
            }
        });
        
        return `{{#seo:|${params.join('|')}}}`;
    }
    
    /**
     * Validate SEO data
     */
    validateSEOData(data) {
        const errors = [];
        
        if (!data.title) {
            errors.push('Title is required');
        }
        
        if (!data.description) {
            errors.push('Description is required');
        }
        
        if (data.description && data.description.length > 160) {
            errors.push('Description should be 160 characters or less');
        }
        
        if (data.title && data.title.length > 60) {
            errors.push('Title should be 60 characters or less');
        }
        
        return errors;
    }
}

// Initialize the SEO extension when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new SEOExtension();
});

// Export for use in other scripts
window.SEOExtension = SEOExtension;

