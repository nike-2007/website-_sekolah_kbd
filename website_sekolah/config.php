<?php
// config.php - koneksi database (PDO) + pengaturan sesi + fungsi bantu
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'db_sekolah';
const DB_USER = 'root';      // GANTI di server asli: pakai user khusus dengan hak terbatas
const DB_PASS = '';          // GANTI dengan password kuat

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // prepared statement ASLI (anti SQL injection)
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());                      // detail error jangan ditampilkan ke user
    exit('Koneksi database gagal.');
}

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
session_start();

// Escape output HTML (anti XSS)
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// CSRF token
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403); exit('Token tidak valid.');
    }
}

// Catat aktivitas ke audit_log
function audit(PDO $pdo, ?int $idUser, string $aksi): void {
    $st = $pdo->prepare('INSERT INTO audit_log (id_user, aksi, ip_addr) VALUES (?, ?, ?)');
    $st->execute([$idUser, $aksi, $_SERVER['REMOTE_ADDR'] ?? 'cli']);
}
