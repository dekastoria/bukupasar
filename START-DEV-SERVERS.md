# 🚀 Start Development Servers - Bukupasar

Panduan menjalankan development servers untuk backend dan frontend.

---

## ⚡ Quick Start (Cara Cepat)

### 1️⃣ Start Backend (Laravel)

Buka **Terminal/PowerShell #1:**

```bash
cd C:\laragon\www\bukupasar\bukupasar-backend
php artisan serve --host=127.0.0.1 --port=8000
```

✅ Backend running di: **http://127.0.0.1:8000**

---

### 2️⃣ Start Frontend (Next.js)

Buka **Terminal/PowerShell #2** (baru):

```bash
cd C:\laragon\www\bukupasar\bukupasar-frontend
npm run dev
```

✅ Frontend running di: **http://localhost:3001**

---

## 🔐 Login ke Aplikasi

1. Buka browser: **http://localhost:3001**
2. Masukkan kredensial:
   ```
   Username: inputer
   Password: password
   Market ID: 1
   ```
3. Klik **"Masuk"**
4. ✅ Redirect ke Dashboard

---

## 🛠️ Troubleshooting

### Problem: "Network Error" saat login

**Penyebab:** Backend tidak running atau CORS issue

**Solusi:**
1. Pastikan backend running di terminal #1 (lihat output `INFO Server running`)
2. Restart backend server:
   - Tekan `Ctrl+C` di terminal backend
   - Jalankan ulang: `php artisan serve --host=127.0.0.1 --port=8000`

3. Restart frontend server:
   - Tekan `Ctrl+C` di terminal frontend
   - Jalankan ulang: `npm run dev`

---

### Problem: Port sudah digunakan

**Backend (Port 8000):**
```bash
# Cek proses yang menggunakan port 8000
Get-Process -Id (Get-NetTCPConnection -LocalPort 8000).OwningProcess

# Kill proses tersebut atau gunakan port lain:
php artisan serve --host=127.0.0.1 --port=8001
```

**Frontend (Port 3000/3001):**
```bash
# Next.js otomatis menggunakan port available berikutnya
# Biasanya akan menggunakan 3001 jika 3000 sibuk
```

---

### Problem: 401 Unauthenticated

**Normal untuk endpoint yang memerlukan login:**
- `/api/categories` ✅ Perlu auth
- `/api/transactions` ✅ Perlu auth
- `/api/auth/login` ❌ Tidak perlu auth

**Test login endpoint:**
```bash
curl.exe -X POST http://127.0.0.1:8000/api/auth/login `
  -H "Content-Type: application/json" `
  -d '{"username":"inputer","password":"password","market_id":1}'
```

**Expected Response:**
```json
{
  "message": "Login berhasil.",
  "data": {
    "token": "...",
    "user": { ... }
  }
}
```

---

## 📊 Status Check

### Backend Health Check
```bash
curl.exe http://127.0.0.1:8000/up
```
**Expected:** Status 200

### Frontend Check
Buka browser: `http://localhost:3001`
**Expected:** Redirect ke `/login`

---

## 🔄 Restart Both Servers

**Quick Restart Script (PowerShell):**

```powershell
# Stop semua proses PHP dan Node
Get-Process | Where-Object {$_.ProcessName -like "php*"} | Stop-Process -Force
Get-Process | Where-Object {$_.ProcessName -like "node*"} | Stop-Process -Force

# Start Backend (Terminal 1)
cd C:\laragon\www\bukupasar\bukupasar-backend
Start-Process powershell -ArgumentList "-NoExit", "-Command", "php artisan serve --host=127.0.0.1 --port=8000"

# Wait 2 seconds
Start-Sleep -Seconds 2

# Start Frontend (Terminal 2)
cd C:\laragon\www\bukupasar\bukupasar-frontend
Start-Process powershell -ArgumentList "-NoExit", "-Command", "npm run dev"
```

---

## 📝 Environment Variables

### Backend (.env)
```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_DATABASE=bukupasar_dev
```

### Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
```

---

## ⚙️ Alternative: Using Laragon Nginx

Jika ingin menggunakan Laragon Nginx (tidak wajib untuk development):

1. **Tambahkan virtual host:**
   - Buka Laragon → Menu → Nginx → sites-enabled
   - Buat file `bukupasar-backend.conf`

2. **Tambahkan ke hosts file:**
   ```
   127.0.0.1   bukupasar-backend.test
   ```
   File location: `C:\Windows\System32\drivers\etc\hosts`

3. **Restart Laragon Nginx**

4. **Update frontend .env.local:**
   ```env
   NEXT_PUBLIC_API_URL=http://bukupasar-backend.test/api
   ```

---

## 🎯 Success Indicators

✅ Backend Terminal shows:
```
INFO  Server running on [http://127.0.0.1:8000].
```

✅ Frontend Terminal shows:
```
▲ Next.js 15.5.5 (Turbopack)
- Local:        http://localhost:3001
✓ Ready in 996ms
```

✅ Browser login page loads tanpa error

✅ Login berhasil → redirect ke Dashboard

---

## 📞 Need Help?

**Error masih terjadi?** Cek:
1. Database MySQL running di Laragon
2. File `.env` backend sudah benar (DB credentials)
3. File `.env.local` frontend sudah benar (API URL)
4. Port 8000 dan 3001 tidak digunakan proses lain

**Last Updated:** 2025-01-15
