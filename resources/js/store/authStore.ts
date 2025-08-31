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
  login: (user: User, token: string) => void;
  logout: () => void;
  setUser: (user: User) => void;
  setToken: (token: string) => void;
  setLoading: (loading: boolean) => void;
  clearAuth: () => void;
  refreshToken: () => string | null;
  isTokenValid: () => boolean;
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
      login: (user: User, token: string) => {
        // Verify the token is valid
        if (!jwtService.verifyToken(token)) {
          console.error('Invalid token provided');
          return;
        }
        
        const expiration = jwtService.getTokenExpiration(token);
        set({
          user,
          token,
          isAuthenticated: true,
          isLoading: false,
          tokenExpiration: expiration,
        });
      },

      logout: () =>
        set({
          user: null,
          token: null,
          isAuthenticated: false,
          isLoading: false,
          tokenExpiration: null,
        }),

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

      refreshToken: () => {
        const { token } = get();
        if (!token) return null;
        
        const newToken = jwtService.refreshToken(token);
        if (newToken) {
          const expiration = jwtService.getTokenExpiration(newToken);
          set({ token: newToken, tokenExpiration: expiration });
        }
        return newToken;
      },

      isTokenValid: () => {
        const { token } = get();
        if (!token) return false;
        return !jwtService.isTokenExpired(token);
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
); 