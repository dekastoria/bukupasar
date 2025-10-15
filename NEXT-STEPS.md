# 🎯 Next Steps - Bukupasar Development

## ✅ Day 45-47 COMPLETED!

Selamat! Dashboard & Navigation sudah selesai dengan sempurna.

---

## 📊 Progress Update

### Phase 4: Frontend SPA (Week 7-8)
**Status:** 🔄 In Progress - **70% Complete**

#### ✅ Completed (Week 7)
- [x] Day 43-44: Auth Setup
  - AuthContext & API client
  - Login page dengan UX lansia
  - Token storage & management
  
- [x] Day 45-47: Dashboard & Navigation
  - Authenticated layout (Header + Navbar)
  - Dashboard dengan 3 summary cards
  - Bottom navigation (5 items)
  - Responsive design verified
  - UX Guidelines lansia terpenuhi

#### ⏳ Next Tasks (Week 8)
- [ ] Day 48-51: Transaction Input Forms (Wizard)
- [ ] Day 52-54: Sewa Form & Tenant Search
- [ ] Day 55-56: Reports Pages

---

## 🚀 Next Development Task: Day 48-51

### Transaction Input Forms (Wizard UX)

**Goal:** Build Pemasukan dan Pengeluaran input pages dengan multi-step wizard

**Pages to Build:**
1. `/pemasukan/tambah` - Input pemasukan form
2. `/pengeluaran/tambah` - Input pengeluaran form

**Wizard Steps:**
1. **Step 1:** Select kategori (large buttons, easy to tap)
2. **Step 2:** Enter details (nominal, tanggal, catatan)
3. **Step 3:** Review and submit

---

## 📋 Requirements for Day 48-51

### 1. Page Structure
```
app/
└── (authenticated)/
    ├── pemasukan/
    │   └── tambah/
    │       └── page.tsx
    └── pengeluaran/
        └── tambah/
            └── page.tsx
```

### 2. Form Components Needed
- `CategorySelector.tsx` - Grid of category buttons
- `TransactionForm.tsx` - Form for nominal, date, notes
- `TransactionReview.tsx` - Preview before submit

### 3. API Integration
- **Endpoint:** `POST /api/transactions`
- **Payload:**
  ```json
  {
    "tanggal": "2025-01-15",
    "jenis": "pemasukan",
    "subkategori": "Retribusi",
    "jumlah": 50000,
    "tenant_id": null,
    "catatan": "Pembayaran retribusi harian"
  }
  ```

### 4. Validation Rules
- ✅ `tanggal`: required, date, max backdate 60 days
- ✅ `jenis`: required, enum (pemasukan/pengeluaran)
- ✅ `subkategori`: required, must exist in categories
- ✅ `jumlah`: required, integer, min 1
- ✅ `catatan`: required if kategori.wajib_keterangan = 1
- ✅ `tenant_id`: optional, select for sewa categories

### 5. UX Guidelines (Lansia-Friendly)
- ✅ Input height: `h-14` (56px)
- ✅ Font size: `text-lg` or `text-xl` (18-20px)
- ✅ Category buttons: Large, colorful, with icons
- ✅ Clear step indicators (Step 1 of 3)
- ✅ Large "Next" and "Submit" buttons
- ✅ Confirmation message after success

---

## 🎨 Design Reference

### Step 1: Category Selection
```
┌─────────────────────────────────────┐
│  Pilih Kategori Pemasukan           │
├─────────────────────────────────────┤
│                                     │
│  ┌─────────┐  ┌─────────┐          │
│  │ 💰      │  │ 🏪      │          │
│  │Retribusi│  │  Sewa   │          │
│  └─────────┘  └─────────┘          │
│                                     │
│  ┌─────────┐  ┌─────────┐          │
│  │ 🎫      │  │ 🅿️      │          │
│  │ Parkir  │  │ Lainnya │          │
│  └─────────┘  └─────────┘          │
│                                     │
└─────────────────────────────────────┘
```

### Step 2: Transaction Details
```
┌─────────────────────────────────────┐
│  Detail Transaksi                   │
├─────────────────────────────────────┤
│                                     │
│  Kategori: Retribusi ✅             │
│                                     │
│  Nominal (Rp)                       │
│  ┌─────────────────────────────┐   │
│  │     50,000                  │   │
│  └─────────────────────────────┘   │
│                                     │
│  Tanggal                            │
│  ┌─────────────────────────────┐   │
│  │  📅  15 Jan 2025            │   │
│  └─────────────────────────────┘   │
│                                     │
│  Catatan                            │
│  ┌─────────────────────────────┐   │
│  │ Pembayaran harian...        │   │
│  └─────────────────────────────┘   │
│                                     │
│  [Kembali]          [Lanjutkan →]  │
│                                     │
└─────────────────────────────────────┘
```

### Step 3: Review
```
┌─────────────────────────────────────┐
│  Review Transaksi                   │
├─────────────────────────────────────┤
│                                     │
│  Jenis:       Pemasukan             │
│  Kategori:    Retribusi             │
│  Nominal:     Rp 50,000             │
│  Tanggal:     15 Jan 2025           │
│  Catatan:     Pembayaran harian...  │
│                                     │
│  [Kembali]          [Simpan ✓]     │
│                                     │
└─────────────────────────────────────┘
```

---

## 📚 AI Prompt Template

When ready to start Day 48-51, use this prompt:

```
Saya sedang develop Bukupasar project - Day 48-51: Transaction Input Forms.

Context:
- Frontend: Next.js 15 + TypeScript + shadcn/ui
- Backend API: Laravel 11 (already implemented)
- Auth: React Context + Sanctum tokens
- Current progress: Dashboard & Navigation complete

Task: Build Pemasukan input page dengan wizard UX (3 steps)

Reference:
- 03-FRONTEND-GUIDE.md section "Transaction Input Form (Wizard)"
- 05-AI-ASSISTANT-GUIDE.md → Template: Generate Next.js Page
- RESPONSIVE-DESIGN-CHECKLIST.md → UX Guidelines

Requirements:
1. Page route: /pemasukan/tambah
2. Three-step wizard:
   - Step 1: Select kategori (fetch from /api/categories?jenis=pemasukan)
   - Step 2: Input form (nominal, tanggal, catatan)
   - Step 3: Review & submit (POST /api/transactions)
3. UX lansia: Large inputs (h-14), big text (text-lg), clear buttons
4. Validation: Use react-hook-form + zod
5. Success: Toast notification + redirect to dashboard

Output:
- app/(authenticated)/pemasukan/tambah/page.tsx
- components/transactions/CategorySelector.tsx (optional)
- components/transactions/TransactionForm.tsx (optional)

Please implement step by step. Start with Step 1 (category selection).
```

---

## 🛠️ Development Checklist

Before starting Day 48-51, ensure:

- [x] Backend API running (`php artisan serve --host=127.0.0.1 --port=8000`)
- [x] Frontend dev server running (`npm run dev`)
- [x] Can login successfully (username: `inputer`, password: `password`)
- [x] Dashboard displays correctly
- [x] Navigation works (click "Masuk" should go to /pemasukan)

---

## 📂 Files to Create

### Day 48-51 (Estimated 4-6 hours)

1. **Page: Pemasukan Input**
   - `app/(authenticated)/pemasukan/tambah/page.tsx`
   - Multi-step wizard component
   - Category selection UI
   - Transaction form
   - Review screen

2. **Page: Pengeluaran Input**
   - `app/(authenticated)/pengeluaran/tambah/page.tsx`
   - Similar to pemasukan, but jenis='pengeluaran'

3. **Shared Components (Optional)**
   - `components/transactions/CategorySelector.tsx`
   - `components/transactions/TransactionFormFields.tsx`
   - `components/transactions/TransactionReview.tsx`

4. **API Hooks**
   - `hooks/useCategories.ts` (already exists, verify)
   - `hooks/useCreateTransaction.ts` (already exists, verify)

---

## 🎯 Success Criteria for Day 48-51

When these work, Day 48-51 is complete:

- [ ] Click "Masuk" in navbar → goes to /pemasukan/tambah
- [ ] Step 1: Categories display as large buttons
- [ ] Step 2: Form has validation (nominal required, date not backdate > 60 days)
- [ ] Step 3: Review shows all data before submit
- [ ] Submit: POST to /api/transactions succeeds
- [ ] Success toast displays
- [ ] Redirects to dashboard after submit
- [ ] Transaction appears in dashboard stats (refresh shows updated saldo)

---

## 📞 Need Help?

**Stuck on a step?** Ask AI:
```
I'm stuck on [specific issue].
Error: [paste error message]
What I tried: [list attempts]
Reference: [file/line number]
```

**Want to verify?** Test each step:
```bash
# 1. Test categories API
curl http://127.0.0.1:8000/api/categories?jenis=pemasukan \
  -H "Authorization: Bearer YOUR_TOKEN"

# 2. Test transaction creation
curl -X POST http://127.0.0.1:8000/api/transactions \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tanggal":"2025-01-15","jenis":"pemasukan","subkategori":"Retribusi","jumlah":50000,"catatan":"Test"}'
```

---

## 🎉 What You've Accomplished So Far

✅ **Phase 1-3:** Backend fully implemented (Laravel + Filament)  
✅ **Phase 4 (70%):** Frontend authentication, dashboard, navigation  
⏳ **Phase 4 (30%):** Transaction forms, reports  
⏳ **Phase 5-6:** Testing, deployment  

**You're making great progress! Keep going! 🚀**

---

**Last Updated:** 2025-01-15  
**Current Phase:** Phase 4 - Frontend SPA (Week 8)  
**Next Milestone:** Transaction Input Forms Complete
