// Session Service for handling automatic logout and session management

class SessionService {
  private readonly SESSION_KEY = 'islamwiki_session';
  private readonly SESSION_TIMEOUT = 24 * 60 * 60 * 1000; // 24 hours

  // Create a new session
  createSession(userId: string, userData: any): void {
    const session = {
      userId,
      userData,
      createdAt: Date.now(),
      expiresAt: Date.now() + this.SESSION_TIMEOUT
    };

    localStorage.setItem(this.SESSION_KEY, JSON.stringify(session));
  }

  // Get current session
  getSession(): any | null {
    try {
      const sessionData = localStorage.getItem(this.SESSION_KEY);
      if (!sessionData) return null;

      const session = JSON.parse(sessionData);
    
      // Check if session is expired
      if (Date.now() > session.expiresAt) {
        this.clearSession();
        return null;
      }

      return session;
    } catch (error) {
      console.error('Failed to get session:', error);
      this.clearSession();
      return null;
    }
  }

  // Check if session is valid
  isSessionValid(): boolean {
    const session = this.getSession();
    return session !== null;
  }

  // Get user ID from session
  getUserId(): string | null {
    const session = this.getSession();
    return session?.userId || null;
  }

  // Get user data from session
  getUserData(): any | null {
    const session = this.getSession();
    return session?.userData || null;
  }

  // Update session data
  updateSession(userData: any): void {
    const session = this.getSession();
    if (session) {
      session.userData = { ...session.userData, ...userData };
      session.expiresAt = Date.now() + this.SESSION_TIMEOUT;
      localStorage.setItem(this.SESSION_KEY, JSON.stringify(session));
    }
  }

  // Extend session timeout
  extendSession(): void {
    const session = this.getSession();
    if (session) {
      session.expiresAt = Date.now() + this.SESSION_TIMEOUT;
      localStorage.setItem(this.SESSION_KEY, JSON.stringify(session));
    }
  }

  // Clear session
  clearSession(): void {
    localStorage.removeItem(this.SESSION_KEY);
  }

  // Get session age in milliseconds
  getSessionAge(): number {
    const session = this.getSession();
    if (!session) return 0;
    
    return Date.now() - session.createdAt;
  }

  // Get time until session expires in milliseconds
  getTimeUntilExpiry(): number {
    const session = this.getSession();
    if (!session) return 0;
    
    return session.expiresAt - Date.now();
  }

  // Check if session is about to expire (within 1 hour)
  isSessionExpiringSoon(): boolean {
    const timeUntilExpiry = this.getTimeUntilExpiry();
    return timeUntilExpiry > 0 && timeUntilExpiry < 60 * 60 * 1000; // 1 hour
  }
}

// Create and export a singleton instance
const sessionService = new SessionService();
export default sessionService; 