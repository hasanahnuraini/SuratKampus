<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) {
    header('Location: ' . role_home($_SESSION['user']['role'] ?? ''));
    exit;
}
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, nama, email, nim, prodi, password, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password']) && is_staff_role($user['role'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        header('Location: admin.php');
        exit;
    }
    $error = 'Email atau kata sandi tidak sesuai, atau akun ini bukan akun admin/petugas.';
}
$pageTitle = 'Masuk Admin';
require 'partials/header.php';
?>
<section class="auth-layout login-layout">
  <div class="auth-aside"><div class="eyebrow">AREA ADMIN KAMPUS</div><h1>Kelola layanan surat kampus dengan lebih mudah.</h1><p>Masuk untuk memeriksa pengajuan mahasiswa, memperbarui status, dan memberikan catatan proses surat.</p><div class="aside-quote">“Administrasi yang tertata dimulai dari proses yang aman dan transparan.”</div></div>
  <div class="auth-form-wrap login-form"><div class="form-heading"><div class="eyebrow">MASUK KHUSUS ADMIN</div><h2>Selamat datang, Admin!</h2><p>Gunakan akun admin atau petugas kampus yang telah terdaftar.</p></div>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="form-stack">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Email admin<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username"></label>
    <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn btn-primary btn-full" type="submit">Masuk sebagai Admin <span>→</span></button>
  </form>
  <p class="auth-switch">Mahasiswa? <a href="login.php">Kembali ke login mahasiswa</a></p>
  </div>
</section>
<?php require 'partials/footer.php'; ?>
