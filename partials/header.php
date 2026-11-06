<?php
$pageTitle = $pageTitle ?? 'Layanan Surat Digital Kampus';
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | SuratKampus</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= $user ? role_home($user['role'] ?? '') : 'index.php' ?>">
    <img src="assets/logo.png"
     alt="Logo SuratKampus"
     class="brand-logo">
    <span class="brand-text">
      <strong>SuratKampus</strong>
      <small>Sistem Layanan Persuratan Digital</small>
    </span>
  </a>

  <nav>
    <?php if ($user): ?>
      <a href="<?= e(role_home($user['role'] ?? '')) ?>">Dashboard</a>
      <?php if (($user['role'] ?? '') === 'mahasiswa'): ?><a href="ajukan.php">Ajukan Surat</a><?php endif; ?>
      <?php if (is_staff_role($user['role'] ?? '')): ?><a href="admin.php">Kelola Pengajuan</a><?php endif; ?>
      <?php if (($user['role'] ?? '') === 'admin'): ?><a href="kelola_admin.php">Kelola Admin</a><?php endif; ?>
      <span class="nav-user"><?= e($user['nama']) ?></span>
      <a class="nav-logout" href="logout.php">Keluar</a>
    <?php else: ?>
      <a href="login.php">Masuk</a>
      <a class="nav-cta" href="register.php">Daftar</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
<?php show_flash(); ?>
