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
    
    // Add toast styles if not already added
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 1rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            }
            .toast-success { border-left: 4px solid #10b981; }
            .toast-error { border-left: 4px solid #ef4444; }
            .toast-info { border-left: 4px solid #3b82f6; }
            .toast-content { display: flex; align-items: center; gap: 0.5rem; flex: 1; }
            .toast-close { background: none; border: none; cursor: pointer; padding: 0.25rem; }
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        `;
        document.head.appendChild(style);
    }
    
    // Add to page
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

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
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    console.log('Found buttons:', buttons.length);
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    console.log('Target tab:', targetTab);
    if (targetTab) {
        targetTab.classList.add('active');
        console.log('Added active to:', targetTab.id);
    } else {
        console.error('Target tab not found:', tabName + '-tab');
    }
    
    // Activate button
    if (element) {
        element.classList.add('active');
        console.log('Activated button:', element);
    }
    
    // Store the active tab in URL for persistence
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

// Make showTab available globally
window.showTab = showTab;

// Tab persistence and initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin system settings page loaded');
    
    // Get active tab from URL or default to 'general'
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'general';
    
    console.log('Active tab from URL:', activeTab);
    
    // Show the active tab
    showTab(activeTab);
    
    // Add click handlers to all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('onclick').match(/showTab\('([^']+)'/)[1];
            console.log('Button clicked, switching to tab:', tabName);
            showTab(tabName, this);
        });
    });
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

// Maintenance mode toggle functionality
document.addEventListener('DOMContentLoaded', function() {
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
    const maintenanceForm = document.querySelector('form input[name="maintenance_mode"]').closest('form');
    if (maintenanceForm) {
        maintenanceForm.addEventListener('submit', function(e) {
            if (maintenanceToggle && maintenanceToggle.checked) {
                if (!confirm('Are you sure you want to enable maintenance mode? This will make your site inaccessible to visitors.')) {
                    e.preventDefault();
                }
            }
        });
    }
    
    // Add current tab tracking to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const currentTabInput = document.createElement('input');
        currentTabInput.type = 'hidden';
        currentTabInput.name = 'current_tab';
        currentTabInput.value = 'general'; // Default to general
        form.appendChild(currentTabInput);
        
        // Update current tab when switching tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('onclick').match(/showTab\('([^']+)'/)[1];
                currentTabInput.value = tabName;
            });
        });
    });
});

// Extension settings toggle
function toggleExtensionSettings(extensionName) {
    const settingsDiv = document.getElementById(extensionName + '-settings');
    const toggleButton = document.getElementById(extensionName + '-toggle');
    
    if (!settingsDiv || !toggleButton) return;
    
    if (settingsDiv.style.display === 'none' || settingsDiv.style.display === '') {
        settingsDiv.style.display = 'block';
        toggleButton.innerHTML = '<i class="iw iw-chevron-up"></i> Hide Settings';
        toggleButton.title = 'Hide Settings';
    } else {
        settingsDiv.style.display = 'none';
        toggleButton.innerHTML = '<i class="iw iw-chevron-down"></i> Show Settings';
        toggleButton.title = 'Show Settings';
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