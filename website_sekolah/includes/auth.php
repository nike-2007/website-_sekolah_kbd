<?php
require_once __DIR__ . '/../config.php';

// Pastikan pengguna sudah login dengan peran yang benar; kembalikan profilnya.
function require_role(string $role): array {
    global $pdo;
    if (empty($_SESSION['id_user'])) { header('Location: ../login.php?as=' . $role); exit; }
    if ($_SESSION['role'] !== $role)  { header('Location: ../dashboard.php'); exit; }
    $sql = $role === 'guru'
        ? 'SELECT * FROM guru WHERE id_user = ?'
        : 'SELECT s.*, k.nama_kelas, k.tahun_ajaran FROM siswa s LEFT JOIN kelas k ON k.id_kelas = s.id_kelas WHERE s.id_user = ?';
    $st = $pdo->prepare($sql);
    $st->execute([(int)$_SESSION['id_user']]);
    $p = $st->fetch();
    if (!$p) exit('Profil tidak ditemukan. Hubungi admin sekolah.');
    return $p;
}
