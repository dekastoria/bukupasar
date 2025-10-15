# Cara Kelola Master Data Jenis Sewa

**Untuk Admin:** Cara manage master data Jenis Sewa tanpa menu di sidebar

---

## 🎯 Kenapa Menu Di-hide?

Menu "Jenis Sewa" **tidak ditampilkan di sidebar** karena:
1. ✅ **Filter di menu Penyewa sudah cukup** untuk sortir data per jenis
2. ✅ **Mengurangi clutter** di sidebar
3. ✅ **Jarang diubah** - Jenis sewa biasanya setup sekali di awal
4. ✅ **Tetap bisa diakses** via URL langsung jika perlu edit

---

## 📊 Cara Lihat Statistik Penyewa per Jenis Sewa

### Via Menu Penyewa (Cara Utama)

1. **Buka menu "Penyewa"**
2. **Klik Filter** (icon funnel di kanan atas)
3. **Pilih "Jenis Sewa"**
   - Bisa pilih 1 atau multiple jenis
   - Contoh: Pilih "Lapak" untuk lihat semua penyewa lapak
4. **Lihat total rows** di pagination
   - Misal: "Showing 1-25 of 25" → Ada 25 penyewa lapak

**Keuntungan:**
- Langsung lihat detail penyewa per jenis
- Bisa kombinasi dengan filter lain (status sewa, outstanding, dll)
- Bisa export data (future feature)

---

## 🔧 Cara Kelola Master Data Jenis Sewa

### Akses via URL Langsung

**URL:** `http://127.0.0.1:8000/admin/rental-types`

**Langkah:**
1. Login ke Filament admin
2. Ketik URL di browser: `http://127.0.0.1:8000/admin/rental-types`
3. Halaman master data Jenis Sewa akan terbuka

**Di halaman ini, admin bisa:**
- ✅ **Lihat semua jenis sewa**
- ✅ **Tambah jenis sewa baru** (tombol "Buat Jenis Sewa")
- ✅ **Edit jenis sewa** (klik row atau icon edit)
- ✅ **Hapus jenis sewa** (hanya jika tidak ada penyewa yang pakai)
- ✅ **Lihat jumlah penyewa** per jenis (kolom "Jumlah Penyewa")
- ✅ **Filter by status aktif/non-aktif**

---

## 📝 Kapan Perlu Akses Master Data?

### Scenario 1: Pasar Baru Setup
**Situasi:** Market baru, perlu define jenis sewa sesuai kondisi pasar

**Langkah:**
1. Akses: `http://127.0.0.1:8000/admin/rental-types`
2. Lihat 5 jenis default: Lapak, Kios, Toko, Ruko, Los
3. **Edit/Hapus** yang tidak sesuai
4. **Tambah** jenis baru sesuai kebutuhan:
   - "Lapak Ikan"
   - "Lapak Sayur"
   - "Kios Sembako"
   - "Warung Makan"
   - dll

---

### Scenario 2: Tambah Jenis Sewa Baru
**Situasi:** Pasar menambah area baru dengan jenis sewa berbeda

**Langkah:**
1. Akses: `http://127.0.0.1:8000/admin/rental-types`
2. Klik **"Buat Jenis Sewa"**
3. Isi:
   - Nama: "Food Court"
   - Keterangan: "Area khusus tenant kuliner modern"
   - Aktif: ON
4. Simpan

**Hasil:** Jenis "Food Court" muncul di dropdown saat create penyewa baru

---

### Scenario 3: Non-aktifkan Jenis Sewa
**Situasi:** Jenis sewa tidak digunakan lagi (misal: Los sudah direnovasi jadi kios)

**Langkah:**
1. Akses: `http://127.0.0.1:8000/admin/rental-types`
2. Klik row "Los" → Edit
3. Toggle **"Aktif"** jadi OFF
4. Simpan

**Hasil:** 
- Jenis "Los" tidak muncul di dropdown saat create penyewa baru
- Penyewa existing yang sudah pakai "Los" tetap tercatat

---

### Scenario 4: Cek Statistik Detail
**Situasi:** Admin ingin tahu detail jumlah penyewa per jenis

**Langkah:**
1. Akses: `http://127.0.0.1:8000/admin/rental-types`
2. Lihat kolom **"Jumlah Penyewa"**:
   - Lapak: 25 penyewa (badge hijau)
   - Kios: 15 penyewa
   - Toko: 8 penyewa
   - Ruko: 3 penyewa
   - Los: 0 penyewa

**Insight:**
- Mayoritas penyewa adalah lapak (25)
- Los tidak terpakai (0) → bisa di-non-aktifkan

---

## 🚫 Validasi & Batasan

### Tidak Bisa Delete Jenis Sewa yang Digunakan

**Error:** "Cannot delete because there are tenants using this rental type"

**Penjelasan:** 
- Jika ada penyewa yang menggunakan jenis sewa tertentu, jenis tersebut **tidak bisa dihapus**
- **Solusi:** Non-aktifkan saja (toggle Aktif jadi OFF)

**Contoh:**
1. Jenis "Lapak" digunakan oleh 25 penyewa
2. Coba delete → ❌ Error
3. Solusi: Set Aktif = OFF → ✅ Berhasil

---

## 💡 Tips & Best Practices

### Tip 1: Setup Lengkap di Awal
**Rekomendasi:**
- Setup semua jenis sewa di awal operasional pasar
- Review dengan kepala pasar sebelum operasional
- Hindari sering ubah-ubah setelah penyewa banyak

### Tip 2: Naming Convention
**Good:**
- "Lapak" (singkat, jelas)
- "Kios Permanen" (descriptive)
- "Toko Lantai 2" (spesifik)

**Bad:**
- "Tempat Jualan Pedagang Kecil" (terlalu panjang)
- "Type A" (tidak jelas)

### Tip 3: Gunakan Keterangan
**Manfaat:**
- Dokumentasi untuk staff baru
- Perjelas perbedaan antar jenis

**Contoh:**
- **Nama:** Lapak
- **Keterangan:** "Tempat berjualan semi permanen ukuran 2x2 meter tanpa dinding, biasanya untuk pedagang sayur/buah"

### Tip 4: Jangan Hapus, Non-aktifkan
**Alasan:**
- Data historis tetap terjaga
- Laporan lama tetap valid
- Bisa diaktifkan lagi jika dibutuhkan

---

## 🔗 URL Reference

| Halaman | URL |
|---------|-----|
| **List Jenis Sewa** | `/admin/rental-types` |
| **Buat Jenis Sewa Baru** | `/admin/rental-types/create` |
| **Edit Jenis Sewa** | `/admin/rental-types/{id}/edit` |

**Catatan:** Replace `{id}` dengan ID jenis sewa yang ingin diedit

---

## ❓ FAQ

### Q: Kenapa menu Jenis Sewa tidak muncul di sidebar?
**A:** Karena jarang diakses dan untuk mengurangi clutter. Filter di menu Penyewa sudah cukup untuk keperluan sehari-hari.

### Q: Bagaimana cara tambah jenis sewa baru?
**A:** Akses URL `/admin/rental-types` → Klik "Buat Jenis Sewa"

### Q: Bisa tidak menampilkan menu di sidebar?
**A:** Bisa. Edit file `RentalTypeResource.php`, ubah `shouldRegisterNavigation()` return `true`

### Q: Data default apa saja yang ter-create?
**A:** 5 jenis: Lapak, Kios, Toko, Ruko, Los (bisa diedit/hapus sesuai kebutuhan)

### Q: Apakah jenis sewa wajib diisi saat create penyewa?
**A:** Ya, field Jenis Sewa **required** saat create/edit penyewa

---

**Last Updated:** 2025-01-16
