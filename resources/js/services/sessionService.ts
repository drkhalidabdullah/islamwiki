// Session Service for handling automatic logout and session management
import { useAuthStore } from '../store/authStore';

class SessionService {
  private timeoutId: NodeJS.Timeout | null = null;
  private readonly SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
  private readonly WARNING_TIME = 5 * 60 * 1000; // 5 minutes before timeout

  // Start session monitoring
  startSessionMonitoring(): void {
    this.resetSessionTimer();
    this.setupActivityListeners();
  }

  // Reset session timer
  resetSessionTimer(): void {
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
    }

    this.timeoutId = setTimeout(() => {
      this.handleSessionTimeout();
    }, this.SESSION_TIMEOUT);

    // Set warning timer
    setTimeout(() => {
      this.showSessionWarning();
    }, this.SESSION_TIMEOUT - this.WARNING_TIME);
  }

  // Setup activity listeners
  private setupActivityListeners(): void {
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    
    events.forEach(event => {
      document.addEventListener(event, () => {
        this.resetSessionTimer();
      }, { passive: true });
    });
  }

  // Handle session timeout
  private handleSessionTimeout(): void {
    const { logout } = useAuthStore.getState();
    
    // Show timeout notification
    this.showSessionExpiredNotification();
    
    // Logout user
    logout();
    
    // Redirect to login
    window.location.href = '/login?message=Session expired due to inactivity';
  }

  // Show session warning
  private showSessionWarning(): void {
    // Create warning notification
    const warning = document.createElement('div');
    warning.id = 'session-warning';
    warning.className = 'fixed top-4 right-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-6 py-3 rounded-lg shadow-lg z-50';
    warning.innerHTML = `
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium">Session expires in 5 minutes</span>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-yellow-600 hover:text-yellow-800">×</button>
      </div>
      <div class="mt-2 text-xs">Click anywhere to extend your session</div>
    `;
    
    document.body.appendChild(warning);
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
      if (warning.parentElement) {
        warning.remove();
      }
    }, 10000);
  }

  // Show session expired notification
  private showSessionExpiredNotification(): void {
    const notification = document.createElement('div');
    notification.id = 'session-expired';
    notification.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 text-red-800 px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = `
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium">Session expired</span>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-600 hover:text-red-800">×</button>
      </div>
      <div class="mt-2 text-xs">You have been logged out due to inactivity</div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 15 seconds
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 15000);
  }

  // Stop session monitoring
  stopSessionMonitoring(): void {
    if (this.timeoutId) {
      clearTimeout(this.timeoutId);
      this.timeoutId = null;
    }
  }

  // Extend session manually
  extendSession(): void {
    this.resetSessionTimer();
    
    // Remove any existing warnings
    const warning = document.getElementById('session-warning');
    if (warning) {
      warning.remove();
    }
  }

  // Get remaining session time in minutes
  getRemainingSessionTime(): number {
    if (!this.timeoutId) return 0;
    
    // This is a simplified calculation - in a real app you'd track the actual timeout
    return Math.ceil(this.SESSION_TIMEOUT / 60000);
  }
}

export const sessionService = new SessionService();
export default sessionService; 