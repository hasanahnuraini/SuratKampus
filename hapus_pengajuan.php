<?php
require_once 'config.php';

require_role('mahasiswa');
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];

$applicationId = filter_var(
    $_POST['application_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$applicationId || $applicationId <= 0) {
    flash('ID pengajuan tidak valid.', 'error');
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Pastikan pengajuan milik mahasiswa yang login
    // dan statusnya masih Menunggu.
    $stmt = $pdo->prepare(
        "SELECT id FROM applications
            WHERE id = ?
            AND user_id = ?
            AND status = 'Menunggu'
            FOR UPDATE"
    );

    $stmt->execute([$applicationId, $userId]);

    $application = $stmt->fetch();

    if (!$application) {
        $pdo->rollBack();

        flash(
            'Pengajuan tidak ditemukan atau tidak dapat dihapus.',
            'error'
        );

        header('Location: dashboard.php');
        exit;
    }

    // Hapus anggota kelompok terlebih dahulu.
    $stmt = $pdo->prepare(
        'DELETE FROM application_members WHERE application_id = ?'
    );

    $stmt->execute([$applicationId]);

    // Hapus pengajuan utama.
    $stmt = $pdo->prepare(
        "DELETE FROM applications
            WHERE id = ?
            AND user_id = ?
            AND status = 'Menunggu'"
    );

    $stmt->execute([$applicationId, $userId]);

    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Penghapusan gagal.');
    }

    $pdo->commit();

    flash('Pengajuan berhasil dihapus.');

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('Terjadi kesalahan saat menghapus pengajuan.', 'error');
}

header('Location: dashboard.php');
exit;