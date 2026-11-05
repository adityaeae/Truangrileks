PATCH FITUR NOTIFIKASI RUANGRILEKS
==================================

Isi patch:
- Penyimpanan notifikasi menggunakan kanal database bawaan Laravel.
- Guru BK mendapat notifikasi saat siswa mengajukan atau membatalkan janji temu.
- Siswa mendapat notifikasi saat Guru BK menyetujui, menolak, atau menyelesaikan janji temu.
- Ikon lonceng di navigasi menampilkan lima notifikasi terbaru dan jumlah yang belum dibaca.
- Pemilik akun dapat membuka satu notifikasi atau menandai semua notifikasi miliknya sebagai dibaca.
- Endpoint notifikasi hanya mengambil data melalui relasi akun yang sedang login.
- Catatan pribadi siswa dan data skrining tidak dimasukkan ke payload notifikasi.

PERSIAPAN
1. Pastikan backup source code dan database terbaru sudah tersedia.
2. Ekstrak isi ZIP patch ke folder proyek:
   C:\xampp\htdocs\ruangrileks
3. Izinkan Windows menimpa file yang disebutkan patch. Patch hanya berisi file
   yang memang perlu ditambah atau diperbarui.
4. Buka PowerShell pada folder proyek, lalu jalankan:

   php artisan migrate

   Perintah ini hanya menambahkan tabel `notifications`; tidak menghapus tabel
   atau data yang sudah ada. Jangan jalankan migrate:fresh.

5. Bangun ulang aset tampilan:

   npm.cmd run build

6. Jalankan pengujian modul notifikasi:

   php artisan test --filter=BkAppointmentNotificationsTest

7. Jalankan seluruh pengujian:

   php artisan test

PENTING UNTUK DATABASE TESTING
Jika pengujian melaporkan tabel `notifications` tidak ditemukan pada database
`ruangrileks_test`, jalankan migrasi khusus file baru ke database testing.
Perintah PowerShell berikut mengganti nama database sementara saja, lalu
mengembalikan environment variable tersebut:

   $env:DB_DATABASE = "ruangrileks_test"
   php artisan migrate --path=database/migrations/2026_11_02_190000_create_notifications_table.php
   Remove-Item Env:DB_DATABASE

Pastikan perintah di atas dijalankan setelah migrasi utama dan hanya jika tabel
notifications belum ada di database testing. Jangan mengarahkan migrasi testing
ke `ruangrileks_v2`.

FILE YANG DITAMBAHKAN
- app/Notifications/BkAppointmentNotification.php
- app/Http/Controllers/NotificationController.php
- database/migrations/2026_11_02_190000_create_notifications_table.php
- resources/views/components/notification-bell.blade.php
- tests/Feature/BkAppointmentNotificationsTest.php

FILE YANG DIPERBARUI
- app/Http/Controllers/Student/BkAppointmentController.php
- app/Http/Controllers/BK/BkAppointmentManagementController.php
- resources/views/layouts/app.blade.php
- routes/web.php

Tidak ada perubahan pada `.env`, credential, hasil skrining, atau tabel akun.
