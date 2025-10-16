'use client';

import { useMemo, useState } from 'react';

import { TrendingDown, TrendingUp } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';

type Transaction = {
  id: number;
  tanggal: string;
  jenis: 'pemasukan' | 'pengeluaran';
  subkategori: string;
  jumlah: number;
  catatan: string | null;
  tenant?: {
    nomor_lapak: string;
    nama: string;
  } | null;
  creator: {
    name: string;
  };
};

type DailyReportResponse = {
  date: string;
  totals: {
    pemasukan: number;
    pengeluaran: number;
  };
  saldo: number;
  transactions: Transaction[];
};

const JENIS_OPTIONS = [
  { value: 'semua', label: 'Semua Jenis' },
  { value: 'pemasukan', label: 'Pemasukan' },
  { value: 'pengeluaran', label: 'Pengeluaran' },
];

export default function LaporanHarianPage() {
  const today = useMemo(() => new Date().toISOString().split('T')[0], []);
  const [selectedDate, setSelectedDate] = useState(today);
  const [jenisFilter, setJenisFilter] = useState<'semua' | 'pemasukan' | 'pengeluaran'>('semua');

  const {
    data,
    isLoading,
    isFetching,
  } = useQuery<DailyReportResponse>({
    queryKey: ['laporan-harian', selectedDate],
    queryFn: async () => {
      const response = await api.get(`/reports/daily?date=${selectedDate}`);
      return response.data;
    },
    staleTime: 60 * 1000,
  });

  const filteredTransactions = useMemo(() => {
    if (!data?.transactions) return [];

    if (jenisFilter === 'semua') {
      return data.transactions;
    }

    return data.transactions.filter((tx) => tx.jenis === jenisFilter);
  }, [data?.transactions, jenisFilter]);

  return (
    <Card>
      <CardHeader>
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <CardTitle className="text-sm text-slate-800">
              Laporan Harian
            </CardTitle>
            <p className="text-base text-slate-600">
              Detail transaksi dan ringkasan pada tanggal yang dipilih.
            </p>
          </div>

          <div className="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div className="space-y-1">
              <Label htmlFor="laporan-date" className="text-sm text-slate-600">
                Pilih Tanggal
              </Label>
              <Input
                id="laporan-date"
                type="date"
                value={selectedDate}
                onChange={(event) => setSelectedDate(event.target.value)}
                className="h-12 text-base"
                max={today}
              />
            </div>

            <div className="space-y-1">
              <Label htmlFor="jenis-filter" className="text-sm text-slate-600">
                Filter Jenis
              </Label>
              <Select
                value={jenisFilter}
                onValueChange={(value) => setJenisFilter(value as typeof jenisFilter)}
              >
                <SelectTrigger id="jenis-filter" className="h-12 text-base">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {JENIS_OPTIONS.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
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
              <div className="rounded-lg border border-green-200 bg-green-50 p-4">
                <p className="text-sm text-green-700">Total Pemasukan</p>
                <p className="text-sm font-bold text-green-600">
                  {formatCurrency(data?.totals?.pemasukan ?? 0)}
                </p>
              </div>
              <div className="rounded-lg border border-red-200 bg-red-50 p-4">
                <p className="text-sm text-red-700">Total Pengeluaran</p>
                <p className="text-sm font-bold text-red-600">
                  {formatCurrency(data?.totals?.pengeluaran ?? 0)}
                </p>
              </div>
              <div className="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <p className="text-sm text-emerald-700">Saldo</p>
                <p className="text-sm font-bold text-emerald-600">
                  {formatCurrency(data?.saldo ?? 0)}
                </p>
              </div>
            </div>

            {isFetching && (
              <p className="text-sm text-slate-500">
                Memperbarui data untuk tanggal {selectedDate}...
              </p>
            )}

            <div className="rounded-xl border border-slate-200">
              {filteredTransactions.length === 0 ? (
                <p className="p-8 text-center text-sm text-slate-600">
                  Tidak ada transaksi yang sesuai filter.
                </p>
              ) : (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead className="text-base">Jenis</TableHead>
                        <TableHead className="text-base">Kategori</TableHead>
                        <TableHead className="text-base">Nominal</TableHead>
                        <TableHead className="text-base">Tenant</TableHead>
                        <TableHead className="text-base">Catatan</TableHead>
                        <TableHead className="text-base">Petugas</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {filteredTransactions.map((tx) => (
                        <TableRow key={tx.id}>
                          <TableCell>
                            <span
                              className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-semibold ${
                                tx.jenis === 'pemasukan'
                                  ? 'bg-green-100 text-green-700'
                                  : 'bg-red-100 text-red-700'
                              }`}
                            >
                              {tx.jenis === 'pemasukan' ? (
                                <TrendingUp className="h-4 w-4" />
                              ) : (
                                <TrendingDown className="h-4 w-4" />
                              )}
                              {tx.jenis === 'pemasukan' ? 'Masuk' : 'Keluar'}
                            </span>
                          </TableCell>
                          <TableCell className="text-base">{tx.subkategori}</TableCell>
                          <TableCell className="text-base font-semibold">
                            {formatCurrency(tx.jumlah)}
                          </TableCell>
                          <TableCell className="text-base text-slate-600">
                            {tx.tenant ? `${tx.tenant.nomor_lapak} - ${tx.tenant.nama}` : '-'}
                          </TableCell>
                          <TableCell className="text-base text-slate-600">
                            {tx.catatan || '-'}
                          </TableCell>
                          <TableCell className="text-base text-slate-600">
                            {tx.creator?.name ?? '-'}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              )}
            </div>
          </>
        )}
      </CardContent>
    </Card>
  );
}
