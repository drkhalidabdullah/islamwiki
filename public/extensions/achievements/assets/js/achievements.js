/**
 * Achievement System JavaScript
 * Handles client-side functionality for the achievement system
 * 
 * @version 1.0.0
 */

class AchievementSystem {
    constructor() {
        this.apiBase = '/api/achievements.php';
        this.notifications = [];
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadUserLevel();
        this.loadNotifications();
        this.setupFilters();
        this.setupProgressBars();
    }
    
    setupEventListeners() {
        // Filter buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('filter-option')) {
                this.handleFilterClick(e.target);
            }
        });
        
        // Achievement cards
        document.addEventListener('click', (e) => {
            if (e.target.closest('.achievement-card')) {
                this.handleAchievementClick(e.target.closest('.achievement-card'));
            }
        });
        
        // Notification close
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('notification-close')) {
                this.closeNotification(e.target.closest('.notification-popup'));
            }
        });
    }
    
    async loadUserLevel() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_user_level`);
            const data = await response.json();
            
            if (data.success) {
                this.updateLevelDisplay(data.level);
            }
        } catch (error) {
            console.error('Error loading user level:', error);
        }
    }
    
    updateLevelDisplay(levelData) {
        const levelBadge = document.querySelector('.level-number');
        const levelText = document.querySelector('.level-text');
        const levelTitle = document.querySelector('.level-title');
        const progressBar = document.querySelector('.level-progress-bar');
        const progressText = document.querySelector('.level-stats');
        
        if (levelBadge) levelBadge.textContent = levelData.level;
        if (levelText) levelText.textContent = 'Level';
        if (levelTitle) levelTitle.textContent = `Level ${levelData.level}`;
        
        if (progressBar && levelData.xp_to_next_level > 0) {
            const progress = (levelData.current_level_xp / (levelData.current_level_xp + levelData.xp_to_next_level)) * 100;
            progressBar.style.width = `${progress}%`;
        }
        
        if (progressText) {
            progressText.innerHTML = `
                <span>${levelData.current_level_xp} XP</span>
                <span>${levelData.total_achievements} Achievements</span>
            `;
        }
    }
    
    async loadNotifications() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_notifications`);
            const data = await response.json();
            
            if (data.success) {
                this.displayNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    displayNotifications(notifications) {
        notifications.forEach(notification => {
            if (!notification.is_read) {
                this.showNotification(notification);
            }
        });
    }
    
    showNotification(notification) {
        const notificationEl = document.createElement('div');
        notificationEl.className = 'notification-popup';
        notificationEl.innerHTML = `
            <div class="notification-header">
                <div class="notification-icon">
                    <i class="${notification.achievement_icon || 'fas fa-trophy'}"></i>
                </div>
                <div>
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                </div>
                <button class="notification-close" style="margin-left: auto; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
            </div>
        `;
        
        document.body.appendChild(notificationEl);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            this.closeNotification(notificationEl);
        }, 5000);
        
        // Mark as read
        this.markNotificationAsRead(notification.id);
    }
    
    closeNotification(notificationEl) {
        notificationEl.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notificationEl.parentNode) {
                notificationEl.parentNode.removeChild(notificationEl);
            }
        }, 300);
    }
    
    async markNotificationAsRead(notificationId) {
        try {
            await fetch(`${this.apiBase}?action=mark_notification_read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ notification_id: notificationId })
            });
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    setupFilters() {
        const filterOptions = document.querySelectorAll('.filter-option');
        filterOptions.forEach(option => {
            option.addEventListener('click', () => {
                this.handleFilterClick(option);
            });
        });
    }
    
    handleFilterClick(option) {
        // Remove active class from all options in the same group
        const group = option.closest('.filter-group');
        group.querySelectorAll('.filter-option').forEach(opt => {
            opt.classList.remove('active');
        });
        
        // Add active class to clicked option
        option.classList.add('active');
        
        // Apply filter
        this.applyFilters();
    }
    
    applyFilters() {
        const activeFilters = this.getActiveFilters();
        const achievementCards = document.querySelectorAll('.achievement-card');
        
        achievementCards.forEach(card => {
            let shouldShow = true;
            
            // Category filter
            if (activeFilters.category && activeFilters.category !== 'all') {
                const cardCategory = card.dataset.category;
                if (cardCategory !== activeFilters.category) {
                    shouldShow = false;
                }
            }
            
            // Type filter
            if (activeFilters.type && activeFilters.type !== 'all') {
                const cardType = card.dataset.type;
                if (cardType !== activeFilters.type) {
                    shouldShow = false;
                }
            }
            
            // Rarity filter
            if (activeFilters.rarity && activeFilters.rarity !== 'all') {
                const cardRarity = card.dataset.rarity;
                if (cardRarity !== activeFilters.rarity) {
                    shouldShow = false;
                }
            }
            
            // Status filter
            if (activeFilters.status && activeFilters.status !== 'all') {
                const isCompleted = card.classList.contains('completed');
                const isLocked = card.classList.contains('locked');
                
                if (activeFilters.status === 'completed' && !isCompleted) {
                    shouldShow = false;
                } else if (activeFilters.status === 'in_progress' && (isCompleted || isLocked)) {
                    shouldShow = false;
                } else if (activeFilters.status === 'locked' && !isLocked) {
                    shouldShow = false;
                }
            }
            
            // Show/hide card
            card.style.display = shouldShow ? 'block' : 'none';
        });
    }
    
    getActiveFilters() {
        const filters = {};
        
        // Get category filter
        const categoryFilter = document.querySelector('.filter-option[data-filter="category"].active');
        if (categoryFilter) {
            filters.category = categoryFilter.dataset.value;
        }
        
        // Get type filter
        const typeFilter = document.querySelector('.filter-option[data-filter="type"].active');
        if (typeFilter) {
            filters.type = typeFilter.dataset.value;
        }
        
        // Get rarity filter
        const rarityFilter = document.querySelector('.filter-option[data-filter="rarity"].active');
        if (rarityFilter) {
            filters.rarity = rarityFilter.dataset.value;
        }
        
        // Get status filter
        const statusFilter = document.querySelector('.filter-option[data-filter="status"].active');
        if (statusFilter) {
            filters.status = statusFilter.dataset.value;
        }
        
        return filters;
    }
    
    setupProgressBars() {
        const progressBars = document.querySelectorAll('.progress-fill');
        progressBars.forEach(bar => {
            const progress = bar.dataset.progress || 0;
            setTimeout(() => {
                bar.style.width = `${progress}%`;
            }, 100);
        });
    }
    
    handleAchievementClick(card) {
        const achievementId = card.dataset.achievementId;
        if (achievementId) {
            this.showAchievementDetails(achievementId);
        }
    }
    
    async showAchievementDetails(achievementId) {
        try {
            const response = await fetch(`${this.apiBase}?action=get_achievement_details&id=${achievementId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayAchievementModal(data.achievement);
            }
        } catch (error) {
            console.error('Error loading achievement details:', error);
        }
    }
    
    displayAchievementModal(achievement) {
        const modal = document.createElement('div');
        modal.className = 'achievement-modal';
        modal.innerHTML = `
            <div class="modal-overlay">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${achievement.name}</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="achievement-icon-large">
                            <i class="${achievement.icon}"></i>
                        </div>
                        <div class="achievement-details">
                            <p class="achievement-description">${achievement.description}</p>
                            ${achievement.long_description ? `<p class="achievement-long-description">${achievement.long_description}</p>` : ''}
                            <div class="achievement-rewards">
                                <div class="reward-item">
                                    <i class="fas fa-star"></i>
                                    <span>${achievement.points} Points</span>
                                </div>
                                <div class="reward-item">
                                    <i class="fas fa-bolt"></i>
                                    <span>${achievement.xp_reward} XP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal handlers
        modal.querySelector('.modal-close').addEventListener('click', () => {
            this.closeModal(modal);
        });
        
        modal.querySelector('.modal-overlay').addEventListener('click', (e) => {
            if (e.target === modal.querySelector('.modal-overlay')) {
                this.closeModal(modal);
            }
        });
    }
    
    closeModal(modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            if (modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }, 300);
    }
    
    async loadLeaderboard() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_leaderboard`);
            const data = await response.json();
            
            if (data.success) {
                this.displayLeaderboard(data.leaderboard);
            }
        } catch (error) {
            console.error('Error loading leaderboard:', error);
        }
    }
    
    displayLeaderboard(leaderboard) {
        const leaderboardContainer = document.querySelector('.leaderboard-items');
        if (!leaderboardContainer) return;
        
        leaderboardContainer.innerHTML = leaderboard.map((user, index) => `
            <div class="leaderboard-item">
                <div class="leaderboard-rank rank-${index < 3 ? index + 1 : 'other'}">
                    ${index + 1}
                </div>
                <div class="leaderboard-user">
                    <div class="leaderboard-username">${user.display_name || user.username}</div>
                    <div class="leaderboard-level">Level ${user.level}</div>
                </div>
                <div class="leaderboard-stats">
                    <div class="leaderboard-xp">${user.total_xp} XP</div>
                    <div class="leaderboard-achievements">${user.total_achievements} Achievements</div>
                </div>
            </div>
        `).join('');
    }
    
    async loadAchievementStats() {
        try {
            const response = await fetch(`${this.apiBase}?action=get_achievement_stats`);
            const data = await response.json();
            
            if (data.success) {
                this.displayAchievementStats(data.stats);
            }
        } catch (error) {
            console.error('Error loading achievement stats:', error);
        }
    }
    
    displayAchievementStats(stats) {
        // Update total achievements
        const totalEl = document.querySelector('.stat-total-achievements');
        if (totalEl) {
            totalEl.textContent = stats.total_achievements;
        }
        
        // Update category stats
        const categoryStatsEl = document.querySelector('.category-stats');
        if (categoryStatsEl && stats.by_category) {
            categoryStatsEl.innerHTML = stats.by_category.map(cat => `
                <div class="category-stat" style="background-color: ${cat.color}">
                    ${cat.name}: ${cat.count}
                </div>
            `).join('');
        }
        
        // Update rarity stats
        const rarityStatsEl = document.querySelector('.rarity-stats');
        if (rarityStatsEl && stats.by_rarity) {
            rarityStatsEl.innerHTML = stats.by_rarity.map(rarity => `
                <div class="rarity-stat rarity-${rarity.rarity}">
                    ${rarity.rarity}: ${rarity.count}
                </div>
            `).join('');
        }
    }
    
    // Public methods for external use
    awardXP(xp, activityType = 'general', activityData = null) {
        return this.makeAPICall('award_xp', {
            xp_amount: xp,
            activity_type: activityType,
            activity_data: activityData
        });
    }
    
    awardPoints(points, activityType = 'general', activityData = null) {
        return this.makeAPICall('award_points', {
            points_amount: points,
            activity_type: activityType,
            activity_data: activityData
        });
    }
    
    async makeAPICall(action, data = {}) {
        try {
            const response = await fetch(`${this.apiBase}?action=${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            return await response.json();
        } catch (error) {
            console.error(`Error making API call ${action}:`, error);
            return { success: false, message: 'Network error' };
        }
    }
}

// Initialize the achievement system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.achievementSystem = new AchievementSystem();
});

// Add CSS for modal and animations
const style = document.createElement('style');
style.textContent = `
    .achievement-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }
    
    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        max-width: 500px;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        animation: slideInUp 0.3s ease;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #7f8c8d;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .achievement-icon-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f39c12, #e67e22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
        margin: 0 auto 20px;
    }
    
    .achievement-long-description {
        color: #7f8c8d;
        font-style: italic;
        margin-top: 10px;
    }
    
    @keyframes slideInUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
