// JWT Service for handling authentication tokens
import * as jose from 'jose';

// In production, this would come from environment variables
const JWT_SECRET = new TextEncoder().encode('islamwiki-secret-key-change-in-production');
const JWT_EXPIRES_IN = '24h';

export interface JWTPayload {
  userId: number;
  email: string;
  role: string;
  username: string;
  [key: string]: any; // Allow additional properties for jose compatibility
}

export interface DecodedToken extends JWTPayload {
  iat: number;
  exp: number;
}

class JWTService {
  // Generate JWT token
  async generateToken(payload: JWTPayload): Promise<string> {
    try {
      const token = await new jose.SignJWT(payload)
        .setProtectedHeader({ alg: 'HS256' })
        .setIssuedAt()
        .setExpirationTime(JWT_EXPIRES_IN)
        .sign(JWT_SECRET);
      return token;
    } catch (error) {
      console.error('JWT generation failed:', error);
      throw new Error('Failed to generate JWT token');
    }
  }

  // Verify and decode JWT token
  async verifyToken(token: string): Promise<DecodedToken | null> {
    try {
      const { payload } = await jose.jwtVerify(token, JWT_SECRET);
      return payload as DecodedToken;
    } catch (error) {
      console.error('JWT verification failed:', error);
      return null;
    }
  }

  // Check if token is expired
  async isTokenExpired(token: string): Promise<boolean> {
    const decoded = await this.verifyToken(token);
    if (!decoded) return true;
    
    const currentTime = Math.floor(Date.now() / 1000);
    return decoded.exp < currentTime;
  }

  // Get token expiration time
  async getTokenExpiration(token: string): Promise<Date | null> {
    const decoded = await this.verifyToken(token);
    if (!decoded) return null;
    
    return new Date(decoded.exp * 1000);
  }

  // Refresh token (generate new token with same payload)
  async refreshToken(token: string): Promise<string | null> {
    const decoded = await this.verifyToken(token);
    if (!decoded) return null;
    
    const { iat, exp, ...payload } = decoded as any;
    return this.generateToken(payload as JWTPayload);
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