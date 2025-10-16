'use client';

import { useEffect, useState, type ReactNode } from 'react';
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
  const [isClient, setIsClient] = useState(false);

  useEffect(() => {
    setIsClient(true);
  }, []);

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace('/login');
    }
  }, [isLoading, user, router]);

  if (isLoading || (!isClient)) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-600">
        <div className="text-sm">Memuat...</div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 pb-16">
      <Header />
      <main className="mx-auto w-full max-w-5xl px-4 py-4 space-y-4">{children}</main>
      <Navbar />
    </div>
  );
}
