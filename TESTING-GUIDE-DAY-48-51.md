# 🧪 Testing Guide - Day 48-51: Transaction Input Forms

## ✅ Features Implemented

### Pages Created
1. `/pemasukan/tambah` - Input pemasukan wizard (3 steps)
2. `/pengeluaran/tambah` - Input pengeluaran wizard (3 steps)

### Wizard Flow
**Step 1:** Select kategori  
**Step 2:** Fill transaction details  
**Step 3:** Review and submit

---

## 🎯 Manual Testing Checklist

### Prerequisites
- [x] Backend running: `php artisan serve --host=127.0.0.1 --port=8000`
- [x] Frontend running: `npm run dev` (usually http://localhost:3001)
- [x] Logged in as: `inputer` / `password` / market ID `1`

---

## Test Case 1: Pemasukan - Happy Path ✅

### Steps:
1. **Login** ke aplikasi
2. Klik **"Masuk"** di bottom navbar
3. Verify redirect ke `/pemasukan/tambah`

### Step 1: Category Selection
- [ ] Page title: "Tambah Pemasukan"
- [ ] Step indicator menunjukkan: **1** (active), 2, 3
- [ ] Kategori buttons display (Retribusi, Sewa, dll)
- [ ] Click **"Retribusi"** button

### Step 2: Transaction Form
- [ ] Step indicator: 1, **2** (active), 3
- [ ] Selected category displayed: "Retribusi"
- [ ] Form fields:
  - [ ] Nominal input (h-14, large text)
  - [ ] Tanggal input (default: today)
  - [ ] Catatan input
- [ ] Enter data:
  - Nominal: `50000`
  - Tanggal: Keep today
  - Catatan: `Retribusi harian pagi`
- [ ] Click **"Lanjutkan"** button

### Step 3: Review
- [ ] Step indicator: 1, 2, **3** (active)
- [ ] Review card shows:
  - Jenis: **Pemasukan**
  - Kategori: **Retribusi**
  - Nominal: **Rp 50.000** (green color)
  - Tanggal: Today (formatted in Indonesian)
  - Catatan: Retribusi harian pagi
- [ ] Click **"Simpan"** button

### Expected Results:
- [ ] Toast notification: "Pemasukan berhasil ditambahkan!"
- [ ] Redirect to `/dashboard`
- [ ] Dashboard stats updated:
  - Pemasukan Hari Ini increased by Rp 50.000
  - Saldo Hari Ini increased by Rp 50.000

**Status:** ⏳ Need Manual Test

---

## Test Case 2: Pengeluaran - Happy Path ✅

### Steps:
1. From dashboard, click **"Keluar"** in navbar
2. Verify redirect to `/pengeluaran/tambah`

### Step 1: Category Selection
- [ ] Page title: "Tambah Pengeluaran"
- [ ] Step indicator: **1** (red theme), 2, 3
- [ ] Kategori pengeluaran buttons displayed
- [ ] Click **"Listrik"** button

### Step 2: Form
- [ ] Step indicator: 1, **2** (red theme), 3
- [ ] Selected: "Listrik"
- [ ] Enter data:
  - Nominal: `200000`
  - Tanggal: Today
  - Catatan: `Bayar listrik bulan Januari`
- [ ] Click **"Lanjutkan"**

### Step 3: Review
- [ ] Jenis: **Pengeluaran**
- [ ] Kategori: **Listrik**
- [ ] Nominal: **Rp 200.000** (red color)
- [ ] Click **"Simpan"** (red button)

### Expected Results:
- [ ] Toast: "Pengeluaran berhasil ditambahkan!"
- [ ] Redirect to `/dashboard`
- [ ] Stats updated:
  - Pengeluaran Hari Ini increased by Rp 200.000
  - Saldo decreased by Rp 200.000

**Status:** ⏳ Need Manual Test

---

## Test Case 3: Validation - Empty Nominal ❌

### Steps:
1. Go to `/pemasukan/tambah`
2. Select kategori: **Retribusi**
3. In Step 2:
   - Leave Nominal **empty**
   - Fill Tanggal: Today
   - Catatan: `Test validation`
4. Click **"Lanjutkan"**

### Expected Results:
- [ ] Toast error: "Nominal harus diisi dan lebih dari 0"
- [ ] Stay on Step 2 (not advance to Step 3)
- [ ] No API call made

**Status:** ⏳ Need Manual Test

---

## Test Case 4: Validation - Catatan Wajib ❌

**Note:** Only test if you have a category with `wajib_keterangan = 1`

### Steps:
1. Go to `/pemasukan/tambah`
2. Select kategori that requires catatan (check backend CategorySeeder)
3. In Step 2:
   - Fill Nominal: `30000`
   - Fill Tanggal: Today
   - Leave Catatan **empty**
4. Click **"Lanjutkan"**

### Expected Results:
- [ ] Toast error: "Catatan wajib diisi untuk kategori [nama kategori]"
- [ ] Stay on Step 2
- [ ] No API call

**Status:** ⏳ Need Manual Test

---

## Test Case 5: Navigation - Back Button ↩️

### Steps:
1. Go to `/pemasukan/tambah`
2. Select kategori: **Retribusi** (go to Step 2)
3. Click **"Kembali"** button

### Expected:
- [ ] Return to Step 1 (category selection)
- [ ] Selected kategori: Still "Retribusi" (data preserved)
- [ ] Can select different kategori

4. Select kategori again, go to Step 2, fill form
5. Click **"Lanjutkan"** to Step 3
6. Click **"Kembali"** button

### Expected:
- [ ] Return to Step 2 (form)
- [ ] All form data preserved (nominal, tanggal, catatan)
- [ ] Can edit and proceed again

**Status:** ⏳ Need Manual Test

---

## Test Case 6: Responsive Design 📱

### Mobile (375px - iPhone SE)
Open DevTools → Toggle Device Toolbar → iPhone SE

#### Pemasukan page:
- [ ] Step indicator fits on screen
- [ ] Category buttons stack properly (2 columns)
- [ ] Buttons are large and tappable
- [ ] Form inputs: h-14 (56px height)
- [ ] Text size: text-xl readable
- [ ] No horizontal scroll
- [ ] Bottom navbar doesn't overlap content

#### Review screen:
- [ ] Data grid displays properly (2 columns)
- [ ] Currency formatted and readable
- [ ] Buttons large enough (h-14)

### Tablet (768px - iPad)
- [ ] Category buttons: 2 columns (sm:grid-cols-2)
- [ ] Review grid: 2 columns
- [ ] All text readable
- [ ] Touch targets appropriate

### Desktop (1280px)
- [ ] Max-width container applies (max-w-5xl)
- [ ] Content centered
- [ ] White space on sides (OK)
- [ ] All interactions work

**Status:** ⏳ Need Manual Test

---

## Test Case 7: UX Lansia Guidelines ✅

### Typography:
- [ ] Headings: `text-3xl` (30px) ✅
- [ ] Body text: `text-lg` (18px) ✅
- [ ] Form labels: `text-lg` (18px) ✅
- [ ] Button text: `text-lg` or `text-xl` ✅

### Touch Targets:
- [ ] Category buttons: `h-20` (80px) ✅
- [ ] Form inputs: `h-14` (56px) ✅
- [ ] Navigation buttons: `h-14` (56px) ✅

### Color Contrast:
- [ ] Text slate-800 on white: AAA ✅
- [ ] Green (pemasukan): #059669 readable ✅
- [ ] Red (pengeluaran): #dc2626 readable ✅

### Clarity:
- [ ] Step indicators: Large numbered circles ✅
- [ ] Progress bar: Visual connection between steps ✅
- [ ] Labels above inputs: Clear hierarchy ✅
- [ ] Required fields marked with * ✅

**Status:** ✅ Design Verified (needs manual UI testing)

---

## Test Case 8: API Integration 🔌

### Verify API Calls (Chrome DevTools Network Tab)

#### Step 1: Categories
When opening `/pemasukan/tambah`:
- [ ] API call: `GET /api/categories?jenis=pemasukan&aktif=1`
- [ ] Response: 200 OK
- [ ] Response body: Array of categories
- [ ] Categories displayed as buttons

#### Step 3: Submit Transaction
When clicking "Simpan" in review:
- [ ] API call: `POST /api/transactions`
- [ ] Request headers: `Authorization: Bearer [token]`
- [ ] Request body:
  ```json
  {
    "tanggal": "2025-01-15",
    "jenis": "pemasukan",
    "subkategori": "Retribusi",
    "jumlah": 50000,
    "catatan": "...",
    "tenant_id": null
  }
  ```
- [ ] Response: 201 Created or 200 OK
- [ ] Dashboard query invalidated (refetch data)

**Status:** ⏳ Need Manual Test

---

## Test Case 9: Error Handling ❌

### Scenario: Backend Down
1. Stop backend server (Ctrl+C di terminal backend)
2. Try to submit transaction

### Expected:
- [ ] Toast error: "Gagal menambahkan pemasukan. Coba lagi."
- [ ] Stay on review screen
- [ ] Button enabled again (not stuck in loading)

### Scenario: Invalid Token
1. Clear localStorage (DevTools → Application → Local Storage)
2. Refresh page

### Expected:
- [ ] Redirect to `/login` (AuthContext should catch 401)

**Status:** ⏳ Need Manual Test

---

## 🐛 Known Issues / Limitations

### Current Implementation:
- ✅ Basic validation (nominal, tanggal, catatan)
- ✅ Category fetching from API
- ✅ 3-step wizard flow
- ✅ Responsive design
- ✅ UX lansia compliant

### Not Yet Implemented:
- ⏳ Backdate validation (max 60 days)
- ⏳ Tenant selection for sewa categories
- ⏳ Transaction list/history page
- ⏳ Edit transaction feature
- ⏳ Delete transaction feature

---

## 📊 Testing Summary

### Automated Tests:
- ✅ TypeScript compilation: PASS
- ✅ Production build: PASS

### Manual Tests:
- [ ] Happy path: Pemasukan
- [ ] Happy path: Pengeluaran
- [ ] Validation: Empty fields
- [ ] Validation: Catatan wajib
- [ ] Navigation: Back buttons
- [ ] Responsive: Mobile/Tablet/Desktop
- [ ] UX: Large text & touch targets
- [ ] API: Categories fetch
- [ ] API: Transaction submit
- [ ] Error: Backend down
- [ ] Error: Invalid token

**Overall Progress:** 2/12 (17%) ⏳

---

## 🚀 Next Steps After Testing

If all tests pass:
1. ✅ Mark Day 48-51 as **COMPLETED**
2. Move to Day 52-54: Sewa Form & Tenant Search
3. Update TO-DO-LIST.md daily log

If tests fail:
1. Document failing test case
2. Fix bug
3. Re-run tests
4. Update this document with resolution

---

**Testing Started:** 2025-01-15  
**Last Updated:** 2025-01-15  
**Tester:** [Your Name / AI Assistant]  
**Environment:** Development (Laragon + localhost)
