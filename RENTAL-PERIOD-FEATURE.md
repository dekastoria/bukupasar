# Fitur Periode Sewa Penyewa - Bukupasar

**Tanggal:** 2025-01-16  
**Status:** ✅ Implemented

---

## 📋 Overview

Menambahkan tracking periode sewa untuk setiap penyewa di pasar, karena setiap pasar memiliki kebijakan sewa yang berbeda-beda.

---

## 🆕 Field Baru di Tabel `tenants`

| Field | Type | Description |
|-------|------|-------------|
| `tanggal_mulai_sewa` | DATE | Tanggal mulai kontrak sewa |
| `tanggal_akhir_sewa` | DATE | Tanggal akhir kontrak sewa / jatuh tempo |
| `tarif_sewa` | BIGINT | Tarif sewa per periode (dalam Rupiah) |
| `periode_sewa` | ENUM | Periode sewa: harian, mingguan, bulanan, tahunan |
| `catatan_sewa` | TEXT | Catatan tambahan tentang sewa |

---

## ✨ Fitur yang Ditambahkan

### 1. Form Input Sewa (Filament)

Di halaman Create/Edit Penyewa, sekarang ada section **"Informasi Sewa"** dengan field:

- ✅ **Tanggal Mulai Sewa** (DatePicker dengan format d/m/Y)
- ✅ **Tanggal Akhir Sewa** (DatePicker, harus >= tanggal mulai)
- ✅ **Tarif Sewa** (Numeric input dengan prefix "Rp")
- ✅ **Periode Sewa** (Dropdown: Harian, Mingguan, Bulanan, Tahunan)
- ✅ **Catatan Sewa** (Textarea untuk informasi tambahan)

Section ini **collapsible** (bisa di-expand/collapse) untuk UX yang lebih clean.

---

### 2. Kolom Tabel Penyewa (Filament)

Ditambahkan kolom baru di list penyewa:

#### Tanggal Mulai Sewa
- Format: dd/mm/yyyy
- Sortable
- Toggleable (bisa di-hide/show)
- Placeholder: "-" jika kosong

#### Tanggal Akhir Sewa
- Format: dd/mm/yyyy dengan **badge berwarna**
- **Indikator Status:**
  - 🔴 **Merah (Danger):** Sewa sudah expired
  - 🟡 **Kuning (Warning):** Sewa akan expired dalam 30 hari
  - 🟢 **Hijau (Success):** Sewa masih lama
- **Description dibawah tanggal:**
  - "Expired" jika sudah lewat
  - "X hari lagi" jika <= 30 hari
- Sortable & Toggleable

#### Tarif Sewa
- Format: Rp 1.000.000
- Sortable & Toggleable
- Placeholder: "-"

#### Periode Sewa
- Badge dengan format: "Bulanan", "Tahunan", dll
- Toggleable

#### Outstanding (Updated)
- Sekarang dengan **color coding:**
  - 🔴 **Merah (Danger):** Ada tunggakan (> 0)
  - 🟢 **Hijau (Success):** Lunas (= 0)

---

### 3. Filter Baru

#### Filter: Status Sewa
- **Semua** (default)
- **Aktif:** Sewa sedang berjalan (mulai <= hari ini, akhir >= hari ini)
- **Expired:** Sewa sudah lewat jatuh tempo

---

### 4. Helper Methods di Model Tenant

```php
// Format tarif sewa
$tenant->formatted_tarif_sewa; // "Rp 500.000"

// Cek status sewa
$tenant->isSewaActive();  // true/false
$tenant->isSewaExpired(); // true/false

// Hitung sisa hari
$tenant->getDaysUntilSewaExpires(); // 15 (hari) atau null
```

---

## 🔄 Migration

File: `database/migrations/2025_01_16_000001_add_rental_period_to_tenants_table.php`

```bash
php artisan migrate
```

**Status:** ✅ Sudah dijalankan

---

## 🎯 Use Cases

### Use Case 1: Input Penyewa Baru
1. Admin pasar buka menu **Penyewa** → **Buat Penyewa**
2. Isi data penyewa (nama, nomor lapak, HP, alamat)
3. Expand section **"Informasi Sewa"**
4. Input:
   - Tanggal Mulai: 01/01/2025
   - Tanggal Akhir: 31/12/2025
   - Tarif Sewa: 500000
   - Periode: Bulanan
   - Catatan: "Kontrak 1 tahun, bisa perpanjang"
5. Simpan

### Use Case 2: Monitor Sewa yang Akan Expired
1. Admin pasar buka menu **Penyewa**
2. Aktifkan kolom **"Akhir Sewa"** (toggle ON)
3. Lihat badge:
   - 🟡 **Kuning + "15 hari lagi"** → Perlu kontak penyewa untuk perpanjangan
   - 🔴 **Merah + "Expired"** → Sewa sudah habis
4. Filter dengan **Status Sewa: Aktif** untuk lihat yang masih berlaku saja

### Use Case 3: Perpanjangan Sewa
1. Admin edit penyewa yang akan expired
2. Update **Tanggal Akhir Sewa** → perpanjang 1 tahun lagi
3. Update **Tarif Sewa** jika ada perubahan harga
4. Tambah **Catatan Sewa:** "Perpanjangan ke-2, 01/01/2026"
5. Simpan

---

## 📊 Keuntungan Fitur Ini

1. ✅ **Tracking otomatis:** Tahu kapan sewa jatuh tempo tanpa manual cek
2. ✅ **Peringatan dini:** Badge kuning muncul 30 hari sebelum expired
3. ✅ **Multi-pasar friendly:** Setiap pasar bisa set tarif & periode berbeda
4. ✅ **History clear:** Catatan sewa untuk dokumentasi perpanjangan
5. ✅ **Filter cepat:** Bisa filter sewa aktif vs expired
6. ✅ **Visualisasi jelas:** Color coding memudahkan identifikasi status

---

## 🧪 Testing Checklist

- [x] Migration berhasil dijalankan
- [x] Model Tenant updated dengan field baru
- [x] TenantResource form menampilkan section Informasi Sewa
- [ ] Create tenant baru dengan data sewa → Berhasil
- [ ] Edit tenant existing, tambah data sewa → Berhasil
- [ ] Filter "Status Sewa: Aktif" → Menampilkan tenant dengan sewa aktif
- [ ] Filter "Status Sewa: Expired" → Menampilkan tenant dengan sewa expired
- [ ] Badge merah muncul untuk sewa expired
- [ ] Badge kuning + "X hari lagi" muncul untuk sewa <= 30 hari
- [ ] Toggle kolom sewa ON/OFF berfungsi

---

## 🔜 Future Enhancements (Optional - Phase 2)

1. **Auto-calculate Outstanding:** Auto hitung tunggakan berdasarkan periode sewa yang terlewat
2. **Notifikasi Email/SMS:** Reminder otomatis 30 hari sebelum expired
3. **History Perpanjangan:** Log setiap kali perpanjangan sewa
4. **Bulk Update:** Perpanjang banyak penyewa sekaligus (misal: naik tarif semua lapak)
5. **Dashboard Widget:** Widget "Sewa Akan Expired" di dashboard
6. **Export Report:** Laporan penyewa per status sewa (aktif, expired, akan expired)

---

## 📝 Notes

- Default `periode_sewa` = 'bulanan' (paling umum di pasar tradisional)
- Field sewa bersifat **optional** (nullable) → backward compatible dengan data lama
- Outstanding tetap manual input via Pembayaran, tidak auto-calculate dari tarif sewa

---

**Implemented by:** AI Assistant  
**Reviewed by:** -  
**Approved by:** -  

---

## 📚 Related Files

- Migration: `database/migrations/2025_01_16_000001_add_rental_period_to_tenants_table.php`
- Model: `app/Models/Tenant.php`
- Resource: `app/Filament/Resources/TenantResource.php`
- Spec: `01-PROJECT-SPEC.md` (perlu update)
