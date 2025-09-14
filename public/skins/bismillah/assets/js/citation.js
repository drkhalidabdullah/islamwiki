/**
 * Citation Modal JavaScript
 * Handles citation generation and modal functionality for wiki articles
 * 
 * @author Khalid Abdullah
 * @version 0.0.0.12
 * @license AGPL-3.0
 */

// Citation data - will be populated from the page
let citationData = {
    title: '',
    author: '',
    siteName: '',
    url: '',
    publishDate: '',
    accessDate: '',
    lastModified: ''
};

/**
 * Initialize citation data from the current page
 */
function initializeCitationData() {
    // Get article title
    const titleElement = document.querySelector('h1');
    citationData.title = titleElement ? titleElement.textContent.trim() : 'Untitled Article';
    
    // Get author information
    const authorElement = document.querySelector('.article-author, .author-name');
    citationData.author = authorElement ? authorElement.textContent.trim() : 'Unknown Author';
    
    // Get site name from meta tags first (most reliable)
    const siteNameMeta = document.querySelector('meta[name="site-name"]');
    if (siteNameMeta) {
        citationData.siteName = siteNameMeta.getAttribute('content') || 'IslamWiki';
    } else {
        // Fallback: extract from page title
        const siteNameElement = document.querySelector('title');
        if (siteNameElement) {
            const titleText = siteNameElement.textContent;
            // Extract site name from title (e.g., "Article Title - Site Name")
            const parts = titleText.split(' - ');
            citationData.siteName = parts.length > 1 ? parts[parts.length - 1].trim() : 'IslamWiki';
        } else {
            citationData.siteName = 'IslamWiki'; // Final fallback
        }
    }
    
    // Get current URL
    citationData.url = window.location.href;
    
    // Get current date for access date
    citationData.accessDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Get publish date (try to find it in the page)
    const publishElement = document.querySelector('.publish-date, .created-date');
    citationData.publishDate = publishElement ? publishElement.textContent.trim() : new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Get last modified date
    const modifiedElement = document.querySelector('.last-modified, .updated-date');
    citationData.lastModified = modifiedElement ? modifiedElement.textContent.trim() : citationData.accessDate;
}

/**
 * Generate citation in the specified format
 */
function generateCitation(format) {
    const { title, author, siteName, url, publishDate, accessDate, lastModified } = citationData;
    
    switch (format) {
        case 'apa':
            return `${author}. (${new Date(publishDate).getFullYear()}). ${title}. ${siteName}. Retrieved ${accessDate}, from ${url}`;
        
        case 'mla':
            return `"${title}." ${siteName}, ${new Date(publishDate).getFullYear()}, ${url}. Accessed ${accessDate}.`;
        
        case 'chicago':
            return `${author}. "${title}." ${siteName}. Last modified ${lastModified}. Accessed ${accessDate}. ${url}.`;
        
        case 'harvard':
            return `${author} ${new Date(publishDate).getFullYear()}, '${title}', ${siteName}, viewed ${accessDate}, <${url}>.`;
        
        case 'ieee':
            return `${author}, "${title}," ${siteName}, ${new Date(publishDate).getFullYear()}. [Online]. Available: ${url}. [Accessed: ${accessDate}].`;
        
        default:
            return `${author}. (${new Date(publishDate).getFullYear()}). ${title}. ${siteName}. Retrieved ${accessDate}, from ${url}`;
    }
}

/**
 * Show the citation modal
 */
function citePage() {
    // Initialize citation data
    initializeCitationData();
    
    // Create modal if it doesn't exist
    if (!document.getElementById('citationModal')) {
        createCitationModal();
    }
    
    // Show the modal
    const modal = document.getElementById('citationModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Generate default citation (APA)
    updateCitation('apa');
}

/**
 * Create the citation modal HTML
 */
function createCitationModal() {
    const modalHTML = `
        <div id="citationModal" class="citation-modal" style="display: none;">
            <div class="citation-modal-content">
                <div class="citation-modal-header">
                    <h3><i class="fas fa-quote-left"></i> Cite this page</h3>
                    <button class="citation-modal-close" onclick="closeCitationModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="citation-modal-body">
                    <div class="citation-format">
                        <label for="citationFormat">Citation Format:</label>
                        <select id="citationFormat" onchange="updateCitation(this.value)">
                            <option value="apa">APA 7th Edition</option>
                            <option value="mla">MLA 9th Edition</option>
                            <option value="chicago">Chicago 17th Edition</option>
                            <option value="harvard">Harvard</option>
                            <option value="ieee">IEEE</option>
                        </select>
                    </div>
                    
                    <div class="citation-preview">
                        <label>Citation Preview:</label>
                        <div id="citationText" class="citation-text"></div>
                    </div>
                    
                    <div class="citation-actions">
                        <button class="btn btn-primary" onclick="copyCitation()">
                            <i class="fas fa-copy"></i> Copy Citation
                        </button>
                        <button class="btn btn-secondary" onclick="downloadCitation()">
                            <i class="fas fa-download"></i> Download
                        </button>
                    </div>
                    
                    <div class="citation-info">
                        <h4>Citation Information:</h4>
                        <div class="citation-details">
                            <p><strong>Title:</strong> <span id="citationTitle">${citationData.title}</span></p>
                            <p><strong>Author:</strong> <span id="citationAuthor">${citationData.author}</span></p>
                            <p><strong>Site:</strong> <span id="citationSite">${citationData.siteName}</span></p>
                            <p><strong>URL:</strong> <span id="citationUrl">${citationData.url}</span></p>
                            <p><strong>Access Date:</strong> <span id="citationAccessDate">${citationData.accessDate}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

/**
 * Update the citation text based on selected format
 */
function updateCitation(format) {
    const citationText = generateCitation(format);
    const citationElement = document.getElementById('citationText');
    if (citationElement) {
        citationElement.textContent = citationText;
    }
}

/**
 * Copy citation to clipboard
 */
async function copyCitation() {
    const citationText = document.getElementById('citationText').textContent;
    
    try {
        await navigator.clipboard.writeText(citationText);
        showToast('Citation copied to clipboard!', 'success');
    } catch (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = citationText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Citation copied to clipboard!', 'success');
    }
}

/**
 * Download citation as text file
 */
function downloadCitation() {
    const citationText = document.getElementById('citationText').textContent;
    const format = document.getElementById('citationFormat').value;
    const filename = `citation_${citationData.title.replace(/[^a-zA-Z0-9]/g, '_')}_${format}.txt`;
    
    const blob = new Blob([citationText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('Citation downloaded!', 'success');
}

/**
 * Close the citation modal
 */
function closeCitationModal() {
    const modal = document.getElementById('citationModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('citationModal');
    if (modal && e.target === modal) {
        closeCitationModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCitationModal();
    }
});

// Make functions globally available
window.citePage = citePage;
window.closeCitationModal = closeCitationModal;
window.updateCitation = updateCitation;
window.copyCitation = copyCitation;
window.downloadCitation = downloadCitation;
