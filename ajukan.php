<?php
require_once 'config.php';
require_role('mahasiswa');
$user = $_SESSION['user'];
$types = [
    'Surat Izin Observasi Mata Kuliah / Mini Riset',
    'Surat Izin Magang Mandiri',
    'Surat Izin Praktik Kerja Lapangan Industri (PKLI)',
    'Surat Izin Observasi Skripsi'
];
$errors = [];
$members = [];
$fields = ['letter_type'=>'', 'target_institution'=>'', 'target_address'=>'', 'course_name'=>'', 'supervisor_name'=>'', 'activity_field'=>'', 'activity_start'=>'', 'activity_end'=>'', 'research_title'=>'', 'description'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($fields as $key => $default) $fields[$key] = trim((string)($_POST[$key] ?? ''));
    if (!in_array($fields['letter_type'], $types, true)) $errors[] = 'Pilih jenis surat yang tersedia.';
    if ($fields['target_institution'] === '') $errors[] = 'Nama sekolah/perguruan tinggi/perusahaan/instansi tujuan wajib diisi.';
    if ($fields['supervisor_name'] === '') $errors[] = 'Nama dosen pembimbing wajib diisi.';
    if ($fields['description'] === '' || strlen($fields['description']) < 10) $errors[] = 'Tujuan atau keterangan kegiatan minimal 10 karakter.';
    if (strlen($fields['description']) > 1500) $errors[] = 'Keterangan maksimal 1.500 karakter.';
    if (strlen($fields['target_institution']) > 190 || strlen($fields['target_address']) > 300 || strlen($fields['course_name']) > 190 || strlen($fields['supervisor_name']) > 190 || strlen($fields['activity_field']) > 190 || strlen($fields['research_title']) > 500) $errors[] = 'Salah satu isian terlalu panjang.';
    if (($fields['activity_start'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fields['activity_start'])) || ($fields['activity_end'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fields['activity_end']))) $errors[] = 'Format tanggal tidak valid.';
    if ($fields['activity_start'] && $fields['activity_end'] && $fields['activity_end'] < $fields['activity_start']) $errors[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    if (str_contains($fields['letter_type'], 'Mata Kuliah') && $fields['course_name'] === '') $errors[] = 'Nama mata kuliah wajib diisi untuk surat observasi mata kuliah/mini riset.';
    if (str_contains($fields['letter_type'], 'Skripsi') && $fields['research_title'] === '') $errors[] = 'Judul skripsi wajib diisi.';
    $memberNames = $_POST['member_nama'] ?? [];
    $memberNims = $_POST['member_nim'] ?? [];
    $memberProdis = $_POST['member_prodi'] ?? [];
    if (!is_array($memberNames) || !is_array($memberNims) || !is_array($memberProdis)) {
        $errors[] = 'Data anggota kelompok tidak valid.';
    } else {
        $count = max(count($memberNames), count($memberNims), count($memberProdis));
        if ($count > 20) $errors[] = 'Maksimal 20 anggota kelompok tambahan.';
        for ($i = 0; $i < min($count, 20); $i++) {
            $nama = trim((string)($memberNames[$i] ?? ''));
            $nim = trim((string)($memberNims[$i] ?? ''));
            $prodi = trim((string)($memberProdis[$i] ?? ''));
            if ($nama === '' && $nim === '' && $prodi === '') continue;
            if ($nama === '' || $nim === '' || $prodi === '') {
                $errors[] = 'Lengkapi nama, NIM, dan program studi untuk setiap anggota kelompok.';
                break;
            }
            if (strlen($nama) > 120 || strlen($nim) > 30 || strlen($prodi) > 190) {
                $errors[] = 'Data salah satu anggota kelompok terlalu panjang.';
                break;
            }
            if ($nim === (string)$user['nim']) {
                $errors[] = 'NIM anggota tidak boleh sama dengan NIM mahasiswa pengaju.';
                break;
            }
            foreach ($members as $existing) {
                if ($existing['nim'] === $nim) { $errors[] = 'NIM anggota kelompok tidak boleh duplikat.'; break 2; }
            }
            $members[] = ['nama'=>$nama, 'nim'=>$nim, 'prodi'=>$prodi];
        }
    }
    if (!$errors) {
        $code = 'KS-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $pdo->prepare('INSERT INTO applications (application_code, user_id, letter_type, description, target_institution, target_address, course_name, supervisor_name, activity_field, activity_start, activity_end, research_title, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'Menunggu\')');
        $stmt->execute([$code, $user['id'], $fields['letter_type'], $fields['description'], $fields['target_institution'], $fields['target_address'] ?: null, $fields['course_name'] ?: null, $fields['supervisor_name'], $fields['activity_field'] ?: null, $fields['activity_start'] ?: null, $fields['activity_end'] ?: null, $fields['research_title'] ?: null]);
        $applicationId = (int)$pdo->lastInsertId();
        if ($members) {
            $memberStmt = $pdo->prepare('INSERT INTO application_members (application_id, nama, nim, prodi) VALUES (?, ?, ?, ?)');
            foreach ($members as $member) $memberStmt->execute([$applicationId, $member['nama'], $member['nim'], $member['prodi']]);
        }
        flash('Pengajuan berhasil dikirim. Simpan nomor pengajuan untuk referensi Anda.');
        header('Location: dashboard.php'); exit;
    }
}
$pageTitle = 'Ajukan Surat';
require 'partials/header.php';
?>
<section class="page-heading"><div><div class="eyebrow">FORMULIR ONLINE</div><h1>Ajukan surat baru</h1><p>Lengkapi data kegiatan agar admin kampus dapat memproses permohonan Anda.</p></div><a class="btn btn-outline" href="dashboard.php">← Kembali</a></section>
<div class="form-page-grid">
  <section class="panel form-panel"><div class="panel-heading"><div><h2>Data permohonan</h2><p>Identitas mahasiswa diambil otomatis dari profil akun.</p></div><span class="required-note">* Wajib diisi</span></div>
  <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="identity-box"><div class="identity-avatar"><?= e(strtoupper(substr($user['nama'], 0, 1))) ?></div><div><strong><?= e($user['nama']) ?></strong><span>NIM: <?= e($user['nim']) ?></span><small><?= e($user['prodi']) ?></small></div></div>
  <form method="post" class="form-stack" id="application-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>Jenis surat <span class="required">*</span><select id="letter_type" name="letter_type" required><option value="">Pilih jenis surat</option><?php foreach ($types as $type): ?><option value="<?= e($type) ?>" <?= $fields['letter_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></label>
    <div class="form-section-title">Informasi instansi dan pembimbing</div>
    <label>Nama sekolah / perguruan tinggi / perusahaan / instansi tujuan <span class="required">*</span><input name="target_institution" maxlength="190" value="<?= e($fields['target_institution']) ?>" placeholder="Contoh: SMK Negeri 1 ..." required></label>
    <label>Alamat instansi tujuan <small>(opsional, dicetak di bawah nama instansi pada surat)</small><textarea name="target_address" rows="2" maxlength="300" placeholder="Contoh: Jl. Gaharu No.1, Gaharu, Medan Timur, Kota Medan"><?= e($fields['target_address']) ?></textarea></label>
    <label>Nama dosen pembimbing <span class="required">*</span><input name="supervisor_name" maxlength="190" value="<?= e($fields['supervisor_name']) ?>" placeholder="Nama lengkap beserta gelar dosen pembimbing" required></label>
    <div id="course-group"><label>Mata kuliah <span class="required">*</span><input name="course_name" maxlength="190" value="<?= e($fields['course_name']) ?>" placeholder="Nama mata kuliah yang diobservasi"></label></div>
    <div id="research-group"><label>Judul skripsi / topik penelitian <span class="required">*</span><textarea name="research_title" rows="3" maxlength="500" placeholder="Masukkan judul skripsi atau topik penelitian"><?= e($fields['research_title']) ?></textarea></label></div>
    <div id="field-group"><label>Bidang / posisi kegiatan <input name="activity_field" maxlength="190" value="<?= e($fields['activity_field']) ?>" placeholder="Contoh: Pengembangan perangkat lunak, RPL, administrasi"></label></div>
    <div class="form-two-columns">
      <label>Tanggal mulai kegiatan <input type="date" name="activity_start" value="<?= e($fields['activity_start']) ?>"></label>
      <label>Tanggal selesai kegiatan <input type="date" name="activity_end" value="<?= e($fields['activity_end']) ?>"></label>
    </div>
    <label>Tujuan dan keterangan kegiatan <span class="required">*</span><textarea name="description" rows="5" maxlength="1500" placeholder="Jelaskan tujuan observasi, mini riset, magang, atau PKLI serta informasi pendukung lainnya..." required><?= e($fields['description']) ?></textarea><small>Minimal 10 karakter, maksimal 1.500 karakter.</small></label>
    <div class="form-section-title">Data anggota kelompok (opsional)</div>
    <p class="form-help">Data mahasiswa pengaju di atas sudah otomatis tercantum pada surat. Tambahkan anggota lain jika pengajuan dilakukan berkelompok.</p>
    <div id="members-container">
      <?php foreach ($members as $member): ?>
      <div class="member-row form-two-columns">
        <label>Nama anggota <input name="member_nama[]" maxlength="120" value="<?= e($member['nama']) ?>" placeholder="Nama lengkap"></label>
        <label>NIM anggota <input name="member_nim[]" maxlength="30" value="<?= e($member['nim']) ?>" placeholder="NIM"></label>
        <label>Program studi <input name="member_prodi[]" maxlength="190" value="<?= e($member['prodi']) ?>" placeholder="Program studi"></label>
        <button type="button" class="btn btn-outline remove-member">Hapus anggota</button>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-outline" id="add-member">+ Tambah Anggota</button>
    <div class="form-actions"><a href="dashboard.php" class="btn btn-outline">Batal</a><button type="submit" class="btn btn-primary">Kirim pengajuan</button></div>
  </form></section>
  <aside class="info-card"><h3>Sebelum mengirim</h3><p>Pastikan seluruh data sudah benar. Admin kampus akan memeriksa permohonan sebelum statusnya dinyatakan selesai.</p><ul><li>Periksa kembali NIM dan program studi.</li><li>Isi nama instansi dan dosen pembimbing dengan benar.</li><li>Isi mata kuliah atau judul skripsi sesuai jenis surat.</li><li>PDF dapat diunduh setelah status pengajuan selesai.</li></ul></aside>
</div>
<script>
(function(){const type=document.getElementById('letter_type');const course=document.getElementById('course-group');const research=document.getElementById('research-group');const field=document.getElementById('field-group');function update(){const v=type.value;const isCourse=v.includes('Mata Kuliah');const isThesis=v.includes('Skripsi');course.hidden=!isCourse;research.hidden=!(isThesis||isCourse);field.hidden=!(v.includes('Magang Mandiri')||v.includes('PKLI'));course.querySelector('input').required=isCourse;research.querySelector('textarea').required=isThesis;}type.addEventListener('change',update);update();
const container=document.getElementById('members-container');
const add=document.getElementById('add-member');
function bindRemove(btn){btn.addEventListener('click',()=>btn.closest('.member-row').remove());}
container.querySelectorAll('.remove-member').forEach(bindRemove);
add.addEventListener('click',()=>{
 if(container.querySelectorAll('.member-row').length>=20){alert('Maksimal 20 anggota tambahan.');return;}
 const row=document.createElement('div');row.className='member-row form-two-columns';
 row.innerHTML='<label>Nama anggota <input name="member_nama[]" maxlength="120" placeholder="Nama lengkap"></label><label>NIM anggota <input name="member_nim[]" maxlength="30" placeholder="NIM"></label><label>Program studi <input name="member_prodi[]" maxlength="190" placeholder="Program studi"></label><button type="button" class="btn btn-outline remove-member">Hapus anggota</button>';
 container.appendChild(row);bindRemove(row.querySelector('.remove-member'));
});})();
</script>
<?php require 'partials/footer.php'; ?>
