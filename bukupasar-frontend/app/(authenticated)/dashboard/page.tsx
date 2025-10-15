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
    <section className="space-y-6">
      <header className="space-y-2">
        <h2 className="text-3xl font-semibold text-slate-800">Dashboard</h2>
        <p className="text-lg text-slate-600">
          Selamat datang kembali{user ? `, ${user.name}` : ''}! Gunakan ringkasan di bawah sebagai panduan cepat.
        </p>
      </header>

      <div className="grid gap-4 md:grid-cols-3">
        <SummaryCard
          title="Pemasukan Hari Ini"
          icon={<TrendingUp className="h-6 w-6 text-green-600" />}
          value={formatCurrency(pemasukan)}
          description="Total pemasukan yang tercatat hari ini"
          loading={isLoading}
          error={isError}
        />

        <SummaryCard
          title="Pengeluaran Hari Ini"
          icon={<TrendingDown className="h-6 w-6 text-red-600" />}
          value={formatCurrency(pengeluaran)}
          description="Total pengeluaran yang tercatat hari ini"
          loading={isLoading}
          error={isError}
        />

        <SummaryCard
          title="Saldo Hari Ini"
          icon={<PiggyBank className={`h-6 w-6 ${saldo >= 0 ? 'text-blue-600' : 'text-red-600'}`} />}
          value={formatCurrency(saldo)}
          description="Selisih pemasukan dan pengeluaran"
          loading={isLoading}
          error={isError}
        />
      </div>

      <Card className="border-slate-200 bg-white">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-2xl text-slate-800">
            <BarChart3 className="h-6 w-6 text-sky-600" />
            Aktivitas Singkat
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3 text-lg text-slate-600">
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
}

function SummaryCard({ title, icon, value, description, loading, error }: SummaryCardProps) {
  return (
    <Card className="border-slate-200 bg-white shadow-sm">
      <CardHeader className="flex flex-row items-center justify-between gap-3 pb-0">
        <CardTitle className="text-lg text-slate-600">{title}</CardTitle>
        {icon}
      </CardHeader>
      <CardContent className="pt-4">
        {loading ? (
          <div className="h-10 w-3/4 animate-pulse rounded bg-slate-200" />
        ) : error ? (
          <p className="text-base text-red-600">Tidak dapat memuat data.</p>
        ) : (
          <p className="text-3xl font-semibold text-slate-800">{value}</p>
        )}
        <p className="mt-2 text-sm text-slate-500">{description}</p>
      </CardContent>
    </Card>
  );
}
