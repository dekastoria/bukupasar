'use client';

import type { ReactNode } from 'react';

import { useQuery } from '@tanstack/react-query';
import {
  BarChart3,
  PiggyBank,
  TrendingDown,
  TrendingUp,
} from 'lucide-react';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';

type DailySummaryResponse = {
  date: string;
  totals: {
    pemasukan: number;
    pengeluaran: number;
  };
  saldo: number;
  transactions: any[];
};

const todayISO = () => new Date().toISOString().split('T')[0];

export default function DashboardPage() {
  const { user } = useAuth();

  const { data, isLoading, isError } = useQuery<DailySummaryResponse>({
    queryKey: ['dashboard-summary', todayISO()],
    queryFn: async () => {
      const response = await api.get(`/reports/daily?date=${todayISO()}`);
      return response.data;
    },
    staleTime: 60_000,
    enabled: !!user, // Only fetch when user is authenticated
  });

  const pemasukan = data?.totals?.pemasukan ?? 0;
  const pengeluaran = data?.totals?.pengeluaran ?? 0;
  const saldo = data?.saldo ?? pemasukan - pengeluaran;

  return (
    <section className="space-y-4">
      <header className="space-y-1">
        <h2 className="text-xl font-semibold text-slate-800">Dashboard</h2>
        <p className="text-sm text-slate-600">
          Selamat datang kembali{user ? `, ${user.name}` : ''}!
        </p>
      </header>

      <div className="grid gap-3 md:grid-cols-3">
        <SummaryCard
          title="Pemasukan Hari Ini"
          icon={<TrendingUp className="h-4 w-4 text-emerald-600" />}
          value={formatCurrency(pemasukan)}
          description="Total pemasukan hari ini"
          loading={isLoading}
          error={isError}
          iconBg="bg-emerald-100"
        />

        <SummaryCard
          title="Pengeluaran Hari Ini"
          icon={<TrendingDown className="h-4 w-4 text-red-600" />}
          value={formatCurrency(pengeluaran)}
          description="Total pengeluaran hari ini"
          loading={isLoading}
          error={isError}
          iconBg="bg-red-100"
        />

        <SummaryCard
          title="Saldo Hari Ini"
          icon={<PiggyBank className={`h-4 w-4 ${saldo >= 0 ? 'text-emerald-600' : 'text-red-600'}`} />}
          value={formatCurrency(saldo)}
          description="Selisih pemasukan dan pengeluaran"
          loading={isLoading}
          error={isError}
          iconBg={saldo >= 0 ? 'bg-emerald-100' : 'bg-red-100'}
        />
      </div>

      <Card className="border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg text-slate-800">
            <div className="rounded-full bg-emerald-100 p-2">
              <BarChart3 className="h-4 w-4 text-emerald-600" />
            </div>
            Aktivitas Singkat
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-2 text-sm text-slate-600">
          <p>
            Gunakan menu di bawah untuk menambah pemasukan, pengeluaran, pembayaran sewa, atau melihat laporan.
          </p>
          <p>
            Pastikan data transaksi harian dimasukkan sebelum akhir hari untuk menjaga saldo tetap akurat.
          </p>
        </CardContent>
      </Card>
    </section>
  );
}

interface SummaryCardProps {
  title: string;
  icon: ReactNode;
  value: string;
  description: string;
  loading?: boolean;
  error?: boolean;
  iconBg?: string;
}

function SummaryCard({ title, icon, value, description, loading, error, iconBg = 'bg-slate-100' }: SummaryCardProps) {
  return (
    <Card className="border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow">
      <CardHeader className="flex flex-row items-center justify-between gap-2 pb-2">
        <CardTitle className="text-sm font-medium text-slate-600">{title}</CardTitle>
        <div className={`rounded-full p-2 ${iconBg}`}>
          {icon}
        </div>
      </CardHeader>
      <CardContent className="pt-2">
        {loading ? (
          <div className="h-8 w-3/4 animate-pulse rounded bg-slate-200" />
        ) : error ? (
          <p className="text-xs text-red-600">Tidak dapat memuat data.</p>
        ) : (
          <p className="text-2xl font-bold text-slate-800">{value}</p>
        )}
        <p className="mt-1 text-xs text-slate-500">{description}</p>
      </CardContent>
    </Card>
  );
}
