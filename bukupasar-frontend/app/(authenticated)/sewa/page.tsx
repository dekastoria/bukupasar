'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Search, Users, Store } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TenantDetailModal } from '@/components/sewa/TenantDetailModal';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';

interface Tenant {
  id: number;
  nama: string;
  nomor_lapak: string;
  hp: string | null;
  outstanding: number;
  rental_type?: {
    nama: string;
  } | null;
}

export default function SewaPage() {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTenantId, setSelectedTenantId] = useState<number | null>(null);

  const { data: tenants = [], isLoading, refetch } = useQuery<Tenant[]>({
    queryKey: ['tenants-list'],
    queryFn: async () => {
      const response = await api.get('/tenants');
      return response.data.data || response.data || [];
    },
    staleTime: 30_000,
  });

  // Filter tenants based on search
  const filteredTenants = tenants.filter((tenant) => {
    const query = searchQuery.toLowerCase();
    return (
      tenant.nama.toLowerCase().includes(query) ||
      tenant.nomor_lapak.toLowerCase().includes(query) ||
      tenant.hp?.toLowerCase().includes(query)
    );
  });

  const handleTenantClick = (tenantId: number) => {
    setSelectedTenantId(tenantId);
  };

  const handleCloseModal = () => {
    setSelectedTenantId(null);
  };

  const handlePaymentSuccess = () => {
    refetch(); // Refresh list after payment
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <header className="space-y-1">
        <h2 className="text-xl font-semibold text-slate-800">Manajemen Sewa</h2>
        <p className="text-sm text-slate-600">
          Daftar penyewa dan pembayaran sewa
        </p>
      </header>

      {/* Search Bar */}
      <Card>
        <CardContent className="pt-4">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <Input
              type="text"
              placeholder="Cari nama penyewa, nomor toko, atau telepon..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10 h-9 text-sm"
            />
          </div>
        </CardContent>
      </Card>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
        <Card>
          <CardContent className="pt-4">
            <div className="flex items-center gap-2">
              <div className="rounded-full bg-emerald-100 p-2">
                <Users className="h-4 w-4 text-emerald-600" />
              </div>
              <div>
                <p className="text-xs text-slate-600">Total Penyewa</p>
                <p className="text-lg font-bold text-slate-800">{tenants.length}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-4">
            <div className="flex items-center gap-2">
              <div className="rounded-full bg-red-100 p-2">
                <Store className="h-4 w-4 text-red-600" />
              </div>
              <div>
                <p className="text-xs text-slate-600">Ada Tunggakan</p>
                <p className="text-lg font-bold text-red-600">
                  {tenants.filter(t => t.outstanding > 0).length}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card className="col-span-2 md:col-span-1">
          <CardContent className="pt-4">
            <div className="flex items-center gap-2">
              <div className="rounded-full bg-amber-100 p-2">
                <Store className="h-4 w-4 text-amber-600" />
              </div>
              <div>
                <p className="text-xs text-slate-600">Total Outstanding</p>
                <p className="text-sm font-bold text-amber-600">
                  {formatCurrency(tenants.reduce((sum, t) => sum + t.outstanding, 0))}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Tenant List */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Daftar Penyewa</CardTitle>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="py-8 text-center text-sm text-slate-600">Memuat data...</div>
          ) : filteredTenants.length === 0 ? (
            <div className="py-8 text-center">
              <p className="text-sm text-slate-600">
                {searchQuery ? 'Tidak ada penyewa yang cocok dengan pencarian' : 'Belum ada data penyewa'}
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="text-xs w-12">No</TableHead>
                    <TableHead className="text-xs">Nama Penyewa</TableHead>
                    <TableHead className="text-xs">Jenis Sewa</TableHead>
                    <TableHead className="text-xs">Nomor Toko</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredTenants.map((tenant, index) => (
                    <TableRow
                      key={tenant.id}
                      onClick={() => handleTenantClick(tenant.id)}
                      className="cursor-pointer hover:bg-slate-50 transition-colors"
                    >
                      <TableCell className="text-sm text-slate-500">{index + 1}</TableCell>
                      <TableCell className="text-sm font-medium text-slate-800">
                        {tenant.nama}
                        {tenant.outstanding > 0 && (
                          <span className="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                            Tunggakan
                          </span>
                        )}
                      </TableCell>
                      <TableCell className="text-sm text-slate-600">
                        {tenant.rental_type?.nama || '-'}
                      </TableCell>
                      <TableCell className="text-sm">
                        <span className="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium">
                          {tenant.nomor_lapak}
                        </span>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Tenant Detail Modal */}
      <TenantDetailModal
        tenantId={selectedTenantId}
        isOpen={selectedTenantId !== null}
        onClose={handleCloseModal}
        onPaymentSuccess={handlePaymentSuccess}
      />
    </div>
  );
}
