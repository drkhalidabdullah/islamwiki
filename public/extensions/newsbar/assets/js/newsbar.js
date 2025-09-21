/**
 * News Bar Extension JavaScript
 */

// Newsbar functionality
window.toggleNewsbar = function() {
        const newsbar = document.querySelector('.newsbar');
        const pauseBtn = document.querySelector('.newsbar-pause i');
        
        if (!newsbar || !pauseBtn) return;
        
        newsbar.classList.toggle('paused');
        
        if (newsbar.classList.contains('paused')) {
            pauseBtn.className = 'iw iw-play';
        } else {
            pauseBtn.className = 'iw iw-pause';
        }
};

window.closeNewsbar = function() {
        const newsbar = document.querySelector('.newsbar');
        const closeBtn = document.querySelector('.newsbar-controls .newsbar-close i');
        const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-floating-close i');
        
        if (!newsbar) {
            console.log('Newsbar element not found');
            return;
        }
        
        console.log('Toggling newsbar, current classes:', newsbar.className);
        
        newsbar.classList.toggle('hidden');
        
        // Adjust main content padding based on newsbar visibility
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            if (newsbar.classList.contains('hidden')) {
                mainContent.style.paddingTop = '60px'; // Only header-dashboard
            } else {
                mainContent.style.paddingTop = '120px'; // Header-dashboard + newsbar
            }
        }
        
        // Update header dashboard toggle button state
        const newsToggleBtn = document.getElementById('newsToggleBtn');
        if (newsToggleBtn) {
            if (newsbar.classList.contains('hidden')) {
                newsToggleBtn.classList.remove('active');
            } else {
                newsToggleBtn.classList.add('active');
            }
        }
        
        // Update button icon and title
        if (newsbar.classList.contains('hidden')) {
            console.log('Newsbar is now hidden, showing arrow');
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'iw iw-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow');
            }
            localStorage.setItem('newsbar-hidden', 'true');
        } else {
            console.log('Newsbar is now visible, hiding arrow');
            if (closeBtn) {
                closeBtn.className = 'iw iw-times';
                closeBtn.parentElement.title = 'Close';
                console.log('Updated close button to X');
            }
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'iw iw-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow');
            }
            localStorage.setItem('newsbar-hidden', 'false');
        }
    } else {
        // Newsbar is visible, ensure main content has proper padding
        const newsbar = document.querySelector('.newsbar');
        const mainContent = document.querySelector('.main-content');
        if (newsbar && mainContent && !newsbar.classList.contains('hidden')) {
            mainContent.style.paddingTop = '120px'; // Header-dashboard + newsbar
        }
    }
};

// Check if newsbar should be hidden on page load
document.addEventListener("DOMContentLoaded", function() {
    const newsbarHidden = localStorage.getItem('newsbar-hidden');
    console.log('Newsbar hidden state from localStorage:', newsbarHidden);
    
    // Default to visible (newsbar shown)
    const shouldHide = newsbarHidden === 'true';
    
    const newsbar = document.querySelector('.newsbar');
    const mainContent = document.querySelector('.main-content');
    const newsToggleBtn = document.getElementById('newsToggleBtn');
    
    if (newsbar) {
        if (shouldHide) {
            newsbar.classList.add('hidden');
            console.log('Added hidden class to newsbar');
            
            // Adjust main content padding when newsbar is hidden
            if (mainContent) {
                mainContent.style.paddingTop = '60px'; // Only header-dashboard
            }
            
            // Update toggle button state
            if (newsToggleBtn) {
                newsToggleBtn.classList.remove('active');
            }
            
            // Update button icons to show arrow
            const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-floating-close i');
            
            console.log('Floating close button found:', floatingCloseBtn);
            
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'iw iw-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow on init');
            }
        } else {
            // Newsbar is visible, ensure main content has proper padding
            if (mainContent) {
                mainContent.style.paddingTop = '120px'; // Header-dashboard + newsbar
            }
            
            // Update toggle button state
            if (newsToggleBtn) {
                newsToggleBtn.classList.add('active');
            }
        }
    }
});

// Auto-pause functionality (if enabled)
document.addEventListener("DOMContentLoaded", function() {
    const newsbar = document.querySelector('.newsbar');
    if (newsbar) {
        // Pause on hover
        newsbar.addEventListener('mouseenter', function() {
            if (this.classList.contains('auto-pause')) {
                this.classList.add('paused');
                const pauseBtn = this.querySelector('.newsbar-pause i');
                if (pauseBtn) {
                    pauseBtn.className = 'iw iw-play';
                }
            }
        });
        
        // Resume on mouse leave
        newsbar.addEventListener('mouseleave', function() {
            if (this.classList.contains('auto-pause')) {
                this.classList.remove('paused');
                const pauseBtn = this.querySelector('.newsbar-pause i');
                if (pauseBtn) {
                    pauseBtn.className = 'iw iw-pause';
                }
            }
        });
    }
});

