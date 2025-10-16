'use client';

import { useEffect, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { X, Phone, MapPin, Store, Calendar, CreditCard, History } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';
import { toast } from 'sonner';
import { useCreatePayment } from '@/hooks/useCreatePayment';

interface Tenant {
  id: number;
  nama: string;
  nomor_lapak: string;
  hp: string | null;
  alamat: string | null;
  outstanding: number;
  rental_type?: {
    nama: string;
    tarif: number;
  } | null;
}

interface Payment {
  id: number;
  tanggal: string;
  jumlah: number;
  catatan: string | null;
  creator: {
    name: string;
  };
}

interface TenantDetailModalProps {
  tenantId: number | null;
  isOpen: boolean;
  onClose: () => void;
  onPaymentSuccess?: () => void;
}

export function TenantDetailModal({ tenantId, isOpen, onClose, onPaymentSuccess }: TenantDetailModalProps) {
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [jumlah, setJumlah] = useState('');
  const [tanggal, setTanggal] = useState(new Date().toISOString().split('T')[0]);
  const [catatan, setCatatan] = useState('');
  
  const { mutate: createPayment, isPending: submitting } = useCreatePayment();

  // Fetch tenant detail
  const { data: tenant, isLoading: loadingTenant, refetch: refetchTenant } = useQuery<Tenant>({
    queryKey: ['tenant-detail', tenantId],
    queryFn: async () => {
      const response = await api.get(`/tenants/${tenantId}`);
      return response.data.data || response.data;
    },
    enabled: !!tenantId && isOpen,
  });

  // Fetch payment history
  const { data: payments = [], isLoading: loadingPayments, refetch: refetchPayments } = useQuery<Payment[]>({
    queryKey: ['tenant-payments', tenantId],
    queryFn: async () => {
      const response = await api.get(`/tenants/${tenantId}/payments`);
      return response.data.data || response.data || [];
    },
    enabled: !!tenantId && isOpen,
  });

  useEffect(() => {
    if (!isOpen) {
      setShowPaymentForm(false);
      setJumlah('');
      setCatatan('');
    }
  }, [isOpen]);

  const parseNumber = (value: string): number => {
    return parseInt(value.replace(/\D/g, ''), 10) || 0;
  };

  const formatNumberInput = (value: string): string => {
    const number = parseNumber(value);
    if (number === 0) return '';
    return number.toLocaleString('id-ID');
  };

  const handleJumlahChange = (value: string) => {
    setJumlah(formatNumberInput(value));
  };

  const handleSubmitPayment = (e: React.FormEvent) => {
    e.preventDefault();

    if (!tenant) return;

    const jumlahValue = parseNumber(jumlah);

    if (!jumlah || jumlahValue <= 0) {
      toast.error('Masukkan jumlah pembayaran');
      return;
    }

    if (jumlahValue > tenant.outstanding) {
      toast.error(`Pembayaran melebihi tunggakan. Maksimal ${formatCurrency(tenant.outstanding)}`);
      return;
    }

    createPayment(
      {
        tenant_id: tenant.id,
        tanggal,
        jumlah: jumlahValue,
        catatan: catatan.trim() || null,
      },
      {
        onSuccess: () => {
          toast.success('Pembayaran berhasil dicatat!');
          setShowPaymentForm(false);
          setJumlah('');
          setCatatan('');
          refetchTenant();
          refetchPayments();
          onPaymentSuccess?.();
        },
        onError: (error: any) => {
          const message = error?.response?.data?.message || 'Gagal mencatat pembayaran';
          toast.error(message);
        },
      }
    );
  };

  if (!tenant && !loadingTenant) {
    return null;
  }

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-lg font-semibold">Detail Penyewa</DialogTitle>
        </DialogHeader>

        {loadingTenant ? (
          <div className="py-8 text-center text-sm text-slate-600">Memuat data...</div>
        ) : tenant ? (
          <div className="space-y-4">
            {/* Profile Section */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <Store className="h-4 w-4" />
                  Informasi Toko
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <p className="text-xs text-slate-500">Nama Penyewa</p>
                    <p className="font-semibold text-slate-800">{tenant.nama}</p>
                  </div>
                  <div>
                    <p className="text-xs text-slate-500">Nomor Toko/Lapak</p>
                    <p className="font-semibold text-slate-800">{tenant.nomor_lapak}</p>
                  </div>
                  <div className="col-span-2">
                    <p className="text-xs text-slate-500">Jenis Sewa</p>
                    <p className="font-semibold text-emerald-600">
                      {tenant.rental_type?.nama || 'Belum ditentukan'}
                      {tenant.rental_type?.tarif && (
                        <span className="text-slate-600 font-normal ml-2">
                          • {formatCurrency(tenant.rental_type.tarif)}/bulan
                        </span>
                      )}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Contact Info */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <Phone className="h-4 w-4" />
                  Data Pribadi
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-2 text-sm">
                <div className="flex items-start gap-2">
                  <Phone className="h-4 w-4 text-slate-400 mt-0.5" />
                  <div>
                    <p className="text-xs text-slate-500">Nomor Telepon</p>
                    <p className="text-slate-800">{tenant.hp || '-'}</p>
                  </div>
                </div>
                <div className="flex items-start gap-2">
                  <MapPin className="h-4 w-4 text-slate-400 mt-0.5" />
                  <div>
                    <p className="text-xs text-slate-500">Alamat</p>
                    <p className="text-slate-800">{tenant.alamat || '-'}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Outstanding & Payment */}
            <Card className={tenant.outstanding > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <CreditCard className="h-4 w-4" />
                  Keterangan Sewa
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <p className="text-xs text-slate-600">Tarif Sewa/Bulan</p>
                    <p className="text-base font-bold text-slate-800">
                      {tenant.rental_type?.tarif ? formatCurrency(tenant.rental_type.tarif) : '-'}
                    </p>
                  </div>
                  <div>
                    <p className="text-xs text-slate-600">Outstanding (Tunggakan)</p>
                    <p className={`text-base font-bold ${tenant.outstanding > 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                      {formatCurrency(tenant.outstanding)}
                    </p>
                  </div>
                </div>

                {tenant.outstanding > 0 && !showPaymentForm && (
                  <Button
                    onClick={() => setShowPaymentForm(true)}
                    className="w-full bg-emerald-600 hover:bg-emerald-700 h-9 text-sm"
                  >
                    Bayar Sekarang
                  </Button>
                )}

                {showPaymentForm && (
                  <form onSubmit={handleSubmitPayment} className="space-y-3 pt-3 border-t">
                    <div>
                      <Label htmlFor="jumlah" className="text-xs">Jumlah Bayar*</Label>
                      <Input
                        id="jumlah"
                        type="text"
                        value={jumlah}
                        onChange={(e) => handleJumlahChange(e.target.value)}
                        placeholder="Masukkan nominal"
                        className="h-9 text-sm"
                      />
                      <p className="text-xs text-slate-500 mt-1">
                        Maksimal: {formatCurrency(tenant.outstanding)}
                      </p>
                    </div>
                    <div>
                      <Label htmlFor="tanggal" className="text-xs">Tanggal Bayar*</Label>
                      <Input
                        id="tanggal"
                        type="date"
                        value={tanggal}
                        onChange={(e) => setTanggal(e.target.value)}
                        className="h-9 text-sm"
                      />
                    </div>
                    <div>
                      <Label htmlFor="catatan" className="text-xs">Catatan</Label>
                      <Textarea
                        id="catatan"
                        value={catatan}
                        onChange={(e) => setCatatan(e.target.value)}
                        placeholder="Catatan pembayaran (opsional)"
                        className="text-sm resize-none"
                        rows={2}
                      />
                    </div>
                    <div className="flex gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => setShowPaymentForm(false)}
                        className="flex-1 h-9 text-sm"
                        disabled={submitting}
                      >
                        Batal
                      </Button>
                      <Button
                        type="submit"
                        className="flex-1 bg-emerald-600 hover:bg-emerald-700 h-9 text-sm"
                        disabled={submitting}
                      >
                        {submitting ? 'Menyimpan...' : 'Simpan Pembayaran'}
                      </Button>
                    </div>
                  </form>
                )}
              </CardContent>
            </Card>

            {/* Payment History */}
            <Card>
              <CardHeader>
                <CardTitle className="text-base flex items-center gap-2">
                  <History className="h-4 w-4" />
                  Riwayat Pembayaran
                </CardTitle>
              </CardHeader>
              <CardContent>
                {loadingPayments ? (
                  <p className="text-sm text-slate-600 text-center py-4">Memuat riwayat...</p>
                ) : payments.length === 0 ? (
                  <p className="text-sm text-slate-600 text-center py-4">Belum ada riwayat pembayaran</p>
                ) : (
                  <div className="overflow-x-auto">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead className="text-xs">Tanggal</TableHead>
                          <TableHead className="text-xs">Jumlah</TableHead>
                          <TableHead className="text-xs">Petugas</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {payments.map((payment) => (
                          <TableRow key={payment.id}>
                            <TableCell className="text-sm">
                              {new Date(payment.tanggal).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                              })}
                            </TableCell>
                            <TableCell className="text-sm font-semibold text-emerald-600">
                              {formatCurrency(payment.jumlah)}
                            </TableCell>
                            <TableCell className="text-xs text-slate-600">
                              {payment.creator.name}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        ) : null}
      </DialogContent>
    </Dialog>
  );
}
