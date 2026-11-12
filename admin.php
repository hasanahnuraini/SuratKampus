
<?php
require_once 'config.php';
require_role(['admin', 'petugas']);

$validStatuses = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];

// Ambil prodi admin yang sedang login (dibaca langsung dari database).
$adminProdi = current_staff_prodi($pdo);

// Admin wajib memiliki prodi.
if ($adminProdi === '') {
    http_response_code(403);
    exit('Akun admin belum memiliki prodi. Hubungi admin utama.');
}

// Proses pembaruan status pengajuan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    $note = trim($_POST['staff_note'] ?? '');

    if (
        !$id ||
        !in_array($status, $validStatuses, true) ||
        strlen($note) > 500
    ) {
        flash('Data pembaruan tidak valid.', 'error');
    } else {
        // Pastikan pengajuan memang milik mahasiswa dari prodi admin.
        $check = $pdo->prepare(
            'SELECT a.id
             FROM applications a
             JOIN users u ON a.user_id = u.id
             WHERE a.id = ? AND u.prodi = ?'
        );
        $check->execute([$id, $adminProdi]);

        if (!$check->fetch()) {
            http_response_code(403);
            exit('Akses ditolak. Pengajuan tersebut bukan milik prodi Anda.');
        }

        // Perbarui hanya pengajuan dari prodi admin yang login.
        $stmt = $pdo->prepare(
            'UPDATE applications a
             JOIN users u ON a.user_id = u.id
             SET a.status = ?,
                 a.staff_note = ?,
                 a.updated_at = CURRENT_TIMESTAMP
             WHERE a.id = ? AND u.prodi = ?'
        );

        $stmt->execute([$status, $note, $id, $adminProdi]);

        flash('Status pengajuan berhasil diperbarui.');
    }

    header('Location: admin.php');
    exit;
}

// Filter status dari halaman dashboard.
$filter = $_GET['status'] ?? '';

// Ambil pengajuan hanya dari prodi admin yang login.
$sql = "
    SELECT a.*, u.nama AS nama_mahasiswa, u.nim, u.prodi
    FROM applications a
    JOIN users u ON a.user_id = u.id
    WHERE u.prodi = ?
";

$params = [$adminProdi];

if (in_array($filter, $validStatuses, true)) {
    $sql .= ' AND a.status = ?';
    $params[] = $filter;
}

$sql .= ' ORDER BY a.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Hitung statistik hanya untuk prodi admin yang login.
$counts = [];

foreach ($validStatuses as $s) {
    $stmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM applications a
         JOIN users u ON a.user_id = u.id
         WHERE a.status = ? AND u.prodi = ?'
    );

    $stmt->execute([$s, $adminProdi]);
    $counts[$s] = (int) $stmt->fetchColumn();
}

$pageTitle = 'Kelola Pengajuan';
require 'partials/header.php';
?>
<section class="page-heading"><div><div class="eyebrow">PANEL ADMIN KAMPUS</div><h1>Kelola pengajuan surat</h1><p>
    Periksa permohonan mahasiswa dan perbarui status prosesnya.
    Prodi: <strong><?= e($_SESSION['user']['prodi']) ?></strong>
</p></div><?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?><a class="btn btn-primary" href="kelola_admin.php">+ Tambah Admin / Petugas</a><?php endif; ?></section>
<div class="stats-grid admin-stats">
  <article class="stat-card"><div class="stat-icon stat-orange">◷</div><span>Menunggu</span><strong><?= $counts['Menunggu'] ?></strong><small>Perlu diperiksa</small></article>
  <article class="stat-card"><div class="stat-icon stat-blue">↻</div><span>Diproses</span><strong><?= $counts['Diproses'] ?></strong><small>Sedang ditangani</small></article>
  <article class="stat-card"><div class="stat-icon stat-green">✓</div><span>Selesai</span><strong><?= $counts['Selesai'] ?></strong><small>Telah diselesaikan</small></article>
</div>
<section class="panel"><div class="panel-heading"><div><h2>Daftar permohonan mahasiswa</h2><p>Gunakan filter untuk memusatkan perhatian pada status tertentu.</p></div><form method="get" class="filter-form"><select name="status" aria-label="Filter status"><option value="">Semua status</option><?php foreach ($validStatuses as $s): ?><option value="<?= e($s) ?>" <?= $filter === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select><button class="btn btn-outline btn-small" type="submit">Filter</button></form></div>
<?php if (!$applications): ?><div class="empty-state"><div class="empty-icon">✓</div><h3>Belum ada permohonan</h3><p>Pengajuan mahasiswa akan ditampilkan di sini.</p></div><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Pengajuan / Mahasiswa</th><th>Detail permohonan</th><th>Status saat ini</th><th>Pembaruan admin</th></tr></thead><tbody>
<?php foreach ($applications as $a): ?><tr>
<td><strong><?= e($a['application_code']) ?></strong><div class="table-sub"><?= e($a['nama_mahasiswa']) ?></div><div class="table-sub">NIM: <?= e($a['nim']) ?></div><div class="table-sub"><?= e($a['prodi']) ?></div><div class="table-sub"><?= e(date('d/m/Y H:i', strtotime($a['created_at']))) ?></div></td>
<td><strong><?= e($a['letter_type']) ?></strong>
<?php if (!empty($a['target_institution'])): ?><div class="table-sub"><strong>Instansi:</strong> <?= e($a['target_institution']) ?></div><?php endif; ?>
<?php if (!empty($a['supervisor_name'])): ?><div class="table-sub"><strong>Dosen pembimbing:</strong> <?= e($a['supervisor_name']) ?></div><?php endif; ?>
<?php if (!empty($a['course_name'])): ?><div class="table-sub"><strong>Mata kuliah:</strong> <?= e($a['course_name']) ?></div><?php endif; ?>
<?php if (!empty($a['research_title'])): ?><div class="table-sub"><strong>Judul/topik:</strong> <?= e($a['research_title']) ?></div><?php endif; ?>
<?php if (!empty($a['activity_field'])): ?><div class="table-sub"><strong>Bidang:</strong> <?= e($a['activity_field']) ?></div><?php endif; ?>
<?php if (!empty($a['activity_start']) || !empty($a['activity_end'])): ?><div class="table-sub"><strong>Periode:</strong> <?= e($a['activity_start'] ?: '-') ?> s.d. <?= e($a['activity_end'] ?: '-') ?></div><?php endif; ?>
<div class="detail-text"><?= e($a['description']) ?></div></td>
<td><span class="status-pill <?= $a['status'] === 'Selesai' ? 'status-selesai' : ($a['status'] === 'Ditolak' ? 'status-ditolak' : ($a['status'] === 'Diproses' ? 'status-proses' : 'status-menunggu')) ?>"><?= e($a['status']) ?></span><div class="table-sub"><?= e($a['staff_note'] ?: 'Belum ada catatan') ?></div><?php if ($a['status'] === 'Selesai'): ?><a class="btn btn-outline btn-small" href="surat_pdf.php?id=<?= (int)$a['id'] ?>">Lihat PDF</a><?php endif; ?></td>
<td><form method="post" class="update-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="application_id" value="<?= (int)$a['id'] ?>"><label class="sr-only" for="status-<?= (int)$a['id'] ?>">Status baru</label><select id="status-<?= (int)$a['id'] ?>" name="status"><?php foreach ($validStatuses as $s): ?><option value="<?= e($s) ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select><input name="staff_note" maxlength="500" value="<?= e($a['staff_note']) ?>" placeholder="Catatan untuk mahasiswa"><button type="submit" class="btn btn-primary btn-small">Simpan</button></form></td>
</tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php require 'partials/footer.php'; ?>
