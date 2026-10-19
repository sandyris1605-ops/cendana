# Cendana Resident — Hunian Premium + Dashboard Inventaris

## Akses
- Website: http://cendana.test/ (beranda, /kamar, /scan/KODE) — tanpa form booking, tanpa harga
- Admin: http://cendana.test/login → admin@cendana.test / password → /admin
- PHP yang dipakai: C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe

## Catatan positioning (front-end)
- Front-end bergaya luxury residence (tema hitam–emas, font Playfair Display): tanpa harga & tanpa booking di UI.
- Route POST /booking dan tabel bookings tetap ada (dipakai backend/dashboard) tapi tidak direferensikan dari halaman publik.
- Nama brand di semua tampilan (front & admin): "Cendana Resident".

## Fitur sesuai permintaan
- Data kamar + tipe + status live di frontend
- Barang per kamar (detail kamar menunjukkan unit + barcode)
- Pembelian barang → otomatis buat N unit + barcode CND-YYYY-0001
- Pengeluaran per kategori
- Aset (nilai perolehan + nilai buku)
- Karyawan, supplier + riwayat beli, penghuni + kontrak + bayar sewa
- Penyusutan garis lurus (tombol Hitung Bulan berjalan)
- Penggantian tahunan (badge Terlambat/Segera/Terjadwal + dashboard H-90 hari)
- Barcode Code128 + QR, halaman cetak label, halaman scan publik /scan/KODE
- Laporan keuangan/okupansi/inventaris/pembelian/penyusutan/penggantian (view + PDF + CSV)

## Tambahan
- Invoice sewa + catat bayar, maintenance → auto jadi pengeluaran, proteksi login + role admin/staff, log login/logout.

## Perintah penting
- migrate:fresh --seed memakai MasterSeeder, InventorySeeder, OccupancySeeder
- Hitung penyusutan: otomatis tiap tanggal 1 jam 01:00 via `aset:hitung-penyusutan`
  (rapel periode tertinggal bila server mati); tombol manual di /admin/penyusutan tetap ada
- Simulasi proyeksi nilai buku (tidak menyimpan) di kartu "Simulasi Penyusutan" /admin/penyusutan
- Cetak barcode di /admin/barcode/cetak (Print → Save PDF)

## Scheduler Windows (Laragon) — wajib agar penyusutan otomatis jalan
1. Buka Task Scheduler → Create Basic Task → nama "Cendana schedule:run".
2. Trigger: Daily, lalu Repeat task every 1 minute.
3. Action: Start a Program →
   Program: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe`
   Arguments: `C:\laragon\www\cendana\artisan schedule:run`
   Start in: `C:\laragon\www\cendana`
4. Cek: `php artisan schedule:list` harus menampilkan `aset:hitung-penyusutan ... monthlyOn`.
