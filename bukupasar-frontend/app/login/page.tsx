'use client';

import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuth } from '@/contexts/AuthContext';

export default function LoginPage() {
  const { login } = useAuth();
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [marketId, setMarketId] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!marketId) {
      toast.error('Silakan isi ID pasar terlebih dahulu.');
      return;
    }

    setIsSubmitting(true);

    try {
      await login(identifier.trim(), password, Number(marketId));
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
      <div className="mb-6 text-center">
        <p className="text-xs uppercase tracking-[0.2em] text-slate-500">Selamat Datang di</p>
        <h1 className="text-xl font-semibold text-slate-800 mt-1">Bukupasar</h1>
        <p className="text-sm text-slate-600 mt-1 max-w-md">
          Aplikasi pencatatan keuangan pasar tradisional
        </p>
      </div>

      <Card className="w-full max-w-md shadow-lg">
        <CardHeader className="pb-3 text-center sm:text-left">
          <CardTitle className="text-lg text-slate-800">Masuk ke Akun Anda</CardTitle>
          <CardDescription className="text-sm text-slate-600">
            Masukkan username, password, dan ID pasar
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-1">
              <Label htmlFor="identifier" className="text-sm text-slate-700">
                Email atau Username
              </Label>
              <Input
                id="identifier"
                type="text"
                value={identifier}
                autoComplete="username"
                onChange={(event) => setIdentifier(event.target.value)}
                required
                className="h-9 text-sm bg-white"
                placeholder="contoh: inputer"
              />
            </div>

            <div className="space-y-1">
              <Label htmlFor="password" className="text-sm text-slate-700">
                Password
              </Label>
              <div className="relative">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  autoComplete="current-password"
                  onChange={(event) => setPassword(event.target.value)}
                  required
                  className="h-9 text-sm bg-white pr-10"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((prev) => !prev)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700"
                  aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
            </div>

            <div className="space-y-1">
              <Label htmlFor="market" className="text-sm text-slate-700">
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
                className="h-9 text-sm bg-white"
                placeholder="Contoh: 1"
              />
              <p className="text-xs text-slate-500">
                Hubungi admin jika belum tahu ID pasar
              </p>
            </div>

            <Button
              type="submit"
              className="w-full h-9 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700"
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
