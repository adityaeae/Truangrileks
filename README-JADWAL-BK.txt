MODUL JADWAL LAYANAN BK DAN JANJI TEMU SISWA — RUANGRILEKS

FITUR
- Admin dapat membuat, mengedit, memublikasikan, mengarsipkan, dan mengelola jadwal untuk Guru BK aktif.
- Guru BK dapat membuat dan mengedit jadwal miliknya sendiri.
- Siswa dapat melihat jadwal yang dipublikasikan dan mengajukan janji temu pada slot yang tersedia.
- Permintaan janji temu berstatus Menunggu Konfirmasi sampai Guru BK menerima atau menolaknya.
- Guru BK dapat menerima, menolak, atau menandai janji temu sebagai selesai.
- Siswa dapat melihat riwayat janji temu miliknya dan membatalkan janji temu aktif sebelum waktu pelayanan.
- Kapasitas slot dihitung dari janji temu yang menunggu dan dikonfirmasi. Bentrok jadwal Guru BK dan janji temu siswa dicegah.
- Admin hanya melihat data ketersediaan jadwal dan jumlah slot aktif; catatan janji temu serta data siswa tidak ditampilkan di panel jadwal Admin.

PEMASANGAN
1. Pastikan backup source code dan database sudah tersedia.
2. Ekstrak isi ZIP ke folder proyek C:\xampp\htdocs\ruangrileks dan pilih Replace jika diminta.
3. Jalankan: php artisan config:clear
4. Jalankan: php artisan migrate
   Perintah ini hanya menerapkan migration baru, bukan menghapus data. Jangan menjalankan migrate:fresh.
5. Setelah migration berhasil, jalankan: php artisan view:clear
6. Jalankan: php artisan route:list --name=service-schedules
7. Uji login dengan peran Admin, Guru BK, dan Siswa.

ROUTE UTAMA
- Admin: /admin/jadwal-layanan
- Guru BK: /bk/jadwal-layanan
- Siswa: /jadwal-temu-bk

MIGRATION TESTING
Jika phpunit.xml menggunakan database ruangrileks_test terpisah, terapkan migration yang sama ke database tersebut sebelum menjalankan test. Gunakan kredensial database lokal yang sama dengan konfigurasi .env, tanpa membagikannya. Jangan menjalankan migrate:fresh.

STATUS PENGUJIAN
File PHP diperiksa dengan php -l. Pengujian Laravel penuh belum dapat dijalankan di lingkungan pembuat patch karena folder vendor tidak tersedia. Jalankan php artisan test di komputer proyek setelah migration testing diterapkan.
