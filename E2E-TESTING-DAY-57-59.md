# E2E Testing Report - Day 57-59
## Bukupasar Project

**Testing Period:** Phase 5 - Week 9  
**Date:** 2025-10-16  
**Tested By:** AI Assistant  

---

## ✅ Pre-Test Checklist

- [x] Backend running: `php artisan serve --host=127.0.0.1 --port=8000`
- [x] Frontend running: `npm run dev` (port 3001)
- [x] Database seeded dengan test data
- [x] All 4 user roles ada: admin_pusat, admin_pasar, inputer, viewer
- [x] Test market (ID: 1) tersedia

---

## 📋 Test Scenarios

### **Day 57: Authentication & Authorization**

#### Scenario 1: Login Flow - All Roles ✅

**Test 1.1: Admin Pusat Login**
- URL: `http://localhost:3001/login`
- Credentials:
  - Username: `adminpusat`
  - Password: `password`
  - Market ID: `1`
- **Expected:** Login berhasil → redirect ke `/dashboard`
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 1.2: Admin Pasar Login**
- Credentials: `adminpasar / password / 1`
- **Expected:** Login berhasil → redirect ke `/dashboard`
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 1.3: Inputer Login**
- Credentials: `inputer / password / 1`
- **Expected:** Login berhasil → redirect ke `/dashboard`
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 1.4: Viewer Login**
- Credentials: `viewer / password / 1`
- **Expected:** Login berhasil → redirect ke `/dashboard`
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 1.5: Invalid Credentials**
- Credentials: `invalid / wrongpass / 1`
- **Expected:** Error: "Username atau password salah"
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

#### Scenario 2: Authorization Checks ✅

**Test 2.1: Inputer Cannot Access Filament Admin**
- Login sebagai `inputer`
- Akses: `http://127.0.0.1:8000/admin`
- **Expected:** Redirect ke login atau 403 Forbidden
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 2.2: Viewer Cannot Create Transaction**
- Login sebagai `viewer` di frontend
- Coba akses: `/pemasukan/tambah`
- **Expected:** 403 atau form disabled
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 2.3: Admin Pasar Can Access Filament**
- Login sebagai `adminpasar`
- Akses: `http://127.0.0.1:8000/admin`
- **Expected:** Dashboard admin tampil
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 2.4: Admin Pusat Can See All Markets**
- Login sebagai `adminpusat` ke Filament
- Navigate to: Markets menu
- **Expected:** Menu Markets visible
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

### **Day 58: Market Scoping & Data Isolation**

#### Scenario 3: Market Scoping ✅

**Test 3.1: Create Second Market**
```sql
INSERT INTO markets (name, code, address) 
VALUES ('Pasar Test 2', 'TEST02', 'Alamat Test 2');
```

**Test 3.2: User Market A Cannot See Market B Data**
- Login sebagai user dari Market 1
- Coba akses transaksi Market 2 via API
- **Expected:** Empty result atau 403
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 3.3: Dashboard Shows Only Own Market Data**
- Login sebagai inputer Market 1
- Check dashboard stats
- Manually verify data only from Market 1
- **Expected:** Hanya data Market 1 yang tampil
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

#### Scenario 4: Business Rules Validation ✅

**Test 4.1: Backdate Limit (60 days)**
- Login sebagai inputer
- Input pemasukan dengan tanggal 65 hari lalu
- **Expected:** Error: "Tanggal melebihi batas backdate"
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 4.2: Future Date Not Allowed**
- Input transaksi dengan tanggal besok
- **Expected:** Error atau warning
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 4.3: Kategori Wajib Keterangan**
- Pilih kategori dengan `wajib_keterangan = 1`
- Submit tanpa isi catatan
- **Expected:** Validation error
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 4.4: Nominal Harus > 0**
- Input transaksi dengan nominal 0 atau negatif
- **Expected:** Validation error: "Jumlah harus lebih dari 0"
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

### **Day 59: Payment & Edit Window**

#### Scenario 5: Payment Validation ✅

**Test 5.1: Payment > Outstanding Rejected**
- Login sebagai inputer
- Pilih tenant dengan outstanding Rp 100,000
- Coba bayar Rp 150,000
- **Expected:** Error: "Pembayaran melebihi tunggakan. Maksimal Rp 100,000"
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 5.2: Payment = Outstanding (Full Payment)**
- Bayar tepat sejumlah outstanding
- **Expected:** Success, outstanding jadi 0
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 5.3: Outstanding Updated After Payment**
- Tenant awal: Rp 500,000
- Bayar: Rp 200,000
- Check outstanding
- **Expected:** Outstanding jadi Rp 300,000
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

#### Scenario 6: Edit Window (24 Hours) ✅

**Test 6.1: Inputer Can Edit Own Transaction Within 24h**
- Login sebagai inputer
- Create transaksi baru
- Immediately try to edit
- **Expected:** Edit berhasil
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 6.2: Inputer Cannot Edit After 24h**
- Manually set transaction `created_at` to 25 hours ago
- Try to edit
- **Expected:** 403 Forbidden atau button edit disabled
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

**Test 6.3: Admin Can Edit Anytime**
- Login sebagai admin_pasar
- Edit transaksi yang > 24 jam
- **Expected:** Edit berhasil
- **Actual:** _[To be tested]_
- **Status:** ⏳ Pending

---

## 🐛 Bugs Found

### Bug #1: [Template]
- **Severity:** High/Medium/Low
- **Description:** [What's broken]
- **Steps to Reproduce:**
  1. [Step 1]
  2. [Step 2]
- **Expected:** [What should happen]
- **Actual:** [What happens]
- **Screenshot:** [If applicable]
- **Status:** Open/Fixed

---

## 📊 Test Summary

| Category | Total | Passed | Failed | Pending |
|----------|-------|--------|--------|---------|
| **Authentication** | 5 | 0 | 0 | 5 |
| **Authorization** | 4 | 0 | 0 | 4 |
| **Market Scoping** | 3 | 0 | 0 | 3 |
| **Validation** | 4 | 0 | 0 | 4 |
| **Payment** | 3 | 0 | 0 | 3 |
| **Edit Window** | 3 | 0 | 0 | 3 |
| **TOTAL** | **22** | **0** | **0** | **22** |

---

## ✅ Completion Criteria

- [ ] All test scenarios executed
- [ ] All bugs documented
- [ ] Critical bugs fixed
- [ ] Test summary updated
- [ ] Screenshots captured for failures

---

## 📝 Notes

- Testing dilakukan secara manual (no automated tests yet)
- Browser: Chrome/Firefox
- Backend: Laravel 11 @ http://127.0.0.1:8000
- Frontend: Next.js @ http://localhost:3001
- Database: MySQL 8 @ bukupasar_dev

---

**Report Status:** 🔄 In Progress  
**Last Updated:** 2025-10-16
