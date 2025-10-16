'use client';

import { useRouter } from 'next/navigation';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useAuth } from '@/contexts/AuthContext';

export default function Header() {
  const router = useRouter();
  const { user } = useAuth();

  if (!user) return null;

  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  return (
    <header className="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-20">
      <div className="mx-auto flex w-full max-w-5xl items-center justify-between px-4 py-3">
        <div>
          <p className="text-xs uppercase tracking-wider text-slate-500">Bukupasar</p>
          <h1 className="text-lg font-semibold text-slate-800">
            {user.market_name || `Pasar ${user.market_id}`}
          </h1>
        </div>

        <button
          onClick={() => router.push('/profile')}
          className="flex items-center gap-3 hover:opacity-80 transition-opacity"
        >
          <div className="text-right hidden sm:block">
            <p className="text-sm font-medium text-slate-700">{user.name}</p>
            <p className="text-xs text-slate-500 capitalize">
              {user.role?.replace('_', ' ') || 'User'}
            </p>
          </div>
          <Avatar className="h-10 w-10 border-2 border-emerald-500">
            <AvatarImage src={user.foto_profile || undefined} alt={user.name} />
            <AvatarFallback className="bg-emerald-100 text-emerald-700 text-sm font-semibold">
              {getInitials(user.name)}
            </AvatarFallback>
          </Avatar>
        </button>
      </div>
    </header>
  );
}
