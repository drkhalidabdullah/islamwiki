<?php
// Right Sidebar - Friends and Messages
if (!is_logged_in() || !$enable_social) return;
?>

<div class="right-sidebar" id="rightSidebar" style="background: #2c3e50 !important; z-index: 10001 !important;">
    <!-- Friends Icon -->
    <div class="right-sidebar-section">
        <div class="right-sidebar-item" title="Friends" onclick="window.location.href='/pages/social/friends.php'">
            <i class="iw iw-users"></i>
            <span class="notification-badge">4</span>
        </div>
    </div>
    
    <!-- Right sidebar now only contains friends icon -->
</div>

<!-- Dropdowns moved to header dashboard -->

<style>
.right-sidebar {
    position: fixed !important;
    top: 60px !important;
    right: 0 !important;
    width: 60px !important;
    height: calc(100vh - 60px) !important;
    background: #2c3e50 !important;
    border-left: 1px solid rgba(255, 255, 255, 0.1) !important;
    z-index: 10001 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    padding: 20px 0 !important;
    box-sizing: border-box !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure right sidebar is visible on desktop by default */
@media (min-width: 769px) {
    .right-sidebar {
        display: flex !important;
    }
}

.right-sidebar-section {
    position: relative;
    margin-bottom: 15px;
}

.right-sidebar-item {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #34495e;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
}

.right-sidebar-item:hover {
    background: #3498db;
    transform: scale(1.05);
}

.right-sidebar-item i {
    font-size: 18px;
    color: #fff;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
    line-height: 1.2;
}

/* Dropdown Styles */
       .right-sidebar-dropdown {
           position: fixed !important;
           right: 80px !important;
           top: 70px !important;
           width: 300px !important;
           background: #2c3e50 !important;
           border: 1px solid rgba(255, 255, 255, 0.1) !important;
           border-radius: 8px !important;
           box-shadow: -4px 0 12px rgba(0, 0, 0, 0.3) !important;
           z-index: 9999999 !important;
           display: none !important;
           opacity: 0 !important;
           transform: translateX(20px) !important;
           transition: all 0.3s ease !important;
           visibility: hidden !important;
       }

.right-sidebar-dropdown.show {
    display: block !important;
    opacity: 1 !important;
    transform: translateX(0) !important;
    visibility: visible !important;
    background: red !important;
    border: 2px solid yellow !important;
}

.dropdown-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-header h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.view-all {
    color: #666;
    font-size: 12px;
    text-decoration: none;
    transition: color 0.2s;
}

.view-all:hover {
    color: #fff;
}

.mark-all-read {
    background: none;
    border: none;
    color: #666;
    font-size: 12px;
    cursor: pointer;
    transition: color 0.2s;
}

.mark-all-read:hover {
    color: #fff;
}

.dropdown-content {
    max-height: 300px;
    overflow-y: auto;
}

.message-item, .notification-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.message-item:hover, .notification-item:hover {
    background-color: #34495e;
}

.message-avatar {
    position: relative;
    margin-right: 12px;
}

.message-avatar img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.unread-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #ff4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
}

.message-info, .notification-info {
    flex: 1;
    min-width: 0;
}

.sender-name {
    display: block;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-preview, .notification-text {
    display: block;
    color: #666;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.message-time, .notification-time {
    display: block;
    color: #999;
    font-size: 11px;
    margin-top: 2px;
}

.notification-item i {
    font-size: 16px;
    color: #666;
    margin-right: 12px;
    width: 20px;
    text-align: center;
}

.dropdown-footer {
    padding: 15px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.dropdown-link {
    color: #6c5ce7;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
}

       .dropdown-link:hover {
           color: #5a4fcf;
       }

       /* Loading and empty states */
       .loading-messages, .loading-notifications {
           padding: 20px;
           text-align: center;
           color: #666;
           font-style: italic;
       }

       .no-messages, .no-notifications {
           padding: 20px;
           text-align: center;
           color: #666;
           font-style: italic;
       }

/* Responsive */
@media (max-width: 768px) {
    .right-sidebar {
        display: none !important;
    }
}
</style>

<script>
       // Right sidebar now only contains friends icon
       // Messages and notifications moved to header dashboard
       </script>
