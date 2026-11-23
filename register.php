<?php
require __DIR__ . '/config.php';
$error = ''; $ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nis      = trim($_POST['nis'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $username))      $error = 'Username 4-30 karakter (huruf, angka, _).';
    elseif (strlen($password) < 8)                              $error = 'Password minimal 8 karakter.';
    elseif (!preg_match('/^[0-9]{5,20}$/', $nis))               $error = 'NIS harus angka 5-20 digit.';
    elseif ($nama === '' || mb_strlen($nama) > 100)             $error = 'Nama wajib diisi.';
    else {
        // TRANSAKSI: users + siswa harus tersimpan keduanya, atau batal keduanya (atomicity)
        $pdo->beginTransaction();
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);   // HASHING PASSWORD (bcrypt/argon)
            $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?,?,"siswa")')
                ->execute([$username, $hash]);
            $idUser = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO siswa (id_user, nis, nama) VALUES (?,?,?)')
                ->execute([$idUser, $nis, $nama]);
            $pdo->commit();
            audit($pdo, $idUser, 'REGISTER');
            $ok = true;
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $error = ($ex->errorInfo[1] ?? 0) === 1062 ? 'Username atau NIS sudah terdaftar.' : 'Pendaftaran gagal.';
        }
    }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Daftar Siswa</title>
<link rel="stylesheet" href="style.css"></head><body><div class="card">
<h2>Pendaftaran Akun Siswa</h2>
<?php if ($ok): ?><p class="ok">Berhasil! <a href="login.php?as=siswa">Login di sini</a></p>
<?php else: ?>
<?php if ($error): ?><p class="err"><?= e($error) ?></p><?php endif; ?>
<form method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Nama Lengkap <input name="nama" required></label>
  <label>NIS <input name="nis" required></label>
  <label>Username <input name="username" required></label>
  <label>Password <input type="password" name="password" required></label>
  <button>Daftar</button>
</form><?php endif; ?>
<p><a href="login.php?as=siswa">Sudah punya akun?</a> · <a href="index.php">Beranda</a></p></div></body></html>
