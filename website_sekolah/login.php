<?php
require __DIR__ . '/config.php';

$as = $_GET['as'] ?? '';
if (!in_array($as, ['guru', 'siswa', 'admin'], true)) { header('Location: index.php'); exit; }
if (!empty($_SESSION['id_user'])) { header('Location: dashboard.php'); exit; }   // sudah login -> diarahkan sesuai peran

$info = [
    'guru'  => ['Guru',          '👩‍🏫', 'Masuk untuk menginput nilai dan melihat jadwal mengajar.'],
    'siswa' => ['Siswa',         '👨‍🎓', 'Masuk untuk melihat nilai rapor dan jadwal pelajaran.'],
    'admin' => ['Administrator', '🛡️', 'Area khusus pengelola sistem sekolah.'],
][$as];
$tujuan = ['admin' => 'dashboard.php', 'guru' => 'guru/index.php', 'siswa' => 'siswa/index.php'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Prepared statement -> aman dari SQL Injection
    $st = $pdo->prepare('SELECT id_user, username, password_hash, role FROM users WHERE username = ?');
    $st->execute([$username]);
    $user = $st->fetch();

    // Password harus cocok DAN peran akun harus sama dengan halaman login yang dipilih
    if ($user && password_verify($password, $user['password_hash']) && $user['role'] === $as) {
        session_regenerate_id(true);
        $_SESSION['id_user']  = (int)$user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id_user=?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id_user']]);
        }
        $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id_user=?')->execute([$user['id_user']]);
        audit($pdo, (int)$user['id_user'], 'LOGIN_SUKSES:' . $as);
        header('Location: ' . $tujuan[$user['role']]); exit;
    }
    audit($pdo, $user ? (int)$user['id_user'] : null, 'LOGIN_GAGAL:' . $as . ':' . substr($username, 0, 30));
    $error = 'Username atau password salah, atau bukan akun ' . $info[0] . '.';
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login <?= e($info[0]) ?></title><link rel="stylesheet" href="assets/portal.css"></head>
<body class="login-body theme-<?= e($as) ?>">
<div class="login-box">
  <div class="login-side"><div class="big"><?= $info[1] ?></div><h2>Portal <?= e($info[0]) ?></h2><p><?= e($info[2]) ?></p></div>
  <div class="login-form">
    <h2>Login <?= e($info[0]) ?></h2><p class="muted">Masukkan username dan password Anda.</p>
    <?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="login.php?as=<?= e($as) ?>">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label>Username</label><input name="username" required autofocus>
      <label>Password</label><input type="password" name="password" required>
      <button class="btn">Masuk</button>
    </form>
    <p class="alt">
      <?php if ($as === 'siswa'): ?>Belum punya akun? <a href="register.php">Daftar</a> · <?php endif; ?>
      <?php if ($as !== 'guru'): ?><a href="login.php?as=guru">Login Guru</a> · <?php endif; ?>
      <?php if ($as !== 'siswa'): ?><a href="login.php?as=siswa">Login Siswa</a> · <?php endif; ?>
      <a href="index.php">Beranda</a>
    </p>
  </div>
</div></body></html>
