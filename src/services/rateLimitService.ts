// Rate Limiting Service for preventing form spam and abuse
interface RateLimitEntry {
  count: number;
  firstAttempt: number;
  blocked: boolean;
}

class RateLimitService {
  private rateLimits = new Map<string, RateLimitEntry>();
  private readonly MAX_ATTEMPTS = 5; // Maximum attempts per window
  private readonly WINDOW_MS = 15 * 60 * 1000; // 15 minutes
  private readonly BLOCK_DURATION = 30 * 60 * 1000; // 30 minutes

  // Check if action is allowed
  isAllowed(action: string, identifier: string = 'default'): boolean {
    const key = `${action}:${identifier}`;
    const now = Date.now();
    const entry = this.rateLimits.get(key);

    // Clean up expired entries
    if (entry && now - entry.firstAttempt > this.WINDOW_MS) {
      this.rateLimits.delete(key);
      return true;
    }

    // Check if blocked
    if (entry?.blocked) {
      if (now - entry.firstAttempt > this.BLOCK_DURATION) {
        // Unblock after duration
        this.rateLimits.delete(key);
        return true;
      }
      return false;
    }

    // Allow if no entry exists
    if (!entry) {
      this.rateLimits.set(key, {
        count: 1,
        firstAttempt: now,
        blocked: false
      });
      return true;
    }

    // Check if within limits
    if (entry.count < this.MAX_ATTEMPTS) {
      entry.count++;
      return true;
    }

    // Block if limit exceeded
    entry.blocked = true;
    return false;
  }

  // Record an attempt
  recordAttempt(action: string, identifier: string = 'default'): void {
    const key = `${action}:${identifier}`;
    const now = Date.now();
    const entry = this.rateLimits.get(key);

    if (entry) {
      entry.count++;
      
      // Block if limit exceeded
      if (entry.count >= this.MAX_ATTEMPTS) {
        entry.blocked = true;
      }
    } else {
      this.rateLimits.set(key, {
        count: 1,
        firstAttempt: now,
        blocked: false
      });
    }
  }

  // Get remaining attempts
  getRemainingAttempts(action: string, identifier: string = 'default'): number {
    const key = `${action}:${identifier}`;
    const entry = this.rateLimits.get(key);
    
    if (!entry) return this.MAX_ATTEMPTS;
    if (entry.blocked) return 0;
    
    return Math.max(0, this.MAX_ATTEMPTS - entry.count);
  }

  // Get time until reset
  getTimeUntilReset(action: string, identifier: string = 'default'): number {
    const key = `${action}:${identifier}`;
    const entry = this.rateLimits.get(key);
    
    if (!entry) return 0;
    
    const now = Date.now();
    const timeSinceFirst = now - entry.firstAttempt;
    
    if (entry.blocked) {
      return Math.max(0, this.BLOCK_DURATION - timeSinceFirst);
    }
    
    return Math.max(0, this.WINDOW_MS - timeSinceFirst);
  }

  // Check if blocked
  isBlocked(action: string, identifier: string = 'default'): boolean {
    const key = `${action}:${identifier}`;
    const entry = this.rateLimits.get(key);
    
    if (!entry) return false;
    
    const now = Date.now();
    
    // Clean up expired entries
    if (now - entry.firstAttempt > this.WINDOW_MS) {
      this.rateLimits.delete(key);
      return false;
    }
    
    return entry.blocked;
  }

  // Reset rate limit for specific action
  reset(action: string, identifier: string = 'default'): void {
    const key = `${action}:${identifier}`;
    this.rateLimits.delete(key);
  }

  // Reset all rate limits
  resetAll(): void {
    this.rateLimits.clear();
  }

  // Get rate limit status
  getStatus(action: string, identifier: string = 'default'): {
    allowed: boolean;
    remaining: number;
    blocked: boolean;
    timeUntilReset: number;
  } {
    return {
      allowed: this.isAllowed(action, identifier),
      remaining: this.getRemainingAttempts(action, identifier),
      blocked: this.isBlocked(action, identifier),
      timeUntilReset: this.getTimeUntilReset(action, identifier)
    };
  }
}

export const rateLimitService = new RateLimitService();
export default rateLimitService; 