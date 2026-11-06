<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) { header('Location: ' . role_home($_SESSION['user']['role'] ?? '')); exit; }
$errors = [];
$nama = $email = $nim = $prodi = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nama = trim($_POST['nama'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $nim = trim($_POST['nim'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($nama === '' || $email === '' || $nim === '' || $prodi === '' || $password === '') $errors[] = 'Semua kolom wajib diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($prodi !== '' && !is_valid_prodi($prodi)) $errors[] = 'Program studi tidak valid. Pilih dari daftar yang tersedia.';
    if (strlen($nim) < 5 || strlen($nim) > 30) $errors[] = 'NIM harus terdiri dari 5–30 karakter.';
    if (strlen($password) < 8) $errors[] = 'Kata sandi minimal 8 karakter.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR nim = ?');
        $stmt->execute([$email, $nim]);
        if ($stmt->fetch()) $errors[] = 'Email atau NIM sudah terdaftar.';
        else {
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, nim, prodi, password, role) VALUES (?, ?, ?, ?, ?, 'mahasiswa')");
            $stmt->execute([$nama, $email, $nim, $prodi, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Pendaftaran berhasil. Silakan masuk menggunakan akun Anda.');
            header('Location: login.php'); exit;
        }
    }
}
$pageTitle = 'Daftar Akun Mahasiswa';
require 'partials/header.php';
?>
<section class="auth-layout">
  <div class="auth-aside"><div class="eyebrow">MULAI DARI SINI</div><h1>Urus kebutuhan administrasi kampus dengan lebih praktis.</h1><p>Daftarkan akun mahasiswa untuk mengajukan surat dan memantau proses permohonan.</p><div class="aside-stat"><span>01</span><div><strong>Buat akun</strong><small>Isi identitas sesuai data mahasiswa.</small></div></div><div class="aside-stat"><span>02</span><div><strong>Gunakan layanan</strong><small>Ajukan surat dan pantau statusnya.</small></div></div></div>
  <div class="auth-form-wrap"><div class="form-heading"><div class="eyebrow">PENDAFTARAN MAHASISWA</div><h2>Buat akun baru</h2><p>Lengkapi informasi di bawah ini untuk mendaftar.</p></div>
  <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="form-stack">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Nama lengkap<input name="nama" value="<?= e($nama) ?>" required autocomplete="name"></label>
    <label>Email aktif<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email"></label>
    <label>NIM<input name="nim" maxlength="30" value="<?= e($nim) ?>" required></label>
    <label>Program studi<select name="prodi" required><?= prodi_options_html($prodi) ?></select><small>Surat Anda akan ditangani oleh admin program studi ini.</small></label>
    <label>Kata sandi<input type="password" name="password" minlength="8" required autocomplete="new-password"><small>Minimal 8 karakter.</small></label>
    <button class="btn btn-primary btn-full" type="submit">Daftar sebagai Mahasiswa <span>→</span></button>
  </form><p class="auth-switch">Sudah punya akun? <a href="login.php">Masuk</a></p></div>
</section>
<?php require 'partials/footer.php'; ?>
