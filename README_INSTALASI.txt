PATCH RUANGRILEKS — PROFIL SUMBER TEKANAN AKADEMIK DESKRIPTIF

Tujuan
- Menambahkan satu ringkasan naratif pada hasil skrining siswa.
- Menampilkan ringkasan profil yang sama kepada Guru BK apabila hasil tersebut dibagikan secara eksplisit oleh siswa.
- Ringkasan mengenali komponen dengan proporsi butir tertinggi yang dijawab "Sering" atau "Selalu".

PENTING: BATAS METODOLOGIS
- Penelitian Sinadia & Gandey (2024) tidak menyediakan pedoman skoring, nilai ambang, kategori rendah/sedang/tinggi, maupun diagnosis.
- Karena itu, ringkasan ini adalah fitur deskriptif tingkat aplikasi, bukan skoring resmi instrumen dan bukan hasil validasi baru.
- Pembandingan dilakukan berdasarkan proporsi butir dengan respons "Sering" atau "Selalu" pada setiap komponen awal. Pembandingan memakai proporsi, bukan jumlah mentah, karena jumlah butir per komponen berbeda (5, 3, 4, 4).
- Jika beberapa komponen memiliki proporsi tertinggi yang sama, aplikasi menyebutkannya bersama. Jika tidak ada jawaban "Sering" atau "Selalu", aplikasi menyatakan belum ada komponen yang menonjol berdasarkan kriteria deskriptif ini.
- Tidak ada skor total, cut-off, tingkat keparahan, atau diagnosis yang ditambahkan.
- Konfirmasi redaksi butir nomor 12 dari artikel kepada dosen pembimbing/penulis sebelum penelitian sebenarnya.

CARA MEMASANG
1. Pastikan backup source code dan database terbaru tersedia.
2. Ekstrak seluruh isi ZIP langsung ke folder proyek:
   C:\xampp\htdocs\ruangrileks
3. Pilih Replace files apabila Windows meminta konfirmasi.
4. Buka PowerShell di C:\xampp\htdocs\ruangrileks.
5. Jalankan:
   php artisan view:clear
   php artisan test --filter=AuthAndScreeningTest
   php artisan test --filter=ScreeningSharingTest
   php artisan test

Tidak ada migration database baru dan tidak ada dependency baru.
Tidak perlu menjalankan migrate, migrate:fresh, atau npm build.

FILE YANG DIPERBARUI
- app/Services/ScreeningService.php
- app/Http/Controllers/ScreeningController.php
- app/Http/Controllers/BK/SharedScreeningController.php
- resources/views/screening/result.blade.php
- resources/views/bk/shares/show.blade.php
- tests/Feature/AuthAndScreeningTest.php
- tests/Feature/ScreeningSharingTest.php

Catatan: patch ini disiapkan berdasarkan source RuangRileks dan patch instrumen yang sebelumnya diunggah untuk percakapan ini. Jalankan seluruh tes lokal sebelum dipakai.
