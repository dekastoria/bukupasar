'use client';

import { LogOut } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useAuth } from '@/contexts/AuthContext';

export default function Header() {
  const { user, logout } = useAuth();

  return (
    <header className="bg-white shadow-sm border-b border-slate-200">
      <div className="mx-auto flex w-full max-w-5xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-sm uppercase tracking-[0.2em] text-slate-500">Bukupasar</p>
          <h1 className="text-3xl font-semibold text-slate-800">Dasbor Pasar</h1>
          {user ? (
            <p className="mt-1 text-base text-slate-600">
              Pasar ID <span className="font-medium text-slate-700">{user.market_id}</span>
            </p>
          ) : null}
        </div>

        <div className="flex items-center gap-3">
          {user ? (
            <div className="text-right">
              <p className="text-lg font-medium text-slate-700">{user.name}</p>
              {user.role ? (
                <p className="text-sm text-slate-500">{user.role}</p>
              ) : null}
            </div>
          ) : null}

          <Button
            type="button"
            onClick={logout}
            variant="outline"
            className="h-12 px-4 text-lg flex items-center gap-2"
          >
            <LogOut className="h-5 w-5" />
            Keluar
          </Button>
        </div>
      </div>
    </header>
  );
}
