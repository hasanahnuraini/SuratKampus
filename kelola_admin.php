<?php
require_once 'config.php';
require_role('admin'); // Hanya admin utama/admin berwenang yang boleh mengelola akun staf.
$errors = [];
$nama = $email = $nim = $prodi = '';
$role = 'admin';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nama = trim($_POST['nama'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    if (!in_array($role, ['admin', 'petugas'], true)) $role = 'admin';
    if ($nama === '' || $email === '' || $nim === '' || $prodi === '' || $password === '') $errors[] = 'Semua kolom wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($prodi !== '' && !is_valid_prodi($prodi)) $errors[] = 'Program studi tidak valid. Pilih dari daftar yang tersedia.';
    if (strlen($nim) < 5 || strlen($nim) > 30) $errors[] = 'ID admin/petugas harus terdiri dari 5–30 karakter.';
    if (strlen($password) < 8) $errors[] = 'Kata sandi minimal 8 karakter.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR nim = ?');
        $stmt->execute([$email, $nim]);
        if ($stmt->fetch()) $errors[] = 'Email atau ID sudah digunakan akun lain.';
        else {
            $stmt = $pdo->prepare('INSERT INTO users (nama, email, nim, prodi, password, role) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nama, $email, $nim, $prodi, password_hash($password, PASSWORD_DEFAULT), $role]);
            flash($role === 'admin' ? 'Akun admin baru berhasil ditambahkan.' : 'Akun petugas berhasil ditambahkan.');
            header('Location: kelola_admin.php'); exit;
        }
    }
}
$stmt = $pdo->query("SELECT id, nama, email, nim, prodi, role, created_at FROM users WHERE role IN ('admin','petugas') ORDER BY role, nama");
$staff = $stmt->fetchAll();
$pageTitle = 'Kelola Admin dan Petugas';
require 'partials/header.php';
?>
<section class="page-heading"><div><div class="eyebrow">PENGELOLAAN AKUN STAF</div><h1>Kelola Admin dan Petugas</h1><p>Halaman ini hanya dapat dibuka oleh akun ber-role admin.</p></div><a class="btn btn-outline" href="admin.php">← Kembali ke pengajuan</a></section>
<div class="form-page-grid">
  <section class="panel form-panel"><div class="panel-heading"><div><h2>Tambah akun staf</h2><p>Buat akun admin atau petugas kampus baru.</p></div></div>
    <?php if ($errors): ?><div class="alert alert-error" style="margin:18px 24px 0"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="form-stack" style="padding:22px 24px 24px">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>Jenis akun<select name="role"><option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option><option value="petugas" <?= $role === 'petugas' ? 'selected' : '' ?>>Petugas kampus</option></select></label>
      <label>Nama lengkap<input name="nama" value="<?= e($nama) ?>" required></label>
      <label>Email aktif<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email"></label>
      <label>ID admin/petugas<input name="nim" maxlength="30" value="<?= e($nim) ?>" required><small>Kolom ini menggunakan kolom NIM/ID yang tersedia di database.</small></label>
      <label>Program studi yang ditangani<select name="prodi" required><?= prodi_options_html($prodi) ?></select><small>Admin ini hanya akan melihat surat mahasiswa dari program studi yang dipilih.</small></label>
      <label>Kata sandi sementara<input type="password" name="password" minlength="8" required autocomplete="new-password"><small>Minimal 8 karakter. Berikan kredensial hanya kepada pemilik akun.</small></label>
      <button class="btn btn-primary btn-full" type="submit">+ Buat akun staf</button>
    </form>
  </section>
  <aside class="help-card"><div class="help-icon">!</div><h3>Informasi keamanan</h3><ul><li>Hanya akun ber-role <strong>admin</strong> yang dapat membuka halaman ini.</li><li>Pendaftaran publik tetap hanya membuat akun mahasiswa.</li><li>Jangan membagikan kata sandi admin melalui kanal umum.</li><li>Gunakan akun petugas untuk staf yang hanya perlu menangani pengajuan.</li></ul></aside>
</div>
<section class="panel" style="margin-top:22px"><div class="panel-heading"><div><h2>Akun admin dan petugas terdaftar</h2><p>Daftar akun yang memiliki akses administrasi.</p></div><span class="muted-count"><?= count($staff) ?> akun</span></div>
<?php if (!$staff): ?><div class="empty-state"><h3>Belum ada akun staf</h3><p>Tambahkan akun admin atau petugas menggunakan formulir di atas.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>ID</th><th>Program studi</th><th>Role</th><th>Dibuat</th></tr></thead><tbody><?php foreach ($staff as $s): ?><tr><td><strong><?= e($s['nama']) ?></strong></td><td><?= e($s['email']) ?></td><td><?= e($s['nim']) ?></td><td><?= e($s['prodi']) ?></td><td><span class="status-pill <?= $s['role'] === 'admin' ? 'status-proses' : 'status-selesai' ?>"><?= e(ucfirst($s['role'])) ?></span></td><td><?= e(date('d/m/Y', strtotime($s['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require 'partials/footer.php'; ?>
