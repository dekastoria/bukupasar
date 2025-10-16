'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { User, Mail, Phone, Building2, Shield, Clock, Camera, ArrowLeft, LogOut } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { PhotoCropModal } from '@/components/profile/PhotoCropModal';
import { useAuth } from '@/contexts/AuthContext';
import { formatDistanceToNow } from 'date-fns';
import { id } from 'date-fns/locale';
import api from '@/lib/api';
import { toast } from 'sonner';

export default function ProfilePage() {
  const router = useRouter();
  const { user, logout, refreshUser } = useAuth();
  const [isUploading, setIsUploading] = useState(false);
  const [cropModalOpen, setCropModalOpen] = useState(false);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);

  if (!user) {
    return null;
  }

  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const handlePhotoSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
      toast.error('File harus berupa gambar');
      return;
    }

    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      toast.error('Ukuran file maksimal 2MB');
      return;
    }

    // Read file and show crop modal
    const reader = new FileReader();
    reader.onload = () => {
      setSelectedImage(reader.result as string);
      setCropModalOpen(true);
    };
    reader.readAsDataURL(file);

    // Reset input
    e.target.value = '';
  };

  const handleCropComplete = async (croppedImageBlob: Blob) => {
    setIsUploading(true);
    
    try {
      const formData = new FormData();
      formData.append('photo', croppedImageBlob, 'profile.jpg');

      await api.post('/profile/upload-photo', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      toast.success('Foto profile berhasil diupload!');
      
      // Refresh user data to get new photo URL
      await refreshUser();
    } catch (error: any) {
      console.error('Upload error:', error);
      const message = error?.response?.data?.message || 'Gagal upload foto';
      toast.error(message);
    } finally {
      setIsUploading(false);
      setSelectedImage(null);
    }
  };

  const getRoleLabel = (role?: string | null) => {
    const roleMap: Record<string, string> = {
      admin_pusat: 'Admin Pusat',
      admin_pasar: 'Admin Pasar',
      inputer: 'Inputer',
      viewer: 'Viewer',
    };
    return role ? roleMap[role] || role : 'Tidak ada role';
  };

  const getRoleBadgeColor = (role?: string | null) => {
    const colorMap: Record<string, string> = {
      admin_pusat: 'bg-purple-100 text-purple-700',
      admin_pasar: 'bg-blue-100 text-blue-700',
      inputer: 'bg-emerald-100 text-emerald-700',
      viewer: 'bg-slate-100 text-slate-700',
    };
    return role ? colorMap[role] || 'bg-slate-100 text-slate-700' : 'bg-slate-100 text-slate-700';
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Button
            variant="ghost"
            size="icon"
            onClick={() => router.push('/dashboard')}
            className="h-9 w-9"
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h2 className="text-xl font-semibold text-slate-800">Profil Saya</h2>
            <p className="text-sm text-slate-600">Informasi akun dan pengaturan</p>
          </div>
        </div>
        <Button
          variant="outline"
          onClick={logout}
          className="h-9 text-sm gap-2 text-red-600 hover:text-red-700 hover:bg-red-50"
        >
          <LogOut className="h-4 w-4" />
          Keluar
        </Button>
      </div>

      {/* Profile Photo Card */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex flex-col items-center text-center space-y-4">
            <div className="relative">
              <Avatar className="h-24 w-24">
                <AvatarImage src={user.foto_profile || undefined} alt={user.name} />
                <AvatarFallback className="text-2xl bg-emerald-100 text-emerald-700">
                  {getInitials(user.name)}
                </AvatarFallback>
              </Avatar>
              <label
                htmlFor="photo-upload"
                className="absolute bottom-0 right-0 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full p-2 cursor-pointer shadow-lg transition-colors"
              >
                <Camera className="h-4 w-4" />
                <input
                  id="photo-upload"
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={handlePhotoSelect}
                  disabled={isUploading}
                />
              </label>
            </div>
            <div>
              <h3 className="text-lg font-semibold text-slate-800">{user.name}</h3>
              <p className="text-sm text-slate-600">@{user.username}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Account Info */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg flex items-center gap-2">
            <User className="h-4 w-4" />
            Informasi Akun
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 gap-4">
            <div className="flex items-start gap-3">
              <Mail className="h-4 w-4 text-slate-400 mt-1" />
              <div className="flex-1">
                <p className="text-xs text-slate-500">Email</p>
                <p className="text-sm text-slate-800">{user.email || '-'}</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Phone className="h-4 w-4 text-slate-400 mt-1" />
              <div className="flex-1">
                <p className="text-xs text-slate-500">Telepon</p>
                <p className="text-sm text-slate-800">{user.phone || '-'}</p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Building2 className="h-4 w-4 text-slate-400 mt-1" />
              <div className="flex-1">
                <p className="text-xs text-slate-500">Pasar</p>
                <p className="text-sm text-slate-800">
                  {user.market_name || `Pasar ID ${user.market_id}`}
                </p>
              </div>
            </div>
            <div className="flex items-start gap-3">
              <Shield className="h-4 w-4 text-slate-400 mt-1" />
              <div className="flex-1">
                <p className="text-xs text-slate-500">Role</p>
                <span
                  className={`inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${getRoleBadgeColor(
                    user.role
                  )}`}
                >
                  {getRoleLabel(user.role)}
                </span>
              </div>
            </div>
            {user.last_login_at && (
              <div className="flex items-start gap-3">
                <Clock className="h-4 w-4 text-slate-400 mt-1" />
                <div className="flex-1">
                  <p className="text-xs text-slate-500">Last Login</p>
                  <p className="text-sm text-slate-800">
                    {formatDistanceToNow(new Date(user.last_login_at), {
                      addSuffix: true,
                      locale: id,
                    })}
                  </p>
                </div>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Actions */}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Pengaturan</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-slate-600">
            Untuk mengubah informasi akun, silakan hubungi administrator pasar Anda.
          </p>
        </CardContent>
      </Card>

      {/* Crop Modal */}
      {selectedImage && (
        <PhotoCropModal
          isOpen={cropModalOpen}
          onClose={() => {
            setCropModalOpen(false);
            setSelectedImage(null);
          }}
          imageSrc={selectedImage}
          onCropComplete={handleCropComplete}
        />
      )}
    </div>
  );
}
