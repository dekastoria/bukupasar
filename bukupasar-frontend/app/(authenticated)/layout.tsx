'use client';

import { useEffect, type ReactNode } from 'react';
import { useRouter } from 'next/navigation';

import Header from '@/components/layouts/Header';
import Navbar from '@/components/layouts/Navbar';
import { useAuth } from '@/contexts/AuthContext';

export default function AuthenticatedLayout({
  children,
}: {
  children: ReactNode;
}) {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace('/login');
    }
  }, [isLoading, user, router]);

  if (isLoading || (!user && typeof window !== 'undefined')) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-700">
        <div className="text-xl font-medium">Memuat data akun…</div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 pb-24">
      <Header />
      <main className="mx-auto w-full max-w-5xl px-4 py-6 space-y-6">{children}</main>
      <Navbar />
    </div>
  );
}
