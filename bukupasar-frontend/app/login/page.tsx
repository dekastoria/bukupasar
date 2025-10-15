'use client';

import { useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuth } from '@/contexts/AuthContext';

export default function LoginPage() {
  const { login } = useAuth();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [marketId, setMarketId] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!marketId) {
      toast.error('Silakan isi ID pasar terlebih dahulu.');
      return;
    }

    setIsSubmitting(true);

    try {
      await login(username.trim(), password, Number(marketId));
      toast.success('Berhasil masuk. Selamat bekerja!');
    } catch (error: any) {
      const message =
        error?.response?.data?.message ||
        error?.message ||
        'Gagal masuk. Periksa kembali data Anda.';
      toast.error(message);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-100 flex flex-col items-center justify-center px-4 py-10">
      <div className="mb-8 text-center">
        <p className="text-sm uppercase tracking-[0.2em] text-slate-500">Selamat Datang di</p>
        <h1 className="text-3xl font-semibold text-slate-800 mt-2">Bukupasar</h1>
        <p className="text-base text-slate-600 mt-2 max-w-md">
          Aplikasi pencatatan keuangan pasar tradisional dengan tampilan besar dan mudah dibaca.
        </p>
      </div>

      <Card className="w-full max-w-xl shadow-lg">
        <CardHeader className="pb-4 text-center sm:text-left">
          <CardTitle className="text-2xl sm:text-3xl text-slate-800">Masuk ke Akun Anda</CardTitle>
          <CardDescription className="text-base sm:text-lg text-slate-600">
            Gunakan username, password, dan ID pasar yang telah diberikan admin pasar.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-6">
            <div className="space-y-2">
              <Label htmlFor="username" className="text-lg text-slate-700">
                Username
              </Label>
              <Input
                id="username"
                type="text"
                value={username}
                autoComplete="username"
                onChange={(event) => setUsername(event.target.value)}
                required
                className="h-14 text-xl bg-white"
                placeholder="contoh: adminpasar"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="password" className="text-lg text-slate-700">
                Password
              </Label>
              <Input
                id="password"
                type="password"
                value={password}
                autoComplete="current-password"
                onChange={(event) => setPassword(event.target.value)}
                required
                className="h-14 text-xl bg-white"
                placeholder="••••••••"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="market" className="text-lg text-slate-700">
                ID Pasar
              </Label>
              <Input
                id="market"
                type="number"
                min="1"
                inputMode="numeric"
                value={marketId}
                onChange={(event) => setMarketId(event.target.value)}
                required
                className="h-14 text-xl bg-white"
                placeholder="Masukkan ID pasar"
              />
              <p className="text-sm text-slate-500">
                Hubungi admin pasar Anda jika belum mengetahui ID pasar.
              </p>
            </div>

            <Button
              type="submit"
              className="w-full h-14 text-xl font-semibold"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Memproses…' : 'Masuk'}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
