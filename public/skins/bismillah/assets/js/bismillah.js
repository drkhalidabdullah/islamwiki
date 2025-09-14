/**
 * Bismillah Skin JavaScript
 * Default skin functionality and enhancements
 */

document.addEventListener("DOMContentLoaded", function() {
    // Skin-specific initialization
    console.log("Bismillah skin loaded");
    
    // Add any skin-specific JavaScript here
    // This can include theme-specific interactions, animations, etc.
    
    // Example: Add smooth transitions to cards
    const cards = document.querySelectorAll('.card, .content-section, .sidebar-section');
    cards.forEach(card => {
        card.style.transition = 'all 0.3s ease';
    });
    
    // Example: Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

/* ========================================
   CORE JAVASCRIPT FUNCTIONS
   Common functions used across multiple pages
   ======================================== */

// Tab functionality - used in admin pages
window.showTab = function(tabName, element) {
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab content
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked button
    if (element) {
        element.classList.add('active');
    }
};

// Markdown parsing function - used in dashboard and social pages
window.parseMarkdown = function(text) {
    // Basic markdown parsing
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`(.*?)`/g, '<code>$1</code>')
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
        .replace(/\n/g, '<br>');
};

// Preview update function - used in dashboard and social pages
window.updatePreview = function() {
    const postInput = document.getElementById('postContent');
    const content = postInput ? postInput.value.trim() : '';
    const previewContent = document.getElementById('postPreviewContent');
    
    if (!previewContent) return;
    
    if (!content) {
        previewContent.innerHTML = '<p class="preview-empty">No content to preview</p>';
        return;
    }
    
    try {
        const html = window.parseMarkdown(content);
        previewContent.innerHTML = html;
    } catch (error) {
        previewContent.innerHTML = '<p class="preview-error">Error parsing content</p>';
    }
};

// Image upload function - used in dashboard
window.uploadImage = function(file, callback) {
    const formData = new FormData();
    formData.append('image', file);
    
    fetch('/api/ajax/upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data.url);
        } else {
            console.error('Upload failed:', data.error);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
    });
};

// Modal functions - used across admin pages
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
};

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});

// Form validation helpers
window.validateEmail = function(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

window.validateRequired = function(field) {
    return field.value.trim() !== '';
};

// Utility functions
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

window.throttle = function(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};


/* ========================================
   AJAX UTILITIES
   Common AJAX functions used across pages
   ======================================== */

// Generic AJAX POST function
window.ajaxPost = function(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (callback) callback(result);
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        if (callback) callback({success: false, error: error.message});
    });
};

// Generic AJAX GET function
window.ajaxGet = function(url, callback) {
    fetch(url)
    .then(response => response.json())
    .then(result => {
        if (callback) callback(result);
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        if (callback) callback({success: false, error: error.message});
    });
};

// Form submission helper
window.submitForm = function(formId, url, callback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (callback) callback(result);
    })
    .catch(error => {
        console.error('Form submission error:', error);
        if (callback) callback({success: false, error: error.message});
    });
};

// Show/hide loading states
window.showLoading = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading">Loading...</div>';
    }
};

window.hideLoading = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '';
    }
};

// Notification helpers
window.showNotification = function(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
};

