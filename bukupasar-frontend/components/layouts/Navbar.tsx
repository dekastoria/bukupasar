'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
  BarChart3,
  Home,
  ReceiptText,
  TrendingDown,
  TrendingUp,
} from 'lucide-react';

const navItems = [
  { href: '/dashboard', label: 'Home', icon: Home },
  { href: '/pemasukan/tambah', label: 'Masuk', icon: TrendingUp },
  { href: '/pengeluaran/tambah', label: 'Keluar', icon: TrendingDown },
  { href: '/sewa', label: 'Sewa', icon: ReceiptText },
  { href: '/laporan', label: 'Laporan', icon: BarChart3 },
];

export default function Navbar() {
  const pathname = usePathname();

  return (
    <nav className="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white shadow-lg">
      <ul className="mx-auto flex h-14 w-full max-w-5xl items-center justify-between px-2">
        {navItems.map(({ href, label, icon: Icon }) => {
          const isActive = pathname.startsWith(href);

          return (
            <li key={href} className="flex-1">
              <Link
                href={href}
                className={`flex flex-col items-center justify-center gap-0.5 rounded-lg py-1.5 text-xs transition-colors duration-150 ${
                  isActive ? 'text-emerald-600 font-semibold' : 'text-slate-500 hover:text-slate-700'
                }`}
              >
                <Icon className={`h-4 w-4 ${isActive ? 'scale-110' : ''}`} aria-hidden />
                <span>{label}</span>
              </Link>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
