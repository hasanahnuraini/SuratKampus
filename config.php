<?php
declare(strict_types=1);
session_start();

$host = 'localhost';
$db   = 'kampus_surat';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL aktif dan database kampus_surat sudah dibuat melalui database.sql.');
}

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * DAFTAR PROGRAM STUDI RESMI (satu-satunya sumber kebenaran).
 * Dipakai oleh register.php (mahasiswa) dan kelola_admin.php (admin).
 * Karena kedua sisi memilih dari daftar yang SAMA, teks prodi mahasiswa
 * dan admin dijamin identik, sehingga surat selalu masuk ke admin prodi yang tepat.
 * Sesuaikan isi daftar ini dengan prodi yang benar-benar ada di kampus Anda.
 */
const PRODI_LIST = [
    'Pendidikan TIK',
    'Pendidikan Tata Boga',
    'Pendidikan Tata Busana',
    'Pendidikan Tata Rias',
    'Pendidikan Teknik Bangunan',
    'Pendidikan Teknik Elektro',
    'Pendidikan Teknik Mesin',
    'Pendidikan Teknik Otomotif',
];

function is_valid_prodi(string $prodi): bool {
    return in_array($prodi, PRODI_LIST, true);
}

function prodi_options_html(string $selected = ''): string {
    $html = '<option value="">-- Pilih program studi --</option>';
    foreach (PRODI_LIST as $p) {
        $html .= '<option value="' . e($p) . '"' . ($p === $selected ? ' selected' : '') . '>' . e($p) . '</option>';
    }
    return $html;
}

/**
 * Prodi admin/petugas yang sedang login, dibaca langsung dari database
 * (bukan dari session) agar perubahan prodi langsung berlaku.
 */
function current_staff_prodi(PDO $pdo): string {
    $stmt = $pdo->prepare('SELECT prodi FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user']['id'] ?? 0]);
    $prodi = trim((string) $stmt->fetchColumn());
    $_SESSION['user']['prodi'] = $prodi;
    return $prodi;
}

function is_staff_role(?string $role): bool {
    return in_array($role, ['admin', 'petugas'], true);
}

function role_home(?string $role): string {
    return is_staff_role($role) ? 'admin.php' : 'dashboard.php';
}

function require_role($role): void {
    require_login();
    $allowed = is_array($role) ? $role : [$role];
    if (!in_array(($_SESSION['user']['role'] ?? ''), $allowed, true)) {
        header('Location: ' . role_home($_SESSION['user']['role'] ?? ''));
        exit;
    }
}

function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}
