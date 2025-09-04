// JWT Service for handling authentication tokens
import { jwtDecode } from 'jwt-decode';

interface JWTPayload {
  sub: string;
  username: string;
  role: string;
  exp: number;
  iat: number;
}

class JWTService {
  private readonly TOKEN_KEY = 'islamwiki_auth_token';
  private readonly REFRESH_TOKEN_KEY = 'islamwiki_refresh_token';

  // Store JWT token
  setToken(token: string): void {
    localStorage.setItem(this.TOKEN_KEY, token);
  }

  // Get JWT token
  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  // Remove JWT token
  removeToken(): void {
    localStorage.removeItem(this.TOKEN_KEY);
  }

  // Store refresh token
  setRefreshToken(token: string): void {
    localStorage.setItem(this.REFRESH_TOKEN_KEY, token);
  }

  // Get refresh token
  getRefreshToken(): string | null {
    return localStorage.getItem(this.REFRESH_TOKEN_KEY);
  }

  // Remove refresh token
  removeRefreshToken(): void {
    localStorage.removeItem(this.REFRESH_TOKEN_KEY);
  }

  // Decode JWT token
  decodeToken(token: string): JWTPayload | null {
    try {
      return jwtDecode<JWTPayload>(token);
    } catch (error) {
      console.error('Failed to decode JWT token:', error);
      return null;
    }
  }

  // Check if token is expired
  isTokenExpired(token: string): boolean {
    try {
      const decoded = this.decodeToken(token);
    if (!decoded) return true;
    
    const currentTime = Math.floor(Date.now() / 1000);
    return decoded.exp < currentTime;
    } catch (error) {
      console.error('Failed to check token expiration:', error);
      return true;
    }
  }

  // Get token expiration time
  getTokenExpiration(token: string): Date | null {
    try {
      const decoded = this.decodeToken(token);
    if (!decoded) return null;
    
    return new Date(decoded.exp * 1000);
    } catch (error) {
      console.error('Failed to get token expiration:', error);
      return null;
    }
  }

  // Verify token validity
  async verifyToken(token: string): Promise<boolean> {
    try {
      if (!token) return false;
      
      const decoded = this.decodeToken(token);
      if (!decoded) return false;
      
      if (this.isTokenExpired(token)) return false;
      
      return true;
    } catch (error) {
      console.error('Token verification failed:', error);
      return false;
    }
  }

  // Get user ID from token
  getUserId(token: string): string | null {
    try {
      const decoded = this.decodeToken(token);
      return decoded?.sub || null;
    } catch (error) {
      console.error('Failed to get user ID from token:', error);
      return null;
    }
  }

  // Get username from token
  getUsername(token: string): string | null {
    try {
      const decoded = this.decodeToken(token);
      return decoded?.username || null;
    } catch (error) {
      console.error('Failed to get username from token:', error);
      return null;
    }
  }

  // Get user role from token
  getUserRole(token: string): string | null {
    try {
      const decoded = this.decodeToken(token);
      return decoded?.role || null;
    } catch (error) {
      console.error('Failed to get user role from token:', error);
      return null;
    }
  }

  // Mock refresh token method for development
  async refreshToken(token: string): Promise<string | null> {
    try {
      // For development, just return the same token if it's not expired
      if (!this.isTokenExpired(token)) {
        return token;
      }
      return null;
    } catch (error) {
      console.error('Failed to refresh token:', error);
      return null;
    }
  }

  // Clear all tokens
  clearTokens(): void {
    this.removeToken();
    this.removeRefreshToken();
  }
}

// Create and export a singleton instance
const jwtService = new JWTService();
export default jwtService; 