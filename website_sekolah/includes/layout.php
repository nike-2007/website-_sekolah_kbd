<?php
// Tampilan bersama untuk portal guru & siswa
const NAMA_SEKOLAH = 'SMA Nusantara';

function hari_ini(): string { return ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][(int)date('w')]; }
function tgl_lengkap(): string {
    $b = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return hari_ini() . ', ' . date('j') . ' ' . $b[(int)date('n')] . ' ' . date('Y');
}
function tgl_pendek(string $t): array {
    $b = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $x = strtotime($t); return [date('j', $x), $b[(int)date('n', $x)]];
}
function salam(): string { $h = (int)date('G'); return $h < 11 ? 'Selamat pagi' : ($h < 15 ? 'Selamat siang' : ($h < 18 ? 'Selamat sore' : 'Selamat malam')); }
function predikat(float $v): array { return $v >= 90 ? ['A','a'] : ($v >= 80 ? ['B','b'] : ($v >= 70 ? ['C','c'] : ['D','d'])); }
function jam(string $t): string { return substr($t, 0, 5); }

function bar(string $label, float $v): void {
    $w = max(0, min(100, $v));
    echo '<div class="bar"><div class="bar-top"><span>' . e($label) . '</span><b>' . number_format($v, 1) . '</b></div>'
       . '<div class="bar-track"><div class="bar-fill" style="width:' . $w . '%"></div></div></div>';
}

// Jadwal mingguan: $rows = hari, jam_mulai, jam_selesai, ruang, nama_mapel, ket
function render_jadwal(array $rows): void {
    $hari = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $per = array_fill_keys($hari, []);
    foreach ($rows as $r) $per[$r['hari']][] = $r;
    echo '<div class="week">';
    foreach ($hari as $h) {
        echo '<section class="day' . ($h === hari_ini() ? ' today' : '') . '"><h4>' . $h . ($h === hari_ini() ? ' <small>Hari ini</small>' : '') . '</h4>';
        if (!$per[$h]) echo '<p class="empty">Tidak ada jadwal</p>';
        foreach ($per[$h] as $r) {
            echo '<div class="slot"><div class="time">' . jam($r['jam_mulai']) . '<br><small>' . jam($r['jam_selesai']) . '</small></div>'
               . '<div><b>' . e($r['nama_mapel']) . '</b><small>' . e($r['ket']) . ' · ' . e($r['ruang']) . '</small></div></div>';
        }
        echo '</section>';
    }
    echo '</div>';
}

function page_start(string $title, string $active, array $p): void {
    $role = $_SESSION['role'];
    $menu = $role === 'guru'
        ? ['index' => ['🏠','Dashboard','index.php'], 'siswa' => ['👨‍🎓','Data Siswa','siswa.php'], 'nilai' => ['📝','Input Nilai','nilai.php'], 'jadwal' => ['📅','Jadwal Mengajar','jadwal.php']]
        : ['index' => ['🏠','Dashboard','index.php'], 'nilai' => ['📝','Nilai & Rapor','nilai.php'], 'jadwal' => ['📅','Jadwal Pelajaran','jadwal.php']];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | <?= e(NAMA_SEKOLAH) ?></title>
<link rel="stylesheet" href="../assets/portal.css">
</head>
<body class="portal theme-<?= e($role) ?>">
<aside class="sidebar">
  <div class="brand"><span class="logo">🎓</span><div><b><?= e(NAMA_SEKOLAH) ?></b><small>Portal <?= $role === 'guru' ? 'Guru' : 'Siswa' ?></small></div></div>
  <nav>
    <p class="nav-title">MENU</p>
    <?php foreach ($menu as $k => [$ico, $label, $href]): ?>
      <a href="<?= e($href) ?>" class="<?= $k === $active ? 'active' : '' ?>"><?= $ico ?> <?= e($label) ?></a>
    <?php endforeach; ?>
    <p class="nav-title">AKUN</p>
    <a href="../index.php">🌐 Beranda Sekolah</a>
    <a href="../logout.php">🚪 Keluar</a>
  </nav>
</aside>
<main class="main">
  <header class="topbar">
    <div><h1><?= e($title) ?></h1><span class="date">📅 <?= e(tgl_lengkap()) ?></span></div>
    <div class="user">
      <div class="who"><b><?= e($p['nama']) ?></b><span class="badge"><?= $role === 'guru' ? 'Guru' : 'Siswa' ?></span></div>
      <div class="avatar"><?= e(strtoupper(mb_substr($p['nama'], 0, 1))) ?></div>
    </div>
  </header>
<?php
}

function page_end(): void { ?>
  <footer>© <?= date('Y') ?> <?= e(NAMA_SEKOLAH) ?> · Data dilindungi hashing password, HTTPS, dan audit log</footer>
</main>
</body>
</html>
<?php }

function panel_pengumuman(PDO $pdo, int $limit = 4): void {
    $rows = $pdo->query('SELECT judul, isi, created_at FROM pengumuman ORDER BY created_at DESC, id_pengumuman DESC LIMIT ' . (int)$limit)->fetchAll();
    echo '<section class="panel"><h3>📢 Pengumuman Sekolah</h3>';
    if (!$rows) echo '<p class="empty">Belum ada pengumuman.</p>';
    foreach ($rows as $r) {
        [$d, $m] = tgl_pendek($r['created_at']);
        echo '<article class="news"><div class="news-date"><b>' . $d . '</b><small>' . e($m) . '</small></div><div><h4>'
           . e($r['judul']) . '</h4><p>' . nl2br(e($r['isi'])) . '</p></div></article>';
    }
    echo '</section>';
}
