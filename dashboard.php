<?php
require_once 'config.php';
require_login();
$user = $_SESSION['user'];
if (is_staff_role($user['role'] ?? '')) { header('Location: admin.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$applications = $stmt->fetchAll();
$total = count($applications);
$pending = count(array_filter($applications, fn($a) => in_array($a['status'], ['Menunggu', 'Diproses'], true)));
$done = count(array_filter($applications, fn($a) => $a['status'] === 'Selesai'));
$pageTitle = 'Dashboard Mahasiswa';
require 'partials/header.php';
?>
<section class="page-heading">
  <div><div class="eyebrow">DASHBOARD MAHASISWA</div><h1>Halo, <?= e($user['nama']) ?> 👋</h1><p>Berikut ringkasan pengajuan surat akademik Anda.</p></div>
  <a class="btn btn-primary" href="ajukan.php"><span>＋</span> Ajukan surat baru</a>
</section>
<div class="stats-grid">
  <article class="stat-card"><div class="stat-icon stat-blue">▤</div><span>Total pengajuan</span><strong><?= $total ?></strong><small>Semua permohonan Anda</small></article>
  <article class="stat-card"><div class="stat-icon stat-orange">◷</div><span>Dalam proses</span><strong><?= $pending ?></strong><small>Menunggu atau diproses</small></article>
  <article class="stat-card"><div class="stat-icon stat-green">✓</div><span>Selesai</span><strong><?= $done ?></strong><small>Pengajuan telah selesai</small></article>
</div>
<section class="panel">
  <div class="panel-heading"><div><h2>Riwayat pengajuan</h2><p>Pantau perkembangan permohonan surat Anda.</p></div><span class="muted-count"><?= $total ?> pengajuan</span></div>
  <?php if (!$applications): ?>
    <div class="empty-state"><div class="empty-icon">▤</div><h3>Belum ada pengajuan</h3><p>Permohonan surat yang Anda buat akan muncul di sini.</p><a class="btn btn-primary" href="ajukan.php">Ajukan surat pertama</a></div>
  <?php else: ?>
  <div class="table-wrap"><table><thead><tr><th>No. Pengajuan</th><th>Jenis surat</th><th>Tanggal</th><th>Status</th><th>Catatan admin</th><th>Tindakan / Dokumen</th></tr></thead><tbody>
  <?php foreach ($applications as $a): ?><tr><td><strong><?= e($a['application_code']) ?></strong></td><td><?= e($a['letter_type']) ?></td><td><?= e(date('d/m/Y', strtotime($a['created_at']))) ?></td><td><span class="status-pill <?= $a['status'] === 'Selesai' ? 'status-selesai' : ($a['status'] === 'Ditolak' ? 'status-ditolak' : ($a['status'] === 'Diproses' ? 'status-proses' : 'status-menunggu')) ?>"><?= e($a['status']) ?></span></td><td><?= e($a['staff_note'] ?: 'Belum ada catatan') ?></td><td><?php if ($a['status'] === 'Diproses'): ?><a class="btn btn-primary btn-small" href="edit_pengajuan.php?id=<?= (int)$a['id'] ?>">Edit / Lengkapi Data</a><div class="table-sub">Perbaiki data sesuai catatan admin</div><?php elseif ($a['status'] === 'Selesai'): ?><a class="btn btn-primary btn-small" href="surat_pdf.php?id=<?= (int)$a['id'] ?>">Unduh PDF</a><?php elseif ($a['status'] === 'Menunggu'): ?><span class="table-sub">Menunggu pemeriksaan admin</span><?php else: ?><span class="table-sub">Hubungi admin jika perlu perubahan</span><?php endif; ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
<?php require 'partials/footer.php'; ?>
