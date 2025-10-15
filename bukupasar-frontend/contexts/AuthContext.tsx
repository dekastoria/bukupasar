'use client';

import { useRouter } from 'next/navigation';
import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';

import api, { setAuthToken } from '@/lib/api';

const TOKEN_STORAGE_KEY = 'bukupasar_token';

export interface AuthUser {
  id: number;
  name: string;
  username: string;
  market_id: number;
  email?: string | null;
  role?: string | null;
  roles?: string[];
}

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  login: (username: string, password: string, marketId: number) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

const extractUser = (payload: any): AuthUser | null => {
  if (!payload) {
    return null;
  }

  if (payload.user) {
    return payload.user as AuthUser;
  }

  if (payload.data?.user) {
    return payload.data.user as AuthUser;
  }

  if (payload.data) {
    return payload.data as AuthUser;
  }

  return payload as AuthUser;
};

export function AuthProvider({ children }: { children: ReactNode }) {
  const router = useRouter();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const clearSession = useCallback(() => {
    if (typeof window !== 'undefined') {
      localStorage.removeItem(TOKEN_STORAGE_KEY);
    }
    setAuthToken(null);
    setUser(null);
  }, []);

  const refreshUser = useCallback(async () => {
    try {
      setIsLoading(true);
      const response = await api.get('/auth/user');
      const nextUser = extractUser(response.data);

      if (nextUser) {
        setUser(nextUser);
      } else {
        clearSession();
      }
    } catch (error) {
      clearSession();
    } finally {
      setIsLoading(false);
    }
  }, [clearSession]);

  useEffect(() => {
    if (typeof window === 'undefined') {
      return;
    }

    const token = localStorage.getItem(TOKEN_STORAGE_KEY);

    if (!token) {
      setIsLoading(false);
      return;
    }

    setAuthToken(token);
    refreshUser();
  }, [refreshUser]);

  useEffect(() => {
    if (typeof window === 'undefined') {
      return;
    }

    const handleStorage = (event: StorageEvent) => {
      if (event.key !== TOKEN_STORAGE_KEY) {
        return;
      }

      if (!event.newValue) {
        clearSession();
      } else {
        setAuthToken(event.newValue);
        refreshUser();
      }
    };

    window.addEventListener('storage', handleStorage);

    return () => {
      window.removeEventListener('storage', handleStorage);
    };
  }, [clearSession, refreshUser]);

  const login = useCallback(
    async (username: string, password: string, marketId: number) => {
      try {
        const response = await api.post('/auth/login', {
          username,
          password,
          market_id: marketId,
        });

        const token: string | null = response.data?.token ?? response.data?.data?.token ?? null;
        const nextUser = extractUser(response.data);

        if (!token) {
          throw new Error('Token tidak ditemukan pada respons.');
        }

        if (typeof window !== 'undefined') {
          localStorage.setItem(TOKEN_STORAGE_KEY, token);
        }

        setAuthToken(token);
        setUser(nextUser);
        router.push('/dashboard');
      } catch (error) {
        clearSession();
        throw error;
      }
    },
    [clearSession, router],
  );

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      clearSession();
      router.push('/login');
    }
  }, [clearSession, router]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isLoading,
      login,
      logout,
      refreshUser,
    }),
    [user, isLoading, login, logout, refreshUser],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = (): AuthContextValue => {
  const context = useContext(AuthContext);

  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }

  return context;
};
