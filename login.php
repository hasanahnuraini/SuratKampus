<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) { header('Location: ' . role_home($_SESSION['user']['role'] ?? '')); exit; }
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, nama, email, nim, prodi, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        header('Location: ' . role_home($user['role'])); exit;
    }
    $error = 'Email atau kata sandi tidak sesuai.';
}
$pageTitle = 'Masuk';
require 'partials/header.php';
?>
<section class="auth-layout login-layout">
  <div class="auth-aside"><div class="eyebrow">SELAMAT DATANG KEMBALI</div><h1>Satu langkah untuk memantau layanan surat kampus Anda.</h1><p>Masuk untuk melihat riwayat pengajuan atau mengelola permohonan surat.</p><div class="aside-quote">“Pelayanan yang baik dimulai dari proses yang jelas dan mudah dipantau.”</div></div>
  <div class="auth-form-wrap login-form"><div class="form-heading"><div class="eyebrow">MASUK KE AKUN</div><h2>Halo, selamat datang!</h2><p>Masukkan email dan kata sandi akun Anda.</p></div>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form-stack">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Email<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username"></label>
    <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn btn-primary btn-full" type="submit">Masuk <span>→</span></button>
  </form><p class="auth-switch">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
  </div>
</section>
<?php require 'partials/footer.php'; ?>
