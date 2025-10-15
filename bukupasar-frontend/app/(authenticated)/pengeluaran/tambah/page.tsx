'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { ArrowLeft, ArrowRight, CheckCircle2 } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCategories } from '@/hooks/useCategories';
import { useCreateTransaction } from '@/hooks/useCreateTransaction';
import { formatCurrency } from '@/lib/utils';

type Category = {
  id: number;
  nama: string;
  jenis: 'pemasukan' | 'pengeluaran';
  wajib_keterangan: boolean;
};

type FormData = {
  kategori: string;
  nominal: string;
  tanggal: string;
  catatan: string;
};

export default function TambahPengeluaranPage() {
  const router = useRouter();
  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [formData, setFormData] = useState<FormData>({
    kategori: '',
    nominal: '',
    tanggal: new Date().toISOString().split('T')[0],
    catatan: '',
  });

  const { data: categories = [], isLoading: loadingCategories } = useCategories('pengeluaran');
  const { mutate: createTransaction, isPending: submitting } = useCreateTransaction();

  const selectedCategory = categories.find((cat: Category) => cat.nama === formData.kategori);
  const requiresCatatan = selectedCategory?.wajib_keterangan ?? false;

  const handleCategorySelect = (kategori: string) => {
    setFormData((prev) => ({ ...prev, kategori }));
    setStep(2);
  };

  const handleFormChange = (field: keyof FormData, value: string) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const validateStep2 = (): boolean => {
    if (!formData.nominal || Number(formData.nominal) <= 0) {
      toast.error('Nominal harus diisi dan lebih dari 0');
      return false;
    }
    if (!formData.tanggal) {
      toast.error('Tanggal harus diisi');
      return false;
    }
    if (requiresCatatan && !formData.catatan.trim()) {
      toast.error(`Catatan wajib diisi untuk kategori ${formData.kategori}`);
      return false;
    }
    return true;
  };

  const handleNext = () => {
    if (step === 2 && validateStep2()) {
      setStep(3);
    }
  };

  const handleSubmit = () => {
    if (!validateStep2()) return;

    const payload = {
      tanggal: formData.tanggal,
      jenis: 'pengeluaran' as const,
      subkategori: formData.kategori,
      jumlah: Number(formData.nominal),
      catatan: formData.catatan.trim() || null,
      tenant_id: null,
    };

    createTransaction(payload, {
      onSuccess: () => {
        toast.success('Pengeluaran berhasil ditambahkan!');
        router.push('/dashboard');
      },
      onError: (error: any) => {
        const message =
          error?.response?.data?.message || 'Gagal menambahkan pengeluaran. Coba lagi.';
        toast.error(message);
      },
    });
  };

  return (
    <div className="space-y-6">
      <header className="space-y-2">
        <h2 className="text-3xl font-semibold text-slate-800">Tambah Pengeluaran</h2>
        <p className="text-lg text-slate-600">
          Ikuti langkah-langkah berikut untuk mencatat pengeluaran baru.
        </p>
      </header>

      {/* Step Indicator */}
      <div className="flex items-center justify-center gap-2">
        {[1, 2, 3].map((num) => (
          <div key={num} className="flex items-center gap-2">
            <div
              className={`flex h-10 w-10 items-center justify-center rounded-full text-lg font-semibold ${
                step >= num
                  ? 'bg-red-600 text-white'
                  : 'bg-slate-200 text-slate-500'
              }`}
            >
              {num}
            </div>
            {num < 3 && (
              <div
                className={`h-1 w-12 ${step > num ? 'bg-red-600' : 'bg-slate-200'}`}
              />
            )}
          </div>
        ))}
      </div>

      {/* Step 1: Category Selection */}
      {step === 1 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-2xl text-slate-800">
              Langkah 1: Pilih Kategori Pengeluaran
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loadingCategories ? (
              <p className="text-lg text-slate-600">Memuat kategori...</p>
            ) : categories.length === 0 ? (
              <p className="text-lg text-red-600">
                Tidak ada kategori tersedia. Hubungi admin pasar.
              </p>
            ) : (
              <div className="grid gap-4 sm:grid-cols-2">
                {categories.map((cat: Category) => (
                  <Button
                    key={cat.id}
                    type="button"
                    onClick={() => handleCategorySelect(cat.nama)}
                    className="h-20 text-xl font-semibold"
                    variant="outline"
                  >
                    {cat.nama}
                  </Button>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {/* Step 2: Transaction Form */}
      {step === 2 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-2xl text-slate-800">
              Langkah 2: Isi Detail Transaksi
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="rounded-lg bg-slate-100 p-4">
              <p className="text-lg text-slate-600">
                Kategori: <span className="font-semibold text-slate-800">{formData.kategori}</span>
              </p>
            </div>

            <div className="space-y-2">
              <Label htmlFor="nominal" className="text-lg text-slate-700">
                Nominal (Rp) <span className="text-red-600">*</span>
              </Label>
              <Input
                id="nominal"
                type="number"
                inputMode="numeric"
                min="1"
                value={formData.nominal}
                onChange={(e) => handleFormChange('nominal', e.target.value)}
                placeholder="Contoh: 50000"
                className="h-14 text-xl"
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="tanggal" className="text-lg text-slate-700">
                Tanggal <span className="text-red-600">*</span>
              </Label>
              <Input
                id="tanggal"
                type="date"
                value={formData.tanggal}
                onChange={(e) => handleFormChange('tanggal', e.target.value)}
                className="h-14 text-xl"
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="catatan" className="text-lg text-slate-700">
                Catatan {requiresCatatan && <span className="text-red-600">*</span>}
              </Label>
              <Input
                id="catatan"
                type="text"
                value={formData.catatan}
                onChange={(e) => handleFormChange('catatan', e.target.value)}
                placeholder="Keterangan tambahan..."
                className="h-14 text-xl"
                required={requiresCatatan}
              />
              {requiresCatatan && (
                <p className="text-sm text-orange-600">
                  Catatan wajib diisi untuk kategori ini.
                </p>
              )}
            </div>

            <div className="flex gap-4">
              <Button
                type="button"
                onClick={() => setStep(1)}
                variant="outline"
                className="h-14 flex-1 text-lg"
              >
                <ArrowLeft className="mr-2 h-5 w-5" />
                Kembali
              </Button>
              <Button
                type="button"
                onClick={handleNext}
                className="h-14 flex-1 text-lg"
              >
                Lanjutkan
                <ArrowRight className="ml-2 h-5 w-5" />
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Step 3: Review */}
      {step === 3 && (
        <Card>
          <CardHeader>
            <CardTitle className="text-2xl text-slate-800">
              Langkah 3: Review Transaksi
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="space-y-4 rounded-lg border-2 border-slate-200 bg-slate-50 p-6">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-base text-slate-600">Jenis</p>
                  <p className="text-xl font-semibold text-slate-800">Pengeluaran</p>
                </div>
                <div>
                  <p className="text-base text-slate-600">Kategori</p>
                  <p className="text-xl font-semibold text-slate-800">{formData.kategori}</p>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <p className="text-base text-slate-600">Nominal</p>
                  <p className="text-2xl font-bold text-red-600">
                    {formatCurrency(Number(formData.nominal))}
                  </p>
                </div>
                <div>
                  <p className="text-base text-slate-600">Tanggal</p>
                  <p className="text-xl font-semibold text-slate-800">
                    {new Date(formData.tanggal).toLocaleDateString('id-ID', {
                      day: 'numeric',
                      month: 'long',
                      year: 'numeric',
                    })}
                  </p>
                </div>
              </div>

              {formData.catatan && (
                <div>
                  <p className="text-base text-slate-600">Catatan</p>
                  <p className="text-lg text-slate-800">{formData.catatan}</p>
                </div>
              )}
            </div>

            <p className="text-base text-slate-600">
              Pastikan semua data sudah benar sebelum menyimpan.
            </p>

            <div className="flex gap-4">
              <Button
                type="button"
                onClick={() => setStep(2)}
                variant="outline"
                className="h-14 flex-1 text-lg"
                disabled={submitting}
              >
                <ArrowLeft className="mr-2 h-5 w-5" />
                Kembali
              </Button>
              <Button
                type="button"
                onClick={handleSubmit}
                className="h-14 flex-1 text-lg bg-red-600 hover:bg-red-700"
                disabled={submitting}
              >
                {submitting ? (
                  'Menyimpan...'
                ) : (
                  <>
                    <CheckCircle2 className="mr-2 h-5 w-5" />
                    Simpan
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
