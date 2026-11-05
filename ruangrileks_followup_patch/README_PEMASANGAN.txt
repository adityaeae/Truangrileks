RUANGRILEKS — PATCH REKOMENDASI TINDAK LANJUT HASIL SKRINING

Tujuan
- Menambahkan satu pertanyaan pendamping tentang dampak tekanan akademik terhadap kegiatan belajar dan aktivitas sehari-hari.
- Pertanyaan ini TERPISAH dari 16 butir Skala Psikologi Stres Akademik Sinadia & Gandey (2024), tidak dihitung dalam distribusi instrumen, dan bukan butir skala tervalidasi.
- Menampilkan saran tindakan yang mengikuti jawaban dampak aktivitas serta profil komponen yang menonjol.
- Menampilkan konteks dan saran tersebut kepada Guru BK hanya ketika siswa secara eksplisit membagikan hasil terkait.
- Tidak membuat skor stres, kategori ringan/sedang/berat, diagnosis, atau nilai ambang.

Pertanyaan tambahan
"Seberapa besar tekanan akademik yang kamu alami mengganggu kegiatan belajar dan aktivitas sehari-hari?"
Pilihan:
- Tidak mengganggu
- Sedikit mengganggu
- Cukup mengganggu
- Sangat mengganggu

Pilihan jawaban ini adalah laporan diri siswa untuk menyesuaikan rekomendasi, bukan skala pengukuran keparahan klinis.

Pemasangan
1. Buat backup source dan database seperti prosedur backup proyek yang sudah digunakan.
2. Ekstrak seluruh isi ZIP ke root proyek Laravel, misalnya C:\xampp\htdocs\ruangrileks.
3. Pilih Replace files jika diminta.
4. Jalankan:
   php artisan view:clear
   php artisan test --filter=AuthAndScreeningTest
   php artisan test --filter=ScreeningSharingTest
   php artisan test

Tidak ada migration baru, tidak ada paket Composer/npm baru, dan tidak perlu menjalankan migrate atau npm build.

Perilaku penyimpanan dan privasi
- Jika siswa menyetujui penyimpanan, pilihan dampak disimpan sebagai metadata tambahan pada hasil yang sama. Ia tidak ditambahkan ke tabel jawaban sebagai butir ke-17.
- Jika siswa menolak penyimpanan atau mencoba sebagai guest/role lain, ringkasan hanya disimpan dalam session sementara sebagaimana alur DEMO yang sudah ada.
- Pilihan dampak dan rekomendasi hanya terlihat Guru BK jika hasil spesifik itu dibagikan dengan persetujuan eksplisit siswa.
- Teks persetujuan berbagi diperjelas untuk menyebut 16 jawaban, jawaban dampak tambahan, dan rekomendasi tindak lanjut.

Catatan metodologi
- Artikel Sinadia & Gandey (2024) tidak menetapkan cut-off untuk kategori tingkat stres. Karena itu patch tidak menyatakan siswa mengalami stres ringan, sedang, atau berat.
- Rekomendasi konsultasi ketika siswa memilih "Cukup mengganggu" atau "Sangat mengganggu" didasarkan pada laporan dampak aktivitas yang dipilih sendiri, bukan hasil diagnosis.
- Redaksi butir nomor 12 artikel sumber masih perlu dikonfirmasi kepada dosen pembimbing/penulis sebelum pengumpulan data penelitian sebenarnya.

File yang diperbarui
- app/Services/ScreeningService.php
- app/Http/Controllers/ScreeningController.php
- app/Http/Controllers/BK/SharedScreeningController.php
- resources/views/screening/questionnaire.blade.php
- resources/views/screening/result.blade.php
- resources/views/screening/shares/create.blade.php
- resources/views/bk/shares/show.blade.php
- tests/Feature/AuthAndScreeningTest.php
- tests/Feature/ScreeningSharingTest.php
