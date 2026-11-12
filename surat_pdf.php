<?php
require_once 'config.php';
require_login();
$user = $_SESSION['user'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID pengajuan tidak valid.'); }
$sql = 'SELECT a.*, u.nama, u.nim, u.prodi, u.email FROM applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?';
$params = [$id];
if (!is_staff_role($user['role'] ?? '')) {
    $sql .= ' AND a.user_id = ?'; $params[] = $user['id'];
} else {
    // Admin/petugas hanya boleh membuka surat dari mahasiswa prodi-nya sendiri.
    $sql .= ' AND u.prodi = ?'; $params[] = current_staff_prodi($pdo);
}
$stmt = $pdo->prepare($sql); $stmt->execute($params); $a = $stmt->fetch();
if (!$a) { http_response_code(404); exit('Pengajuan tidak ditemukan atau bukan milik akun ini.'); }
if ($a['status'] !== 'Selesai') { http_response_code(403); exit('Surat PDF hanya dapat diunduh setelah pengajuan berstatus Selesai.'); }
$memberStmt = $pdo->prepare('SELECT nama, nim, prodi FROM application_members WHERE application_id = ? ORDER BY id ASC');
$memberStmt->execute([$id]);
$groupMembers = $memberStmt->fetchAll();

// Tata letak surat mengikuti contoh di folder referensi_surat/ (lihat surat_pdf_lib.php).
require_once __DIR__ . '/surat_pdf_lib.php';

$logoPath = __DIR__ . '/referensi_surat/logo-unimed.jpg';
if (!is_file($logoPath) || filesize($logoPath) === 0) {
    exit('File logo-unimed.jpg tidak ditemukan atau kosong.');
}

$pdf = surat_build($a, $groupMembers, $logoPath);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Surat-' . preg_replace('/[^A-Za-z0-9_-]/', '-', (string)$a['application_code']) . '.pdf"');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
