# SuratKampus — Layanan Surat Digital Kampus

Prototipe web PHP Native + MySQL untuk pengajuan surat administrasi mahasiswa. Fitur inti dipertahankan dari proyek SuratWarga; istilah dan jenis surat disesuaikan ke ranah kampus.

## Fitur
- Registrasi dan login mahasiswa melalui `login.php`.
- Satu halaman login (`login.php`) untuk mahasiswa dan admin/petugas; setelah masuk, pengguna diarahkan otomatis sesuai role.
- Form pengajuan surat dan riwayat status.
- Jenis surat: Observasi Mata Kuliah/Mini Riset, Magang Mandiri, PKLI, dan Observasi Skripsi.
- Panel admin/petugas untuk melihat pengajuan, memfilter status, menambahkan catatan, dan mengubah status.
- Unduh PDF setelah status pengajuan menjadi `Selesai`.
- Prepared statements, password hashing, session, dan CSRF token.

## Instalasi awal di XAMPP
1. Ekstrak folder `SuratKampus` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih menu **Import**, lalu impor `database.sql`. File ini sudah mencakup semua kolom yang dibutuhkan formulir pengajuan terbaru.
5. Buka `http://localhost/SuratKampus/`.
6. Daftarkan akun mahasiswa melalui web.

> Jika folder proyek berada di dalam folder SuratKampus lain, alamat web bisa menjadi `http://localhost/SuratKampus/SuratKampus/`. Pastikan URL mengarah ke folder yang berisi `index.php`.

## Menyiapkan akun admin
Untuk keamanan, pendaftaran publik selalu membuat akun ber-role `mahasiswa`. Buat/daftarkan akun terlebih dahulu, kemudian ubah role akun yang memang ditujukan sebagai admin melalui phpMyAdmin: pilih database `kampus_surat` > tab SQL. Ganti email contoh dengan email akun yang benar-benar terdaftar. Setelah itu admin masuk lewat halaman login yang sama (`http://localhost/SuratKampus/login.php`) dan otomatis diarahkan ke panel admin.

```sql
UPDATE users
SET role = 'admin'
WHERE email = 'email-admin-anda@example.com';

SELECT id, nama, email, role
FROM users
WHERE email = 'email-admin-anda@example.com';
```

Untuk membuat akun sebagai petugas, gunakan `SET role = 'petugas'`. Admin dan petugas sama-sama dapat mengelola pengajuan.

## Jika database `kampus_surat` sudah ada
Jangan impor ulang untuk database yang berisi data penting tanpa membuat cadangan terlebih dahulu. Jika tabel `users` sudah memakai kolom `nama`, `email`, `nim`, `prodi`, `password`, dan `role`, jalankan migrasi berikut agar role `petugas` dapat digunakan:

```sql
ALTER TABLE users
MODIFY role ENUM('mahasiswa','admin','petugas')
NOT NULL DEFAULT 'mahasiswa';
```

Jika tabelmu masih memiliki kolom `nik` dan `alamat` dari proyek desa, jangan jalankan migrasi di atas saja; gunakan database baru dari `database.sql` atau sesuaikan skema setelah mencadangkan data.

## Tentang PDF surat
PDF dibuat berdasarkan struktur contoh surat yang disertakan: surat observasi mini riset, observasi skripsi, dan PKLI. Surat Magang Mandiri mengikuti susunan surat PKLI dengan redaksi berbeda. Tata letak (kertas F4 612 x 936 pt, Times 12 pt, kop, tabel, tanda tangan) ada di `surat_pdf_lib.php` dan sudah diukur dari file contoh. Nomor urut surat sengaja dikosongkan seperti contoh (`/UN33.5.1/PG/tahun`) untuk diisi petugas; ubah `SURAT_NOMOR_URUT` bila ingin terisi otomatis. Nama/NIP penandatangan dan catatan draf (`SURAT_CATATAN_DRAF`) juga diatur di bagian atas file itu. Untuk database yang sudah ada, jalankan `upgrade_target_address.sql` agar alamat instansi bisa diisi dan dicetak.

## Catatan
- Koneksi default XAMPP: host `localhost`, user `root`, password kosong. Sesuaikan di `config.php` bila konfigurasi MySQL berbeda.
- Uji alur registrasi, login, pengajuan, perubahan status, dan unduh PDF sebelum demo.

## Struktur database untuk formulir terbaru
`database.sql` sudah mencakup kolom `target_institution`, `course_name`, `supervisor_name`, `activity_field`, `activity_start`, `activity_end`, dan `research_title` pada tabel `applications`.

- **Instalasi baru:** cukup impor `database.sql`.
- **Database lama yang belum memiliki kolom-kolom tersebut:** buat cadangan terlebih dahulu, lalu jalankan `upgrade_form_fields.sql` satu kali melalui phpMyAdmin > database `kampus_surat` > tab SQL.
- **Database yang sudah memiliki semua kolom tersebut:** jangan jalankan `upgrade_form_fields.sql` lagi, karena MySQL akan melaporkan kolom duplikat.

Jangan mengimpor ulang `database.sql` ke database yang sudah berisi data penting. `CREATE TABLE IF NOT EXISTS` tidak mengubah struktur tabel yang sudah ada; gunakan migrasi yang sesuai untuk database lama.


## Alur revisi pengajuan
- Jika admin meminta perbaikan, ubah status menjadi `Diproses` dan tulis catatan yang jelas pada kolom catatan admin.
- Mahasiswa akan melihat tombol **Edit / Lengkapi Data** di dashboard selama status `Diproses`.
- Saat mahasiswa menyimpan revisi, data pada pengajuan yang sama diperbarui dan status kembali menjadi `Menunggu` agar admin memeriksa ulang. Nomor pengajuan tidak berubah dan catatan admin sebelumnya tetap tersimpan.
- Tombol edit hanya tersedia untuk pemilik pengajuan dan hanya saat status `Diproses`.


## Pengelolaan akun admin/petugas

Pendaftaran publik di `register.php` hanya membuat akun mahasiswa. Untuk membuat akun admin pertama kali, buat akun mahasiswa lalu ubah rolenya satu kali melalui phpMyAdmin (tabel `users`, kolom `role`) menjadi `admin`, atau gunakan SQL UPDATE yang dijelaskan di bagian database. Setelah admin utama dapat login, gunakan menu **Kelola Admin** atau tombol **Tambah Admin / Petugas** di panel admin untuk membuat akun staf berikutnya. Halaman `kelola_admin.php` hanya dapat diakses oleh akun dengan role `admin`.


## Anggota kelompok pada pengajuan surat

Formulir pengajuan kini memungkinkan mahasiswa menambahkan hingga 20 anggota kelompok. Identitas mahasiswa yang login tetap diambil otomatis dari profil, sedangkan anggota tambahan disimpan pada tabel `application_members` dan dicantumkan pada PDF surat.

Sebelum menggunakan fitur ini, buka phpMyAdmin, pilih database `kampus_surat`, tab SQL, lalu jalankan isi file `upgrade_group_members.sql`. Migrasi ini hanya menambahkan tabel anggota kelompok dan tidak menghapus data yang ada.


## Pembagian surat per program studi
Surat mahasiswa hanya tampil di dashboard admin/petugas dengan program studi yang sama. Pencocokan memakai teks prodi yang identik, sehingga `register.php` dan `kelola_admin.php` memakai dropdown dari satu daftar, `PRODI_LIST` di `config.php`. Edit daftar itu agar sesuai prodi kampus. Untuk akun yang sudah ada, jalankan `upgrade_normalisasi_prodi.sql` (sesuaikan dulu isinya dengan hasil langkah 1).
