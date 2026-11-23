<?php
require __DIR__ . '/config.php';
require __DIR__ . '/includes/layout.php';
$n = $pdo->query('SELECT (SELECT COUNT(*) FROM siswa) AS siswa, (SELECT COUNT(*) FROM guru) AS guru,
                         (SELECT COUNT(*) FROM kelas) AS kelas, (SELECT COUNT(*) FROM mapel) AS mapel')->fetch();
$logged = !empty($_SESSION['id_user']);
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Beranda | <?= e(NAMA_SEKOLAH) ?></title><link rel="stylesheet" href="assets/portal.css"></head>
<body class="landing">
<header class="nav">
  <div class="brand"><span class="logo">🎓</span><div><b><?= e(NAMA_SEKOLAH) ?></b><small>Sistem Informasi Sekolah</small></div></div>
  <div class="nav-links"><a href="#fitur">Fitur</a><a href="#info">Pengumuman</a>
    <?php if ($logged): ?><a class="btn" href="dashboard.php">Ke Dashboard</a><?php else: ?><a href="login.php?as=admin">Admin</a><?php endif; ?></div>
</header>

<section class="land-hero">
  <h1>Selamat Datang di <?= e(NAMA_SEKOLAH) ?></h1>
  <p>Portal terpadu untuk guru dan siswa: lihat jadwal, input dan pantau nilai, serta ikuti pengumuman sekolah secara aman dan online.</p>
</section>

<section class="roles">
  <a class="role-card g" href="login.php?as=guru"><div class="big">👩‍🏫</div><h3>Masuk sebagai Guru</h3><p>Input nilai, lihat data siswa, dan jadwal mengajar.</p><span class="btn">Login Guru</span></a>
  <a class="role-card s" href="login.php?as=siswa"><div class="big">👨‍🎓</div><h3>Masuk sebagai Siswa</h3><p>Lihat nilai rapor, jadwal pelajaran, dan pengumuman.</p><span class="btn">Login Siswa</span></a>
</section>
<p style="text-align:center" class="muted">Belum punya akun siswa? <a class="link" href="register.php">Daftar di sini</a></p>

<section class="section"><div class="pubstats">
  <div><b><?= (int)$n['siswa'] ?></b>Siswa</div><div><b><?= (int)$n['guru'] ?></b>Guru</div>
  <div><b><?= (int)$n['kelas'] ?></b>Kelas</div><div><b><?= (int)$n['mapel'] ?></b>Mata Pelajaran</div></div></section>

<section class="section" id="fitur"><h2>Fitur Unggulan</h2><div class="feat">
  <div><span>📝</span><h4>Nilai Online</h4><p>Guru menginput nilai, siswa langsung melihat rapor lengkap dengan predikat.</p></div>
  <div><span>📅</span><h4>Jadwal Digital</h4><p>Jadwal mengajar dan pelajaran selalu terbarui, hari ini otomatis disorot.</p></div>
  <div><span>🔐</span><h4>Aman & Terpercaya</h4><p>Password di-hash, anti SQL injection, audit log, dan backup rutin.</p></div></div></section>

<section class="section" id="info"><?php
  $st = $pdo->query('SELECT judul, isi, created_at FROM pengumuman ORDER BY created_at DESC, id_pengumuman DESC LIMIT 3')->fetchAll();
  echo '<h2>Pengumuman Terbaru</h2>';
  foreach ($st as $r) { [$d, $m] = tgl_pendek($r['created_at']);
    echo '<section class="panel"><article class="news"><div class="news-date"><b>' . $d . '</b><small>' . e($m) . '</small></div><div><h4>' . e($r['judul']) . '</h4><p>' . nl2br(e($r['isi'])) . '</p></div></article></section>'; }
  if (!$st) echo '<p class="empty" style="text-align:center">Belum ada pengumuman.</p>';
?></section>
<footer>© <?= date('Y') ?> <?= e(NAMA_SEKOLAH) ?> · Dibangun dengan PHP & MySQL</footer>
</body></html>
