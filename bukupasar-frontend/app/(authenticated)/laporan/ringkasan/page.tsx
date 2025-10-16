'use client';

import { useMemo, useState } from 'react';

import { useQuery } from '@tanstack/react-query';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';

type SummaryResponse = {
  range: {
    from: string;
    to: string;
  };
  totals: {
    pemasukan: number;
    pengeluaran: number;
    saldo: number;
  };
  by_category: Array<{
    jenis: 'pemasukan' | 'pengeluaran';
    subkategori: string;
    total: number;
  }>;
};

const todayIso = new Date().toISOString().split('T')[0];

export default function LaporanRingkasanPage() {
  const defaultFrom = useMemo(() => {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), 1)
      .toISOString()
      .split('T')[0];
  }, []);

  const [dateFrom, setDateFrom] = useState(defaultFrom);
  const [dateTo, setDateTo] = useState(todayIso);

  const { data, isLoading, isFetching, refetch } = useQuery<SummaryResponse>({
    queryKey: ['laporan-ringkasan', dateFrom, dateTo],
    queryFn: async () => {
      const response = await api.get(`/reports/summary?from=${dateFrom}&to=${dateTo}`);
      return response.data;
    },
    enabled: !!dateFrom && !!dateTo,
    staleTime: 60 * 1000,
  });

  const handleDateFromChange = (value: string) => {
    if (!value) return;
    if (value > dateTo) {
      setDateTo(value);
    }
    setDateFrom(value);
  };

  const handleDateToChange = (value: string) => {
    if (!value) return;
    if (value < dateFrom) {
      setDateFrom(value);
    }
    setDateTo(value);
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex flex-col gap-4">
          <CardTitle className="text-sm text-slate-800">Laporan Ringkasan</CardTitle>
          <p className="text-base text-slate-600">
            Rekap total pemasukan dan pengeluaran pada rentang tanggal tertentu.
          </p>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-1">
              <Label htmlFor="summary-from" className="text-sm text-slate-600">
                Dari tanggal
              </Label>
              <Input
                id="summary-from"
                type="date"
                value={dateFrom}
                max={dateTo}
                onChange={(event) => handleDateFromChange(event.target.value)}
                className="h-12 text-base"
              />
            </div>
            <div className="space-y-1">
              <Label htmlFor="summary-to" className="text-sm text-slate-600">
                Sampai tanggal
              </Label>
              <Input
                id="summary-to"
                type="date"
                value={dateTo}
                min={dateFrom}
                max={todayIso}
                onChange={(event) => handleDateToChange(event.target.value)}
                className="h-12 text-base"
              />
            </div>
          </div>
        </div>
      </CardHeader>

      <CardContent className="space-y-6">
        {isLoading && !data ? (
          <p className="text-center text-sm text-slate-600">Memuat data...</p>
        ) : (
          <>
            <div className="grid gap-4 md:grid-cols-3">
              <div className="rounded-lg border border-green-200 bg-green-50 p-6">
                <p className="text-base text-green-700">Total Pemasukan</p>
                <p className="text-base font-bold text-green-600">
                  {formatCurrency(data?.totals?.pemasukan ?? 0)}
                </p>
              </div>
              <div className="rounded-lg border border-red-200 bg-red-50 p-6">
                <p className="text-base text-red-700">Total Pengeluaran</p>
                <p className="text-base font-bold text-red-600">
                  {formatCurrency(data?.totals?.pengeluaran ?? 0)}
                </p>
              </div>
              <div className="rounded-lg border border-emerald-200 bg-emerald-50 p-6">
                <p className="text-base text-emerald-700">Saldo Bersih</p>
                <p className="text-base font-bold text-emerald-600">
                  {formatCurrency(data?.totals?.saldo ?? 0)}
                </p>
              </div>
            </div>

            {isFetching && (
              <p className="text-sm text-slate-500">
                Memperbarui data untuk rentang {dateFrom} s/d {dateTo}...
              </p>
            )}

            {data?.by_category && data.by_category.length > 0 ? (
              <div className="space-y-3">
                <Card className="border-slate-200">
                  <CardHeader>
                    <CardTitle className="text-xl text-slate-800">
                      Rincian per Kategori
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-3">
                    {data.by_category.map((row, index) => (
                      <div
                        key={`${row.jenis}-${row.subkategori}-${index}`}
                        className="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3"
                      >
                        <div>
                          <p className="text-sm font-semibold text-slate-800">
                            {row.subkategori}
                          </p>
                          <p className="text-sm text-slate-500">
                            {row.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}
                          </p>
                        </div>
                        <p
                          className={`text-base font-bold ${
                            row.jenis === 'pemasukan' ? 'text-green-600' : 'text-red-600'
                          }`}
                        >
                          {formatCurrency(row.total)}
                        </p>
                      </div>
                    ))}
                  </CardContent>
                </Card>
              </div>
            ) : (
              <p className="text-center text-sm text-slate-600">
                Tidak ada transaksi pada rentang tanggal ini.
              </p>
            )}

            <button
              type="button"
              onClick={() => refetch()}
              className="w-full rounded-lg border border-slate-200 bg-white py-3 text-base font-medium text-slate-600 hover:border-emerald-200 hover:text-emerald-600"
            >
              Segarkan Data
            </button>
          </>
        )}
      </CardContent>
    </Card>
  );
}
