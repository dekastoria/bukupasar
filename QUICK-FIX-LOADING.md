# Quick Fix: "Memuat data akun..." Loading Stuck

## Masalah
Frontend stuck di "Memuat data akun..." dan tidak bisa masuk.

## Penyebab
1. Multiple node processes running (conflict)
2. Next.js cache issue
3. Backend/Frontend connection issue

## ✅ Solusi Cepat

### Opsi 1: Manual Restart (Paling Reliable)

**Terminal 1 - Backend:**
```powershell
cd C:\laragon\www\bukupasar\bukupasar-backend
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 - Frontend:**
```powershell
cd C:\laragon\www\bukupasar\bukupasar-frontend

# Clear cache dulu
Remove-Item -Recurse -Force .next

# Start dev server
npm run dev
```

**Browser:**
1. Buka `http://localhost:3000`
2. Hard refresh: `Ctrl + Shift + R`
3. Login:
   - Username: `inputer`
   - Password: `password`
   - Market ID: `1`

---

### Opsi 2: Automated Script

**Run script:**
```powershell
cd C:\laragon\www\bukupasar
powershell -ExecutionPolicy Bypass -File START-DEV-SERVERS.md
```

---

## 🔍 Troubleshooting

### Jika Masih Stuck di Loading:

**1. Cek Backend Running:**
```powershell
curl.exe http://127.0.0.1:8000/up
```
Expected: HTML response (Laravel health check)

**2. Cek API Categories (Test tanpa auth):**
```powershell
curl.exe http://127.0.0.1:8000/api/categories
```
Expected: `{"message":"Unauthenticated."}` (normal, butuh login)

**3. Cek Frontend Console:**
- Buka browser Developer Tools (F12)
- Tab Console
- Lihat error merah

**Common Errors:**
- `CORS error` → Backend CORS config issue
- `Network failed` → Backend tidak running
- `401 Unauthorized` → Normal, butuh login dulu
- `500 Internal Server Error` → Backend error, cek Laravel logs

**4. Clear Browser Data:**
```
1. F12 (DevTools)
2. Application tab
3. Clear storage
4. Refresh: Ctrl + Shift + R
```

**5. Check .env.local:**
```powershell
cat C:\laragon\www\bukupasar\bukupasar-frontend\.env.local
```
Harus:
```
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
```

---

## 🐛 Debug Mode

Jika masih bermasalah, enable debug:

**AuthContext.tsx - Add console logs:**

Line 81-85:
```typescript
const refreshUser = useCallback(async () => {
    try {
      console.log('🔄 Refreshing user...'); // ADD THIS
      setIsLoading(true);
      const response = await api.get('/auth/user');
      console.log('✅ User response:', response.data); // ADD THIS
      const nextUser = extractUser(response.data);
```

Line 98-101:
```typescript
} catch (error: any) {
      console.error('❌ refreshUser error:', error); // ADD THIS
      if (error?.response?.status === 401) {
        clearSession();
      }
```

**Lalu refresh browser dan lihat console.**

---

## ✅ Expected Behavior

**Saat Load Pertama Kali:**
1. "Memuat data akun..." muncul (2-3 detik)
2. Jika tidak ada token → Redirect ke `/login`
3. Jika ada token valid → Load dashboard
4. Jika token invalid → Redirect ke `/login`

**Tidak Boleh:**
- Loading lebih dari 10 detik
- Stuck tanpa redirect
- White screen

---

## 🔄 Reset Total (Last Resort)

Jika semua cara gagal:

```powershell
# 1. Stop all processes
Get-Process -Name "node" | Stop-Process -Force
Get-Process -Name "php" | Where-Object {$_.CommandLine -like "*artisan*"} | Stop-Process -Force

# 2. Clear all caches
cd C:\laragon\www\bukupasar\bukupasar-frontend
Remove-Item -Recurse -Force .next
Remove-Item -Recurse -Force node_modules\.cache

# 3. Rebuild
npm run build

# 4. Start fresh
# Terminal 1:
cd C:\laragon\www\bukupasar\bukupasar-backend
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2:
cd C:\laragon\www\bukupasar\bukupasar-frontend
npm run dev
```

---

## 📞 Masih Error?

**Informasi yang Dibutuhkan:**
1. Screenshot error di browser console (F12)
2. Output dari terminal backend
3. Output dari terminal frontend
4. Error message spesifik

**Kemungkinan Root Cause:**
- Database connection issue (backend)
- Token parsing issue (AuthContext)
- API response format changed
- Network configuration (firewall, antivirus)

---

**Last Updated:** 2025-01-16  
**Status:** Quick fix guide for loading stuck issue
