# Dokumentasi Hubungan Frontend dan Database SuratKampus

## 1. Gambaran Umum
Hubungan frontend dan database pada website SuratKampus digunakan untuk menghubungkan antarmuka pengguna dengan penyimpanan data sistem.

Frontend berfungsi sebagai tempat pengguna berinteraksi dengan website, sedangkan database digunakan untuk menyimpan dan mengelola data. Keduanya dihubungkan melalui PHP Native sebagai penghubung antara halaman website dengan MySQL.

## 2. Alur Hubungan Frontend dan Database
Secara umum, proses hubungan frontend dan database berlangsung melalui tahapan berikut:
1. Pengguna mengakses halaman website.
2. Pengguna memasukkan atau memilih data melalui formulir.
3. Data dikirim ke server melalui metode HTTP, seperti GET atau POST.
4. PHP menerima dan memproses data yang dikirimkan.
5. PHP menjalankan perintah SQL untuk mengambil, menyimpan, memperbarui, atau menghapus data pada MySQL.
6. Database memberikan hasil pemrosesan kepada PHP.
7. PHP menampilkan hasil tersebut kembali pada halaman website.

## 3. Hubungan Halaman Website dengan Database
Berikut hubungan antara halaman frontend SuratKampus dengan tabel database yang digunakan.

| Halaman Website | Tabel Database | Fungsi |
|---|---|---|
| Registrasi (`register.php`) | `users` | Menyimpan data akun mahasiswa baru. |
| Login (`login.php`) | `users` | Memeriksa email, kata sandi, dan role pengguna. |
| Dashboard Mahasiswa (`dashboard.php`) | `users`, `applications` | Menampilkan informasi akun dan riwayat pengajuan surat. |
| Pengajuan Surat (`ajukan.php`) | `applications`, `application_members` | Menyimpan data pengajuan dan anggota kelompok. |
| Edit Pengajuan (`edit_pengajuan.php`) | `applications`, `application_members` | Memperbarui data pengajuan dan anggota kelompok. |
| Dashboard Admin (`admin.php`) | `users`, `applications` | Menampilkan dan mengelola data pengajuan mahasiswa. |
| Kelola Admin (`kelola_admin.php`) | `users` | Mengelola akun admin dan petugas. |
| Unduh Surat (`surat_pdf.php`) | `users`, `applications`, `application_members` | Mengambil data untuk menghasilkan dokumen surat PDF. |

## 4. Proses Pengolahan Data

### 4.1 Proses Registrasi dan Login
Pada proses registrasi, data mahasiswa dimasukkan ke dalam tabel `users`. Setelah akun dibuat, mahasiswa dapat melakukan login menggunakan email dan kata sandi.
Sistem memeriksa data akun pada tabel `users` sebelum memberikan akses ke halaman sesuai dengan role pengguna.

### 4.2 Proses Pengajuan Surat
Mahasiswa mengisi formulir pengajuan melalui halaman `ajukan.php`.
Data utama pengajuan disimpan pada tabel `applications`, sedangkan data anggota kelompok disimpan pada tabel `application_members`.
Kedua tabel tersebut dihubungkan menggunakan kolom `application_id`.

### 4.3 Proses Pemeriksaan Pengajuan
Admin atau petugas mengakses halaman `admin.php` untuk melihat dan memeriksa data pengajuan.
Perubahan status dan catatan pemeriksaan disimpan pada tabel `applications`.
Status pengajuan meliputi:
- Menunggu.
- Diproses.
- Selesai.
- Ditolak.

### 4.4 Proses Perbaikan Data
Apabila terdapat data yang perlu dilengkapi, mahasiswa dapat mengakses halaman `edit_pengajuan.php`.
Perubahan data akan diperbarui pada tabel `applications` dan `application_members` sesuai dengan data yang diubah.

### 4.5 Proses Pembuatan Surat PDF
Ketika pengajuan telah berstatus Selesai, mahasiswa dapat mengunduh surat melalui halaman `surat_pdf.php`.
Halaman tersebut mengambil data pengguna, pengajuan, dan anggota kelompok dari database untuk menghasilkan dokumen PDF.

## 5. Diagram Hubungan Frontend, Backend, dan Database
```text
+--------------------------+
|        PENGGUNA          |
| Mahasiswa / Admin /      |
| Petugas                  |
+--------------------------+
             |
             v
+--------------------------+
|        FRONTEND          |
| HTML, CSS, JavaScript    |
| Halaman dan Formulir     |
+--------------------------+
             |
             v
+--------------------------+
|         BACKEND          |
|       PHP Native         |
| Validasi dan Pemrosesan  |
| Data serta Query SQL     |
+--------------------------+
             |
             v
+--------------------------+
|         DATABASE         |
|          MySQL           |
|                          |
| 1. users                 |
| 2. applications          |
| 3. application_members   |
+--------------------------+
             |
             v
+--------------------------+
|      HASIL PEMROSESAN    |
| Informasi, Status,       |
| Riwayat, dan Surat PDF   |
+--------------------------+
```

## 6. Kesimpulan
Frontend, backend, dan database pada website SuratKampus memiliki hubungan yang saling terintegrasi.
Frontend menyediakan antarmuka bagi pengguna, PHP Native menangani proses pengolahan dan komunikasi data, sedangkan MySQL menyimpan seluruh informasi yang dibutuhkan sistem.
Integrasi ketiga komponen tersebut memungkinkan proses registrasi, login, pengajuan surat, pemeriksaan administrasi, perbaikan data, hingga pembuatan surat PDF berjalan dalam satu sistem layanan surat digital kampus.
