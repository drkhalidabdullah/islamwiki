// Activity Page JavaScript

// Filter activities by type
function filterActivities(type) {
    const activityItems = document.querySelectorAll('.activity-item');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    // Update active filter button
    filterButtons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.filter === type) {
            btn.classList.add('active');
        }
    });
    
    // Show/hide activity items
    activityItems.forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.classList.remove('hidden');
        } else {
            item.classList.add('hidden');
        }
    });
}

// Initialize filter buttons
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const filterType = this.dataset.filter;
            filterActivities(filterType);
        });
    });
    
    // Add hover effects to activity items
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f9fafb';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'transparent';
        });
    });
    
    // Initialize like buttons
    const likeButtons = document.querySelectorAll('.like-btn');
    likeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            toggleLike(postId, this);
        });
    });
});

// Toggle like on a post
function toggleLike(postId, button) {
    const isLiked = button.classList.contains('liked');
    const likeCount = button.querySelector('span');
    const currentCount = parseInt(likeCount.textContent);
    
    // Update UI immediately for better UX
    if (isLiked) {
        button.classList.remove('liked');
        likeCount.textContent = currentCount - 1;
    } else {
        button.classList.add('liked');
        likeCount.textContent = currentCount + 1;
    }
    
    // Send request to server
    const formData = new FormData();
    formData.append('post_id', postId);
    
    fetch('/api/ajax/toggle_like.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert UI changes if server request failed
            if (isLiked) {
                button.classList.add('liked');
                likeCount.textContent = currentCount;
            } else {
                button.classList.remove('liked');
                likeCount.textContent = currentCount;
            }
            showNotification(data.message || 'Failed to update like', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert UI changes if request failed
        if (isLiked) {
            button.classList.add('liked');
            likeCount.textContent = currentCount;
        } else {
            button.classList.remove('liked');
            likeCount.textContent = currentCount;
        }
        showNotification('An error occurred while updating like', 'error');
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification-content {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .notification-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    `;
    document.head.appendChild(style);
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Initialize page
console.log('Activity page loaded');
