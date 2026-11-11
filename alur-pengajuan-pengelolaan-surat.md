# Dokumentasi Proses Pengajuan dan Pengelolaan Surat SuratKampus

## 1. Gambaran Umum

Proses pengajuan surat melibatkan mahasiswa sebagai pemohon dan admin/petugas sebagai pemeriksa. File utama yang terlibat adalah `ajukan.php`, `dashboard.php`, `admin.php`, `edit_pengajuan.php`, `surat_pdf.php`, dan `surat_pdf_lib.php`.

## 2. Pengajuan Surat oleh Mahasiswa

1. Mahasiswa login ke aplikasi.
2. Mahasiswa membuka halaman `ajukan.php`. Halaman ini dibatasi untuk role `mahasiswa`.
3. Mahasiswa memilih jenis surat dan mengisi data yang diminta, seperti instansi tujuan, alamat, dosen pembimbing, mata kuliah atau judul penelitian, bidang kegiatan, periode kegiatan, dan deskripsi sesuai jenis surat.
4. Backend memverifikasi token CSRF dan memproses data formulir.
5. Data utama disimpan ke tabel `applications` dengan status awal `Menunggu`.
6. Jika mahasiswa mengisi anggota tambahan, data anggota disimpan ke tabel `application_members`.
7. Setelah berhasil, mahasiswa diarahkan ke `dashboard.php`.

## 3. Pemantauan Pengajuan

Pada `dashboard.php`, sistem mengambil pengajuan berdasarkan `user_id` dari session. Dashboard menampilkan kode pengajuan, jenis surat, tanggal pengajuan, status, dan catatan staf.

Tindakan yang ditampilkan mengikuti status:
- `Menunggu`: pengajuan sedang menunggu pemeriksaan.
- `Diproses`: mahasiswa mendapatkan akses untuk membuka halaman revisi.
- `Selesai`: mahasiswa mendapatkan tautan unduh PDF.
- `Ditolak`: status penolakan ditampilkan dan mahasiswa diarahkan untuk menghubungi admin jika membutuhkan perubahan.

## 4. Pemeriksaan dan Pengelolaan oleh Admin/Petugas

1. Admin atau petugas login dan membuka `admin.php`.
2. Sistem menampilkan daftar pengajuan beserta informasi mahasiswa.
3. Daftar dapat difilter berdasarkan status.
4. Pengajuan petugas dibatasi berdasarkan program studi petugas; admin dapat mengelola pengajuan sesuai cakupan yang diterapkan aplikasi.
5. Staf memilih status baru dan dapat menambahkan catatan untuk mahasiswa.
6. Backend memverifikasi CSRF, memvalidasi status terhadap daftar status yang diperbolehkan, lalu memperbarui status dan catatan pada data pengajuan.

Status yang digunakan pada proses pengelolaan adalah `Menunggu`, `Diproses`, `Selesai`, dan `Ditolak`.

## 5. Proses Revisi

1. Jika staf menetapkan status `Diproses`, mahasiswa dapat memilih tindakan edit dari dashboard.
2. `edit_pengajuan.php` mengambil data berdasarkan ID pengajuan dan `user_id` mahasiswa yang sedang login.
3. Sistem memastikan status pengajuan benar-benar `Diproses`.
4. Mahasiswa memperbaiki informasi yang diminta.
5. Backend memperbarui record pengajuan yang sama, bukan membuat pengajuan baru.
6. Setelah pembaruan berhasil, status diubah kembali menjadi `Menunggu` dan `updated_at` diperbarui.
7. Pengajuan kembali tersedia untuk diperiksa staf.

## 6. Pembuatan dan Unduhan Surat PDF

1. Pengguna yang sudah login membuka `surat_pdf.php` dengan parameter ID pengajuan.
2. Backend mengambil data pengajuan dan data pemiliknya dari database.
3. Sistem memeriksa bahwa mahasiswa hanya mengakses pengajuan miliknya; akses staf mengikuti pemeriksaan role.
4. Jika status pengajuan bukan `Selesai`, server mengembalikan HTTP 403 dan proses unduhan dihentikan.
5. Jika status sudah `Selesai`, sistem mengambil data anggota tambahan dari `application_members`.
6. `surat_pdf_lib.php` membentuk isi dan tata letak dokumen PDF.
7. `surat_pdf.php` mengirimkan hasil sebagai unduhan dengan tipe konten `application/pdf`.

## 7. Ringkasan Status Pengajuan

| Status | Arti dalam alur |
|---|---|
| `Menunggu` | Pengajuan menunggu pemeriksaan atau menunggu pemeriksaan ulang setelah revisi. |
| `Diproses` | Pengajuan sedang ditangani staf; mahasiswa dapat diminta memperbaiki data. |
| `Selesai` | Pengajuan selesai dan PDF dapat diunduh. |
| `Ditolak` | Pengajuan ditolak; status ditampilkan pada dashboard mahasiswa. |

## 8. Tabel Database yang Terlibat

- `users`: menyimpan akun mahasiswa dan staf.
- `applications`: menyimpan data pengajuan surat, status, catatan staf, serta waktu pengajuan/perubahan.
- `application_members`: menyimpan data anggota tambahan pada pengajuan.

## 9. Catatan

Uraian ini disusun dari alur yang diimplementasikan pada file PHP. Dokumen ini tidak menyatakan bahwa surat telah dikirim ke sistem eksternal atau bahwa terdapat notifikasi otomatis, karena hal tersebut tidak ditunjukkan oleh alur kode yang diperiksa.
