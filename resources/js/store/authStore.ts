import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import jwtService from '../services/jwtService';

interface User {
  id: number;
  username: string;
  email: string;
  first_name: string;
  last_name: string;
  role_name: string;
  status: string;
  created_at: string;
}

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  tokenExpiration: Date | null;
}

interface AuthActions {
  // eslint-disable-next-line no-unused-vars
  login: (user: User, token: string) => Promise<void>;
  logout: () => void;
  // eslint-disable-next-line no-unused-vars
  setUser: (user: User) => void;
  // eslint-disable-next-line no-unused-vars
  setToken: (token: string) => void;
  // eslint-disable-next-line no-unused-vars
  setLoading: (loading: boolean) => void;
  clearAuth: () => void;
  refreshToken: () => Promise<string | null>;
  isTokenValid: () => Promise<boolean>;
  validateAndRestoreSession: () => Promise<boolean>;
  checkStoredAuth: () => boolean;
}

type AuthStore = AuthState & AuthActions;

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      // State
      user: null,
      token: null,
      isAuthenticated: false,
      isLoading: false,
      tokenExpiration: null,

      // Actions
      login: async (user: User, token: string) => {
        console.log('🔐 Login called with:', { user: user.username, token: token.substring(0, 20) + '...' });
        
        // Verify the token is valid
        const isValid = await jwtService.verifyToken(token);
        if (!isValid) {
          console.error('❌ Invalid token provided');
          return;
        }
        
        const expiration = await jwtService.getTokenExpiration(token);
        console.log('✅ Login successful, setting state with expiration:', expiration);
        
        set({
          user,
          token,
          isAuthenticated: true,
          isLoading: false,
          tokenExpiration: expiration,
        });
        
        // Verify state was set
        const currentState = get();
        console.log('🔍 Current state after login:', {
          isAuthenticated: currentState.isAuthenticated,
          hasUser: !!currentState.user,
          hasToken: !!currentState.token
        });
      },

      logout: () => {
        console.log('🚪 Logout called, clearing authentication state');
        set({
          user: null,
          token: null,
          isAuthenticated: false,
          isLoading: false,
          tokenExpiration: null,
        });
        
        // Verify state was cleared
        const currentState = get();
        console.log('🔍 Current state after logout:', {
          isAuthenticated: currentState.isAuthenticated,
          hasUser: !!currentState.user,
          hasToken: !!currentState.token
        });
      },

      setUser: (user: User) =>
        set({
          user,
          isAuthenticated: !!user,
        }),

      setToken: (token: string) =>
        set({
          token,
          isAuthenticated: !!token,
        }),

      setLoading: (isLoading: boolean) =>
        set({ isLoading }),

      clearAuth: () =>
        set({
          user: null,
          token: null,
          isAuthenticated: false,
          isLoading: false,
          tokenExpiration: null,
        }),

      refreshToken: async () => {
        const { token } = get();
        if (!token) return null;
        
        const newToken = await jwtService.refreshToken(token);
        if (newToken) {
          const expiration = await jwtService.getTokenExpiration(newToken);
          set({ token: newToken, tokenExpiration: expiration });
        }
        return newToken;
      },

      isTokenValid: async () => {
        const { token } = get();
        if (!token) return false;
        return !(await jwtService.isTokenExpired(token));
      },

      // New method to validate and restore session
      validateAndRestoreSession: async () => {
        console.log('🔄 Starting session validation...');
        const { token, user } = get();
        
        console.log('🔍 Current stored state:', {
          hasToken: !!token,
          hasUser: !!user,
          tokenLength: token ? token.length : 0,
          username: user?.username
        });
        
        if (!token || !user) {
          console.log('❌ No token or user found, clearing auth state');
          set({ isAuthenticated: false, isLoading: false });
          return false;
        }

        try {
          // Check if token is expired
          const isExpired = await jwtService.isTokenExpired(token);
          
          if (isExpired) {
            // Try to refresh the token
            const newToken = await jwtService.refreshToken(token);
            if (newToken) {
              const expiration = await jwtService.getTokenExpiration(newToken);
              set({ 
                token: newToken, 
                tokenExpiration: expiration,
                isAuthenticated: true,
                isLoading: false 
              });
              return true;
            } else {
              // Token refresh failed, clear auth
              set({ 
                user: null, 
                token: null, 
                isAuthenticated: false, 
                isLoading: false,
                tokenExpiration: null 
              });
              return false;
            }
          } else {
            // Token is still valid
            set({ 
              isAuthenticated: true, 
              isLoading: false 
            });
            return true;
          }
        } catch (error) {
          console.error('Error validating session:', error);
          // Clear auth on error
          set({ 
            user: null, 
            token: null, 
            isAuthenticated: false, 
            isLoading: false,
            tokenExpiration: null 
          });
          return false;
        }
      },

      // Simple method to check if there's stored authentication data
      checkStoredAuth: () => {
        const { token, user } = get();
        const hasStoredAuth = !!(token && user);
        console.log('🔍 Checking stored auth:', { hasStoredAuth, hasToken: !!token, hasUser: !!user });
        return hasStoredAuth;
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated,
        tokenExpiration: state.tokenExpiration,
      }),
    }
  )
); 