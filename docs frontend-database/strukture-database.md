# Dokumentasi Struktur Database SuratKampus

## 1. Gambaran Umum
Database SuratKampus merupakan tempat penyimpanan data yang digunakan untuk mendukung sistem layanan pengajuan surat administrasi kampus secara digital.
Database ini digunakan untuk menyimpan informasi pengguna, data pengajuan surat, serta data anggota kelompok yang terlibat dalam pengajuan surat.
Sistem database menggunakan MySQL dengan storage engine InnoDB untuk mendukung relasi antartabel melalui Primary Key dan Foreign Key.

## 2. Teknologi Database
Teknologi yang digunakan dalam pengelolaan database SuratKampus adalah:
- **MySQL**: Sistem manajemen basis data untuk menyimpan dan mengelola data.
- **phpMyAdmin**: Antarmuka untuk mengelola database melalui browser.
- **PHP Native**: Menghubungkan aplikasi dengan database.
- **InnoDB**: Storage engine yang mendukung relasi dan integritas data.

## 3. Identitas Database

| Keterangan | Informasi |
|---|---|
| Nama database | `kampus_surat` |
| DBMS | MySQL |
| Storage Engine | InnoDB |
| Character Set | utf8mb4 |
| Collation | utf8mb4_unicode_ci |

## 4. Struktur Tabel Database
Database SuratKampus memiliki tiga tabel utama, yaitu `users`, `applications`, dan `application_members`.

### 4.1 Tabel Users
Tabel `users` digunakan untuk menyimpan data akun pengguna yang dapat mengakses website SuratKampus, yaitu mahasiswa, admin, dan petugas.
| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| id | INT UNSIGNED | Primary Key, identitas pengguna, Auto Increment |
| nama | VARCHAR(120) | Nama lengkap pengguna |
| email | VARCHAR(190) | Email pengguna, bersifat UNIQUE |
| nim | VARCHAR(30) | Nomor Induk Mahasiswa, bersifat UNIQUE |
| prodi | VARCHAR(190) | Program studi pengguna |
| password | VARCHAR(255) | Kata sandi yang tersimpan dalam bentuk hash |
| role | ENUM | Hak akses: mahasiswa, admin, petugas |
| created_at | TIMESTAMP | Waktu pembuatan akun |

**Fungsi tabel:**
- Menyimpan informasi akun pengguna.
- Mengelola identitas mahasiswa, admin, dan petugas.
- Menentukan hak akses pengguna berdasarkan role.
- Menjadi sumber data pengguna yang melakukan pengajuan surat.

**Primary Key:** `id`

### 4.2 Tabel Applications
Tabel `applications` digunakan untuk menyimpan seluruh data pengajuan surat yang dilakukan oleh mahasiswa.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| id | INT UNSIGNED | Primary Key, Auto Increment |
| application_code | VARCHAR(32) | Kode unik pengajuan |
| user_id | INT UNSIGNED | Foreign Key yang mengacu ke users.id |
| letter_type | VARCHAR(160) | Jenis surat yang diajukan |
| description | TEXT | Deskripsi pengajuan |
| target_institution | VARCHAR(190) | Nama instansi tujuan |
| target_address | VARCHAR(300) | Alamat instansi tujuan |
| course_name | VARCHAR(190) | Nama mata kuliah |
| supervisor_name | VARCHAR(190) | Nama dosen pembimbing |
| activity_field | VARCHAR(190) | Bidang kegiatan |
| activity_start | DATE | Tanggal mulai kegiatan |
| activity_end | DATE | Tanggal selesai kegiatan |
| research_title | VARCHAR(500) | Judul penelitian |
| status | ENUM | Status pengajuan surat |
| staff_note | VARCHAR(500) | Catatan dari admin atau petugas |
| created_at | TIMESTAMP | Waktu pengajuan dibuat |
| updated_at | TIMESTAMP | Waktu terakhir data diperbarui |

Status pengajuan memiliki empat pilihan:
- `Menunggu`: Pengajuan baru dan menunggu pemeriksaan.
- `Diproses`: Pengajuan sedang diperiksa atau memerlukan perbaikan.
- `Selesai`: Pengajuan telah diselesaikan.
- `Ditolak`: Pengajuan tidak disetujui.

**Fungsi tabel:**
- Menyimpan data pengajuan surat mahasiswa.
- Menyimpan informasi tujuan dan keperluan surat.
- Menyimpan status proses pengajuan.
- Menyimpan catatan perbaikan dari admin atau petugas.
- Menjadi sumber data untuk proses pembuatan surat PDF.

**Primary Key:** `id`
**Foreign Key:** `user_id` mengacu pada `users.id`.

### 4.3 Tabel Application_Members
Tabel `application_members` digunakan untuk menyimpan data anggota kelompok yang mengikuti pengajuan surat secara berkelompok.
Tabel ini dibuat melalui file migrasi `upgrade_group_members.sql` atau `upgrade_semua_aman.sql`.

| Nama Kolom | Tipe Data | Keterangan |
|---|---|---|
| id | INT UNSIGNED | Primary Key, Auto Increment |
| application_id | INT UNSIGNED | Foreign Key yang mengacu ke applications.id |
| nama | VARCHAR(120) | Nama anggota kelompok |
| nim | VARCHAR(30) | Nomor Induk Mahasiswa anggota |
| prodi | VARCHAR(190) | Program studi anggota |
| created_at | TIMESTAMP | Waktu data anggota ditambahkan |

**Fungsi tabel:**
- Menyimpan identitas anggota kelompok.
- Menghubungkan anggota dengan pengajuan surat tertentu.
- Mendukung pengajuan surat yang melibatkan beberapa mahasiswa.
- Menyimpan nama, NIM, dan program studi setiap anggota.

**Primary Key:** `id`
**Foreign Key:** `application_id` mengacu pada `applications.id`.

## 5. Relasi Antar Tabel
Database SuratKampus memiliki hubungan antartabel sebagai berikut:

### 5.1 Relasi Users dengan Applications
Hubungan antara tabel `users` dan `applications` adalah **One-to-Many (1:N)**.
Artinya, satu pengguna dapat membuat beberapa pengajuan surat, sedangkan satu pengajuan surat hanya dimiliki oleh satu pengguna.

Hubungan tersebut menggunakan:
- `users.id` sebagai Primary Key.
- `applications.user_id` sebagai Foreign Key.

### 5.2 Relasi Applications dengan Application_Members
Hubungan antara tabel `applications` dan `application_members` adalah **One-to-Many (1:N)**.
Artinya, satu pengajuan surat dapat memiliki beberapa anggota kelompok, sedangkan satu data anggota hanya terhubung dengan satu pengajuan surat.

Hubungan tersebut menggunakan:
- `applications.id` sebagai Primary Key.
- `application_members.application_id` sebagai Foreign Key.

### 5.3 Diagram Relasi Antar Tabel

```text
+------------------+
|      users       |
+------------------+
| PK id            |
| nama             |
| email            |
| nim              |
| prodi            |
| password         |
| role             |
| created_at       |
+------------------+
         |
         | 1
         |
         | N
+----------------------+
|     applications     |
+----------------------+
| PK id                |
| application_code    |
| FK user_id           |
| letter_type          |
| description          |
| target_institution   |
| target_address       |
| course_name          |
| supervisor_name      |
| activity_field       |
| activity_start       |
| activity_end         |
| research_title       |
| status               |
| staff_note           |
| created_at           |
| updated_at           |
+----------------------+
         |
         | 1
         |
         | N
+--------------------------+
|    application_members   |
+--------------------------+
| PK id                    |
| FK application_id        |
| nama                     |
| nim                      |
| prodi                    |
| created_at               |
+--------------------------+
```

## 6. Integritas dan Keamanan Data
Database SuratKampus menerapkan beberapa aturan untuk menjaga konsistensi data, yaitu:
1. **Primary Key** digunakan sebagai identitas unik setiap data pada masing-masing tabel.
2. **Foreign Key** digunakan untuk menghubungkan tabel pengguna dengan pengajuan surat serta pengajuan surat dengan anggota kelompok.
3. **UNIQUE** diterapkan pada kolom email dan NIM dalam tabel users agar tidak terdapat akun dengan email atau NIM yang sama.
4. **ENUM** digunakan untuk membatasi pilihan role pengguna dan status pengajuan.
5. **ON DELETE CASCADE** diterapkan pada relasi pengajuan dengan pengguna serta relasi anggota kelompok dengan pengajuan. Jika data induk dihapus, data yang terhubung juga akan dihapus.
6. **Timestamp** digunakan untuk mencatat waktu pembuatan dan perubahan data.

## 7. File SQL dan Migrasi Database
Pengelolaan struktur database dilakukan melalui beberapa file SQL yang tersedia di repository.

| Nama File | Fungsi |
|---|---|
| `database.sql` | Membuat database kampus_surat serta tabel users dan applications. |
| `upgrade_role.sql` | Memperbarui pilihan role pada tabel users. |
| `upgrade_form_fields.sql` | Menambahkan kolom kebutuhan formulir pengajuan surat. |
| `upgrade_target_address.sql` | Menambahkan kolom alamat instansi tujuan. |
| `upgrade_group_members.sql` | Membuat tabel application_members untuk menyimpan anggota kelompok. |
| `upgrade_semua_aman.sql` | Menyelaraskan struktur database lama dengan struktur terbaru. |
| `upgrade_normalisasi_prodi.sql` | Membantu menyeragamkan penulisan nama program studi. |

**Catatan:** Tidak semua kolom dan tabel tambahan langsung tersedia ketika hanya menjalankan `database.sql`. Beberapa struktur diperoleh melalui file migrasi. Untuk mendapatkan struktur database terbaru, file migrasi yang diperlukan harus dijalankan sesuai kondisi database yang digunakan.

## 8. Kesimpulan
Database SuratKampus menggunakan MySQL dengan tiga tabel utama, yaitu `users`, `applications`, dan `application_members`.
Ketiga tabel tersebut saling berhubungan untuk mendukung proses pengajuan surat secara digital. Tabel users menyimpan data pengguna, tabel applications menyimpan informasi pengajuan surat, sedangkan tabel application_members menyimpan data anggota kelompok.
Struktur database ini dirancang untuk mendukung pengelolaan akun, pengajuan surat, pemeriksaan administrasi, pengelolaan status, serta penyimpanan data anggota kelompok secara terstruktur.
