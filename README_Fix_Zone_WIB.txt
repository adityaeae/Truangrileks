PATCH ZONA WAKTU WIB - RUANGRILEKS

Tujuan:
Mengubah tampilan timestamp database UTC ke Asia/Jakarta (WIB, UTC+7), tanpa mengubah data tanggal/jam yang sudah tersimpan.

File yang diperbarui:
1. resources/views/admin/articles/index.blade.php
2. resources/views/admin/users/index.blade.php
3. resources/views/bk/shares/index.blade.php (waktu skrining dan waktu persetujuan)
4. resources/views/bk/shares/show.blade.php
5. resources/views/components/notification-bell.blade.php
6. resources/views/screening/history/index.blade.php
7. resources/views/screening/shares/create.blade.php
8. resources/views/student/bk-appointments/index.blade.php (waktu pengajuan)

Catatan:
- Database dan config/app.timezone tetap UTC.
- Jadwal BK starts_at dan ends_at sengaja tidak dikonversi. Form datetime-local menyimpan jam pelayanan yang diisi dalam waktu lokal; menggesernya lagi +7 akan membuat jam janji temu salah.
- Script melakukan preflight seluruh pola. Jika satu pola tidak ditemukan atau muncul lebih dari sekali, script berhenti tanpa mengubah file.
- Sebelum mengubah file, script membuat salinan ke storage/app/patch-backups/timezone-wib_<timestamp>.

Cara menjalankan:
1. Ekstrak ZIP.
2. Simpan file .ps1 pada folder C:\xampp\htdocs\ruangrileks.
3. Buka PowerShell pada folder proyek.
4. Jalankan:
   powershell -ExecutionPolicy Bypass -File .\RuangRileks_Fix_Zone_WIB.ps1
5. Setelah berhasil, jalankan:
   php artisan view:clear
   php artisan test

Jika script berhenti karena pola tidak ditemukan, jangan edit secara manual dulu. Kirimkan pesan yang ditampilkan agar patch disesuaikan dengan versi file aktif.
