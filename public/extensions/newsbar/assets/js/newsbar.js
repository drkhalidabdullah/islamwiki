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
            pauseBtn.className = 'fas fa-play';
        } else {
            pauseBtn.className = 'fas fa-pause';
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
        
        // Update button icon and title
        if (newsbar.classList.contains('hidden')) {
            console.log('Newsbar is now hidden, showing arrow');
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'fas fa-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow');
            }
            localStorage.setItem('newsbar-hidden', 'true');
        } else {
            console.log('Newsbar is now visible, hiding arrow');
            if (closeBtn) {
                closeBtn.className = 'fas fa-times';
                closeBtn.parentElement.title = 'Close';
                console.log('Updated close button to X');
            }
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'fas fa-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow');
            }
            localStorage.setItem('newsbar-hidden', 'false');
        }
};

// Check if newsbar should be hidden on page load
document.addEventListener("DOMContentLoaded", function() {
    const newsbarHidden = localStorage.getItem('newsbar-hidden');
    console.log('Newsbar hidden state from localStorage:', newsbarHidden);
    
    if (newsbarHidden === 'true') {
        const newsbar = document.querySelector('.newsbar');
        console.log('Found newsbar element:', newsbar);
        
        if (newsbar) {
            newsbar.classList.add('hidden');
            console.log('Added hidden class to newsbar');
            
            // Update button icons to show arrow
            const floatingCloseBtn = document.querySelector('.newsbar-floating-controls .newsbar-floating-close i');
            
            console.log('Floating close button found:', floatingCloseBtn);
            
            if (floatingCloseBtn) {
                floatingCloseBtn.className = 'fas fa-arrow-down';
                floatingCloseBtn.parentElement.title = 'Show Newsbar';
                console.log('Updated floating close button to arrow on init');
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
                    pauseBtn.className = 'fas fa-play';
                }
            }
        });
        
        // Resume on mouse leave
        newsbar.addEventListener('mouseleave', function() {
            if (this.classList.contains('auto-pause')) {
                this.classList.remove('paused');
                const pauseBtn = this.querySelector('.newsbar-pause i');
                if (pauseBtn) {
                    pauseBtn.className = 'fas fa-pause';
                }
            }
        });
    }
});

