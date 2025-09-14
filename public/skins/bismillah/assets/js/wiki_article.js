/**
 * Wiki Article JavaScript
 * Handles table of contents generation and functionality
 * 
 * @author Khalid Abdullah
 * @version 0.0.0.14
 * @license AGPL-3.0
 */

// Table of Contents functionality
let tocGenerated = false;
let tocCollapsed = false;

/**
 * Generate table of contents from article headings
 */
function generateTOC() {
    if (tocGenerated) return;
    
    const tocContent = document.getElementById('toc-content');
    if (!tocContent) return;
    
    // Find all headings in the article content
    const articleContent = document.querySelector('.article-content');
    if (!articleContent) return;
    
    const headings = articleContent.querySelectorAll('h1, h2, h3, h4, h5, h6');
    
    if (headings.length === 0) {
        tocContent.innerHTML = '<div class="toc-empty">No headings found in this article.</div>';
        tocGenerated = true;
        return;
    }
    
    // Generate TOC HTML
    let tocHTML = '<ul class="toc-list">';
    let currentLevel = 1;
    
    headings.forEach((heading, index) => {
        const level = parseInt(heading.tagName.charAt(1));
        const id = `heading-${index}`;
        const text = heading.textContent.trim();
        
        // Add ID to heading for linking
        heading.id = id;
        
        // Create TOC item
        if (level > currentLevel) {
            // Add nested lists for deeper levels
            for (let i = currentLevel; i < level; i++) {
                tocHTML += '<ul class="toc-sublist">';
            }
        } else if (level < currentLevel) {
            // Close nested lists for shallower levels
            for (let i = currentLevel; i > level; i--) {
                tocHTML += '</ul>';
            }
        }
        
        tocHTML += `
            <li class="toc-item toc-level-${level}">
                <a href="#${id}" class="toc-link" data-target="${id}">
                    ${text}
                </a>
            </li>
        `;
        
        currentLevel = level;
    });
    
    // Close any remaining nested lists
    for (let i = currentLevel; i > 1; i--) {
        tocHTML += '</ul>';
    }
    
    tocHTML += '</ul>';
    
    // Update TOC content
    tocContent.innerHTML = tocHTML;
    
    // Bind click events to TOC links
    bindTOCEvents();
    
    // Start active section tracking
    startActiveSectionTracking();
    
    tocGenerated = true;
}

/**
 * Bind events to TOC links
 */
function bindTOCEvents() {
    const tocLinks = document.querySelectorAll('.toc-link');
    
    tocLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                // Smooth scroll to target
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update active TOC item
                updateActiveTOCItem(targetId);
            }
        });
    });
}

/**
 * Update active TOC item
 */
function updateActiveTOCItem(activeId) {
    // Remove active class from all TOC links
    const tocLinks = document.querySelectorAll('.toc-link');
    tocLinks.forEach(link => link.classList.remove('active'));
    
    // Add active class to current link
    const activeLink = document.querySelector(`[data-target="${activeId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

/**
 * Start tracking which section is currently in view
 */
function startActiveSectionTracking() {
    const headings = document.querySelectorAll('.article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6');
    
    if (headings.length === 0) return;
    
    // Create intersection observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateActiveTOCItem(entry.target.id);
            }
        });
    }, {
        rootMargin: '-20% 0px -70% 0px'
    });
    
    // Observe all headings
    headings.forEach(heading => {
        observer.observe(heading);
    });
}

/**
 * Toggle TOC visibility
 */
function toggleTOC() {
    const tocContent = document.getElementById('toc-content');
    const toggleButton = document.querySelector('.toc-toggle i');
    
    if (!tocContent) return;
    
    tocCollapsed = !tocCollapsed;
    
    if (tocCollapsed) {
        tocContent.classList.add('collapsed');
        if (toggleButton) {
            toggleButton.classList.remove('fa-chevron-down');
            toggleButton.classList.add('fa-chevron-right');
        }
    } else {
        tocContent.classList.remove('collapsed');
        if (toggleButton) {
            toggleButton.classList.remove('fa-chevron-right');
            toggleButton.classList.add('fa-chevron-down');
        }
    }
}

/**
 * Initialize TOC when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Generate TOC after a short delay to ensure content is loaded
    setTimeout(() => {
        generateTOC();
    }, 100);
});

// Make functions globally available
window.toggleTOC = toggleTOC;
window.generateTOC = generateTOC;

