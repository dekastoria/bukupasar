Filament Admin Panel (super admin dan admin pasar) - re desain

admin panel ada 2 jenis, yaitu super admin (pengelola aplikasi) dan admin pasar, pastikan ketika login user di arahkan ke dashboard yang sesuai, 
   login super admin akan masuk ke dashboard super admin berisi pengaturan pasar, dan user admin tiap pasar akan masuk ke dashboar admin pasar

kemudian buat agar dashboard lebih interaktif dengan menambahkan widget filament 4
-----------------
## Rekomendasi Widget Filament 4 untuk Dashboard Keuangan

   1. `StatsOverviewWidget` (stat cards dengan sparkline)
     •  Tampilkan metrik cepat: total pemasukan hari ini, total pengeluaran, saldo kas,
        piutang sewa, dll.
     •  Gunakan ->chart([...]), ->color('success'/'danger'), dan ->descriptionIcon() untuk
         memberi indikasi tren (naik/turun).

   2. `ChartWidget` (Chart.js)
     •  Cocok untuk tren periode: line chart pemasukan/pengeluaran per minggu/bulan, bar
        chart per kategori, atau pie chart komposisi pengeluaran.
     •  Manfaatkan getFilters() untuk filter rentang waktu (harian, mingguan, bulanan).

   3. `TableWidget`
     •  Ringkasan data detail: daftar 10 transaksi terbesar hari ini, top penyewa dengan
        tunggakan, atau reminder pembayaran mendekati jatuh tempo.
     •  Gunakan kolom monetari (format Rupiah) dan Tables\Actions\Action untuk aksi cepat
        (lihat detail, kirim reminder).

   4. `InfolistWidget` (jika butuh tampilan ringkas detail pasar/pengaturan)
     •  Menampilkan informasi pasar aktif: saldo awal, batas backdate, hari input yang
        diizinkan, dsb., dengan layout vertikal yang rapi.

   5. Custom Livewire Card (Widget bawaan dengan view sendiri)
     •  Untuk call-to-action khusus (mis. “Ada 5 tenant belum bayar >30 hari – klik untuk
        follow up”), bisa extend Widget biasa dan render Blade view custom.

   Kombinasi Layout
   •  Atur grid di dashboard page: misalnya 2 kolom atas untuk StatsOverviewWidget, bar
      chart di bawahnya, lalu table widget di kolom kanan.
   •  Manfaatkan ->columns() atau Grid::make() pada halaman dashboard agar responsif di
      mobile.

   Dengan paket bawaan ini, kita bisa menyusun dashboard laporan keuangan yang informatif
   tanpa dependensi tambahan.

### Perubahan Dashboard Super Admin


1. **Akses Dashboard Filament**  
   Hanya role `admin_pusat` dan `admin_pasar` yang diizinkan masuk ke Filament.

2. **Tab 1 – “Data Pasar”**  
   - Menampilkan daftar pasar beserta tombol “Tambah Pasar”.  
   - Wizard dua langkah:  
     - Langkah 1: data pasar lengkap (nama, alamat, telepon, foto/logo, koordinat peta).  
     - Langkah 2: pembuatan admin pasar awal (nama, email, role admin, telepon, foto opsional).  
   - Implikasi: butuh kolom tambahan pada tabel `markets` dan penanganan upload/storage untuk logo dsb.

3. **Tab 2 – “Manajemen User”**  
   - Menyajikan daftar pasar → detail user per pasar → tombol “Tambah User”, dengan dukungan upload foto (crop & compress).  
   - **Reset & Lockout Frontend:** pengguna frontend (inputer/viewer/admin pasar) yang salah login ≥5× akan dikunci dan melihat pesan “Akun Anda terkunci, hubungi admin atau coba lagi setelah 1 jam.” Akun terbuka otomatis setelah 1 jam.  
   - Super admin/admin pasar memiliki action “Buka Kunci & Reset Password” yang menghapus status terkunci, mereset percobaan login, menghasilkan password baru (opsional kirim email), dan setiap aksi tercatat di audit log.  
   - **Lupa Password Admin (Filament):** admin dapat klik “Lupa Password” di login Filament; password secure (kombinasi huruf besar/kecil + angka) dikirim via email; setelah login mereka dapat mengganti password di dashboard admin pasar.

4. **Tab 3 – “Laporan Pasar”**  
   - **Tujuan:** pasar dapat menentukan kategori pemasukan/pengeluaran yang relevan (template default seperti Retribusi, Parkir, Sewa, Honor, dll., dan kategori custom tambahan).  
   - **Implementasi Teknis:**  
     1. Kategori default tetap dikelola oleh tabel `categories`; tambahkan kolom `is_default` agar admin tahu mana kategori bawaan.  
     2. Kategori custom sudah didukung (via `categories` per pasar); pastikan form “Tambah Kategori” mempermudah pembuatan kategori baru.  
     3. Konfigurasi laporan disimpan di `settings` atau tabel baru `report_settings`, berupa daftar ID kategori yang tampil di laporan (pemasukan/pengeluaran).  
     4. UI Tab 3 terdiri dari dua section (“Kategori Pemasukan” dan “Kategori Pengeluaran”) berisi checklist atau list drag-and-drop kategori default + custom, dilengkapi tombol “Tambah Kategori” (shortcut ke `CategoryResource`) dan tombol “Reset ke Default”.  
   - **Dampak Database:** tabel `categories` tetap dipakai (ditambah flag `is_default`), dan konfigurasi pilihan kategori per pasar disimpan di `settings/report_settings`.

5. **Tab 4 – “Setting”**  
   - Form konfigurasi SMTP (host, port, user, password terenkripsi, encryption, sender) dengan tombol “Test SMTP”.  
   - Pengaturan template email pendaftaran pasar (mengirim username & password awal ke admin pasar).  
   - Pengaturan template email “Lupa Password”.  
   - Menu pengiriman informasi lainnya (mis. maintenance/newsletter) juga dikelola dari tab ini.

------------------------------------------------------------------

## Panduan Revisi UI/UX Dashboard Admin Pasar

   Gambaran Umum

   Struktur baru terdiri dari 5 tab utama (dengan 1 tab opsional) yang menata ulang fitur
   dashboard agar lebih mudah dipahami, mendukung laporan fleksibel per pasar, dan siap
   ditindaklanjuti ke implementasi Filament 4 tanpa migrasi besar.

   ──────────────────────────────────────────

   1. Tab **Dashboard (Overview)**
   •  Isi Utama:
     •  KPI cepat: total pemasukan, total pengeluaran, saldo kas, jumlah penyewa telat,
        dsb.
     •  Grafik perbandingan harian/bulanan Pendapatan vs Pengeluaran lengkap dengan
        surplus/defisit.
     •  Card alert (contoh: “Ada 5 tenant tunggakan >30 hari”).
     •  Quick actions (tombol ke input transaksi/pembayaran).
   •  Catatan Teknis:
     •  Data berasal dari tabel transactions (filter jenis).
     •  Gunakan StatsOverviewWidget, ChartWidget, dan TableWidget mini.
     •  Tambahkan caching untuk statistik agar tidak membebani query.

   ──────────────────────────────────────────

   2. Tab **Laporan**
   •  Pilihan Laporan (toggle/dropdown/tab sekunder):
     1. Buku Kas
       •  Daftar kronologis transaksi (debit/kredit) dengan saldo berjalan.
       •  Opsi filter harian/bulanan + unduh PDF.
     2. Laporan Laba Rugi
       •  Ringkasan pendapatan dan beban per periode (harian/bulanan).
       •  Menggunakan kategori yang dikonfigurasi pasar.
   •  Catatan Teknis:
     •  Buat ReportService untuk menghitung saldo berjalan dan agregat laba rugi.
     •  Gunakan transactions, categories, settings.
     •  Dukungan PDF (mis. laravel-dompdf), siapkan Blade khusus print.

   ──────────────────────────────────────────

   3. Tab **Pendapatan**
   •  Fitur:
     •  Rekap pendapatan per kategori (sewa, parkir, retribusi, dll.).
     •  Chart tren 12 bulan per kategori.
     •  Indikator tren (naik/turun vs bulan sebelumnya).
     •  Filter periode + opsi export PDF/CSV.
   •  Teknis:
     •  Query transactions jenis='pemasukan' + GROUP BY kategori, bulan.
     •  Gunakan konfigurasi kategori per pasar (disimpan di categories +
        settings/report_settings).
     •  Simpan hasil agregasi ke cache jika dataset besar.

   Tab **Sewa** (Opsional – Tab ini hanya muncul bila kategori sewa dipilih saat setup)
   •  Section 1: Data Penyewa
     CRUD tenant, info masa sewa, kategori lapak, outstanding.
   •  Section 2: Laporan Pembayaran Sewa
     Rekap pembayaran per tenant + chart 12 bulan.
   •  Section 3: Laporan Piutang Sewa
     Outstanding tenant dengan indikator tren (perlu snapshot atau hitung dari data).
   •  Teknis:
     •  tenants, payments, transactions subkategori sewa.
     •  Jika ingin tren piutang historis, pertimbangkan catatan saldo bulanan atau rebuild
         dari data transaksi.

   ──────────────────────────────────────────

   4. Tab **Pengeluaran**
   •  Fitur:
     •  Rekap pengeluaran operasional per kategori (kebersihan, keamanan, listrik, dll.).
     •  Chart 12 bulan + indikator naik/turun.
     •  Highlight pengeluaran melebihi target (opsional).
   •  Teknis:
     •  transactions filter jenis='pengeluaran'.
     •  Target pengeluaran dapat disimpan di settings.
     •  Gunakan pendekatan agregasi sama seperti tab Pendapatan.

   ──────────────────────────────────────────

   5. Tab **Pengaturan Pasar**
   •  Isi:
     •  Detail pasar (nama, kode, alamat, logo, kontak, koordinat).
     •  Manajemen user pasar (list, tambah, reset password/lockout).
     •  Pengaturan operasional (allowed days, backdate, batas pembayaran, konfigurasi
        laporan) via key-value settings.
   •  Teknis:
     •  Manfaatkan Relasi Filament (mis. RelationManager di MarketResource) atau page
        khusus.
     •  Pastikan penanganan locked_until/login_attempts jika reset lockout diperlukan.
     •  Simpan konfigurasi laporan (kategori yang tampil) di settings/report_settings.

   ──────────────────────────────────────────

   Rekomendasi Implementasi
   1. Service Layer & Caching
     •  Buat service (DashboardService, ReportService) agar logic agregasi terpisah dari
        UI.
     •  Gunakan cache (cache()->remember) untuk statistik 12 bulan.

   2. Konfigurasi Laporan per Pasar
     •  Tambah kolom is_default di categories.  •  Simpan pilihan kategori laporan di
                                                   settings (key:
                                                   report_income_categories,
                                                   report_expense_categories).
                                                •  UI referensi: checklist/drag-drop di
                                                   tab Laporan atau Pengaturan.

   3. PDF & Export
     •  Sediakan tombol export (PDF/CSV) minimal untuk Buku Kas, Laba Rugi, Rekap
        Pendapatan, Piutang, Pembayaran.
     •  Gunakan komponen Filament Tables\Actions\ExportAction atau custom.

   4. Tab Optional Sewa
     •  Deteksi kategori “sewa” saat runtime; jika tidak ada, sembunyikan tab.
     •  Tawarkan penambahan wizard saat setup pasar.

   5. RBAC
     •  Pastikan admin_pasar hanya melihat data market-nya; admin_pusat dapat melihat
        semua (mungkin pilih pasar via switcher).

   6. UI Consistency & Layout
     •  Gunakan grid responsif (->columns() / Grid::make) di tiap tab.
     •  Pertahankan gaya minimal Filament; gunakan warna (->color('success'),
        ->descriptionIcon()) untuk tren.