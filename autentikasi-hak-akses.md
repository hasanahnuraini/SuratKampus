# Dokumentasi Autentikasi dan Hak Akses SuratKampus

## 1. Gambaran Umum

Autentikasi digunakan untuk memastikan pengguna masuk menggunakan akun yang terdaftar. Hak akses membatasi halaman dan tindakan berdasarkan role pengguna. Implementasi utamanya terdapat pada `config.php`, `register.php`, `login.php`, `logout.php`, serta halaman mahasiswa dan staf.

## 2. Registrasi Akun Mahasiswa

Proses pada `register.php`:
1. Pengguna mengisi nama, email, NIM, program studi, dan password.
2. Sistem memverifikasi token CSRF melalui `verify_csrf()`.
3. Sistem memeriksa apakah email atau NIM sudah terdaftar.
4. Jika belum digunakan, akun disimpan ke tabel `users`.
5. Role akun yang dibuat melalui registrasi publik ditetapkan sebagai `mahasiswa`.
6. Password disimpan menggunakan `password_hash()` dengan `PASSWORD_DEFAULT`.

## 3. Proses Login

Proses pada `login.php`:
1. Pengguna mengirim email dan password.
2. Sistem memverifikasi token CSRF.
3. Sistem mencari akun berdasarkan email menggunakan prepared statement PDO.
4. Password yang dimasukkan diverifikasi terhadap hash di database menggunakan `password_verify()`.
5. Jika cocok, sistem memanggil `session_regenerate_id(true)` dan menyimpan informasi pengguna ke session.
6. Pengguna diarahkan melalui `role_home()` sesuai role.

Jika kredensial tidak cocok, pengguna tidak dibuatkan session login dan halaman menampilkan pesan kegagalan.

## 4. Manajemen Session dan Logout

- `config.php` menjalankan `session_start()` untuk mengaktifkan session.
- `require_login()` memeriksa keberadaan data pengguna pada session. Jika belum login, pengguna diarahkan ke `login.php`.
- Setelah login berhasil, ID session diregenerasi untuk mengurangi risiko session fixation.
- `logout.php` menghapus cookie session jika cookie digunakan, menjalankan `session_destroy()`, lalu mengarahkan pengguna ke `index.php`.

## 5. Role dan Hak Akses

| Role | Hak akses berdasarkan implementasi |
|---|---|
| `mahasiswa` | Membuat pengajuan melalui `ajukan.php`, melihat pengajuan sendiri melalui `dashboard.php`, mengedit pengajuan sendiri saat status `Diproses`, dan mengunduh PDF pengajuan sendiri saat status `Selesai`. |
| `petugas` | Mengakses `admin.php` dan menangani pengajuan yang sesuai dengan program studi petugas. |
| `admin` | Mengakses pengelolaan pengajuan dan halaman `kelola_admin.php` untuk membuat serta melihat akun staf. |

## 6. Pemeriksaan Hak Akses

Fungsi yang tersedia di `config.php`:
- `require_login()`: memastikan pengguna sudah login.
- `require_role($role)`: memastikan pengguna memiliki role yang diizinkan; jika tidak, pengguna diarahkan ke halaman sesuai role.
- `is_staff_role($role)`: mengenali role staf, yaitu `admin` dan `petugas`.
- `role_home($role)`: menentukan halaman tujuan berdasarkan role.
- `current_staff_prodi($pdo)`: mengambil program studi staf dari data pengguna di database.

Penerapan pada halaman:
- `ajukan.php` dan `edit_pengajuan.php` menggunakan `require_role('mahasiswa')`.
- `admin.php` menggunakan `require_role(['admin', 'petugas'])`.
- `kelola_admin.php` menggunakan `require_role('admin')`.
- `dashboard.php` dan `surat_pdf.php` mewajibkan pengguna login.

Pada `surat_pdf.php`, mahasiswa dibatasi untuk mengakses pengajuan miliknya. Akses PDF juga ditolak dengan HTTP 403 jika status belum `Selesai`.

## 7. Perlindungan Tambahan

- Prepared statement PDO digunakan pada query yang menerima input.
- `e()` tersedia untuk melakukan escaping output HTML.
- `csrf_token()` membuat token acak dan menyimpannya pada session.
- `verify_csrf()` membandingkan token formulir dengan token session menggunakan `hash_equals()`.
- Token CSRF digunakan pada formulir yang memproses perubahan data.

## 8. Catatan

Dokumentasi ini menjelaskan mekanisme yang ditemukan pada kode. Konfigurasi keamanan server seperti HTTPS, cookie `Secure`, dan pengaturan produksi perlu diperiksa pada lingkungan deployment; tidak disimpulkan hanya dari file aplikasi.
