# Login Credentials - Bukupasar

## 🔐 Akun Testing

Gunakan kredensial berikut untuk login ke aplikasi Bukupasar (frontend Next.js):

### 📍 Informasi Pasar
- **Market ID:** `1`
- **Nama Pasar:** Pasar Test
- **Kode:** TEST01

---

## 👥 User Accounts

### 1. Admin Pusat
```
Username: adminpusat
Password: password
Market ID: 1
Role: admin_pusat
```
**Akses:**
- Semua fitur (full access)
- Dapat mengelola semua market
- Akses Filament admin panel

---

### 2. Admin Pasar
```
Username: adminpasar
Password: password
Market ID: 1
Role: admin_pasar
```
**Akses:**
- Mengelola user dalam market
- CRUD transaksi, tenant, kategori
- Akses laporan & export
- Akses Filament admin panel

---

### 3. Inputer
```
Username: inputer
Password: password
Market ID: 1
Role: inputer
```
**Akses:**
- Input pemasukan/pengeluaran
- Input pembayaran sewa
- View laporan
- Edit transaksi sendiri dalam 24 jam
- **Akses Next.js frontend saja** (tidak bisa akses Filament)

---

### 4. Viewer
```
Username: viewer
Password: password
Market ID: 1
Role: viewer
```
**Akses:**
- View transaksi (read-only)
- View laporan (read-only)
- Tidak bisa input/edit data

---

## 🚀 Cara Login

### Frontend (Next.js)
1. Buka browser: `http://localhost:3000`
2. Otomatis redirect ke halaman login
3. Masukkan kredensial:
   - **Username:** (pilih salah satu dari atas)
   - **Password:** `password`
   - **Market ID:** `1`
4. Klik tombol **"Masuk"**
5. Akan redirect ke Dashboard

### Backend (Filament Admin)
1. Buka browser: `http://bukupasar-backend.test/admin`
2. Login dengan email:
   - **Email:** `admin.pusat@example.com` atau `admin.pasar@example.com`
   - **Password:** `password`

---

## 📝 Catatan

- **Password default:** Semua akun menggunakan password `password`
- **Market ID:** Untuk testing gunakan ID `1` (Pasar Test)
- **Ganti password:** Di production wajib ganti password yang lebih kuat
- **Seeder:** User ini dibuat oleh `UserSeeder.php` saat `php artisan db:seed`

---

## 🔄 Reset Password (Development)

Jika lupa password atau ingin reset:

```bash
cd bukupasar-backend
php artisan tinker
```

Kemudian jalankan:
```php
$user = App\Models\User::where('username', 'inputer')->first();
$user->password = Hash::make('password');
$user->save();
```

---

## ⚠️ Security Warning

**Untuk Production:**
- Jangan gunakan password `password`
- Hapus atau ganti semua akun testing
- Gunakan password minimal 8 karakter dengan kombinasi huruf, angka, simbol
- Enable rate limiting di login endpoint
- Gunakan HTTPS

---

**Last Updated:** 2025-01-15
