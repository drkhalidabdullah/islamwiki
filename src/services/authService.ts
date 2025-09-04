import apiClient from './apiClient';

export interface LoginCredentials {
  username: string;
  password: string;
}

export interface RegisterData {
  username: string;
  email: string;
  password: string;
  password_confirmation: string;
  first_name: string;
  last_name: string;
}

export interface AuthResponse {
  success: boolean;
  data: {
    user: {
      id: number;
      username: string;
      email: string;
      first_name: string;
      last_name: string;
      display_name: string;
      roles: string[];
      is_active: boolean;
    };
    token: string;
    expires_at: number;
  };
  message: string;
}

export interface UserProfile {
  id: number;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  display_name: string;
  bio?: string;
  avatar?: string;
  roles: string[];
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface PasswordChangeData {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface ProfileUpdateData {
  first_name?: string;
  last_name?: string;
  display_name?: string;
  bio?: string;
}

class AuthService {
  private tokenKey = 'islamwiki_auth_token';
  private userKey = 'islamwiki_user_data';

  /**
   * User login
   */
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/api/', {
        action: 'login',
        email: credentials.username, // The API expects 'email' field
        password: credentials.password
      }) as { data: AuthResponse };
      
      if (response.data.success) {
        this.setToken(response.data.data.token);
        // Convert the user data to match UserProfile interface
        const userProfile: UserProfile = {
          ...response.data.data.user,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        };
        this.setUser(userProfile);
      }
      
      return response.data;
    } catch (error: any) {
      throw new Error(error instanceof Error ? error.message : 'Login failed');
    }
  }

  /**
   * User registration
   */
  async register(userData: RegisterData): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/auth/register', userData) as { data: AuthResponse };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Registration failed');
    }
  }

  /**
   * User logout
   */
  async logout(): Promise<void> {
    try {
      await apiClient.post('/auth/logout', {}) as { data: any };
      this.clearAuth();
    } catch (error: any) {
      // Even if logout fails, clear local auth
      this.clearAuth();
      throw new Error(error.response?.data?.error || 'Logout failed');
    }
  }

  /**
   * Refresh authentication token
   */
  async refreshToken(): Promise<AuthResponse> {
    try {
      const response = await apiClient.post('/auth/refresh', {}) as { data: AuthResponse };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Token refresh failed');
    }
  }

  /**
   * Get user profile
   */
  async getProfile(): Promise<UserProfile> {
    try {
      const response = await apiClient.get('/auth/profile') as { data: { data: UserProfile } };
      return response.data.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to get profile');
    }
  }

  /**
   * Update user profile
   */
  async updateProfile(data: ProfileUpdateData): Promise<UserProfile> {
    try {
      const response = await apiClient.put('/auth/update-profile', data) as { data: { data: UserProfile } };
      return response.data.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to update profile');
    }
  }

  /**
   * Change password
   */
  async changePassword(data: PasswordChangeData): Promise<{ message: string }> {
    try {
      const response = await apiClient.put('/auth/change-password', data) as { data: { message: string } };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to change password');
    }
  }

  /**
   * Forgot password
   */
  async forgotPassword(email: string): Promise<{ message: string }> {
    try {
      const response = await apiClient.post('/auth/forgot-password', { email }) as { data: { message: string } };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to process password reset');
    }
  }

  /**
   * Reset password
   */
  async resetPassword(token: string, password: string, password_confirmation: string): Promise<{ message: string }> {
    try {
      const response = await apiClient.post('/auth/reset-password', {
        token,
        password,
        password_confirmation
      }) as { data: { message: string } };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to reset password');
    }
  }

  /**
   * Verify email
   */
  async verifyEmail(token: string): Promise<{ message: string }> {
    try {
      const response = await apiClient.post('/auth/verify-email', { token }) as { data: { message: string } };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to verify email');
    }
  }

  /**
   * Resend verification email
   */
  async resendVerification(email: string): Promise<{ message: string }> {
    try {
      const response = await apiClient.post('/auth/resend-verification', { email }) as { data: { message: string } };
      return response.data;
    } catch (error: any) {
      throw new Error(error.response?.data?.error || 'Failed to resend verification');
    }
  }

  /**
   * Check if user is authenticated
   */
  isAuthenticated(): boolean {
    const token = this.getToken();
    if (!token) return false;

    // Check if token is expired
    const user = this.getUser();
    if (!user) return false;

    return true;
  }

  /**
   * Get current user
   */
  getCurrentUser(): UserProfile | null {
    return this.getUser();
  }

  /**
   * Check if user has role
   */
  hasRole(role: string): boolean {
    const user = this.getUser();
    if (!user) return false;

    return user.roles.includes(role);
  }

  /**
   * Check if user has permission
   */
  hasPermission(permission: string): boolean {
    const user = this.getUser();
    if (!user) return false;

    // Simple permission check based on roles
    const rolePermissions: Record<string, string[]> = {
      'admin': ['*'],
      'moderator': ['content.moderate', 'users.view', 'comments.moderate'],
      'editor': ['content.create', 'content.edit', 'content.publish'],
      'user': ['content.view', 'comments.create', 'profile.edit']
    };

    const userRoles = user.roles;
    
    for (const role of userRoles) {
      const permissions = rolePermissions[role] || [];
      if (permissions.includes('*') || permissions.includes(permission)) {
        return true;
      }
    }

    return false;
  }

  // Private helper methods

  private setToken(token: string): void {
    localStorage.setItem(this.tokenKey, token);
  }

  private getToken(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  private setUser(user: UserProfile): void {
    localStorage.setItem(this.userKey, JSON.stringify(user));
  }

  private getUser(): UserProfile | null {
    const userData = localStorage.getItem(this.userKey);
    return userData ? JSON.parse(userData) : null;
  }

  private clearAuth(): void {
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.userKey);
  }
}

export const authService = new AuthService(); 