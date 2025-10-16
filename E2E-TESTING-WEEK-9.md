# E2E Testing Week 9 (Day 57-59)

## 📋 Testing Checklist untuk Phase 5 - Integration & Testing

**Date:** 2025-10-15  
**Environment:** Development (Laragon + localhost)  
**Frontend:** http://localhost:3001  
**Backend:** http://127.0.0.1:8000  

---

## ✅ Prerequisites

- [x] Backend server running (`php artisan serve --host=127.0.0.1 --port=8000`)
- [x] Frontend server running (`npm run dev` di port 3001)
- [x] Database seeded dengan test data
- [x] Login credentials ready (lihat LOGIN-CREDENTIALS.md)

---

## 🧪 Test Scenarios

### Test Case 1: Complete User Flow - Inputer Daily Operations

**User:** `inputer` / `password` / Market ID: `1`

#### 1.1 Login Flow
- [ ] Buka http://localhost:3001
- [ ] Verify redirect ke `/login`
- [ ] Masukkan username: `inputer`
- [ ] Masukkan password: `password`
- [ ] Masukkan market_id: `1`
- [ ] Click "Masuk"
- [ ] **Expected:** Redirect ke `/dashboard`, toast "Login berhasil"

#### 1.2 Dashboard Verification
- [ ] Verify header menampilkan nama user
- [ ] Verify bottom navigation visible
- [ ] Verify 3 cards: Pemasukan, Pengeluaran, Saldo
- [ ] **Expected:** Numbers match today's totals

#### 1.3 Input Pemasukan
- [ ] Click "Masuk" di bottom navbar
- [ ] **Step 1:** Select kategori "Retribusi"
- [ ] **Step 2:** Isi:
  - Nominal: `50000`
  - Tanggal: Today
  - Catatan: `Test manual E2E`
- [ ] **Step 3:** Review data, click "Simpan"
- [ ] **Expected:** Toast "Pemasukan berhasil ditambahkan!", redirect ke dashboard
- [ ] **Verify:** Pemasukan hari ini bertambah Rp 50.000

#### 1.4 Input Pengeluaran
- [ ] Click "Keluar" di bottom navbar
- [ ] **Step 1:** Select kategori "Listrik"
- [ ] **Step 2:** Isi:
  - Nominal: `200000`
  - Tanggal: Today
  - Catatan: `Test pengeluaran`
- [ ] **Step 3:** Review, click "Simpan"
- [ ] **Expected:** Toast "Pengeluaran berhasil ditambahkan!", redirect ke dashboard
- [ ] **Verify:** Pengeluaran hari ini bertambah Rp 200.000

#### 1.5 Pembayaran Sewa
- [ ] Click "Sewa" di bottom navbar
- [ ] **Step 1:** Search tenant: ketik "test"
- [ ] Select tenant dari dropdown
- [ ] Click "Cek Tunggakan"
- [ ] **Expected:** Outstanding amount muncul
- [ ] Isi nominal pembayaran (≤ outstanding)
- [ ] Click "Simpan"
- [ ] **Expected:** Toast "Pembayaran berhasil!"

#### 1.6 View Reports
- [ ] Click "Laporan" di bottom navbar
- [ ] Verify redirect ke `/laporan/harian`
- [ ] Filter: Choose today's date
- [ ] **Expected:** Table shows 2 transactions (pemasukan + pengeluaran)
- [ ] Click "Ringkasan" tab
- [ ] **Expected:** Summary shows correct totals

#### 1.7 Logout
- [ ] Click profile icon di header
- [ ] Click "Logout"
- [ ] **Expected:** Redirect ke `/login`, localStorage cleared

**Status:** ⏳ Manual Test Required

---

### Test Case 2: Authorization Test - Inputer vs Admin

**User:** `inputer` / `password`

#### 2.1 Access Admin Panel
- [ ] Login sebagai inputer
- [ ] Navigate to: http://127.0.0.1:8000/admin
- [ ] **Expected:** 
  - Option A: 403 Forbidden
  - Option B: Redirect ke login
  - Option C: Login page
- [ ] **Should NOT:** Access Filament admin dashboard

**Status:** ⏳ Manual Test Required

---

### Test Case 3: Market Scoping Test

**Setup:**
- User A: `inputer` (Market ID: 1)
- User B: Create new user with Market ID: 2 (via Filament)

#### 3.1 Create Cross-Market Data
- [ ] Login sebagai admin_pusat via Filament
- [ ] Create new market: "Test Market 2" (id: 2)
- [ ] Create inputer user for Market 2: `inputer2` / `password` (market_id: 2)
- [ ] Create categories for Market 2
- [ ] Create transactions for Market 2

#### 3.2 Test Isolation
- [ ] Login sebagai `inputer` (Market 1)
- [ ] Dashboard: Should only show Market 1 totals
- [ ] Categories: Should only show Market 1 categories
- [ ] Reports: Should only show Market 1 transactions
- [ ] **Expected:** No Market 2 data visible

#### 3.3 Test Cross-Market API Access
- [ ] Use Postman/DevTools to test:
  - GET /api/categories?market_id=2
  - GET /api/transactions?market_id=2
- [ ] **Expected:** Empty results or 403

**Status:** ⏳ Manual Test Required

---

### Test Case 4: Business Rules Validation

#### 4.1 Transaction Validation
- [ ] Login sebagai inputer
- [ ] Try submit pemasukan tanpa nominal
- [ ] **Expected:** Error toast "Nominal harus diisi"

#### 4.2 Edit Window Test (24 hours)
- [ ] Create transaction now
- [ ] Try to edit immediately via API (if implemented)
- [ ] **Expected:** Success (within 24h window)
- [ ] Try to edit after 24h (simulate by changing created_at)
- [ ] **Expected:** Error "Edit window expired" (if inputer)

#### 4.3 Outstanding Payment Validation
- [ ] Go to sewa page
- [ ] Create tenant with outstanding: 100000
- [ ] Try payment: jumlah > outstanding
- [ ] **Expected:** Error "Jumlah tidak boleh melebihi outstanding"

#### 4.4 Mandatory Catatan Test
- [ ] Create category with `wajib_keterangan = 1`
- [ ] Try submit transaction for this category tanpa catatan
- [ ] **Expected:** Error "Catatan wajib diisi"

**Status:** ⏳ Manual Test Required

---

### Test Case 5: Hydration & Responsive Testing

#### 5.1 Hydration Test
- [ ] Open DevTools Console
- [ ] Refresh page
- [ ] **Expected:** NO hydration warnings/errors
- [ ] Check for browser extension attributes (bis_register, __processed_*)

#### 5.2 Mobile Responsive Test
- [ ] Use DevTools responsive mode (iPhone SE: 375px)
- [ ] Verify:
  - Text sizes: readable (18px+)
  - Touch targets: ≥44px
  - Navigation: bottom nav fits
  - No horizontal scroll
  - Cards stack properly

#### 5.3 Tablet/Desktop Test
- [ ] Test iPad (768px) and Desktop (1280px)
- [ ] Verify grid layouts work
- [ ] Max-width container applies correctly

**Status:** ⏳ Manual Test Required

---

### Test Case 6: Performance & Error Handling

#### 6.1 API Error Test
- [ ] Stop backend server (Ctrl+C)
- [ ] Try to submit transaction
- [ ] **Expected:** 
  - Toast error: "Gagal menambahkan pemasukan. Coba lagi."
  - Loading state ends

#### 6.2 Network Error Test
- [ ] Set browser offline
- [ ] Try to load dashboard
- [ ] **Expected:** Error state with clear message

#### 6.3 Load Performance
- [ ] Check DevTools Network tab
- [ **Expected:** Initial load < 3s
- [ ] Check bundle sizes (should be reasonable)

**Status:** ⏳ Manual Test Required

---

## 🐛 Bug Tracking Template

Copy template below untuk setiap bug found:

```markdown
## Bug #[Number]

### Description
[Brief description of what's broken]

### Steps to Reproduce
1. [ ]
2. [ ]
3. [ ]

### Expected Behavior
[What should happen]

### Actual Behavior
[What actually happens]

### Priority
- High (blocks core functionality)
- Medium (impacts UX but workaround exists)
- Low (minor issue)

### Status
- Open
- In Progress
- Fixed

### Notes
[Any additional context, screenshots, logs]
```

---

## 📊 Test Results Summary

### Automated Tests
- [x] TypeScript compilation: PASS
- [x] Production build: PASS
- [x] Backend migrations: PASS
- [x] API endpoints reachable: PASS

### Manual Tests
- [ ] Test Case 1: Complete User Flow
- [ ] Test Case 2: Authorization
- [ ] Test Case 3: Market Scoping
- [ ] Test Case 4: Business Rules
- [ ] Test Case 5: Hydration & Responsive
- [ ] Test Case 6: Performance & Errors

**Overall Progress:** 0/6 (0%) ⏳

---

## 🚀 Next Steps

If all tests pass:
1. ✅ Mark Phase 5 Day 57-59 as **COMPLETED**
2. Move to Phase 5 Day 60-63: Mobile Responsiveness Testing
3. Update TO-DO-LIST.md with results

If tests fail:
1. Document bugs using template above
2. Fix top priority bugs first
3. Re-run tests
4. Update progress

---

## 📝 Test Environment Details

**Frontend URL:** http://localhost:3001  
**Backend URL:** http://127.0.0.1:8000  
**Database:** bukupasar_dev (MySQL 8)  
**Browser:** Chrome (latest) with DevTools  
**Device Testing:** Chrome DevTools responsive mode  

**Test Accounts:**
- Inputer: `inputer` / `password` / Market ID: `1`
- Admin Pasar: `admin_pasar` / `password` / Market ID: `1`
- Admin Pusat: `admin_pusat` / `password` / All markets

---

**Testing Started:** 2025-10-15  
**Last Updated:** 2025-10-15  
**Tester:** AI Assistant + ManualVerification Required
