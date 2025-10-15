# Fitur Klasifikasi Jenis Sewa - Bukupasar

**Tanggal:** 2025-01-16  
**Status:** ✅ Implemented

---

## 📋 Overview

Menambahkan klasifikasi jenis sewa untuk penyewa (Lapak, Kios, Toko, Ruko, Los, dll) agar:
1. Setiap pasar bisa define jenis sewa sendiri sesuai kebutuhan
2. Admin bisa filter & sortir penyewa berdasarkan jenis sewa
3. Admin bisa lihat statistik: berapa penyewa lapak, berapa kios, dll
4. Label "Nomor Lapak" diganti jadi "Nomor" yang lebih umum

---

## 🆕 Tabel Baru: `rental_types`

### Schema:
| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT | Primary key |
| `market_id` | BIGINT | FK ke markets (setiap pasar punya jenis sewa sendiri) |
| `nama` | VARCHAR(100) | Nama jenis sewa (unique per market) |
| `keterangan` | TEXT | Deskripsi jenis sewa |
| `aktif` | BOOLEAN | Status aktif/non-aktif |
| `timestamps` | | created_at, updated_at |

### Relasi:
- `rental_types` → `markets` (belongsTo)
- `rental_types` → `tenants` (hasMany)

---

## 🔄 Perubahan Tabel `tenants`

### Field Baru:
- `rental_type_id` (BIGINT, nullable, FK ke rental_types)

### Relasi Baru:
- `tenants` → `rental_types` (belongsTo)

---

## ✨ Fitur yang Ditambahkan

### 1. Master Data "Jenis Sewa"

Admin dapat mengelola master data jenis sewa per pasar:

**Lokasi:** Hidden dari sidebar (akses via URL: `/admin/rental-types`)  
**Alasan:** Filter di menu Penyewa sudah cukup untuk keperluan sehari-hari

**Form Fields:**
- ✅ **Pasar** (select, hanya admin_pusat yang lihat)
- ✅ **Nama Jenis Sewa** (required, unique per market)
  - Contoh: Lapak, Kios, Toko, Ruko, Lapak Ikan, Los, dll
- ✅ **Keterangan** (textarea, optional)
- ✅ **Status Aktif** (toggle, default: true)

**Table Columns:**
- Pasar (hanya admin_pusat)
- Jenis Sewa (bold)
- **Jumlah Penyewa** (badge hijau, count berapa penyewa yang pakai jenis ini)
- Status Aktif (icon)
- Dibuat (toggleable, hidden by default)

**Filter:**
- By Pasar (admin_pusat only)
- By Status Aktif

**Validasi:**
- ❌ Tidak bisa delete jika ada penyewa yang menggunakan
- ✅ Nama jenis sewa harus unique per market

**Authorization:**
- admin_pusat & admin_pasar: Full CRUD
- inputer & viewer: No access

---

### 2. Update Form Penyewa

**Field Baru:**
- ✅ **Jenis Sewa** (select, **required**, posisi setelah "Nama Penyewa")
  - Dropdown options dari master rental_types (hanya yang aktif)
  - Searchable & preload
  - Helper text: "Pilih jenis tempat sewa (Lapak, Kios, Toko, dll)"

**Label yang Diubah:**
- ❌ "Nomor Lapak" → ✅ **"Nomor"**
  - Helper text: "Nomor unit/tempat sewa"
  - Lebih umum, tidak spesifik ke lapak saja

---

### 3. Update Table Penyewa

**Kolom Baru:**
- ✅ **Jenis Sewa** (posisi pertama setelah Pasar)
  - Badge berwarna info (biru)
  - Searchable & sortable
  - Relasi: `rentalType.nama`

**Urutan Kolom Baru:**
1. Pasar (admin_pusat only)
2. **Jenis Sewa** (badge biru) ← **BARU**
3. **Nomor** (dulu: Nomor Lapak) ← **RENAMED**
4. Nama Penyewa
5. No. HP
6. ... (kolom sewa & outstanding)

**Filter Baru:**
- ✅ **Filter: Jenis Sewa** (multiple select)
  - Bisa pilih lebih dari 1 jenis
  - Contoh: Filter hanya Lapak & Kios

---

## 📊 Use Cases

### Use Case 1: Admin Setup Jenis Sewa

**Skenario:** Pasar baru ingin define jenis tempat sewa mereka

1. Admin pasar login ke Filament
2. Klik menu **"Jenis Sewa"**
3. Klik **"Buat Jenis Sewa"**
4. Input:
   - Nama: "Lapak Sayur"
   - Keterangan: "Lapak khusus pedagang sayur"
   - Aktif: ON
5. Simpan
6. Ulangi untuk jenis lain (Lapak Ikan, Kios Daging, Toko Sembako, dll)

**Hasil:** Master data jenis sewa tersedia untuk dipilih saat create penyewa

---

### Use Case 2: Create Penyewa dengan Jenis Sewa

**Skenario:** Input penyewa baru

1. Admin klik menu **"Penyewa"** → **"Buat Penyewa"**
2. Isi:
   - Nama: Pak Budi
   - **Jenis Sewa:** Lapak (dropdown) ← **REQUIRED**
   - **Nomor:** A01
   - HP: 08123456789
   - (+ data sewa lainnya)
3. Simpan

**Hasil:** Penyewa tercatat dengan jenis sewa "Lapak"

---

### Use Case 3: Statistik Penyewa per Jenis

**Skenario:** Admin ingin tahu berapa penyewa per jenis sewa

**Cara 1: Via Menu Jenis Sewa**
1. Klik menu **"Jenis Sewa"**
2. Lihat kolom **"Jumlah Penyewa"**
   - Lapak: 25 penyewa (badge hijau)
   - Kios: 15 penyewa
   - Toko: 8 penyewa
   - Ruko: 3 penyewa

**Cara 2: Via Filter di Menu Penyewa**
1. Klik menu **"Penyewa"**
2. Klik **Filter** → **Jenis Sewa** → Pilih "Lapak"
3. Table menampilkan hanya penyewa lapak
4. Lihat total rows di pagination (misal: "Showing 1-25 of 25")

---

### Use Case 4: Laporan per Jenis Sewa

**Skenario:** Admin ingin export data penyewa lapak saja

1. Klik menu **"Penyewa"**
2. Filter: **Jenis Sewa = Lapak**
3. (Future: Export to Excel button)
4. Download list penyewa lapak

---

## 🗂️ Data Awal (Seeder)

Setiap market otomatis mendapat 5 jenis sewa default:

| Nama | Keterangan |
|------|------------|
| **Lapak** | Lapak pedagang kecil |
| **Kios** | Kios permanen dengan dinding |
| **Toko** | Toko dengan ukuran lebih besar |
| **Ruko** | Rumah toko 2 lantai |
| **Los** | Tempat terbuka tanpa dinding |

**Catatan:** Admin bebas tambah/edit/hapus sesuai kebutuhan pasar masing-masing

---

## 🔍 Query untuk Statistik

### Count Penyewa per Jenis Sewa
```php
$stats = RentalType::forMarket($marketId)
    ->withCount('tenants')
    ->get()
    ->map(fn($type) => [
        'jenis' => $type->nama,
        'jumlah' => $type->tenants_count
    ]);

// Output:
// [
//   ['jenis' => 'Lapak', 'jumlah' => 25],
//   ['jenis' => 'Kios', 'jumlah' => 15],
//   ...
// ]
```

---

## 🧪 Testing Checklist

- [x] Migration berhasil (rental_types table created)
- [x] Migration add rental_type_id to tenants berhasil
- [x] Model RentalType & relationships berfungsi
- [x] Seeder berhasil (5 jenis sewa default tersedia)
- [x] Menu "Jenis Sewa" di-hide dari sidebar
- [ ] CRUD Jenis Sewa berfungsi via URL `/admin/rental-types`
- [ ] Validation: tidak bisa delete jenis sewa yang digunakan penyewa
- [ ] Form Penyewa menampilkan dropdown "Jenis Sewa" (required)
- [ ] Label "Nomor Lapak" berubah jadi "Nomor"
- [ ] Table Penyewa menampilkan kolom "Jenis Sewa" (badge)
- [ ] Filter "Jenis Sewa" berfungsi (multiple select)
- [ ] Count "Jumlah Penyewa" di table Jenis Sewa akurat

---

## 🔜 Future Enhancements (Optional - Phase 2)

1. **Dashboard Widget:** Chart pie penyewa per jenis sewa
2. **Bulk Assignment:** Assign jenis sewa ke banyak penyewa sekaligus
3. **Tarif Default per Jenis:** Set tarif sewa default per jenis (misal: Lapak = 50rb, Kios = 100rb)
4. **Custom Fields per Jenis:** Jenis sewa bisa punya field tambahan (misal: Lapak punya field "ukuran_meter")

---

**Implemented by:** AI Assistant  
**Migration Files:**
- `2025_01_16_000002_create_rental_types_table.php`
- `2025_01_16_000003_add_rental_type_id_to_tenants_table.php`

**Model Files:**
- `app/Models/RentalType.php`
- `app/Models/Tenant.php` (updated)
- `app/Models/Market.php` (updated)

**Filament Resource:**
- `app/Filament/Resources/RentalTypeResource.php`
- `app/Filament/Resources/TenantResource.php` (updated)

**Seeder:**
- `database/seeders/RentalTypeSeeder.php`
