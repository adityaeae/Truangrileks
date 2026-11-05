RUANGRILEKS — PERBAIKAN FORM PERTANYAAN DAMPAK AKTIVITAS

PENJELASAN
Patch Tingkat Kebutuhan Pendampingan sebelumnya telah menambahkan validasi `daily_impact` di controller, tetapi paket tersebut tidak menyertakan pembaruan halaman kuesioner. Akibatnya, form mengirim 16 butir saja sementara server mewajibkan jawaban pertanyaan tambahan.

FILE YANG DIPERBARUI
- resources/views/screening/questionnaire.blade.php

PERTANYAAN TAMBAHAN YANG DITAMPILKAN
"Seberapa besar tekanan akademik yang kamu alami mengganggu kegiatan belajar dan aktivitas sehari-hari?"
- Tidak mengganggu
- Sedikit mengganggu
- Cukup mengganggu
- Sangat mengganggu

Pertanyaan ini ditandai sebagai pertanyaan tambahan di luar 16 butir instrumen. Jawabannya tidak dihitung dalam distribusi instrumen.

CARA MEMASANG
1. Buat salinan cadangan file:
   resources/views/screening/questionnaire.blade.php
2. Ekstrak isi ZIP ke root proyek Laravel:
   C:\xampp\htdocs\ruangrileks
3. Pilih Replace jika Windows meminta konfirmasi.
4. Jalankan:
   php artisan view:clear
5. Buka kembali halaman kuesioner dan pastikan pertanyaan dampak aktivitas muncul setelah empat kelompok 16 butir dan sebelum tombol Kirim.
6. Jalankan tes:
   php artisan test --filter=AuthAndScreeningTest
   php artisan test --filter=ScreeningSupportPathwayTest
   php artisan test

CATATAN
- Patch ini hanya mengganti satu file Blade.
- Tidak ada perubahan controller, service, route, database, .env, migration, atau npm build.
- Pertanyaan tambahan ini bukan bagian dari skala 16 butir Sinadia & Gandey (2024) dan bukan alat diagnosis.
