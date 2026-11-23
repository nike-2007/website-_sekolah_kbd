<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$s = require_role('siswa'); $id = (int)$s['id_siswa'];

function ambil(PDO $pdo, string $sql, array $par): array { $st = $pdo->prepare($sql); $st->execute($par); return $st->fetchAll(); }

$ringkas = ambil($pdo, 'SELECT AVG(nilai_angka) AS rata, MAX(nilai_angka) AS maks, COUNT(DISTINCT id_mapel) AS mapel, MAX(semester) AS smt FROM nilai WHERE id_siswa = ?', [$id])[0];
$smt = (int)($ringkas['smt'] ?? 0);
$nilaiSmt = $smt ? ambil($pdo, 'SELECT m.nama_mapel, n.nilai_angka FROM nilai n JOIN mapel m ON m.id_mapel = n.id_mapel
                     WHERE n.id_siswa = ? AND n.semester = ? ORDER BY n.nilai_angka DESC', [$id, $smt]) : [];
$hariIni = ambil($pdo, 'SELECT j.jam_mulai, j.jam_selesai, j.ruang, m.nama_mapel, g.nama AS ket
    FROM jadwal j JOIN mapel m ON m.id_mapel = j.id_mapel JOIN guru g ON g.id_guru = j.id_guru
    WHERE j.id_kelas = ? AND j.hari = ? ORDER BY j.jam_mulai', [$s['id_kelas'], hari_ini()]);
$rata = $ringkas['rata'] !== null ? (float)$ringkas['rata'] : null;

page_start('Dashboard Siswa', 'index', $s);
?>
<section class="hero">
  <div>
    <h2><?= e(salam()) ?>, <?= e($s['nama']) ?>! 👋</h2>
    <p><?= $s['nama_kelas'] ? 'Kelas ' . e($s['nama_kelas']) . ' · TA ' . e($s['tahun_ajaran']) : 'Belum ditempatkan di kelas' ?> · NIS <?= e($s['nis']) ?></p>
    <p><?= count($hariIni) ? 'Hari ini ada ' . count($hariIni) . ' pelajaran. Semangat belajar!' : 'Tidak ada pelajaran hari ini.' ?></p>
  </div>
  <?php if ($rata !== null): [$pr] = predikat($rata); ?>
    <div class="hero-score"><small>Rata-rata Nilai</small><b><?= number_format($rata, 1) ?></b><span>Predikat <?= $pr ?></span></div>
  <?php endif; ?>
</section>

<section class="stats">
  <div class="stat c1"><span class="ico">⭐</span><div><b><?= $rata !== null ? number_format($rata, 1) : '-' ?></b><small>Rata-rata Nilai</small></div></div>
  <div class="stat c2"><span class="ico">🏆</span><div><b><?= $ringkas['maks'] !== null ? number_format((float)$ringkas['maks'], 1) : '-' ?></b><small>Nilai Tertinggi</small></div></div>
  <div class="stat c3"><span class="ico">📚</span><div><b><?= (int)$ringkas['mapel'] ?></b><small>Mapel Dinilai</small></div></div>
  <div class="stat c4"><span class="ico">⏰</span><div><b><?= count($hariIni) ?></b><small>Pelajaran Hari Ini</small></div></div>
</section>

<div class="grid">
  <section class="panel">
    <h3>⏰ Jadwal Hari Ini (<?= e(hari_ini()) ?>)</h3>
    <?php if (!$hariIni): ?><p class="empty">Tidak ada pelajaran hari ini 🎉</p><?php endif; ?>
    <?php foreach ($hariIni as $r): ?>
      <div class="slot"><div class="time"><?= e(jam($r['jam_mulai'])) ?><br><small><?= e(jam($r['jam_selesai'])) ?></small></div>
      <div><b><?= e($r['nama_mapel']) ?></b><small><?= e($r['ket']) ?> · <?= e($r['ruang']) ?></small></div></div>
    <?php endforeach; ?>
  </section>
  <section class="panel">
    <h3>📊 Nilai Semester <?= $smt ?: '-' ?></h3>
    <?php if (!$nilaiSmt): ?><p class="empty">Belum ada nilai.</p><?php endif; ?>
    <?php foreach ($nilaiSmt as $n) bar($n['nama_mapel'], (float)$n['nilai_angka']); ?>
    <p><a class="link" href="nilai.php">Lihat rapor lengkap →</a></p>
  </section>
</div>
<div class="grid"><?php panel_pengumuman($pdo, 3); ?>
  <section class="panel profile">
    <h3>🪪 Kartu Pelajar</h3>
    <div class="idcard"><div class="avatar big"><?= e(strtoupper(mb_substr($s['nama'], 0, 1))) ?></div>
      <div><b><?= e($s['nama']) ?></b><small>NIS <?= e($s['nis']) ?></small><small>Kelas <?= e($s['nama_kelas'] ?? '-') ?></small>
      <small>Jenis kelamin: <?= e($s['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($s['jenis_kelamin'] === 'P' ? 'Perempuan' : '-')) ?></small></div></div>
  </section>
</div>
<?php page_end();
