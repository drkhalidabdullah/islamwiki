// Admin System Settings JavaScript

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="iw iw-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="iw iw-times"></i>
        </button>
    `;
    
    // Calculate position for stacking toasts
    const existingToasts = document.querySelectorAll('.toast');
    const toastHeight = 60; // Approximate height of each toast
    const spacing = 10; // Space between toasts
    const topOffset = 80; // Base top position (below header)
    
    // Position this toast below existing ones
    const topPosition = topOffset + (existingToasts.length * (toastHeight + spacing));
    toast.style.top = `${topPosition}px`;
    
    // Add toast styles if not already added
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast {
                position: fixed;
                top: 80px;
                right: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 1rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10002;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                min-width: 300px;
                max-width: 400px;
                animation: slideIn 0.3s ease;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                line-height: 1.4;
            }
            .toast-success { 
                border-left: 4px solid #10b981; 
                background: #f0fdf4;
                border-color: #bbf7d0;
            }
            .toast-error { 
                border-left: 4px solid #ef4444; 
                background: #fef2f2;
                border-color: #fecaca;
            }
            .toast-info { 
                border-left: 4px solid #3b82f6; 
                background: #eff6ff;
                border-color: #bfdbfe;
            }
            .toast-content { 
                display: flex; 
                align-items: center; 
                gap: 0.5rem; 
                flex: 1; 
                color: #1f2937;
                font-weight: 500;
            }
            .toast-content i {
                font-size: 16px;
                flex-shrink: 0;
            }
            .toast-success .toast-content i { color: #10b981; }
            .toast-error .toast-content i { color: #ef4444; }
            .toast-info .toast-content i { color: #3b82f6; }
            .toast-close { 
                background: none; 
                border: none; 
                cursor: pointer; 
                padding: 0.25rem; 
                color: #6b7280;
                border-radius: 4px;
                transition: all 0.2s ease;
            }
            .toast-close:hover {
                background: rgba(0,0,0,0.1);
                color: #374151;
            }
            @keyframes slideIn { 
                from { 
                    transform: translateX(100%); 
                    opacity: 0; 
                } 
                to { 
                    transform: translateX(0); 
                    opacity: 1; 
                } 
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add to page
    document.body.appendChild(toast);
    
    // Function to reposition toasts when one is removed
    function repositionToasts() {
        const allToasts = document.querySelectorAll('.toast');
        const toastHeight = 60;
        const spacing = 10;
        const topOffset = 80;
        
        allToasts.forEach((toast, index) => {
            const topPosition = topOffset + (index * (toastHeight + spacing));
            toast.style.top = `${topPosition}px`;
        });
    }
    
    // Override the close button to reposition toasts
    const closeButton = toast.querySelector('.toast-close');
    closeButton.onclick = function() {
        toast.remove();
        repositionToasts();
    };
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
            repositionToasts();
        }
    }, 5000);
}

// Test function for toasts (can be called from console)
window.testToast = function(type = 'success') {
    const messages = {
        success: 'Module enabled successfully!',
        error: 'Failed to enable module.',
        info: 'This is an info message.'
    };
    showToast(messages[type] || messages.success, type);
};

// Define showTab function
function showTab(tabName, element) {
    console.log('showTab called with:', tabName, element);
    
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    console.log('Found tabs:', tabs.length);
    tabs.forEach(tab => {
        tab.classList.remove('active');
        console.log('Removed active from:', tab.id);
    });
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    console.log('Target tab:', targetTab);
    if (targetTab) {
        targetTab.classList.add('active');
        console.log('Added active to:', targetTab.id);
    } else {
        console.error('Target tab not found:', tabName + '-tab');
    }
    
    // Handle active class for tab buttons when user clicks
    if (element) {
        // Remove active class from all tab buttons
        const allButtons = document.querySelectorAll('.tab-button');
        allButtons.forEach(button => {
            button.classList.remove('active');
        });
        
        // Add active class to clicked button
        element.classList.add('active');
        console.log('Added active class to clicked button for tab:', tabName);
    }
    
    // Store the active tab in URL for persistence
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
    
    // Update all current_tab hidden inputs in forms
    const currentTabInputs = document.querySelectorAll('input[name="current_tab"]');
    console.log('Found current_tab inputs:', currentTabInputs.length, 'for tab:', tabName);
    currentTabInputs.forEach((input, index) => {
        input.value = tabName;
        console.log('Updated current_tab input', index, 'to:', tabName);
    });
    
    // Ensure all forms have current_tab input (in case some were missed)
    if (window.ensureCurrentTabInputs) {
        setTimeout(() => {
            window.ensureCurrentTabInputs(tabName);
        }, 50);
    }
}

// Make showTab available globally
window.showTab = showTab;

// Tab persistence and initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin system settings page loaded');
    
    // Get active tab from URL or default to 'general'
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'general';
    
    console.log('=== TAB DEBUGGING ===');
    console.log('Current URL:', window.location.href);
    console.log('URL search params:', window.location.search);
    console.log('URL params object:', urlParams);
    console.log('Active tab from URL:', activeTab);
    console.log('All tab buttons:', document.querySelectorAll('.tab-button'));
    console.log('========================');
    
    // Show the active tab
    showTab(activeTab);
    
    // Don't override PHP-generated active classes - just ensure tab content is shown
    // The PHP already sets the correct active class on the button
    
    // Tab buttons use onclick handlers - no need for additional event listeners
    
    // Function to ensure all forms have current_tab input
    function ensureCurrentTabInputs(tabName) {
        const forms = document.querySelectorAll('form');
        console.log('Ensuring current_tab inputs for tab:', tabName, 'Found forms:', forms.length);
        
        forms.forEach((form, index) => {
            let currentTabInput = form.querySelector('input[name="current_tab"]');
            if (!currentTabInput) {
                currentTabInput = document.createElement('input');
                currentTabInput.type = 'hidden';
                currentTabInput.name = 'current_tab';
                form.appendChild(currentTabInput);
                console.log('Added missing current_tab input to form', index);
            }
            currentTabInput.value = tabName;
        });
    }
    
    // Make function available globally
    window.ensureCurrentTabInputs = ensureCurrentTabInputs;
    
    // Set up MutationObserver to handle dynamically added forms
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a form or contains forms
                        const forms = node.tagName === 'FORM' ? [node] : node.querySelectorAll ? node.querySelectorAll('form') : [];
                        forms.forEach(function(form) {
                            console.log('New form detected, ensuring current_tab input');
                            ensureCurrentTabInputs(getCurrentActiveTab());
                        });
                    }
                });
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Helper function to get current active tab
    function getCurrentActiveTab() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tab') || 'general';
    }
    
    // Removed complex highlighting functions - now handled directly in showTab
    
    // Special handling for modules tab
    if (activeTab === 'modules') {
        console.log('Modules tab is active, ensuring module forms have current_tab input');
        setTimeout(() => {
            ensureCurrentTabInputs('modules');
        }, 200);
    }
    
    // Add event listeners to all submit buttons to ensure current_tab is set
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        const form = button.closest('form');
        if (form) {
            button.addEventListener('click', function() {
                console.log('Submit button clicked, ensuring current_tab is set');
                let currentTabInput = form.querySelector('input[name="current_tab"]');
                if (!currentTabInput) {
                    currentTabInput = document.createElement('input');
                    currentTabInput.type = 'hidden';
                    currentTabInput.name = 'current_tab';
                    form.appendChild(currentTabInput);
                    console.log('Added current_tab input to form');
                }
                
                // Determine the correct tab based on the form's action
                let tabValue = 'general'; // default
                const actionInput = form.querySelector('input[name="action"]');
                if (actionInput) {
                    const action = actionInput.value;
                    if (action === 'toggle_module') {
                        tabValue = 'modules';
                    } else if (action === 'toggle_extension' || action === 'update_extension_settings') {
                        tabValue = 'extensions';
                    } else if (action === 'activate_skin' || action === 'update_skin_settings') {
                        tabValue = 'skins';
                    } else if (action === 'update_security') {
                        tabValue = 'security';
                    } else if (action === 'update_email' || action === 'test_email') {
                        tabValue = 'email';
                    } else if (action === 'clear_cache') {
                        // For cache clear, try to get the current tab from URL or use general
                        const urlParams = new URLSearchParams(window.location.search);
                        tabValue = urlParams.get('tab') || 'general';
                    }
                }
                
                currentTabInput.value = tabValue;
                console.log('Set current_tab to', tabValue, 'for form with action:', actionInput ? actionInput.value : 'unknown');
            });
        }
    });
    
    // Maintenance mode toggle functionality
    const maintenanceToggle = document.querySelector('input[name="maintenance_mode"]');
    const maintenanceSettings = document.getElementById('maintenance-settings');
    
    if (maintenanceToggle && maintenanceSettings) {
        // Show/hide maintenance settings based on toggle state
        function toggleMaintenanceSettings() {
            if (maintenanceToggle.checked) {
                maintenanceSettings.style.display = 'block';
            } else {
                maintenanceSettings.style.display = 'none';
            }
        }
        
        // Initial state
        toggleMaintenanceSettings();
        
        // Add event listener
        maintenanceToggle.addEventListener('change', toggleMaintenanceSettings);
    }
    
    // Add confirmation for maintenance mode toggle
    const maintenanceForm = document.querySelector('form input[name="maintenance_mode"]')?.closest('form');
    if (maintenanceForm) {
        maintenanceForm.addEventListener('submit', function(e) {
            if (maintenanceToggle && maintenanceToggle.checked) {
                if (!confirm('Are you sure you want to enable maintenance mode? This will make your site inaccessible to visitors.')) {
                    e.preventDefault();
                }
            }
        });
    }
});

// Extension management functions
function addNewsItem() {
    const container = document.getElementById('news-items-container');
    if (!container) return;
    
    const newRow = document.createElement('div');
    newRow.className = 'news-item-row';
    newRow.innerHTML = `
        <input type="text" name="news_item_time[]" placeholder="Time (e.g., 2 hours ago)" value="">
        <input type="text" name="news_item_text[]" placeholder="News text" value="">
        <button type="button" onclick="removeNewsItem(this)">Remove</button>
    `;
    container.appendChild(newRow);
}

function removeNewsItem(button) {
    button.parentElement.remove();
}


// Extension settings toggle
function toggleExtensionSettings(extensionName) {
    const settingsDiv = document.getElementById('extension-settings-' + extensionName);
    const toggleButton = document.querySelector(`button[onclick="toggleExtensionSettings('${extensionName}')"]`);
    
    if (!settingsDiv || !toggleButton) return;
    
    if (settingsDiv.style.display === 'none' || settingsDiv.style.display === '') {
        settingsDiv.style.display = 'block';
        toggleButton.innerHTML = '<i class="iw iw-chevron-up"></i> Hide Options';
        toggleButton.title = 'Hide Extension Settings';
    } else {
        settingsDiv.style.display = 'none';
        toggleButton.innerHTML = '<i class="iw iw-chevron-down"></i> More Options';
        toggleButton.title = 'Show Extension Settings';
    }
}

// Site Logo Upload Functions
function uploadSiteLogo() {
    const fileInput = document.getElementById('site_logo_input');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file to upload.');
        return;
    }
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];
    if (!allowedTypes.includes(file.type)) {
        alert('Invalid file type. Please select a JPEG, PNG, GIF, or SVG file.');
        return;
    }
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        alert('File too large. Maximum size is 5MB.');
        return;
    }
    
    // Show loading state
    const uploadButton = document.querySelector('.logo-upload-controls .btn-secondary');
    const originalText = uploadButton.innerHTML;
    uploadButton.innerHTML = '<i class="iw iw-spinner iw-spin"></i> Uploading...';
    uploadButton.disabled = true;
    
    // Create FormData
    const formData = new FormData();
    formData.append('site_logo', file);
    
    // Upload file
    fetch('/api/upload_site_logo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update logo preview
            updateLogoPreview(data.url, data.filename, file.name, data.dimensions);
            
            // Show success message
            showToast('Site logo uploaded successfully!', 'success');
        } else {
            alert('Error uploading logo: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Error uploading logo. Please try again.');
    })
    .finally(() => {
        // Reset button state
        uploadButton.innerHTML = originalText;
        uploadButton.disabled = false;
        fileInput.value = '';
    });
}

function updateLogoPreview(url, filename, originalName, dimensions) {
    const logoPreview = document.getElementById('logo-preview');
    const uploadButton = document.querySelector('.logo-upload-controls .btn-secondary');
    const removeButton = document.querySelector('.logo-upload-controls .btn-danger');
    
    // Update preview
    logoPreview.innerHTML = `
        <img src="${url}" alt="Site Logo" id="current-logo">
        <div class="logo-info">
            <small>
                ${originalName}
                ${dimensions ? ` (${dimensions.width}×${dimensions.height})` : ''}
            </small>
        </div>
    `;
    
    // Update button text
    uploadButton.innerHTML = '<i class="iw iw-upload"></i> Change Logo';
    
    // Show remove button if not already visible
    if (!removeButton) {
        const controls = document.querySelector('.logo-upload-controls');
        const newRemoveButton = document.createElement('button');
        newRemoveButton.type = 'button';
        newRemoveButton.className = 'btn btn-danger';
        newRemoveButton.onclick = removeSiteLogo;
        newRemoveButton.innerHTML = '<i class="iw iw-trash"></i> Remove';
        controls.appendChild(newRemoveButton);
    }
}

function removeSiteLogo() {
    if (!confirm('Are you sure you want to remove the site logo?')) {
        return;
    }
    
    // Show loading state
    const removeButton = document.querySelector('.logo-upload-controls .btn-danger');
    const originalText = removeButton.innerHTML;
    removeButton.innerHTML = '<i class="iw iw-spinner iw-spin"></i> Removing...';
    removeButton.disabled = true;
    
    // Remove logo from database
    fetch('/api/remove_site_logo.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reset preview
            const logoPreview = document.getElementById('logo-preview');
            logoPreview.innerHTML = `
                <div class="no-logo">
                    <i class="iw iw-image"></i>
                    <p>No logo uploaded</p>
                </div>
            `;
            
            // Update button text
            const uploadButton = document.querySelector('.logo-upload-controls .btn-secondary');
            uploadButton.innerHTML = '<i class="iw iw-upload"></i> Upload Logo';
            
            // Remove remove button
            removeButton.remove();
            
            // Show success message
            showToast('Site logo removed successfully!', 'success');
        } else {
            alert('Error removing logo: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Remove error:', error);
        alert('Error removing logo. Please try again.');
    })
    .finally(() => {
        // Reset button state
        removeButton.innerHTML = originalText;
        removeButton.disabled = false;
    });
}

// Initialize logo upload functionality
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('site_logo_input');
    if (fileInput) {
        fileInput.addEventListener('change', uploadSiteLogo);
    }
});