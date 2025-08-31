// JWT Service for handling authentication tokens
import jwt from 'jsonwebtoken';

// In production, this would come from environment variables
const JWT_SECRET = 'islamwiki-secret-key-change-in-production';
const JWT_EXPIRES_IN = '24h';

export interface JWTPayload {
  userId: number;
  email: string;
  role: string;
  username: string;
}

export interface DecodedToken extends JWTPayload {
  iat: number;
  exp: number;
}

class JWTService {
  // Generate JWT token
  generateToken(payload: JWTPayload): string {
    return jwt.sign(payload, JWT_SECRET, { expiresIn: JWT_EXPIRES_IN });
  }

  // Verify and decode JWT token
  verifyToken(token: string): DecodedToken | null {
    try {
      return jwt.verify(token, JWT_SECRET) as DecodedToken;
    } catch (error) {
      console.error('JWT verification failed:', error);
      return null;
    }
  }

  // Check if token is expired
  isTokenExpired(token: string): boolean {
    const decoded = this.verifyToken(token);
    if (!decoded) return true;
    
    const currentTime = Math.floor(Date.now() / 1000);
    return decoded.exp < currentTime;
  }

  // Get token expiration time
  getTokenExpiration(token: string): Date | null {
    const decoded = this.verifyToken(token);
    if (!decoded) return null;
    
    return new Date(decoded.exp * 1000);
  }

  // Refresh token (generate new token with same payload)
  refreshToken(token: string): string | null {
    const decoded = this.verifyToken(token);
    if (!decoded) return null;
    
    const { iat, exp, ...payload } = decoded;
    return this.generateToken(payload);
  }

  // Extract token from Authorization header
  extractTokenFromHeader(authHeader: string): string | null {
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return null;
    }
    return authHeader.substring(7);
  }
}

export const jwtService = new JWTService();
export default jwtService; 