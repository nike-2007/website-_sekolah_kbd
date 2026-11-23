<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$g = require_role('guru'); $id = (int)$g['id_guru'];

function ambil(PDO $pdo, string $sql, array $par): array { $st = $pdo->prepare($sql); $st->execute($par); return $st->fetchAll(); }

$s1 = ambil($pdo, 'SELECT COUNT(DISTINCT id_kelas) AS kelas, COUNT(DISTINCT id_mapel) AS mapel FROM jadwal WHERE id_guru = ?', [$id])[0];
$totalSiswa = (int)ambil($pdo, 'SELECT COUNT(*) AS n FROM siswa WHERE id_kelas IN (SELECT id_kelas FROM jadwal WHERE id_guru = ?)', [$id])[0]['n'];
$hariIni = ambil($pdo, 'SELECT j.jam_mulai, j.jam_selesai, j.ruang, m.nama_mapel, k.nama_kelas AS ket
    FROM jadwal j JOIN mapel m ON m.id_mapel = j.id_mapel JOIN kelas k ON k.id_kelas = j.id_kelas
    WHERE j.id_guru = ? AND j.hari = ? ORDER BY j.jam_mulai', [$id, hari_ini()]);
$rataMapel = ambil($pdo, 'SELECT m.nama_mapel, AVG(n.nilai_angka) AS rata, COUNT(*) AS jml
    FROM nilai n JOIN mapel m ON m.id_mapel = n.id_mapel WHERE n.id_guru = ? GROUP BY m.id_mapel, m.nama_mapel', [$id]);
$terbaru = ambil($pdo, 'SELECT s.nama, m.nama_mapel, n.nilai_angka, n.semester
    FROM nilai n JOIN siswa s ON s.id_siswa = n.id_siswa JOIN mapel m ON m.id_mapel = n.id_mapel
    WHERE n.id_guru = ? ORDER BY n.id_nilai DESC LIMIT 5', [$id]);

page_start('Dashboard Guru', 'index', $g);
?>
<section class="hero">
  <div>
    <h2><?= e(salam()) ?>, <?= e($g['nama']) ?>! 👋</h2>
    <p>NIP <?= e($g['nip']) ?> · <?= count($hariIni) ? 'Hari ini Anda mengajar ' . count($hariIni) . ' sesi.' : 'Tidak ada jadwal mengajar hari ini. Waktu yang pas untuk menginput nilai.' ?></p>
  </div>
  <div class="hero-actions"><a class="btn light" href="nilai.php">📝 Input Nilai</a><a class="btn ghost" href="siswa.php">👨‍🎓 Data Siswa</a></div>
</section>

<section class="stats">
  <div class="stat c1"><span class="ico">🏫</span><div><b><?= (int)$s1['kelas'] ?></b><small>Kelas Diajar</small></div></div>
  <div class="stat c2"><span class="ico">📚</span><div><b><?= (int)$s1['mapel'] ?></b><small>Mata Pelajaran</small></div></div>
  <div class="stat c3"><span class="ico">👨‍🎓</span><div><b><?= $totalSiswa ?></b><small>Total Siswa</small></div></div>
  <div class="stat c4"><span class="ico">⏰</span><div><b><?= count($hariIni) ?></b><small>Sesi Hari Ini (<?= e(hari_ini()) ?>)</small></div></div>
</section>

<div class="grid">
  <section class="panel">
    <h3>⏰ Jadwal Mengajar Hari Ini</h3>
    <?php if (!$hariIni): ?><p class="empty">Tidak ada jadwal hari ini 🎉</p><?php endif; ?>
    <?php foreach ($hariIni as $r): ?>
      <div class="slot"><div class="time"><?= e(jam($r['jam_mulai'])) ?><br><small><?= e(jam($r['jam_selesai'])) ?></small></div>
      <div><b><?= e($r['nama_mapel']) ?></b><small>Kelas <?= e($r['ket']) ?> · <?= e($r['ruang']) ?></small></div></div>
    <?php endforeach; ?>
  </section>
  <section class="panel">
    <h3>📊 Rata-rata Nilai per Mata Pelajaran</h3>
    <?php if (!$rataMapel): ?><p class="empty">Belum ada nilai yang Anda input.</p><?php endif; ?>
    <?php foreach ($rataMapel as $r) bar($r['nama_mapel'] . ' (' . $r['jml'] . ' nilai)', (float)$r['rata']); ?>
  </section>
</div>

<div class="grid">
  <?php panel_pengumuman($pdo, 3); ?>
  <section class="panel">
    <h3>📝 Nilai yang Anda Input</h3>
    <?php if (!$terbaru): ?><p class="empty">Belum ada data.</p><?php else: ?>
    <table><tr><th>Siswa</th><th>Mapel</th><th>Smt</th><th>Nilai</th></tr>
    <?php foreach ($terbaru as $t): [$pr, $cl] = predikat((float)$t['nilai_angka']); ?>
      <tr><td><?= e($t['nama']) ?></td><td><?= e($t['nama_mapel']) ?></td><td><?= (int)$t['semester'] ?></td>
          <td><span class="pill <?= $cl ?>"><?= number_format((float)$t['nilai_angka'], 1) ?> · <?= $pr ?></span></td></tr>
    <?php endforeach; ?></table><?php endif; ?>
  </section>
</div>
<?php page_end();
