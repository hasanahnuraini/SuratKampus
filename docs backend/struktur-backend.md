# Dokumentasi Backend SuratKampus

Branch kerja yang direncanakan: `feature/backend`  
Teknologi: PHP Native, PDO, MySQL, session PHP, dan generator PDF internal.

> Dokumentasi ini disusun berdasarkan file yang tersedia di repository. Detail database mengikuti query yang digunakan oleh aplikasi; file skema database terpisah tidak ditemukan pada daftar file utama.

## 1. Gambaran Umum

Backend SuratKampus menangani registrasi dan login pengguna, pengajuan surat mahasiswa, pemeriksaan dan revisi oleh admin/petugas, serta pembuatan surat PDF ketika pengajuan selesai. Aplikasi menggunakan PHP Native dan akses database melalui PDO.

## 2. Struktur File Backend

| File | Fungsi |
|---|---|
| `config.php` | Memulai session, mengatur koneksi PDO, menyediakan helper escaping, validasi program studi, pemeriksaan login/role, flash message, serta token dan verifikasi CSRF. |
| `index.php` | Halaman awal/landing aplikasi. |
| `register.php` | Registrasi akun mahasiswa; memeriksa email atau NIM yang sudah digunakan dan menyimpan password dengan `password_hash()`. |
| `login.php` | Autentikasi berdasarkan email dan password; memverifikasi password dengan `password_verify()`, memperbarui ID session, lalu mengarahkan pengguna berdasarkan role. |
| `logout.php` | Menghapus cookie session (bila digunakan), menghancurkan session, dan mengarahkan ke halaman awal. |
| `dashboard.php` | Dashboard mahasiswa; menampilkan pengajuan milik pengguna, ringkasan status, tautan revisi, dan unduhan PDF untuk surat selesai. |
| `ajukan.php` | Form dan proses pembuatan pengajuan surat; menyimpan data pengajuan dan anggota tambahan bila ada. |
| `edit_pengajuan.php` | Memuat pengajuan milik mahasiswa yang sedang login dan mengizinkan revisi hanya saat status `Diproses`; setelah disimpan status kembali `Menunggu`. |
| `admin_login.php` | Halaman login staf/admin. |
| `admin.php` | Halaman pengelolaan pengajuan untuk staf; akses staf dibatasi menurut role dan program studi. |
| `kelola_admin.php` | Pengelolaan akun admin/petugas; halaman khusus role `admin`, membuat akun staf dan menampilkan daftar staf. |
| `surat_pdf.php` | Memeriksa akses dan status pengajuan, mengambil data anggota, lalu mengirim PDF sebagai unduhan. |
| `surat_pdf_lib.php` | Library internal untuk menyusun layout dan output PDF, termasuk kop surat, alamat, tabel mahasiswa, tanda tangan, pemenggalan baris, dan objek PDF. |
| `partials/header.php` | Komponen header/navigasi yang digunakan ulang pada halaman. |
| `partials/footer.php` | Komponen footer yang digunakan ulang. |
| `assets/app.js` | JavaScript sisi browser untuk interaksi antarmuka. |
| `assets/style.css` | Styling aplikasi; bukan logika backend. |

## 3. Komponen Backend

- **Autentikasi:** registrasi, login, logout, dan session.
- **Manajemen pengguna:** data nama, email, NIM/ID, program studi, password, dan role.
- **Pengajuan surat:** jenis surat, instansi tujuan, alamat, dosen pembimbing, mata kuliah/topik penelitian, bidang dan periode kegiatan, deskripsi, serta anggota pengajuan.
- **Workflow:** status pengajuan dan catatan staf.
- **Dokumen:** pembuatan surat PDF menggunakan library PHP internal.

## 4. Autentikasi dan Hak Akses

### Autentikasi

1. Pengguna mengirim email dan password melalui `login.php`.
2. Backend mencari pengguna berdasarkan email menggunakan prepared statement.
3. Password dibandingkan dengan hash menggunakan `password_verify()`.
4. Jika valid, session ID diregenerasi dengan `session_regenerate_id(true)` dan data pengguna disimpan dalam session.
5. Pengguna diarahkan ke halaman sesuai role.
6. `logout.php` menghapus cookie session dan menghancurkan session.

Saat registrasi, akun publik dibuat dengan role `mahasiswa`. Password disimpan menggunakan `password_hash()`.

### Role dan akses

| Role | Akses utama |
|---|---|
| `mahasiswa` | Membuat pengajuan, melihat pengajuan miliknya, memperbaiki pengajuan yang berstatus `Diproses`, dan mengunduh PDF yang berstatus `Selesai`. |
| `petugas` | Menangani pengajuan sesuai cakupan program studi yang ditetapkan. |
| `admin` | Mengelola pengajuan dan akun staf; halaman `kelola_admin.php` khusus admin. |

Pemeriksaan akses terpusat tersedia melalui `require_login()` dan `require_role()`. Fungsi `is_staff_role()` mengenali role `admin` dan `petugas`, sedangkan `role_home()` menentukan halaman tujuan berdasarkan role. Program studi staf diambil dari database melalui `current_staff_prodi()`.

## 5. Alur Pengajuan dan Pengelolaan Surat

### Pengajuan baru

1. Mahasiswa login dan membuka `ajukan.php`.
2. Mahasiswa memilih jenis surat dan mengisi informasi tujuan serta keterangan.
3. Backend memvalidasi data dan menyimpan record ke tabel `applications` dengan status awal `Menunggu`.
4. Jika ada anggota tambahan, data disimpan ke `application_members`.
5. Mahasiswa dapat melihat pengajuan melalui `dashboard.php`.

### Pemeriksaan staf

1. Staf membuka halaman pengelolaan pengajuan.
2. Pengajuan ditinjau sesuai program studi staf.
3. Staf dapat memperbarui status dan memberikan catatan.
4. Status `Diproses` digunakan untuk meminta mahasiswa melengkapi atau memperbaiki data.
5. Status `Selesai` menandakan surat siap diunduh. Status `Ditolak` juga ditampilkan pada dashboard mahasiswa.

### Revisi

1. Mahasiswa hanya dapat membuka `edit_pengajuan.php` untuk pengajuan miliknya yang berstatus `Diproses`.
2. Mahasiswa memperbaiki data yang diminta.
3. Backend memperbarui record pengajuan yang sama (bukan membuat pengajuan baru).
4. Status dikembalikan menjadi `Menunggu` dan `updated_at` diperbarui agar dapat diperiksa ulang.

### Unduh PDF

1. Pengguna login membuka `surat_pdf.php` dengan ID pengajuan.
2. Backend mengambil pengajuan beserta data pemilik.
3. Mahasiswa hanya dapat mengakses pengajuan miliknya; staf mengikuti pemeriksaan role.
4. Unduhan ditolak jika status belum `Selesai` (HTTP 403).
5. Data anggota diambil, `surat_pdf_lib.php` membangun dokumen, dan respons dikirim dengan `Content-Type: application/pdf`.

## 6. Status Pengajuan

- `Menunggu` — menunggu pemeriksaan staf.
- `Diproses` — perlu tindak lanjut/revisi mahasiswa.
- `Selesai` — surat dapat diunduh sebagai PDF.
- `Ditolak` — pengajuan ditolak; dashboard menampilkan status ini.

## 7. Database yang Digunakan

Berdasarkan query pada kode, aplikasi menggunakan tabel berikut:

- `users` — akun pengguna dan staf, termasuk role serta program studi.
- `applications` — data utama pengajuan surat, status, catatan staf, dan waktu.
- `application_members` — daftar anggota tambahan pada pengajuan.

Operasi database menggunakan PDO dan prepared statement pada proses autentikasi, pengajuan, revisi, dan pengambilan data. Nama kolom yang digunakan kode antara lain `application_code`, `user_id`, `letter_type`, `description`, `target_institution`, `target_address`, `course_name`, `supervisor_name`, `activity_field`, `activity_start`, `activity_end`, `research_title`, `status`, `staff_note`, `created_at`, dan `updated_at`.

## 8. Validasi dan Keamanan

- Password di-hash menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Session ID diregenerasi setelah login.
- Query database menggunakan prepared statement PDO.
- Helper `e()` tersedia untuk escaping output HTML.
- Token CSRF disediakan melalui `csrf_token()` dan diperiksa melalui `verify_csrf()`.
- Akses halaman dilindungi dengan pemeriksaan login dan role.
- Mahasiswa dibatasi pada data pengajuan miliknya.
- Unduhan PDF hanya tersedia untuk status `Selesai`.

