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
  phone?: string | null;
  foto_profile?: string | null;
  last_login_at?: string | null;
  market_name?: string | null;
  role?: string | null;
  roles?: string[];
}

interface AuthContextValue {
  user: AuthUser | null;
  isLoading: boolean;
  login: (identifier: string, password: string, marketId: number) => Promise<void>;
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
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const clearSession = useCallback(() => {
    if (mounted) {
      localStorage.removeItem(TOKEN_STORAGE_KEY);
    }
    setAuthToken(null);
    setUser(null);
  }, [mounted]);

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
    if (!mounted) {
      return;
    }

    const token = localStorage.getItem(TOKEN_STORAGE_KEY);

    if (!token) {
      setIsLoading(false);
      return;
    }

    setAuthToken(token);
    refreshUser();
  }, [refreshUser, mounted]);

  useEffect(() => {
    if (!mounted) {
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
  }, [clearSession, refreshUser, mounted]);

  const login = useCallback(
    async (identifier: string, password: string, marketId: number) => {
      try {
        const response = await api.post('/auth/login', {
          identifier,
          password,
          market_id: marketId,
        });

        const token: string | null = response.data?.token ?? response.data?.data?.token ?? null;
        const nextUser = extractUser(response.data);

        if (!token) {
          throw new Error('Token tidak ditemukan pada respons.');
        }

        if (mounted) {
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
