<?php
require_once 'config.php';
require_role('mahasiswa');
$user = $_SESSION['user'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID pengajuan tidak valid.'); }

$stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $user['id']]);
$application = $stmt->fetch();
if (!$application) { http_response_code(404); exit('Pengajuan tidak ditemukan atau bukan milik akun ini.'); }
if ($application['status'] !== 'Diproses') {
    flash('Pengajuan hanya dapat diedit ketika admin meminta revisi melalui status Diproses.', 'error');
    header('Location: dashboard.php'); exit;
}

$types = [
    'Surat Izin Observasi Mata Kuliah / Mini Riset',
    'Surat Izin Magang Mandiri',
    'Surat Izin Praktik Kerja Lapangan Industri (PKLI)',
    'Surat Izin Observasi Skripsi'
];
$fields = [
    'letter_type' => $application['letter_type'] ?? '',
    'target_institution' => $application['target_institution'] ?? '',
    'target_address' => $application['target_address'] ?? '',
    'course_name' => $application['course_name'] ?? '',
    'supervisor_name' => $application['supervisor_name'] ?? '',
    'activity_field' => $application['activity_field'] ?? '',
    'activity_start' => $application['activity_start'] ?? '',
    'activity_end' => $application['activity_end'] ?? '',
    'research_title' => $application['research_title'] ?? '',
    'description' => $application['description'] ?? ''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($fields as $key => $default) {
        $fields[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if (!in_array($fields['letter_type'], $types, true)) $errors[] = 'Jenis surat tidak valid.';
    if ($fields['target_institution'] === '') $errors[] = 'Nama instansi tujuan wajib diisi.';
    if ($fields['supervisor_name'] === '') $errors[] = 'Nama dosen pembimbing wajib diisi.';
    if ($fields['description'] === '' || strlen($fields['description']) < 10) $errors[] = 'Tujuan atau keterangan kegiatan minimal 10 karakter.';
    if (strlen($fields['description']) > 1500) $errors[] = 'Keterangan maksimal 1.500 karakter.';
    if (strlen($fields['target_institution']) > 190 || strlen($fields['target_address']) > 300 || strlen($fields['course_name']) > 190 || strlen($fields['supervisor_name']) > 190 || strlen($fields['activity_field']) > 190 || strlen($fields['research_title']) > 500) $errors[] = 'Salah satu isian terlalu panjang.';
    if (($fields['activity_start'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fields['activity_start'])) || ($fields['activity_end'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fields['activity_end']))) $errors[] = 'Format tanggal tidak valid.';
    if ($fields['activity_start'] && $fields['activity_end'] && $fields['activity_end'] < $fields['activity_start']) $errors[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    if (str_contains($fields['letter_type'], 'Mata Kuliah') && $fields['course_name'] === '') $errors[] = 'Nama mata kuliah wajib diisi untuk surat observasi mata kuliah/mini riset.';
    if (str_contains($fields['letter_type'], 'Skripsi') && $fields['research_title'] === '') $errors[] = 'Judul skripsi wajib diisi.';

    if (!$errors) {
        // Status dikembalikan ke Menunggu agar admin memeriksa ulang data revisi.
        // application_code dan user_id tetap sama; tidak membuat pengajuan baru.
        $update = $pdo->prepare("UPDATE applications SET letter_type = ?, description = ?, target_institution = ?, target_address = ?, course_name = ?, supervisor_name = ?, activity_field = ?, activity_start = ?, activity_end = ?, research_title = ?, status = 'Menunggu', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND status = 'Diproses'");
        $update->execute([
            $fields['letter_type'], $fields['description'], $fields['target_institution'], $fields['target_address'] ?: null,
            $fields['course_name'] ?: null, $fields['supervisor_name'],
            $fields['activity_field'] ?: null, $fields['activity_start'] ?: null,
            $fields['activity_end'] ?: null, $fields['research_title'] ?: null,
            $id, $user['id']
        ]);
        if ($update->rowCount() >= 0) {
            flash('Revisi berhasil disimpan. Pengajuan dikirim kembali kepada admin untuk diperiksa.');
            header('Location: dashboard.php'); exit;
        }
        $errors[] = 'Revisi belum berhasil disimpan. Muat ulang halaman dan coba lagi.';
    }
}

$pageTitle = 'Revisi Pengajuan';
require 'partials/header.php';
?>
<section class="page-heading"><div><div class="eyebrow">REVISI PENGAJUAN</div><h1>Perbaiki data surat</h1><p>Perbarui data yang diminta admin. Setelah disimpan, status akan kembali menjadi Menunggu untuk diperiksa ulang.</p></div><a class="btn btn-outline" href="dashboard.php">← Kembali</a></section>
<section class="panel form-panel">
  <div class="panel-heading"><div><h2>Nomor pengajuan: <?= e($application['application_code']) ?></h2><p>Pengajuan yang sama akan diperbarui, bukan dibuat ulang.</p></div><span class="status-pill status-proses">Diproses</span></div>
  <?php if (!empty($application['staff_note'])): ?><div class="alert alert-error"><strong>Catatan admin:</strong> <?= e($application['staff_note']) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" class="form-stack" id="application-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Jenis surat <span class="required">*</span><select id="letter_type" name="letter_type" required><option value="">Pilih jenis surat</option><?php foreach ($types as $type): ?><option value="<?= e($type) ?>" <?= $fields['letter_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
    <div class="form-section-title">Informasi instansi dan pembimbing</div>
    <label>Nama sekolah / perguruan tinggi / perusahaan / instansi tujuan <span class="required">*</span><input name="target_institution" maxlength="190" value="<?= e($fields['target_institution']) ?>" required></label>
    <label>Alamat instansi tujuan <small>(opsional, dicetak di bawah nama instansi pada surat)</small><textarea name="target_address" rows="2" maxlength="300"><?= e($fields['target_address']) ?></textarea></label>
    <label>Nama dosen pembimbing <span class="required">*</span><input name="supervisor_name" maxlength="190" value="<?= e($fields['supervisor_name']) ?>" required></label>
    <div id="course-group"><label>Mata kuliah <span class="required">*</span><input name="course_name" maxlength="190" value="<?= e($fields['course_name']) ?>"></label></div>
    <div id="research-group"><label>Judul skripsi / topik penelitian <span class="required">*</span><textarea name="research_title" rows="3" maxlength="500"><?= e($fields['research_title']) ?></textarea></label></div>
    <div id="field-group"><label>Bidang / posisi kegiatan <input name="activity_field" maxlength="190" value="<?= e($fields['activity_field']) ?>"></label></div>
    <div class="form-two-columns">
      <label>Tanggal mulai kegiatan <input type="date" name="activity_start" value="<?= e($fields['activity_start']) ?>"></label>
      <label>Tanggal selesai kegiatan <input type="date" name="activity_end" value="<?= e($fields['activity_end']) ?>"></label>
    </div>
    <label>Tujuan dan keterangan kegiatan <span class="required">*</span><textarea name="description" rows="5" maxlength="1500" required><?= e($fields['description']) ?></textarea><small>Minimal 10 karakter, maksimal 1.500 karakter.</small></label>
    <div class="form-actions"><a href="dashboard.php" class="btn btn-outline">Batal</a><button type="submit" class="btn btn-primary">Simpan revisi</button></div>
  </form>
</section>
<script>
(function(){const type=document.getElementById('letter_type');const course=document.getElementById('course-group');const research=document.getElementById('research-group');const field=document.getElementById('field-group');function update(){const v=type.value;const isCourse=v.includes('Mata Kuliah');const isThesis=v.includes('Skripsi');course.hidden=!isCourse;research.hidden=!(isThesis||isCourse);field.hidden=!(v.includes('Magang Mandiri')||v.includes('PKLI'));course.querySelector('input').required=isCourse;research.querySelector('textarea').required=isThesis;}type.addEventListener('change',update);update();})();
</script>
<?php require 'partials/footer.php'; ?>
