# 03-FRONTEND-GUIDE.md
# Bukupasar — Frontend Implementation Guide

**Next.js 14 + Tailwind CSS + shadcn/ui** untuk SPA mobile-first inputer.

---

## 📋 Table of Contents

1. [Next.js Project Setup](#nextjs-project-setup)
2. [Project Structure](#project-structure)
3. [Authentication Flow](#authentication-flow)
4. [Pages Specification](#pages-specification)
5. [Components Library](#components-library)
6. [UX Guidelines for Elderly](#ux-guidelines-for-elderly)
7. [API Integration](#api-integration)
8. [Theme & Styling](#theme--styling)

---

## 1. Next.js Project Setup

### Create Next.js Project

```bash
# Navigate to development folder
cd C:\laragon\www

# Create Next.js 14 with App Router
npx create-next-app@latest bukupasar-frontend

# Options:
# ✔ TypeScript? › Yes
# ✔ ESLint? › Yes  
# ✔ Tailwind CSS? › Yes
# ✔ `src/` directory? › Yes
# ✔ App Router? › Yes
# ✔ Import alias (@/*)? › Yes

cd bukupasar-frontend
```

### Install Dependencies

```bash
# shadcn/ui components
npx shadcn-ui@latest init

# Options:
# ✔ Style: Default
# ✔ Base color: Sky
# ✔ CSS variables: Yes

# Install shadcn components
npx shadcn-ui@latest add button
npx shadcn-ui@latest add input
npx shadcn-ui@latest add card
npx shadcn-ui@latest add form
npx shadcn-ui@latest add table
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add select
npx shadcn-ui@latest add toast
npx shadcn-ui@latest add tabs
npx shadcn-ui@latest add badge
npx shadcn-ui@latest add calendar
npx shadcn-ui@latest add popover

# TanStack Query (data fetching)
npm install @tanstack/react-query

# React Hook Form + Zod validation
npm install react-hook-form @hookform/resolvers zod

# Date handling
npm install date-fns

# HTTP client
npm install axios

# Icons
npm install lucide-react
```

### Environment Variables

**Create `.env.local`:**
```env
NEXT_PUBLIC_API_URL=http://bukupasar-backend.test/api
NEXT_PUBLIC_APP_NAME=Bukupasar
```

---

## 2. Project Structure

```
bukupasar-frontend/
├── src/
│   ├── app/
│   │   ├── layout.tsx          # Root layout
│   │   ├── page.tsx            # Home/redirect to login
│   │   ├── login/
│   │   │   └── page.tsx        # Login page
│   │   ├── (authenticated)/    # Protected routes group
│   │   │   ├── layout.tsx      # Authenticated layout
│   │   │   ├── dashboard/
│   │   │   │   └── page.tsx    # Dashboard overview
│   │   │   ├── pemasukan/
│   │   │   │   ├── page.tsx    # List pemasukan
│   │   │   │   └── tambah/page.tsx
│   │   │   ├── pengeluaran/
│   │   │   │   ├── page.tsx    # List pengeluaran
│   │   │   │   └── tambah/page.tsx
│   │   │   ├── sewa/
│   │   │   │   └── page.tsx    # Form bayar sewa
│   │   │   └── laporan/
│   │   │       ├── harian/page.tsx
│   │   │       ├── bulanan/page.tsx
│   │   │       └── laba-rugi/page.tsx
│   │   │
│   │   └── api/                # API route handlers (optional)
│   │
│   ├── components/
│   │   ├── ui/                 # shadcn/ui components
│   │   ├── forms/
│   │   │   ├── TransactionForm.tsx
│   │   │   ├── PaymentForm.tsx
│   │   │   └── DatePicker.tsx
│   │   ├── layouts/
│   │   │   ├── Header.tsx
│   │   │   ├── Navbar.tsx
│   │   │   └── Footer.tsx
│   │   ├── reports/
│   │   │   ├── DailyReportTable.tsx
│   │   │   └── SummaryCard.tsx
│   │   └── shared/
│   │       ├── LoadingSpinner.tsx
│   │       └── ErrorMessage.tsx
│   │
│   ├── lib/
│   │   ├── api.ts              # Axios instance
│   │   ├── auth.ts             # Auth helpers
│   │   └── utils.ts            # Utility functions
│   │
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useTransactions.ts
│   │   ├── usePayments.ts
│   │   └── useSettings.ts
│   │
│   ├── types/
│   │   ├── api.ts              # API response types
│   │   ├── models.ts           # Data models
│   │   └── forms.ts            # Form schemas
│   │
│   └── contexts/
│       ├── AuthContext.tsx
│       └── ThemeContext.tsx
│
├── public/
│   ├── logo.png
│   └── favicon.ico
├── .env.local
├── package.json
├── tailwind.config.ts
└── tsconfig.json
```

---

## 3. Authentication Flow

### Auth Context

**File:** `src/contexts/AuthContext.tsx`

```tsx
'use client';

import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { useRouter } from 'next/navigation';
import api from '@/lib/api';

interface User {
  id: number;
  name: string;
  username: string;
  market_id: number;
  role: string;
}

interface AuthContextType {
  user: User | null;
  login: (username: string, password: string, marketId: number) => Promise<void>;
  logout: () => Promise<void>;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    // Check if token exists on mount
    const token = localStorage.getItem('token');
    if (token) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      fetchUser();
    } else {
      setIsLoading(false);
    }
  }, []);

  const fetchUser = async () => {
    try {
      const response = await api.get('/auth/user');
      setUser(response.data.user);
    } catch (error) {
      localStorage.removeItem('token');
      delete api.defaults.headers.common['Authorization'];
    } finally {
      setIsLoading(false);
    }
  };

  const login = async (username: string, password: string, marketId: number) => {
    const response = await api.post('/auth/login', {
      username,
      password,
      market_id: marketId,
    });

    const { token, user } = response.data;
    
    localStorage.setItem('token', token);
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    
    setUser(user);
    router.push('/dashboard');
  };

  const logout = async () => {
    await api.post('/auth/logout');
    localStorage.removeItem('token');
    delete api.defaults.headers.common['Authorization'];
    setUser(null);
    router.push('/login');
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, isLoading }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}
```

### Login Page

**File:** `src/app/login/page.tsx`

```tsx
'use client';

import { useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from '@/components/ui/use-toast';

export default function LoginPage() {
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [marketId, setMarketId] = useState('1');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      await login(username, password, parseInt(marketId));
    } catch (error: any) {
      toast({
        title: 'Login Gagal',
        description: error.response?.data?.message || 'Username atau password salah',
        variant: 'destructive',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <Card className="w-full max-w-md">
        <CardHeader>
          <CardTitle className="text-2xl text-center">Bukupasar</CardTitle>
          <p className="text-center text-gray-600">Masuk ke akun Anda</p>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="username" className="text-base">Username</Label>
              <Input
                id="username"
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
                className="h-12 text-lg"
                autoComplete="username"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password" className="text-base">Password</Label>
              <Input
                id="password"
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="h-12 text-lg"
                autoComplete="current-password"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="marketId" className="text-base">ID Pasar</Label>
              <Input
                id="marketId"
                type="number"
                value={marketId}
                onChange={(e) => setMarketId(e.target.value)}
                required
                className="h-12 text-lg"
              />
            </div>

            <Button
              type="submit"
              className="w-full h-12 text-lg"
              disabled={isLoading}
            >
              {isLoading ? 'Memproses...' : 'Masuk'}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
```

### Protected Route Middleware

**File:** `src/app/(authenticated)/layout.tsx`

```tsx
'use client';

import { useAuth } from '@/contexts/AuthContext';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import Header from '@/components/layouts/Header';
import Navbar from '@/components/layouts/Navbar';

export default function AuthenticatedLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }
  }, [user, isLoading, router]);

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-lg">Loading...</div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      <div className="pb-20">
        {children}
      </div>
      <Navbar />
    </div>
  );
}
```

---

## 4. Pages Specification

### Dashboard Overview

**File:** `src/app/(authenticated)/dashboard/page.tsx`

```tsx
'use client';

import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, TrendingDown, Wallet } from 'lucide-react';

export default function DashboardPage() {
  const { data: summary, isLoading } = useQuery({
    queryKey: ['dashboard-summary'],
    queryFn: async () => {
      const today = new Date().toISOString().split('T')[0];
      const response = await api.get(`/reports/daily?date=${today}`);
      return response.data;
    },
  });

  if (isLoading) {
    return <div className="p-4">Loading...</div>;
  }

  const pemasukan = summary?.pemasukan || 0;
  const pengeluaran = summary?.pengeluaran || 0;
  const saldo = pemasukan - pengeluaran;

  return (
    <div className="p-4 space-y-4">
      <h1 className="text-2xl font-bold">Dashboard</h1>
      
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-gray-600 flex items-center gap-2">
              <TrendingUp className="h-4 w-4" />
              Pemasukan Hari Ini
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">
              Rp {pemasukan.toLocaleString('id-ID')}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-gray-600 flex items-center gap-2">
              <TrendingDown className="h-4 w-4" />
              Pengeluaran Hari Ini
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-600">
              Rp {pengeluaran.toLocaleString('id-ID')}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm text-gray-600 flex items-center gap-2">
              <Wallet className="h-4 w-4" />
              Saldo
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className={`text-2xl font-bold ${saldo >= 0 ? 'text-blue-600' : 'text-red-600'}`}>
              Rp {saldo.toLocaleString('id-ID')}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
```

### Transaction Input Form (Wizard)

**File:** `src/app/(authenticated)/pemasukan/tambah/page.tsx`

```tsx
'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useQuery, useMutation } from '@tanstack/react-query';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import api from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { toast } from '@/components/ui/use-toast';

const formSchema = z.object({
  tanggal: z.string(),
  subkategori: z.string().min(1, 'Kategori harus dipilih'),
  jumlah: z.number().min(1, 'Jumlah harus lebih dari 0'),
  catatan: z.string().optional(),
});

export default function TambahPemasukanPage() {
  const router = useRouter();
  const [step, setStep] = useState(1);

  const { data: categories } = useQuery({
    queryKey: ['categories', 'pemasukan'],
    queryFn: async () => {
      const response = await api.get('/categories?jenis=pemasukan&aktif=1');
      return response.data;
    },
  });

  const form = useForm({
    resolver: zodResolver(formSchema),
    defaultValues: {
      tanggal: new Date().toISOString().split('T')[0],
      subkategori: '',
      jumlah: 0,
      catatan: '',
    },
  });

  const createMutation = useMutation({
    mutationFn: async (data: any) => {
      return api.post('/transactions', {
        ...data,
        jenis: 'pemasukan',
      });
    },
    onSuccess: () => {
      toast({
        title: 'Berhasil',
        description: 'Pemasukan berhasil ditambahkan',
      });
      router.push('/pemasukan');
    },
    onError: (error: any) => {
      toast({
        title: 'Gagal',
        description: error.response?.data?.message || 'Terjadi kesalahan',
        variant: 'destructive',
      });
    },
  });

  const onSubmit = form.handleSubmit((data) => {
    createMutation.mutate(data);
  });

  return (
    <div className="p-4 max-w-2xl mx-auto">
      <h1 className="text-2xl font-bold mb-6">Tambah Pemasukan</h1>

      <form onSubmit={onSubmit} className="space-y-6">
        {/* Step 1: Kategori */}
        {step === 1 && (
          <div className="space-y-4">
            <div>
              <Label className="text-lg">Pilih Kategori</Label>
              <Select {...form.register('subkategori')} className="h-14 text-lg mt-2">
                <option value="">-- Pilih Kategori --</option>
                {categories?.map((cat: any) => (
                  <option key={cat.id} value={cat.nama}>
                    {cat.nama}
                  </option>
                ))}
              </Select>
              {form.formState.errors.subkategori && (
                <p className="text-red-600 text-sm mt-1">
                  {form.formState.errors.subkategori.message}
                </p>
              )}
            </div>

            <Button
              type="button"
              className="w-full h-14 text-lg"
              onClick={() => {
                if (form.getValues('subkategori')) {
                  setStep(2);
                }
              }}
            >
              Lanjut
            </Button>
          </div>
        )}

        {/* Step 2: Nominal & Detail */}
        {step === 2 && (
          <div className="space-y-4">
            <div>
              <Label className="text-lg">Tanggal</Label>
              <Input
                type="date"
                {...form.register('tanggal')}
                className="h-14 text-lg mt-2"
              />
            </div>

            <div>
              <Label className="text-lg">Jumlah (Rp)</Label>
              <Input
                type="number"
                {...form.register('jumlah', { valueAsNumber: true })}
                className="h-14 text-lg mt-2"
                placeholder="0"
              />
              {form.formState.errors.jumlah && (
                <p className="text-red-600 text-sm mt-1">
                  {form.formState.errors.jumlah.message}
                </p>
              )}
            </div>

            <div>
              <Label className="text-lg">Catatan (Opsional)</Label>
              <Textarea
                {...form.register('catatan')}
                className="text-lg mt-2"
                rows={3}
              />
            </div>

            <div className="flex gap-2">
              <Button
                type="button"
                variant="outline"
                className="flex-1 h-14 text-lg"
                onClick={() => setStep(1)}
              >
                Kembali
              </Button>
              <Button
                type="submit"
                className="flex-1 h-14 text-lg"
                disabled={createMutation.isPending}
              >
                {createMutation.isPending ? 'Menyimpan...' : 'Simpan'}
              </Button>
            </div>
          </div>
        )}
      </form>
    </div>
  );
}
```

---

## 5. Components Library

### Mobile Navigation

**File:** `src/components/layouts/Navbar.tsx`

```tsx
'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Home, TrendingUp, TrendingDown, Receipt, BarChart } from 'lucide-react';

const navItems = [
  { href: '/dashboard', label: 'Home', icon: Home },
  { href: '/pemasukan', label: 'Masuk', icon: TrendingUp },
  { href: '/pengeluaran', label: 'Keluar', icon: TrendingDown },
  { href: '/sewa', label: 'Sewa', icon: Receipt },
  { href: '/laporan', label: 'Laporan', icon: BarChart },
];

export default function Navbar() {
  const pathname = usePathname();

  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
      <div className="flex justify-around items-center h-16">
        {navItems.map((item) => {
          const Icon = item.icon;
          const isActive = pathname.startsWith(item.href);

          return (
            <Link
              key={item.href}
              href={item.href}
              className={`flex flex-col items-center justify-center flex-1 h-full ${
                isActive ? 'text-primary' : 'text-gray-600'
              }`}
            >
              <Icon className="h-6 w-6" />
              <span className="text-xs mt-1">{item.label}</span>
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
```

---

## 6. UX Guidelines for Elderly

### Design Principles

**Typography:**
- Base font size: 18px minimum
- Headings: 24px-32px
- Button text: 18px-20px
- High contrast: text dark on light background

**Touch Targets:**
- Minimum button height: 48px (44px iOS minimum)
- Minimum width: 48px
- Spacing between buttons: 8px minimum

**Colors:**
- High contrast ratios (WCAG AAA preferred)
- Clear visual hierarchy
- Use color + icons (not color alone)
- Error messages: red + icon + clear text

**Forms:**
- Labels above fields (not placeholder)
- One question per screen (wizard approach)
- Inline validation with clear messages
- Large, clear submit buttons

**Language:**
- Simple, direct language
- Avoid technical jargon
- Positive framing ("Tambah Pemasukan" not "Input Transaction")
- Clear error messages with solutions

### Example Component (Large Button)

```tsx
import { Button } from '@/components/ui/button';

export function LargeButton({ children, ...props }: any) {
  return (
    <Button
      className="h-14 text-lg font-medium min-w-[200px]"
      {...props}
    >
      {children}
    </Button>
  );
}
```

---

## 7. API Integration

### Axios Instance

**File:** `src/lib/api.ts`

```ts
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### React Query Setup

**File:** `src/app/layout.tsx`

```tsx
'use client';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from '@/contexts/AuthContext';
import { Toaster } from '@/components/ui/toaster';
import { useState } from 'react';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  const [queryClient] = useState(() => new QueryClient({
    defaultOptions: {
      queries: {
        staleTime: 60 * 1000, // 1 minute
        refetchOnWindowFocus: false,
      },
    },
  }));

  return (
    <html lang="id">
      <body>
        <QueryClientProvider client={queryClient}>
          <AuthProvider>
            {children}
            <Toaster />
          </AuthProvider>
        </QueryClientProvider>
      </body>
    </html>
  );
}
```

---

## 8. Theme & Styling

### Tailwind Config

**File:** `tailwind.config.ts`

```ts
import type { Config } from 'tailwindcss';

const config: Config = {
  darkMode: ['class'],
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0ea5e9',
          foreground: '#ffffff',
        },
      },
      fontSize: {
        base: '18px',
      },
    },
  },
  plugins: [require('tailwindcss-animate')],
};

export default config;
```

### Global CSS (UX Enhancements)

**File:** `src/app/globals.css`

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  /* Larger base font for elderly users */
  html {
    font-size: 18px;
  }

  /* High contrast focus */
  *:focus-visible {
    outline: 3px solid #0ea5e9;
    outline-offset: 2px;
  }

  /* Touch-friendly spacing */
  button, a {
    min-height: 44px;
    min-width: 44px;
  }
}
```

---

## 📝 Next Steps

After completing frontend:
1. ✅ Next.js project created and configured
2. ✅ Authentication flow implemented
3. ✅ Core pages created (Dashboard, Forms, Reports)
4. ✅ Mobile-first responsive design
5. ➡️ **Proceed to:** [04-DEPLOYMENT-OPS.md](04-DEPLOYMENT-OPS.md)

---

**Document Status:** ✅ Complete | **Last Updated:** 2025-01-15
