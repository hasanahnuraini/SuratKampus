# Dokumentasi Fungsi Utama Sistem SuratKampus

## 1. Tujuan

Dokumen ini menjelaskan fungsi utama yang tersedia pada aplikasi SuratKampus berdasarkan implementasi file PHP di repository.

## 2. Fungsi Utama

### 2.1 Registrasi Mahasiswa — `register.php`
- Menerima nama, email, NIM, program studi, dan password.
- Memeriksa apakah email atau NIM sudah digunakan.
- Membuat akun baru dengan role awal `mahasiswa`.
- Menyimpan password dalam bentuk hash menggunakan `password_hash()`.

### 2.2 Login dan Logout — `login.php`, `logout.php`
- Login mencari akun berdasarkan email dan memverifikasi password.
- Jika berhasil, sistem membuat session pengguna dan mengarahkan pengguna sesuai role.
- Logout menghapus cookie session, menghancurkan session, lalu mengarahkan pengguna ke halaman awal.

### 2.3 Dashboard Mahasiswa — `dashboard.php`
- Mengambil daftar pengajuan milik mahasiswa yang sedang login.
- Menampilkan kode pengajuan, jenis surat, tanggal, status, dan catatan staf.
- Menyediakan akses revisi ketika status `Diproses` dan unduhan PDF ketika status `Selesai`.

### 2.4 Pengajuan Surat — `ajukan.php`
- Menyediakan formulir pengajuan surat.
- Menyimpan data pengajuan ke tabel `applications`.
- Menyimpan anggota tambahan ke `application_members` jika diisi.
- Memberikan status awal `Menunggu`.

### 2.5 Revisi Pengajuan — `edit_pengajuan.php`
- Memuat pengajuan berdasarkan ID dan pemilik yang sedang login.
- Mengizinkan perubahan hanya ketika status pengajuan `Diproses`.
- Memperbarui data pengajuan yang sama dan mengembalikan status menjadi `Menunggu`.

### 2.6 Pengelolaan Pengajuan oleh Staf — `admin.php`
- Menampilkan daftar pengajuan untuk admin dan petugas.
- Menyediakan filter berdasarkan status.
- Membatasi daftar pengajuan petugas berdasarkan program studi.
- Memungkinkan staf memperbarui status dan menambahkan catatan untuk mahasiswa.

### 2.7 Pengelolaan Akun Staf — `kelola_admin.php`
- Hanya dapat diakses role `admin`.
- Membuat akun staf dengan role `admin` atau `petugas`.
- Menampilkan daftar akun staf yang sudah terdaftar.

### 2.8 Pembuatan Surat PDF — `surat_pdf.php`, `surat_pdf_lib.php`
- Memeriksa login, kepemilikan pengajuan, role, dan status pengajuan.
- Mengambil data pengajuan serta anggota tambahan.
- Membentuk dokumen PDF dan mengirimkannya sebagai file unduhan.
- PDF hanya dapat diunduh ketika status pengajuan `Selesai`.

## 3. Komponen Pendukung

- `config.php`: koneksi PDO, session, helper akses, escaping output, flash message, dan CSRF.
- `partials/header.php` dan `partials/footer.php`: komponen tampilan yang digunakan ulang.
- `assets/app.js` dan `assets/style.css`: interaksi dan tampilan sisi browser; bukan pemrosesan utama backend.

## 4. Ringkasan Alur Fungsi

Mahasiswa membuat akun atau login, mengajukan surat, lalu memantau status melalui dashboard. Staf memeriksa pengajuan dan memperbarui status/catatan. Jika perlu revisi, mahasiswa memperbaiki pengajuan yang sama. Setelah status `Selesai`, surat dapat diunduh sebagai PDF.

## 5. Batasan Dokumentasi

Penjelasan ini merujuk pada fungsi yang terlihat di file repository. Tidak menyatakan bahwa fitur telah diuji melalui pengujian otomatis atau pengujian integrasi.
