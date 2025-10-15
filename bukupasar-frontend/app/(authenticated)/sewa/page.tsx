'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { ArrowLeft, DollarSign, Calendar, FileText, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { TenantSearch } from '@/components/sewa/TenantSearch';
import { useCreatePayment } from '@/hooks/useCreatePayment';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

interface Tenant {
  id: number;
  nama: string;
  nomor_lapak: string;
  outstanding: number;
  rental_type?: {
    nama: string;
  };
}

export default function SewaPage() {
  const router = useRouter();
  const createPayment = useCreatePayment();

  const [selectedTenant, setSelectedTenant] = useState<Tenant | null>(null);
  const [showOutstanding, setShowOutstanding] = useState(false);
  const [jumlah, setJumlah] = useState('');
  const [tanggal, setTanggal] = useState(
    new Date().toISOString().split('T')[0]
  );
  const [catatan, setCatatan] = useState('');
  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    setShowOutstanding(false);

    if (!selectedTenant) {
      return;
    }

    setErrors((prev) => {
      if (!prev.tenant) {
        return prev;
      }

      const { tenant, ...rest } = prev;
      return rest;
    });
  }, [selectedTenant]);

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value);
  };

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
    
    // Clear error when user types
    if (errors.jumlah) {
      setErrors((prev) => ({ ...prev, jumlah: '' }));
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!selectedTenant) {
      newErrors.tenant = 'Pilih penyewa terlebih dahulu';
    }

    const jumlahValue = parseNumber(jumlah);
    if (!jumlah || jumlahValue <= 0) {
      newErrors.jumlah = 'Masukkan jumlah pembayaran';
    } else if (selectedTenant && jumlahValue > selectedTenant.outstanding) {
      newErrors.jumlah = `Pembayaran melebihi tunggakan. Maksimal ${formatCurrency(
        selectedTenant.outstanding
      )}`;
    }

    if (!tanggal) {
      newErrors.tanggal = 'Pilih tanggal pembayaran';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      toast.error('Mohon perbaiki error pada form');
      return;
    }

    try {
      await createPayment.mutateAsync({
        tenant_id: selectedTenant!.id,
        jumlah: parseNumber(jumlah),
        tanggal,
        catatan: catatan.trim() || null,
      });

      toast.success('Pembayaran sewa berhasil dicatat!');
      router.push('/');
    } catch (error: any) {
      const errorMessage =
        error?.response?.data?.errors?.jumlah?.[0] ||
        error?.response?.data?.message ||
        error?.message ||
        'Gagal mencatat pembayaran';
      toast.error(errorMessage);
    }
  };

  const handleReset = () => {
    setSelectedTenant(null);
    setJumlah('');
    setTanggal(new Date().toISOString().split('T')[0]);
    setCatatan('');
    setErrors({});
    setShowOutstanding(false);
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-24">
      {/* Header */}
      <div className="bg-white border-b sticky top-0 z-10">
        <div className="max-w-5xl mx-auto px-4 py-4">
          <div className="flex items-center gap-3">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => router.push('/')}
              className="h-12 w-12"
            >
              <ArrowLeft className="h-6 w-6" />
            </Button>
            <h1 className="text-2xl md:text-3xl font-bold text-slate-900">
              Pembayaran Sewa
            </h1>
          </div>
        </div>
      </div>

      {/* Form */}
      <form onSubmit={handleSubmit} className="max-w-5xl mx-auto px-4 py-6 space-y-6">
        {/* Tenant Search */}
        <Card>
          <CardHeader>
            <CardTitle className="text-2xl">Pilih Penyewa</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <TenantSearch
              onSelect={setSelectedTenant}
              selectedTenant={selectedTenant}
            />
            {errors.tenant && (
              <p className="text-red-600 text-base">{errors.tenant}</p>
            )}

            {selectedTenant && (
              <Button
                type="button"
                variant="outline"
                onClick={() => {
                  setShowOutstanding(true);
                  toast.info('Data tunggakan diperbarui', {
                    description: `Sisa tunggakan: ${formatCurrency(
                      selectedTenant.outstanding
                    )}`,
                  });
                }}
                className="h-12 text-lg"
              >
                Cek Tunggakan
              </Button>
            )}

            {/* Outstanding Info */}
            {selectedTenant && showOutstanding && (
              <Alert className={cn(
                "border-2",
                selectedTenant.outstanding > 0
                  ? "border-red-200 bg-red-50"
                  : "border-green-200 bg-green-50"
              )}>
                <AlertCircle className={cn(
                  "h-5 w-5",
                  selectedTenant.outstanding > 0 ? "text-red-600" : "text-green-600"
                )} />
                <AlertDescription className="text-lg">
                  <div className="space-y-1">
                    <p className="font-medium">
                      {selectedTenant.rental_type && (
                        <span className="text-blue-600">
                          {selectedTenant.rental_type.nama}{' '}
                        </span>
                      )}
                      {selectedTenant.nomor_lapak} - {selectedTenant.nama}
                    </p>
                    <p className={cn(
                      "text-xl font-bold",
                      selectedTenant.outstanding > 0 ? "text-red-700" : "text-green-700"
                    )}>
                      Tunggakan: {formatCurrency(selectedTenant.outstanding)}
                    </p>
                    {selectedTenant.outstanding === 0 && (
                      <p className="text-green-700">
                        Penyewa ini tidak memiliki tunggakan
                      </p>
                    )}
                  </div>
                </AlertDescription>
              </Alert>
            )}
          </CardContent>
        </Card>

        {/* Payment Details */}
        {selectedTenant && (
          <Card>
            <CardHeader>
              <CardTitle className="text-2xl">Detail Pembayaran</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Jumlah */}
              <div className="space-y-2">
                <Label htmlFor="jumlah" className="text-xl font-medium flex items-center gap-2">
                  <DollarSign className="h-5 w-5" />
                  Jumlah Bayar
                </Label>
                <div className="relative">
                  <span className="absolute left-4 top-1/2 -translate-y-1/2 text-xl text-slate-500">
                    Rp
                  </span>
                  <Input
                    id="jumlah"
                    type="text"
                    inputMode="numeric"
                    placeholder="0"
                    value={jumlah}
                    onChange={(e) => handleJumlahChange(e.target.value)}
                    className={cn(
                      "h-14 pl-12 text-xl",
                      errors.jumlah && "border-red-500"
                    )}
                  />
                </div>
                {errors.jumlah && (
                  <p className="text-red-600 text-base">{errors.jumlah}</p>
                )}
                {selectedTenant.outstanding > 0 && jumlah && (
                  <p className="text-sm text-slate-600">
                    Sisa tunggakan setelah bayar:{' '}
                    <span className="font-medium">
                      {formatCurrency(
                        Math.max(0, selectedTenant.outstanding - parseNumber(jumlah))
                      )}
                    </span>
                  </p>
                )}
              </div>

              {/* Tanggal */}
              <div className="space-y-2">
                <Label htmlFor="tanggal" className="text-xl font-medium flex items-center gap-2">
                  <Calendar className="h-5 w-5" />
                  Tanggal Bayar
                </Label>
                <Input
                  id="tanggal"
                  type="date"
                  value={tanggal}
                  onChange={(e) => setTanggal(e.target.value)}
                  className={cn(
                    "h-14 text-xl",
                    errors.tanggal && "border-red-500"
                  )}
                />
                {errors.tanggal && (
                  <p className="text-red-600 text-base">{errors.tanggal}</p>
                )}
              </div>

              {/* Catatan */}
              <div className="space-y-2">
                <Label htmlFor="catatan" className="text-xl font-medium flex items-center gap-2">
                  <FileText className="h-5 w-5" />
                  Catatan (Opsional)
                </Label>
                <Textarea
                  id="catatan"
                  placeholder="Misal: Lunas sewa bulan Januari 2025"
                  value={catatan}
                  onChange={(e) => setCatatan(e.target.value)}
                  rows={3}
                  className="text-lg resize-none"
                />
              </div>
            </CardContent>
          </Card>
        )}

        {/* Action Buttons */}
        {selectedTenant && (
          <div className="flex flex-col sm:flex-row gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={handleReset}
              className="h-14 text-xl flex-1"
              disabled={createPayment.isPending}
            >
              Reset
            </Button>
            <Button
              type="submit"
              className="h-14 text-xl flex-1 bg-green-600 hover:bg-green-700"
              disabled={createPayment.isPending || selectedTenant.outstanding === 0}
            >
              {createPayment.isPending ? 'Menyimpan...' : 'Catat Pembayaran'}
            </Button>
          </div>
        )}
      </form>
    </div>
  );
}
