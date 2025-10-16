'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

import { cn } from '@/lib/utils';

const tabs = [
  { href: '/laporan/harian', label: 'Laporan Harian' },
  { href: '/laporan/ringkasan', label: 'Laporan Ringkasan' },
];

export default function LaporanLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();

  return (
    <div className="space-y-4">
      <header className="space-y-1">
        <h2 className="text-lg font-semibold text-slate-800">Laporan Keuangan</h2>
        <p className="text-sm text-slate-600">
          Lihat detail transaksi harian atau ringkasan periode tertentu.
        </p>
      </header>

      <nav className="grid grid-cols-2 gap-2 sm:w-2/3 md:w-1/2">
        {tabs.map((tab) => {
          const active = pathname.startsWith(tab.href);
          return (
            <Link
              key={tab.href}
              href={tab.href}
              className={cn(
                'rounded-xl border px-4 py-2 text-center text-sm font-semibold transition-all duration-150',
                active
                  ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow'
                  : 'border-slate-200 bg-white text-slate-500 hover:border-emerald-200 hover:text-emerald-600'
              )}
            >
              {tab.label}
            </Link>
          );
        })}
      </nav>

      {children}
    </div>
  );
}
