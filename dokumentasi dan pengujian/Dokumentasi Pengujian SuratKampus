# Dokumentasi Pengujian SuratKampus

## 1. Metode Pengujian

Pengujian sistem SuratKampus dilakukan menggunakan metode **Black Box Testing**. Pengujian dilakukan dengan memberikan input atau kondisi tertentu pada setiap fitur, kemudian melihat output yang dihasilkan oleh sistem tanpa melihat proses internal program.

Pengujian dilakukan untuk memastikan fitur-fitur utama pada sistem dapat berjalan sesuai dengan kebutuhan yang telah ditentukan.

## 2. Pengujian Fungsional

Pengujian fungsional dilakukan terhadap fitur utama yang terdapat pada sistem SuratKampus. Pengujian mencakup proses registrasi, login, pengajuan surat, pengelolaan pengajuan oleh admin, revisi pengajuan, penghapusan pengajuan, preview PDF, unduh PDF, serta pembatasan hak akses.

### Daftar Test Case

| No. | Fitur | Skenario Pengujian | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| TC-01 | Registrasi | Mendaftarkan akun mahasiswa dengan data valid | Akun berhasil dibuat dan pengguna dapat login | Berhasil |
| TC-02 | Registrasi | Mendaftarkan akun dengan email yang sudah digunakan | Sistem menolak pendaftaran | Berhasil |
| TC-03 | Registrasi | Mendaftarkan akun dengan NIM yang sudah digunakan | Sistem menolak pendaftaran | Berhasil |
| TC-04 | Login | Login menggunakan akun mahasiswa yang benar | Pengguna masuk ke dashboard mahasiswa | Berhasil |
| TC-05 | Login | Login menggunakan password yang salah | Sistem menolak login dan menampilkan pesan kesalahan | Berhasil |
| TC-06 | Login Admin | Login menggunakan akun admin/petugas yang valid | Pengguna diarahkan ke halaman admin/petugas | Berhasil |
| TC-07 | Pengajuan Surat | Membuka halaman pengajuan surat | Form pengajuan surat dapat ditampilkan | Berhasil |
| TC-08 | Pengajuan Surat | Mengirim pengajuan dengan data lengkap | Pengajuan berhasil disimpan dengan status Menunggu | Berhasil |
| TC-09 | Pengajuan Surat | Mengirim form tanpa memilih jenis surat | Sistem menolak pengajuan | Berhasil |
| TC-10 | Pengajuan Surat | Mengisi keterangan kurang dari 10 karakter | Sistem menolak data dan menampilkan informasi minimal 10 karakter | Berhasil |
| TC-11 | Pengajuan Surat | Mengisi tanggal selesai sebelum tanggal mulai | Sistem menolak data dan menampilkan informasi tanggal tidak valid | Berhasil |
| TC-12 | Anggota Kelompok | Menambahkan anggota kelompok dengan data lengkap | Anggota berhasil ditambahkan | Berhasil |
| TC-13 | Anggota Kelompok | Mengisi data anggota kelompok tidak lengkap | Sistem menolak data dan meminta data dilengkapi | Berhasil |
| TC-14 | Dashboard | Melihat riwayat pengajuan | Data pengajuan dan status ditampilkan | Berhasil |
| TC-15 | Admin | Melihat daftar pengajuan mahasiswa | Daftar pengajuan dapat ditampilkan | Berhasil |
| TC-16 | Admin | Mengubah status pengajuan menjadi Diproses | Status dan catatan berhasil disimpan | Berhasil |
| TC-17 | Revisi | Mahasiswa memperbaiki pengajuan setelah diminta admin | Data berhasil diperbarui | Berhasil |
| TC-18 | Admin | Mengubah status pengajuan menjadi Selesai | Status berubah menjadi Selesai dan akses surat tersedia | Berhasil |
| TC-19 | Unduh PDF | Mahasiswa mengunduh surat yang sudah selesai | Sistem menghasilkan dan mengunduh file PDF | Berhasil |
| TC-20 | Keamanan Akses | Mahasiswa mencoba membuka halaman admin | Sistem menolak akses | Berhasil |
| TC-21 | Kelola Admin | Admin menambahkan akun petugas | Akun petugas berhasil dibuat | Berhasil |
| TC-23 | Hapus Pengajuan | Mahasiswa menghapus pengajuan berstatus Menunggu | Pengajuan berhasil dihapus | Berhasil |
| TC-24 | Hapus Pengajuan | Mahasiswa mencoba menghapus pengajuan berstatus Diproses | Sistem menolak penghapusan | Berhasil |
| TC-25 | Hapus Pengajuan | Mahasiswa mencoba menghapus pengajuan berstatus Selesai | Sistem menolak penghapusan | Berhasil |
| TC-26 | Hapus Pengajuan | Mahasiswa mencoba menghapus pengajuan berstatus Ditolak | Sistem menolak penghapusan | Berhasil |
| TC-27 | Hapus Pengajuan | Menghapus pengajuan yang memiliki anggota kelompok | Pengajuan dan data anggota terkait ikut terhapus | Berhasil |
| TC-28 | Hapus Pengajuan | Mahasiswa mencoba menghapus pengajuan milik pengguna lain | Sistem menolak akses | Berhasil |
| TC-30 | Preview PDF | Admin melihat preview surat berstatus Menunggu | Surat ditampilkan dalam format PDF | Berhasil |
| TC-31 | Preview PDF | Admin melihat preview surat berstatus Diproses | Surat ditampilkan dalam format PDF | Berhasil |
| TC-32 | Preview PDF | Admin melihat preview surat yang belum Selesai | Admin tetap dapat melihat preview PDF sesuai hak akses | Berhasil |
| TC-33 | Preview PDF | Memastikan PDF dibuka menggunakan mode inline | PDF terbuka langsung di browser | Berhasil |
| TC-34 | Akses PDF | Mahasiswa mencoba mengunduh surat sebelum status Selesai | Mahasiswa tidak dapat mengunduh surat | Berhasil |
| TC-35 | Akses PDF | Mahasiswa mengunduh surat setelah status Selesai | Mahasiswa dapat mengunduh surat dalam format PDF | Berhasil |

## 3. Kriteria Hasil Pengujian

Status pengujian menggunakan kriteria berikut:

- **Berhasil:** Sistem memberikan hasil sesuai dengan hasil yang diharapkan.
- **Gagal:** Sistem memberikan hasil yang tidak sesuai dengan hasil yang diharapkan.
- **Belum diuji:** Pengujian belum dilakukan secara langsung pada sistem.

Berdasarkan dokumentasi pengujian, seluruh test case yang diuji menunjukkan status **Berhasil**.

## 4. Kesimpulan Pengujian

Berdasarkan pengujian fungsional yang dilakukan, fitur-fitur utama pada sistem SuratKampus dapat berjalan sesuai dengan skenario dan hasil yang diharapkan.

Pengujian mencakup proses registrasi dan login, pengajuan surat, pengelolaan pengajuan, revisi, penghapusan pengajuan, pengelolaan PDF, serta pembatasan hak akses pengguna.
