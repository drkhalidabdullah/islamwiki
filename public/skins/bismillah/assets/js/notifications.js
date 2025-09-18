// Notification System
class NotificationManager {
    constructor() {
        this.badge = document.getElementById('notificationBadge');
        this.content = document.getElementById('notificationContent');
        this.markAllReadBtn = document.getElementById('markAllRead');
        this.isLoading = false;
        this.notifications = [];
        this.unreadCount = 0;
        
        this.init();
    }
    
    init() {
        // Load notifications on page load
        this.loadNotifications();
        
        // Set up event listeners
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => this.markAllAsRead());
        }
        
        // Set up click listener for notification dropdown trigger
        const notificationTrigger = document.querySelector('.sidebar-notifications .user-icon-trigger');
        if (notificationTrigger) {
            notificationTrigger.addEventListener('click', (e) => {
                // Small delay to ensure dropdown is open before loading
                setTimeout(() => {
                    this.loadNotifications();
                }, 100);
            });
        }
        
        // Auto-refresh notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications(true);
        }, 30000);
    }
    
    async loadNotifications(silent = false) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        
        if (!silent) {
            this.showLoading();
        }
        
        try {
            console.log('Loading notifications...');
            const response = await fetch('/api/ajax/get_notifications.php?limit=10', {
                method: 'GET',
                credentials: 'same-origin', // Include cookies for session
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log('Response text:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Failed to parse JSON response:', parseError);
                console.error('Response was:', responseText);
                throw new Error('Invalid JSON response from server');
            }
            
            if (data.success) {
                console.log('Notifications loaded successfully:', data);
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
                this.updateBadge();
                this.renderNotifications();
                
                // If user is not logged in, show a message
                if (data.message === 'User not logged in') {
                    if (!silent) {
                        this.showError('Please log in to view notifications');
                    }
                }
            } else {
                console.error('Failed to load notifications:', data.message);
                if (!silent) {
                    this.showError('Failed to load notifications: ' + (data.message || 'Unknown error'));
                }
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            if (!silent) {
                this.showError('Error loading notifications: ' + error.message);
            }
        } finally {
            this.isLoading = false;
        }
    }
    
    updateBadge() {
        if (this.badge) {
            if (this.unreadCount > 0) {
                this.badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                this.badge.style.display = 'flex';
            } else {
                this.badge.style.display = 'none';
            }
        }
    }
    
    renderNotifications() {
        if (!this.content) return;
        
        if (this.notifications.length === 0) {
            this.content.innerHTML = '<div class="notification-empty">No new notifications</div>';
            return;
        }
        
        const html = this.notifications.map(notification => this.createNotificationHTML(notification)).join('');
        this.content.innerHTML = html;
        
        // Add click handlers to notification items
        this.content.querySelectorAll('.notification-item').forEach((item, index) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const notification = this.notifications[index];
                this.handleNotificationClick(notification);
            });
        });
    }
    
    createNotificationHTML(notification) {
        const timeAgo = this.getTimeAgo(notification.time);
        const typeIcon = this.getTypeIcon(notification.type);
        
        // Build stats display if available
        let statsHtml = '';
        if (notification.stats) {
            const stats = [];
            if (notification.stats.likes) stats.push(`<span class="stat"><i class="iw iw-heart"></i> ${notification.stats.likes}</span>`);
            if (notification.stats.comments) stats.push(`<span class="stat"><i class="iw iw-comment"></i> ${notification.stats.comments}</span>`);
            if (notification.stats.views) stats.push(`<span class="stat"><i class="iw iw-eye"></i> ${notification.stats.views}</span>`);
            if (stats.length > 0) {
                statsHtml = `<div class="notification-stats">${stats.join(' ')}</div>`;
            }
        }
        
        return `
            <a href="${notification.url}" class="notification-item ${notification.unread ? 'unread' : ''}" data-type="${notification.type}">
                <img src="${notification.avatar}" alt="Avatar" class="notification-avatar" onerror="this.src='/assets/images/default-avatar.svg'">
                <div class="notification-details">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-description">${notification.description}</div>
                    <div class="notification-content-text">${notification.content}</div>
                    ${statsHtml}
                    <div class="notification-time">${timeAgo}</div>
                </div>
                <i class="iw ${typeIcon} notification-type-icon"></i>
            </a>
        `;
    }
    
    getTypeIcon(type) {
        const icons = {
            'message': 'iw-envelope',
            'post': 'iw-share',
            'article': 'iw-file-alt',
            'watchlist': 'iw-eye',
            'interaction': 'iw-heart',
            'comment': 'iw-comment',
            'follow': 'iw-user-plus',
            'like': 'iw-heart',
            'share': 'iw-share',
            'view': 'iw-eye'
        };
        return icons[type] || 'iw-bell';
    }
    
    getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes}m ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours}h ago`;
        } else if (diffInSeconds < 604800) {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days}d ago`;
        } else {
            return date.toLocaleDateString();
        }
    }
    
    handleNotificationClick(notification) {
        // Mark as read when clicked
        if (notification.unread) {
            this.markAsRead(notification.id);
        }
        
        // Navigate to the notification URL
        window.location.href = notification.url;
    }
    
    async markAsRead(notificationId) {
        try {
            // In a real implementation, you would send a request to mark this specific notification as read
            // For now, we'll just update the local state
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification && notification.unread) {
                notification.unread = false;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    async markAllAsRead() {
        try {
            // In a real implementation, you would send a request to mark all notifications as read
            // For now, we'll just update the local state
            this.notifications.forEach(notification => {
                notification.unread = false;
            });
            this.unreadCount = 0;
            this.updateBadge();
            this.renderNotifications();
            
            // Show success message
            if (window.showToast) {
                window.showToast('All notifications marked as read', 'success');
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }
    
    showLoading() {
        if (this.content) {
            this.content.innerHTML = '<div class="notification-loading">Loading notifications...</div>';
        }
    }
    
    showError(message) {
        if (this.content) {
            this.content.innerHTML = `<div class="notification-empty">${message}</div>`;
        }
        console.error('Notification error:', message);
    }
    
    // Debug method to check system status
    debugStatus() {
        console.log('Notification Manager Debug Status:');
        console.log('- Badge element:', this.badge);
        console.log('- Content element:', this.content);
        console.log('- Mark all read button:', this.markAllReadBtn);
        console.log('- Current notifications:', this.notifications.length);
        console.log('- Unread count:', this.unreadCount);
        console.log('- Is loading:', this.isLoading);
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page with notifications
    if (document.getElementById('notificationBadge')) {
        window.notificationManager = new NotificationManager();
        
        // Add debug function to global scope
        window.debugNotifications = function() {
            if (window.notificationManager) {
                window.notificationManager.debugStatus();
            } else {
                console.log('Notification manager not initialized');
            }
        };
        
        console.log('Notification manager initialized');
    } else {
        console.log('Notification badge not found, skipping notification manager initialization');
    }
});
