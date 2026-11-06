<?php
require_once 'config.php';
if (!empty($_SESSION['user'])) {
    header('Location: ' . role_home($_SESSION['user']['role'] ?? ''));
    exit;
}
$pageTitle = 'Beranda';
require 'partials/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <div class="eyebrow"><span class="eyebrow-dot"></span> LAYANAN ADMINISTRASI DIGITAL KAMPUS</div>
    <h1>Urus surat kampus,<br><span>lebih mudah.</span></h1>
    <p>Ajukan surat secara online, pantau status permohonan, dan dapatkan informasi proses pengajuan tanpa harus berulang kali datang ke bagian administrasi kampus.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="register.php">Mulai Ajukan Surat <span>→</span></a>
      <a class="btn btn-outline" href="login.php">Masuk akun</a>
    </div>
    <div class="hero-note"><span class="check-icon">✓</span> Praktis · Terpantau · Terorganisasi</div>
  </div>
  <div class="hero-visual">
    <div class="visual-orb orb-one"></div><div class="visual-orb orb-two"></div>
    <div class="document-card">
      <div class="doc-top"><div class="doc-icon">▤</div><span class="status-pill status-proses">Dalam proses</span></div>
      <div class="doc-label">CONTOH STATUS PENGAJUAN</div>
      <h3>Surat Izin Observasi Mata Kuliah / Mini Riset</h3>
      <p class="doc-number">No. Pengajuan: KS-2026-001</p>
      <div class="progress-line"><span></span></div>
      <div class="progress-labels"><span>Diajukan</span><span>Diproses</span><span>Selesai</span></div>
      <div class="doc-divider"></div>
      <div class="doc-meta"><span>Estimasi proses</span><strong>Menunggu verifikasi</strong></div>
    </div>
    <div class="floating-note"><span class="floating-check">✓</span><div><strong>Status transparan</strong><small>Pantau pengajuan kapan saja</small></div></div>
    <div class="hero-shape"></div>
  </div>
</section>
<section class="section">
  <div class="section-heading"><div><div class="eyebrow">FITUR UTAMA</div><h2>Semua proses dalam satu tempat</h2></div><p>Dirancang untuk membantu mahasiswa dan admin kampus mengelola permohonan surat dengan lebih tertib.</p></div>
  <div class="feature-grid">
    <article class="feature-card"><div class="feature-icon icon-blue">▤</div><h3>Ajukan surat online</h3><p>Isi formulir permohonan surat dari perangkat Anda dengan mudah.</p></article>
    <article class="feature-card"><div class="feature-icon icon-green">◷</div><h3>Pantau status</h3><p>Lihat apakah pengajuan masih menunggu, sedang diproses, atau sudah selesai.</p></article>
    <article class="feature-card"><div class="feature-icon icon-orange">✓</div><h3>Pengelolaan admin kampus</h3><p>Admin Kampus dapat memeriksa permohonan dan memperbarui statusnya.</p></article>
  </div>
</section>
<section class="cta-strip"><div><h2>Siap mengajukan surat?</h2><p>Buat akun mahasiswa untuk memulai permohonan secara digital.</p></div><a class="btn btn-light" href="register.php">Daftar akun <span>→</span></a></section>
<?php require 'partials/footer.php'; ?>
