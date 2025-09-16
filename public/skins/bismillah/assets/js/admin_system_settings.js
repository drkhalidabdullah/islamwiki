// Define showTab function immediately after header load
window.showTab = function(tabName, element) {
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Activate button
    if (element) {
        element.classList.add('active');
    }
    
    // Store the active tab in URL for persistence
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
};

// Also define as global function for compatibility

// Tab persistence with proper DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = '';
    
    if (activeTab && activeTab !== 'general') {
        // Check if the correct tab is already active (server-side rendering worked)
        const activeButton = document.querySelector('.tab-button.active');
        const activeContent = document.querySelector('.tab-content.active');
        
        if (activeButton && activeContent) {
            const buttonText = activeButton.textContent.trim().toLowerCase();
            const contentId = activeContent.id;
            
            // If the correct tab is already active, don't do anything
            if (buttonText.includes(activeTab.toLowerCase()) && contentId === activeTab + '-tab') {
                console.log('Tab already active from server-side rendering');
                return;
            }
        }
        
        // Find the correct button and call showTab with it
        const buttons = document.querySelectorAll('.tab-button');
        for (let i = 0; i < buttons.length; i++) {
            const button = buttons[i];
            const text = button.textContent.trim().toLowerCase();
            if (text.includes(activeTab.toLowerCase())) {
                // Call showTab with the button element
                if (typeof window.showTab === 'function') {
                    window.showTab(activeTab, button);
                } else if (typeof showTab === 'function') {
                    showTab(activeTab, button);
                } else {
                    // Fallback: click the button
                    button.click();
                }
                break;
            }
        }
    }
});

// Don't clear the session variable - keep it for persistence

// Extension settings toggle functionality
function toggleExtensionSettings(extensionName) {
    const settingsDiv = document.getElementById('extension-settings-' + extensionName);
    const toggleButton = document.querySelector(`[onclick="toggleExtensionSettings('${extensionName}')"]`);
    const icon = toggleButton.querySelector('i');
    
    if (!settingsDiv || !toggleButton) {
        console.error('Extension settings elements not found for:', extensionName);
        return;
    }
    
    const isHidden = settingsDiv.style.display === 'none' || 
                    getComputedStyle(settingsDiv).display === 'none';
    
    if (isHidden) {
        // Show settings
        settingsDiv.style.display = 'block';
        icon.className = 'iw iw-chevron-up';
        toggleButton.title = 'Hide Settings';
    } else {
        // Hide settings
        settingsDiv.style.display = 'none';
        icon.className = 'iw iw-chevron-down';
        toggleButton.title = 'Show Settings';
    }
}
</script>
<script>

// Extension management functions
function addNewsItem() {
    const container = document.getElementById('news-items-container');
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

// Add confirmation for maintenance mode toggle and show/hide settings
document.addEventListener('DOMContentLoaded', function() {
    
    // Add current tab tracking to clear cache form
    const clearCacheForm = document.querySelector('form input[value="clear_cache"]').closest('form');
    if (clearCacheForm) {
        const currentTabInput = document.createElement('input');
        currentTabInput.type = 'hidden';
        currentTabInput.name = 'current_tab';
        currentTabInput.value = 'general'; // Default to general
        clearCacheForm.appendChild(currentTabInput);
        
        // Update current tab when switching tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('onclick').match(/showTab\('([^']+)'/)[1];
                currentTabInput.value = tabName;
            });
        });
    }
    
    const maintenanceToggle = document.querySelector('input[name="maintenance_mode"]');
    const maintenanceSettings = document.getElementById('maintenance-settings');
    
    if (maintenanceToggle) {
        // Show/hide maintenance settings based on toggle state
        function toggleMaintenanceSettings() {
            if (maintenanceToggle.checked) {
                maintenanceSettings.style.display = 'block';
                // Add smooth animation
                maintenanceSettings.style.opacity = '0';
                maintenanceSettings.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    maintenanceSettings.style.transition = 'all 0.3s ease';
                    maintenanceSettings.style.opacity = '1';
                    maintenanceSettings.style.transform = 'translateY(0)';
                }, 10);
            } else {
                maintenanceSettings.style.transition = 'all 0.3s ease';
                maintenanceSettings.style.opacity = '0';
                maintenanceSettings.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    maintenanceSettings.style.display = 'none';
                }, 300);
            }
        }
        
        // Initial state - show if maintenance mode is already enabled
        toggleMaintenanceSettings();
        
        // Listen for changes
        maintenanceToggle.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Are you sure you want to enable maintenance mode? This will make your site inaccessible to regular users.')) {
                    this.checked = false;
                    toggleMaintenanceSettings();
                    return;
                }
            }
            toggleMaintenanceSettings();
        });
    }
    
    // Add confirmation for cache clear
    const cacheForm = document.querySelector('form[action*="clear_cache"]');
    if (cacheForm) {
        cacheForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to clear the system cache?')) {
                e.preventDefault();
            }
        });
    }
});
// Define showTab function immediately after header load
window.showTab = function(tabName, element) {
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all buttons
    const buttons = document.querySelectorAll('.tab-button');
    buttons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Activate button
    if (element) {
        element.classList.add('active');
    }
    
    // Store the active tab in URL for persistence
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
};

// Also define as global function for compatibility

// Tab persistence with proper DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = '';
    
    if (activeTab && activeTab !== 'general') {
        // Check if the correct tab is already active (server-side rendering worked)
        const activeButton = document.querySelector('.tab-button.active');
        const activeContent = document.querySelector('.tab-content.active');
        
        if (activeButton && activeContent) {
            const buttonText = activeButton.textContent.trim().toLowerCase();
            const contentId = activeContent.id;
            
            // If the correct tab is already active, don't do anything
            if (buttonText.includes(activeTab.toLowerCase()) && contentId === activeTab + '-tab') {
                console.log('Tab already active from server-side rendering');
                return;
            }
        }
        
        // Find the correct button and call showTab with it
        const buttons = document.querySelectorAll('.tab-button');
        for (let i = 0; i < buttons.length; i++) {
            const button = buttons[i];
            const text = button.textContent.trim().toLowerCase();
            if (text.includes(activeTab.toLowerCase())) {
                // Call showTab with the button element
                if (typeof window.showTab === 'function') {
                    window.showTab(activeTab, button);
                } else if (typeof showTab === 'function') {
                    showTab(activeTab, button);
                } else {
                    // Fallback: click the button
                    button.click();
                }
                break;
            }
        }
    }
});

// Don't clear the session variable - keep it for persistence

// Extension settings toggle functionality
function toggleExtensionSettings(extensionName) {
    const settingsDiv = document.getElementById('extension-settings-' + extensionName);
    const toggleButton = document.querySelector(`[onclick="toggleExtensionSettings('${extensionName}')"]`);
    const icon = toggleButton.querySelector('i');
    
    if (!settingsDiv || !toggleButton) {
        console.error('Extension settings elements not found for:', extensionName);
        return;
    }
    
    const isHidden = settingsDiv.style.display === 'none' || 
                    getComputedStyle(settingsDiv).display === 'none';
    
    if (isHidden) {
        // Show settings
        settingsDiv.style.display = 'block';
        icon.className = 'iw iw-chevron-up';
        toggleButton.title = 'Hide Settings';
    } else {
        // Hide settings
        settingsDiv.style.display = 'none';
        icon.className = 'iw iw-chevron-down';
        toggleButton.title = 'Show Settings';
    }
}
</script>
<script>

// Extension management functions
function addNewsItem() {
    const container = document.getElementById('news-items-container');
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

// Add confirmation for maintenance mode toggle and show/hide settings
document.addEventListener('DOMContentLoaded', function() {
    
    // Add current tab tracking to clear cache form
    const clearCacheForm = document.querySelector('form input[value="clear_cache"]').closest('form');
    if (clearCacheForm) {
        const currentTabInput = document.createElement('input');
        currentTabInput.type = 'hidden';
        currentTabInput.name = 'current_tab';
        currentTabInput.value = 'general'; // Default to general
        clearCacheForm.appendChild(currentTabInput);
        
        // Update current tab when switching tabs
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('onclick').match(/showTab\('([^']+)'/)[1];
                currentTabInput.value = tabName;
            });
        });
    }
    
    const maintenanceToggle = document.querySelector('input[name="maintenance_mode"]');
    const maintenanceSettings = document.getElementById('maintenance-settings');
    
    if (maintenanceToggle) {
        // Show/hide maintenance settings based on toggle state
        function toggleMaintenanceSettings() {
            if (maintenanceToggle.checked) {
                maintenanceSettings.style.display = 'block';
                // Add smooth animation
                maintenanceSettings.style.opacity = '0';
                maintenanceSettings.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    maintenanceSettings.style.transition = 'all 0.3s ease';
                    maintenanceSettings.style.opacity = '1';
                    maintenanceSettings.style.transform = 'translateY(0)';
                }, 10);
            } else {
                maintenanceSettings.style.transition = 'all 0.3s ease';
                maintenanceSettings.style.opacity = '0';
                maintenanceSettings.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    maintenanceSettings.style.display = 'none';
                }, 300);
            }
        }
        
        // Initial state - show if maintenance mode is already enabled
        toggleMaintenanceSettings();
        
        // Listen for changes
        maintenanceToggle.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Are you sure you want to enable maintenance mode? This will make your site inaccessible to regular users.')) {
                    this.checked = false;
                    toggleMaintenanceSettings();
                    return;
                }
            }
            toggleMaintenanceSettings();
        });
    }
    
    // Add confirmation for cache clear
    const cacheForm = document.querySelector('form[action*="clear_cache"]');
    if (cacheForm) {
        cacheForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to clear the system cache?')) {
                e.preventDefault();
            }
        });
    }
});
