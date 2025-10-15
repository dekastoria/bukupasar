'use client';

import { useState } from 'react';
import { Calendar, Download, TrendingUp, TrendingDown } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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

export default function LaporanPage() {
  const [tab, setTab] = useState<'harian' | 'ringkasan'>('harian');
  const [dateHarian, setDateHarian] = useState(new Date().toISOString().split('T')[0]);
  const [dateFrom, setDateFrom] = useState(
    new Date(new Date().setDate(1)).toISOString().split('T')[0]
  );
  const [dateTo, setDateTo] = useState(new Date().toISOString().split('T')[0]);

  // Laporan Harian
  const { data: harianData, isLoading: loadingHarian } = useQuery<DailyReportResponse>({
    queryKey: ['laporan-harian', dateHarian],
    queryFn: async () => {
      const response = await api.get(`/reports/daily?date=${dateHarian}`);
      return response.data;
    },
    enabled: tab === 'harian' && typeof window !== 'undefined',
  });

  // Laporan Ringkasan
  const { data: ringkasanData, isLoading: loadingRingkasan } = useQuery({
    queryKey: ['laporan-ringkasan', dateFrom, dateTo],
    queryFn: async () => {
      const response = await api.get(`/reports/summary?from=${dateFrom}&to=${dateTo}`);
      return response.data;
    },
    enabled: tab === 'ringkasan' && typeof window !== 'undefined',
  });

  return (
    <div className="space-y-6">
      <header className="space-y-2">
        <h2 className="text-3xl font-semibold text-slate-800">Laporan Keuangan</h2>
        <p className="text-lg text-slate-600">
          Lihat detail transaksi harian dan ringkasan periode tertentu.
        </p>
      </header>

      <Tabs value={tab} onValueChange={(v) => setTab(v as any)}>
        <TabsList className="grid w-full grid-cols-2 h-14">
          <TabsTrigger value="harian" className="text-lg">
            Laporan Harian
          </TabsTrigger>
          <TabsTrigger value="ringkasan" className="text-lg">
            Laporan Ringkasan
          </TabsTrigger>
        </TabsList>

        {/* Laporan Harian */}
        <TabsContent value="harian" className="space-y-4">
          <Card>
            <CardHeader>
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <CardTitle className="text-2xl text-slate-800">Detail Transaksi</CardTitle>
                <div className="flex items-center gap-2">
                  <Label htmlFor="dateHarian" className="text-base">
                    Tanggal:
                  </Label>
                  <Input
                    id="dateHarian"
                    type="date"
                    value={dateHarian}
                    onChange={(e) => setDateHarian(e.target.value)}
                    className="h-12 text-base w-auto"
                  />
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              {loadingHarian ? (
                <p className="text-center text-lg text-slate-600">Memuat data...</p>
              ) : (
                <>
                  {/* Summary Cards */}
                  <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-lg bg-green-50 p-4 border border-green-200">
                      <p className="text-sm text-green-700">Pemasukan</p>
                      <p className="text-2xl font-bold text-green-600">
                        {formatCurrency(harianData?.totals?.pemasukan ?? 0)}
                      </p>
                    </div>
                    <div className="rounded-lg bg-red-50 p-4 border border-red-200">
                      <p className="text-sm text-red-700">Pengeluaran</p>
                      <p className="text-2xl font-bold text-red-600">
                        {formatCurrency(harianData?.totals?.pengeluaran ?? 0)}
                      </p>
                    </div>
                    <div className="rounded-lg bg-blue-50 p-4 border border-blue-200">
                      <p className="text-sm text-blue-700">Saldo</p>
                      <p className="text-2xl font-bold text-blue-600">
                        {formatCurrency(harianData?.saldo ?? 0)}
                      </p>
                    </div>
                  </div>

                  {/* Transactions Table */}
                  <div className="rounded-lg border border-slate-200">
                    {!harianData?.transactions || harianData.transactions.length === 0 ? (
                      <p className="p-8 text-center text-lg text-slate-600">
                        Tidak ada transaksi pada tanggal ini.
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
                            {harianData.transactions.map((tx: Transaction) => (
                              <TableRow key={tx.id}>
                                <TableCell>
                                  <span
                                    className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium ${
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
                                  {tx.tenant
                                    ? `${tx.tenant.nomor_lapak} - ${tx.tenant.nama}`
                                    : '-'}
                                </TableCell>
                                <TableCell className="text-base text-slate-600">
                                  {tx.catatan || '-'}
                                </TableCell>
                                <TableCell className="text-base text-slate-600">
                                  {tx.creator.name}
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
        </TabsContent>

        {/* Laporan Ringkasan */}
        <TabsContent value="ringkasan" className="space-y-4">
          <Card>
            <CardHeader>
              <div className="flex flex-col gap-4">
                <CardTitle className="text-2xl text-slate-800">Ringkasan Periode</CardTitle>
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                  <div className="flex-1 space-y-2">
                    <Label htmlFor="dateFrom" className="text-base">
                      Dari Tanggal:
                    </Label>
                    <Input
                      id="dateFrom"
                      type="date"
                      value={dateFrom}
                      onChange={(e) => setDateFrom(e.target.value)}
                      className="h-12 text-base"
                    />
                  </div>
                  <div className="flex-1 space-y-2">
                    <Label htmlFor="dateTo" className="text-base">
                      Sampai Tanggal:
                    </Label>
                    <Input
                      id="dateTo"
                      type="date"
                      value={dateTo}
                      onChange={(e) => setDateTo(e.target.value)}
                      className="h-12 text-base"
                    />
                  </div>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              {loadingRingkasan ? (
                <p className="text-center text-lg text-slate-600">Memuat data...</p>
              ) : (
                <>
                  {/* Summary Stats */}
                  <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-lg bg-green-50 p-6 border border-green-200">
                      <p className="text-base text-green-700 mb-2">Total Pemasukan</p>
                      <p className="text-3xl font-bold text-green-600">
                        {formatCurrency(ringkasanData?.totals?.pemasukan ?? 0)}
                      </p>
                    </div>
                    <div className="rounded-lg bg-red-50 p-6 border border-red-200">
                      <p className="text-base text-red-700 mb-2">Total Pengeluaran</p>
                      <p className="text-3xl font-bold text-red-600">
                        {formatCurrency(ringkasanData?.totals?.pengeluaran ?? 0)}
                      </p>
                    </div>
                    <div className="rounded-lg bg-blue-50 p-6 border border-blue-200">
                      <p className="text-base text-blue-700 mb-2">Saldo Bersih</p>
                      <p className="text-3xl font-bold text-blue-600">
                        {formatCurrency(ringkasanData?.saldo ?? 0)}
                      </p>
                    </div>
                  </div>

                  {/* By Category */}
                  {ringkasanData?.by_category && ringkasanData.by_category.length > 0 && (
                    <Card className="border-slate-200">
                      <CardHeader>
                        <CardTitle className="text-xl text-slate-800">
                          Rincian per Kategori
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="space-y-3">
                          {ringkasanData.by_category.map((cat: any, idx: number) => (
                            <div
                              key={idx}
                              className="flex items-center justify-between rounded-lg bg-slate-50 p-4"
                            >
                              <div className="flex items-center gap-3">
                                <span
                                  className={`inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold ${
                                    cat.jenis === 'pemasukan'
                                      ? 'bg-green-200 text-green-700'
                                      : 'bg-red-200 text-red-700'
                                  }`}
                                >
                                  {cat.jenis === 'pemasukan' ? '+' : '-'}
                                </span>
                                <div>
                                  <p className="text-lg font-semibold text-slate-800">
                                    {cat.subkategori}
                                  </p>
                                  <p className="text-sm text-slate-500">
                                    {cat.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}
                                  </p>
                                </div>
                              </div>
                              <p className="text-xl font-bold text-slate-800">
                                {formatCurrency(cat.total)}
                              </p>
                            </div>
                          ))}
                        </div>
                      </CardContent>
                    </Card>
                  )}
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
