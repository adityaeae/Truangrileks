# Modul Jadwal Layanan BK — RuangRileks

## Alur
- Admin dan Guru BK aktif dapat membuat serta mengelola jadwal layanan.
- Admin dapat menugaskan jadwal kepada Guru BK aktif; Guru BK hanya mengelola jadwal miliknya sendiri.
- Siswa hanya melihat jadwal yang dipublikasikan dan dibuat untuk Guru BK aktif.
- Siswa dapat mengajukan janji temu dengan status awal `pending`, melihat daftar janji temu miliknya, dan membatalkan janji temu aktif sebelum waktu layanan.
- Guru BK pemilik jadwal dapat mengonfirmasi, menolak, atau menandai janji temu selesai.

## Privasi dan perlindungan
- Admin tidak melihat catatan janji temu pribadi siswa pada halaman manajemen jadwal.
- Guru BK hanya melihat permintaan janji temu pada jadwal yang dimilikinya.
- Siswa hanya melihat data janji temu milik akun sendiri.
- Permintaan `pending` dan `approved` menggunakan kapasitas slot. Permintaan dibatalkan atau ditolak tidak lagi menghitung kapasitas aktif.
- Jadwal layanan Guru BK tidak boleh tumpang tindih dengan jadwal terpublikasi lain milik Guru BK yang sama.
- Siswa tidak dapat mengajukan dua janji temu aktif yang waktunya bertabrakan.
- Jadwal yang sudah memiliki riwayat janji temu tidak dapat dihapus; jadwal dapat diarsipkan dengan menonaktifkan publikasinya.
- Waktu, pemilik Guru BK, moda, dan lokasi tidak dapat diganti selama masih ada janji temu aktif. Deskripsi/judul boleh diperbarui; kapasitas tidak boleh diturunkan di bawah jumlah janji temu aktif.

## Routes
- Admin: `/admin/jadwal-layanan`
- Guru BK: `/bk/jadwal-layanan`
- Siswa: `/jadwal-temu-bk`

## Database
Migration baru: `2026_11_02_180000_create_bk_service_schedules_and_appointments_tables.php`.
Tabel baru: `bk_service_schedules` dan `bk_appointments`.

## Validasi
File PHP pada patch sudah diperiksa menggunakan `php -l`. Pengujian Laravel belum dapat dieksekusi di lingkungan pembuat patch karena dependensi `vendor` proyek tidak tersedia. Jalankan `php artisan test` di komputer proyek setelah migration diterapkan pada database testing.
