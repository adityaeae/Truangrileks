# AGENTS.md — RuangRileks

## Aturan Proyek
- Framework: Laravel 12, PHP 8.2+, Blade, Tailwind CSS 4 melalui Vite, dan MariaDB/MySQL.
- Bahasa antarmuka: Bahasa Indonesia.
- Arah visual: teal, putih, dan beige; pertahankan desain sederhana dan responsif.
- Gunakan konvensi Laravel dan PSR-12; gunakan relasi Eloquent yang jelas.
- Jangan mengubah teknologi atau membangun ulang project tanpa instruksi.
- Jangan membaca, menampilkan, atau mengubah nilai rahasia dari `.env`.
- Jangan menaruh kredensial database, API key, atau password dalam source code, dokumentasi, test config, maupun commit.
- Registrasi publik hanya boleh membuat akun `siswa`. Pembuatan akun `guru_bk` dilakukan admin melalui panel; akun `admin` tidak dibuat dari panel ini.
- Akun `siswa` dan `guru_bk` dapat dinonaktifkan admin tanpa menghapus data terkait. Akun nonaktif tidak boleh login/mengakses area terautentikasi. Penonaktifan siswa mencabut akses berbagi yang masih aktif.
- Panel admin hanya mengelola metadata akun dan konten; jangan menambahkan query/metric yang membaca jawaban atau hasil skrining pribadi.
- Jawaban skrining hanya boleh disimpan permanen jika pengguna sudah login sebagai siswa dan memberikan persetujuan penyimpanan secara eksplisit.
- Hasil tersimpan hanya boleh dibaca oleh siswa pemiliknya, kecuali hasil tertentu yang dibagikan secara eksplisit kepada satu akun guru BK dan aksesnya masih aktif.
- Persetujuan berbagi hasil wajib terpisah dari persetujuan penyimpanan. Simpan bukti persetujuan berbagi dan waktu pencabutan akses.
- Guru BK hanya boleh membaca hasil yang secara khusus dibagikan kepada akun guru BK tersebut; pencabutan akses harus langsung menghentikan akses.
- Pengisian baru menggunakan 16 butir Skala Psikologi Stres Akademik Sinadia & Gandey (2024), bukan SIVASS.
- Respons mengikuti label sumber: Tidak pernah, Sangat jarang, Kadang-kadang, Sering, dan Selalu.
- Jangan menjumlahkan respons, membuat cut-off/kategori tingkat tekanan, atau menyatakan diagnosis; artikel sumber tidak memberikan pedoman skoring atau nilai ambang.
- Kuesioner DEMO lama hanya dipertahankan untuk membaca riwayat historis.
- Jangan mencatat jawaban skrining ke log atau mem-flash jawaban sensitif sebagai old input.
- Jangan menjalankan `migrate`, `migrate:fresh`, seeder, reset database, atau operasi database destruktif tanpa instruksi eksplisit dari pemilik project.
- Jangan menghapus atau menimpa file yang sudah ada tanpa memeriksa dampaknya.
- Batasi perubahan pada file yang benar-benar diperlukan dan jalankan pemeriksaan yang tidak mengubah database.

## Struktur Penting
- `app/Http/Controllers/` — controller aplikasi.
- `app/Http/Middleware/` — autentikasi role.
- `app/Models/` — model dan relasi Eloquent.
- `app/Services/` — definisi butir instrumen, opsi respons, ringkasan frekuensi, dan dukungan baca hasil DEMO lama.
- `app/Http/Controllers/Admin/` — pengelolaan akun, artikel, dan kategori oleh admin.
- `app/Models/Article.php` dan `ArticleCategory.php` — konten edukasi.
- `resources/views/` — tampilan Blade.
- `routes/web.php` — definisi route.
- `database/migrations/` — definisi skema database.

## Status Pengembangan
Lihat `PROJECT_STATE.md` untuk fitur yang sudah ada, batasan yang diketahui, dan pekerjaan berikutnya.
