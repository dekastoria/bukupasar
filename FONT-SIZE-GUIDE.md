# Font Size Guide - Bukupasar UI

**Panduan ukuran font yang konsisten di seluruh aplikasi.**

---

## 📏 Standard Font Sizes

### Hierarchy (dari besar ke kecil):

| Level | Tailwind Class | Pixel Size | Penggunaan |
|-------|---------------|------------|------------|
| **H1 (Page Title)** | `text-xl` | **20px** | Judul halaman utama (Dashboard, Laporan Keuangan, dll) |
| **H2 (Subjudul)** | `text-lg` | **18px** | Subjudul section, Card title besar |
| **Body / Default** | `text-sm` | **14px** | Text biasa, deskripsi, form labels |
| **Small / Helper** | `text-xs` | **12px** | Helper text, metadata, timestamps |

---

## 📊 Ukuran Font Per Komponen

### 1. Page Headers

```tsx
// CORRECT ✅
<header className="space-y-1">
  <h2 className="text-xl font-semibold text-slate-800">    {/* 20px - Page Title */}
    Dashboard
  </h2>
  <p className="text-sm text-slate-600">                   {/* 14px - Description */}
    Selamat datang kembali!
  </p>
</header>
```

**Dipakai di:**
- Dashboard
- Laporan Keuangan
- Form Pemasukan/Pengeluaran
- Form Sewa

---

### 2. Card Titles

```tsx
// Section Card Title - text-lg (18px)
<CardTitle className="text-lg text-slate-800">
  Aktivitas Singkat
</CardTitle>

// Summary Card Title - text-sm (14px)
<CardTitle className="text-sm font-medium text-slate-600">
  Pemasukan Hari Ini
</CardTitle>
```

**Hierarchy:**
- **Large Card Title:** `text-lg` (18px) - untuk card penting
- **Small Card Title:** `text-sm` (14px) - untuk summary cards

---

### 3. Tabs / Navigation

```tsx
// Tab buttons - text-sm (14px)
<Link className="text-sm font-semibold">
  Laporan Harian
</Link>

// Navbar icons - text-xs (12px)
<Link className="text-xs">
  Home
</Link>
```

**Ukuran:**
- **Tab buttons:** `text-sm` (14px)
- **Navbar labels:** `text-xs` (12px)

---

### 4. Form Elements

```tsx
// Label - text-sm (14px)
<Label className="text-sm font-medium text-slate-700">
  Nominal *
</Label>

// Input - text-sm (14px)
<Input className="h-9 text-sm" />

// Button - text-sm (14px) atau text-base (16px)
<Button className="h-9 text-sm">
  Simpan
</Button>

// Helper text - text-xs (12px)
<p className="text-xs text-slate-500">
  Masukkan nominal dalam Rupiah
</p>
```

---

### 5. Tables

```tsx
// Table Header - text-sm (14px) font-semibold
<TableHead className="text-sm font-semibold">
  Tanggal
</TableHead>

// Table Cell - text-sm (14px)
<TableCell className="text-sm">
  01 Jan 2025
</TableCell>
```

---

### 6. Values / Numbers

```tsx
// Large values (Dashboard summary) - text-2xl (24px)
<p className="text-2xl font-bold text-slate-800">
  {formatCurrency(500000)}
</p>

// Medium values - text-lg (18px)
<p className="text-lg font-semibold">
  Rp 250.000
</p>

// Small values - text-sm (14px)
<p className="text-sm">
  Rp 50.000
</p>
```

**Hierarchy:**
- **Dashboard summary:** `text-2xl` (24px)
- **Card values:** `text-lg` (18px)
- **Table values:** `text-sm` (14px)

---

### 7. Wizard Step Indicators

```tsx
// Step number - text-sm (14px)
<div className="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold">
  1
</div>

// Step title - text-lg (18px)
<CardTitle className="text-lg text-slate-800">
  Langkah 1: Pilih Kategori
</CardTitle>

// Step description - text-sm (14px)
<p className="text-sm text-slate-600">
  Pilih kategori pemasukan
</p>
```

---

## 🎨 Complete Example: Laporan Keuangan

```tsx
<div className="space-y-4">
  {/* Page Header */}
  <header className="space-y-1">
    <h2 className="text-lg font-semibold text-slate-800">     {/* 18px - Subjudul */}
      Laporan Keuangan
    </h2>
    <p className="text-sm text-slate-600">                     {/* 14px - Deskripsi */}
      Lihat detail transaksi harian atau ringkasan periode tertentu.
    </p>
  </header>

  {/* Tab Navigation */}
  <nav>
    <Link className="text-sm font-semibold">                  {/* 14px - Tab */}
      Laporan Harian
    </Link>
  </nav>

  {/* Content Card */}
  <Card>
    <CardHeader>
      <CardTitle className="text-lg">                         {/* 18px - Card Title */}
        Detail Transaksi
      </CardTitle>
    </CardHeader>
    <CardContent>
      <p className="text-sm text-slate-600">                  {/* 14px - Content */}
        Menampilkan transaksi tanggal...
      </p>
      <p className="text-xs text-slate-500">                  {/* 12px - Helper */}
        Total: 15 transaksi
      </p>
    </CardContent>
  </Card>
</div>
```

---

## 📐 Size Comparison

```
H1 Page Title    ████████████████████  20px (text-xl)
H2 Subjudul      ██████████████████    18px (text-lg)
Body Text        ██████████████        14px (text-sm)  ← DEFAULT
Small Text       ████████████          12px (text-xs)
```

**Visual Balance:**
- Page title: **Sedikit lebih besar** (20px vs 18px subjudul)
- Subjudul: **Sedikit lebih besar** (18px vs 14px body)
- Body: **Standard** (14px)
- Small: **Lebih kecil** (12px untuk metadata)

---

## ✅ Do's and Don'ts

### ✅ DO:

```tsx
// Good - Consistent hierarchy
<h2 className="text-lg">Laporan Keuangan</h2>      // 18px subjudul
<p className="text-sm">Deskripsi...</p>             // 14px body
<span className="text-xs">Helper text</span>        // 12px small
```

### ❌ DON'T:

```tsx
// Bad - Inconsistent, too large
<h2 className="text-3xl">Laporan Keuangan</h2>      // 30px - TERLALU BESAR!
<p className="text-lg">Deskripsi...</p>             // 18px - Terlalu besar untuk deskripsi
```

---

## 🔍 Quick Reference

**Saat coding, gunakan ini:**

| Element | Class | Size |
|---------|-------|------|
| Page title | `text-xl` | 20px |
| Section title | `text-lg` | 18px |
| Body/Default | `text-sm` | 14px |
| Helper/Meta | `text-xs` | 12px |
| Dashboard value | `text-2xl` | 24px |

**Formula:**
- **Subjudul = Body + 4px** (18px vs 14px)
- **Page title = Subjudul + 2px** (20px vs 18px)

---

## 🎯 Migration Notes

### Before Redesign (Lansia):
```
Page Title:   30px (text-3xl)  ❌
Subjudul:     24px (text-2xl)  ❌
Body:         18px (text-lg)   ❌
Helper:       16px (text-base) ❌
```

### After Redesign (Modern):
```
Page Title:   20px (text-xl)   ✅
Subjudul:     18px (text-lg)   ✅
Body:         14px (text-sm)   ✅
Helper:       12px (text-xs)   ✅
```

**Reduction:** ~40% smaller overall, much more modern!

---

## 📱 Responsive Considerations

**Font sizes are same across all breakpoints.**

We don't scale fonts up on desktop because:
- ✅ Modern design prefers consistent sizing
- ✅ Desktop users typically sit further from screen
- ✅ 14px is perfectly readable on all devices

**Exception:** Dashboard value cards slightly larger (24px) for emphasis.

---

## 🔄 How to Change Font Sizes

**Global change (all body text):**
```tsx
// In tailwind.config.js (if needed)
theme: {
  fontSize: {
    'sm': '14px',  // Default body
  }
}
```

**Component-specific:**
```tsx
// Just change the Tailwind class
<p className="text-sm">  → <p className="text-base">
```

---

## 📊 Font Weight Guide

| Weight | Class | Usage |
|--------|-------|-------|
| **Bold** | `font-bold` | Page titles, numbers |
| **Semibold** | `font-semibold` | Subjudul, buttons, card titles |
| **Medium** | `font-medium` | Labels, small titles |
| **Normal** | `font-normal` | Body text, deskripsi (default) |

---

**Last Updated:** 2025-01-16  
**Status:** Active - Consistent across all pages
