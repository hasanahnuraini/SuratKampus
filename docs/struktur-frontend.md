# Dokumentasi Struktur Frontend SuratKampus

## 1. Gambaran Umum
Frontend SuratKampus merupakan bagian antarmuka pengguna yang digunakan untuk mengakses layanan pengajuan surat administrasi kampus secara digital.
Frontend dirancang agar mahasiswa dapat melakukan pengajuan surat, melihat riwayat pengajuan, memantau status permohonan, serta mengunduh surat yang telah selesai diproses. Selain itu, admin dan petugas dapat mengelola pengajuan surat sesuai dengan hak akses masing-masing.
Pengembangan frontend menggunakan PHP Native, HTML, CSS, dan JavaScript.

## 2. Teknologi yang Digunakan
Teknologi yang digunakan dalam pengembangan frontend SuratKampus meliputi:
- **HTML**: Membuat struktur dan elemen halaman website.
- **CSS**: Mengatur tampilan, tata letak, warna, dan desain antarmuka.
- **JavaScript**: Menambahkan interaksi pada halaman website, seperti tombol tampil/sembunyikan kata sandi dan indikator proses pengiriman formulir.
- **PHP Native**: Mengatur tampilan halaman secara dinamis serta menghubungkan antarmuka dengan proses backend.
- **MySQL**: Menyimpan data pengguna dan pengajuan surat.

## 3. Struktur Halaman Frontend
Berikut merupakan halaman utama yang terdapat pada website SuratKampus:

### 3.1 Halaman Beranda (`index.php`)
Halaman beranda merupakan halaman awal website yang menampilkan informasi mengenai layanan SuratKampus.
Komponen halaman:
- Informasi dan pengenalan layanan SuratKampus.
- Tombol untuk melakukan registrasi.
- Tombol untuk masuk ke akun.
- Informasi fitur utama website.

### 3.2 Halaman Login (`login.php`)
Halaman login digunakan oleh mahasiswa, admin, dan petugas untuk masuk ke dalam sistem menggunakan email dan kata sandi.
Komponen halaman:
- Form email.
- Form kata sandi.
- Tombol masuk.
- Informasi kesalahan apabila login gagal.
Sistem akan mengarahkan pengguna ke halaman yang sesuai dengan perannya setelah berhasil login.

### 3.3 Halaman Registrasi (`register.php`)
Halaman registrasi digunakan oleh mahasiswa untuk membuat akun baru.
Komponen halaman:
- Form nama lengkap.
- Form NIM.
- Pilihan program studi.
- Form email.
- Form kata sandi.
- Tombol daftar akun.

### 3.4 Dashboard Mahasiswa (`dashboard.php`)
Dashboard mahasiswa merupakan halaman utama setelah mahasiswa berhasil login.
Komponen halaman:
- Informasi akun mahasiswa.
- Ringkasan pengajuan surat.
- Riwayat pengajuan surat.
- Informasi status pengajuan.
- Tombol untuk mengajukan surat baru.
- Tombol edit atau lengkapi data apabila pengajuan memerlukan perbaikan.
- Tombol unduh surat apabila status pengajuan telah selesai.

### 3.5 Halaman Pengajuan Surat (`ajukan.php`)
Halaman ini digunakan mahasiswa untuk mengisi formulir pengajuan surat secara online.
Jenis surat yang tersedia:
- Observasi Mata Kuliah / Mini Riset.
- Magang Mandiri.
- PKLI.
- Observasi Skripsi.
Formulir menampilkan kolom isian sesuai dengan jenis surat yang dipilih, seperti tujuan instansi, mata kuliah, dosen pembimbing, bidang kegiatan, tanggal kegiatan, dan judul penelitian.
Mahasiswa juga dapat memasukkan data anggota kelompok apabila pengajuan dilakukan secara berkelompok.

### 3.6 Halaman Edit Pengajuan (`edit_pengajuan.php`)
Halaman edit pengajuan digunakan mahasiswa untuk memperbaiki atau melengkapi data pengajuan yang telah diperiksa oleh admin atau petugas.
Halaman ini digunakan ketika pengajuan memiliki status Diproses dan terdapat catatan perbaikan dari pihak administrasi.
Setelah mahasiswa menyimpan perubahan, pengajuan akan kembali ke status Menunggu untuk diperiksa ulang.

### 3.7 Dashboard Admin dan Petugas (`admin.php`)
Halaman ini digunakan admin dan petugas untuk mengelola pengajuan surat mahasiswa.
Komponen halaman:
- Daftar pengajuan surat mahasiswa.
- Informasi identitas pemohon.
- Informasi jenis surat.
- Filter status pengajuan.
- Kolom catatan admin.
- Fitur perubahan status pengajuan.
- Fitur pemeriksaan data pengajuan.
Admin dan petugas hanya dapat mengelola pengajuan mahasiswa dari program studi yang sesuai dengan program studi akun mereka.

### 3.8 Halaman Login Admin (`admin_login.php`)
File `admin_login.php` merupakan halaman khusus yang tersedia dalam struktur proyek. Namun, alur login utama untuk mahasiswa, admin, dan petugas menggunakan `login.php`.

### 3.9 Halaman Kelola Admin (`kelola_admin.php`)
Halaman ini digunakan admin untuk mengelola akun yang memiliki hak akses administrasi.
Komponen halaman:
- Form penambahan akun admin atau petugas.
- Informasi nama dan email.
- Informasi program studi.
- Daftar akun admin dan petugas yang terdaftar.

### 3.10 Halaman Unduh Surat (`surat_pdf.php`)
Halaman ini berfungsi untuk menghasilkan dan mengunduh surat dalam format PDF.
Surat hanya dapat diunduh apabila pengajuan telah memiliki status Selesai.
Proses pembuatan dokumen PDF menggunakan file `surat_pdf_lib.php` sebagai pustaka pembentukan tata letak surat.

## 4. Komponen Pendukung Frontend
Selain halaman utama, terdapat beberapa file pendukung yang digunakan untuk mengatur tampilan dan interaksi website.

|        File           |                          Fungsi                       |
|-----------------------|-------------------------------------------------------|
| `assets/style.css`    | Mengatur desain dan tampilan seluruh halaman website. |
| `assets/app.js`       | Mengatur interaksi JavaScript pada halaman website.   |
| `assets/logo.png`     | Menampilkan logo SuratKampus.                         |
| `partials/header.php` | Menyediakan bagian header, logo, dan navigasi website.|
| `partials/footer.php` | Menyediakan bagian footer dan pemanggilan JavaScript. |

## 5. Alur Antarmuka Pengguna
Secara umum, alur penggunaan frontend SuratKampus adalah sebagai berikut:
1. Pengguna membuka halaman beranda website.
2. Mahasiswa melakukan registrasi atau login.
3. Setelah login, mahasiswa diarahkan ke dashboard mahasiswa.
4. Mahasiswa mengisi formulir pengajuan surat.
5. Mahasiswa dapat melihat riwayat dan status pengajuan melalui dashboard.
6. Admin atau petugas login dan memeriksa pengajuan mahasiswa melalui dashboard administrasi.
7. Admin atau petugas dapat memperbarui status pengajuan serta memberikan catatan perbaikan.
8. Mahasiswa melengkapi data apabila terdapat permintaan perbaikan.
9. Setelah pengajuan dinyatakan selesai, mahasiswa dapat mengunduh surat dalam format PDF.

## 6. Hak Akses Pengguna
Frontend SuratKampus memiliki tiga jenis pengguna dengan hak akses yang berbeda.

|  Pengguna   |                                                              Hak Akses                                                                  |
|-------------|-----------------------------------------------------------------------------------------------------------------------------------------|
| Mahasiswa   | Registrasi, login, mengajukan surat, melihat riwayat, memperbaiki pengajuan, dan mengunduh surat.                                       |
| Admin       | Login, mengelola pengajuan mahasiswa sesuai program studi, mengubah status, memberikan catatan, serta mengelola akun admin dan petugas. |
| Petugas     | Login, melihat dan mengelola pengajuan mahasiswa sesuai program studi, mengubah status, serta memberikan catatan.                       |

## 7. Kesimpulan
Frontend SuratKampus dikembangkan menggunakan PHP Native dengan dukungan HTML, CSS, dan JavaScript. Struktur frontend terdiri dari halaman beranda, autentikasi pengguna, dashboard mahasiswa, formulir pengajuan surat, dashboard administrasi, serta halaman pengunduhan surat PDF.
Setiap halaman memiliki fungsi yang saling terhubung untuk mendukung proses pelayanan administrasi surat kampus secara digital, mulai dari pengajuan oleh mahasiswa hingga penyelesaian oleh admin atau petugas.
