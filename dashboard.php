<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['id_user'])) { header('Location: login.php?as=admin'); exit; }
if ($_SESSION['role'] === 'guru')  { header('Location: guru/index.php');  exit; }
if ($_SESSION['role'] === 'siswa') { header('Location: siswa/index.php'); exit; }

$idUser = (int)$_SESSION['id_user'];
$role   = $_SESSION['role'];
$isStaff = in_array($role, ['admin', 'guru'], true);

// ---- Data umum ----
$stat = $pdo->query('SELECT
    (SELECT COUNT(*) FROM siswa)  AS siswa,
    (SELECT COUNT(*) FROM guru)   AS guru,
    (SELECT COUNT(*) FROM kelas)  AS kelas,
    (SELECT COUNT(*) FROM mapel)  AS mapel')->fetch();

$pengumuman = $pdo->query(
  'SELECT p.judul, p.isi, p.created_at, u.username
     FROM pengumuman p JOIN users u ON u.id_user = p.id_user
    ORDER BY p.created_at DESC LIMIT 5')->fetchAll();

// ---- Data per peran ----
$profil = null; $nilai = []; $rata = null; $siswaBaru = []; $logs = [];
if ($role === 'siswa') {
    $st = $pdo->prepare('SELECT s.nama, s.nis, k.nama_kelas, k.tahun_ajaran
                           FROM siswa s LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
                          WHERE s.id_user = ?');
    $st->execute([$idUser]);
    $profil = $st->fetch();

    $st = $pdo->prepare('SELECT m.nama_mapel, n.semester, n.nilai_angka
                           FROM nilai n
                           JOIN siswa s ON s.id_siswa = n.id_siswa
                           JOIN mapel m ON m.id_mapel = n.id_mapel
                          WHERE s.id_user = ? ORDER BY n.semester, m.nama_mapel');
    $st->execute([$idUser]);
    $nilai = $st->fetchAll();
    if ($nilai) $rata = array_sum(array_column($nilai, 'nilai_angka')) / count($nilai);
} else {
    $siswaBaru = $pdo->query(
      'SELECT s.nis, s.nama, COALESCE(k.nama_kelas, "-") AS kelas, u.created_at
         FROM siswa s JOIN users u ON u.id_user = s.id_user
         LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
        ORDER BY s.id_siswa DESC LIMIT 6')->fetchAll();
}
if ($role === 'admin') {
    $logs = $pdo->query(
      'SELECT l.aksi, l.ip_addr, l.waktu, COALESCE(u.username, "-") AS username
         FROM audit_log l LEFT JOIN users u ON u.id_user = l.id_user
        ORDER BY l.id_log DESC LIMIT 6')->fetchAll();
}

// ---- Helper tampilan ----
$bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$tgl   = $hari[(int)date('w')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y');
function tglID(string $t, array $b): string { $x = strtotime($t); return date('j', $x) . ' ' . substr($b[(int)date('n', $x)], 0, 3) . ' ' . date('Y', $x); }
$inisial = strtoupper(substr($_SESSION['username'], 0, 1));
$nama = $profil['nama'] ?? $_SESSION['username'];
$h = (int)date('G');
$salam = $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 18 ? 'Selamat sore' : 'Selamat malam'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard | SMA Nusantara</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="dash">

<aside class="sidebar">
  <div class="brand"><span class="logo">🎓</span><div><b>SMA Nusantara</b><small>Sistem Informasi Sekolah</small></div></div>
  <nav>
    <p class="nav-title">MENU UTAMA</p>
    <a class="active" href="dashboard.php">🏠 Dashboard</a>
    <?php if ($isStaff): ?>
      <a href="#">👨‍🎓 Data Siswa</a>
      <a href="#">👩‍🏫 Data Guru</a>
      <a href="#">🏫 Data Kelas</a>
      <a href="#">📚 Mata Pelajaran</a>
      <a href="#">📝 Input Nilai</a>
    <?php else: ?>
      <a href="#">📝 Nilai Saya</a>
      <a href="#">📅 Jadwal Pelajaran</a>
    <?php endif; ?>
    <a href="#">📢 Pengumuman</a>
    <?php if ($role === 'admin'): ?>
      <p class="nav-title">ADMINISTRASI</p>
      <a href="#">🛡️ Audit Log</a>
      <a href="#">💾 Backup Data</a>
    <?php endif; ?>
    <p class="nav-title">AKUN</p>
    <a href="logout.php">🚪 Keluar</a>
  </nav>
</aside>

<main class="main">
  <header class="topbar">
    <div><h1>Dashboard</h1><span class="date">📅 <?= e($tgl) ?></span></div>
    <div class="user">
      <div class="who"><b><?= e($nama) ?></b><span class="badge <?= e($role) ?>"><?= e(ucfirst($role)) ?></span></div>
      <div class="avatar"><?= e($inisial) ?></div>
    </div>
  </header>

  <section class="hero">
    <div>
      <h2><?= e($salam) ?>, <?= e($nama) ?>! 👋</h2>
      <p><?php if ($role === 'siswa'): ?>
        <?= $profil && $profil['nama_kelas'] ? 'Kelas ' . e($profil['nama_kelas']) . ' · TA ' . e($profil['tahun_ajaran']) . ' · NIS ' . e($profil['nis']) : 'Anda belum ditempatkan di kelas. Hubungi admin sekolah.' ?>
      <?php else: ?>Berikut ringkasan data sekolah hari ini.<?php endif; ?></p>
    </div>
    <?php if ($role === 'siswa' && $rata !== null): ?>
      <div class="hero-score"><small>Rata-rata Nilai</small><b><?= number_format($rata, 1) ?></b></div>
    <?php endif; ?>
  </section>

  <section class="stats">
    <div class="stat blue"><span class="ico">👨‍🎓</span><div><b><?= (int)$stat['siswa'] ?></b><small>Total Siswa</small></div></div>
    <div class="stat green"><span class="ico">👩‍🏫</span><div><b><?= (int)$stat['guru'] ?></b><small>Total Guru</small></div></div>
    <div class="stat orange"><span class="ico">🏫</span><div><b><?= (int)$stat['kelas'] ?></b><small>Jumlah Kelas</small></div></div>
    <div class="stat purple"><span class="ico">📚</span><div><b><?= (int)$stat['mapel'] ?></b><small>Mata Pelajaran</small></div></div>
  </section>

  <div class="grid">
    <section class="panel">
      <h3>📢 Pengumuman Terbaru</h3>
      <?php if (!$pengumuman): ?><p class="empty">Belum ada pengumuman.</p><?php endif; ?>
      <?php foreach ($pengumuman as $p): ?>
        <article class="news">
          <div class="news-date"><b><?= date('j', strtotime($p['created_at'])) ?></b><small><?= e(substr($bulan[(int)date('n', strtotime($p['created_at']))], 0, 3)) ?></small></div>
          <div><h4><?= e($p['judul']) ?></h4><p><?= nl2br(e($p['isi'])) ?></p><small>Oleh <?= e($p['username']) ?></small></div>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="panel">
      <?php if ($role === 'siswa'): ?>
        <h3>📝 Nilai Saya</h3>
        <?php if (!$nilai): ?><p class="empty">Belum ada nilai yang diinput.</p>
        <?php else: ?>
        <table>
          <tr><th>Mata Pelajaran</th><th>Smt</th><th>Nilai</th></tr>
          <?php foreach ($nilai as $n): $v = (float)$n['nilai_angka']; ?>
            <tr><td><?= e($n['nama_mapel']) ?></td><td><?= (int)$n['semester'] ?></td>
                <td><span class="score <?= $v >= 75 ? 'ok' : 'low' ?>"><?= number_format($v, 1) ?></span></td></tr>
          <?php endforeach; ?>
        </table>
        <?php endif; ?>
      <?php else: ?>
        <h3>👨‍🎓 Siswa Terbaru</h3>
        <?php if (!$siswaBaru): ?><p class="empty">Belum ada data siswa.</p>
        <?php else: ?>
        <table>
          <tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Terdaftar</th></tr>
          <?php foreach ($siswaBaru as $s): ?>
            <tr><td><?= e($s['nis']) ?></td><td><?= e($s['nama']) ?></td><td><?= e($s['kelas']) ?></td><td><?= e(tglID($s['created_at'], $bulan)) ?></td></tr>
          <?php endforeach; ?>
        </table>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>

  <?php if ($role === 'admin'): ?>
  <section class="panel">
    <h3>🛡️ Aktivitas Sistem Terbaru (Audit Trail)</h3>
    <table>
      <tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>IP</th></tr>
      <?php foreach ($logs as $l): ?>
        <tr><td><?= e($l['waktu']) ?></td><td><?= e($l['username']) ?></td>
            <td><span class="tag <?= str_contains($l['aksi'], 'GAGAL') ? 'bad' : 'good' ?>"><?= e($l['aksi']) ?></span></td>
            <td><?= e($l['ip_addr'] ?? '-') ?></td></tr>
      <?php endforeach; ?>
    </table>
  </section>
  <?php endif; ?>

  <footer>© <?= date('Y') ?> SMA Nusantara · Data dilindungi dengan hashing password, HTTPS, dan audit log</footer>
</main>
</body>
</html>
