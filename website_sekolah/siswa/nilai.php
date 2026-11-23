<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$s = require_role('siswa');
$st = $pdo->prepare('SELECT n.semester, m.nama_mapel, g.nama AS guru, n.nilai_angka
    FROM nilai n JOIN mapel m ON m.id_mapel = n.id_mapel JOIN guru g ON g.id_guru = n.id_guru
    WHERE n.id_siswa = ? ORDER BY n.semester, m.nama_mapel');
$st->execute([(int)$s['id_siswa']]);
$per = [];
foreach ($st->fetchAll() as $r) $per[(int)$r['semester']][] = $r;

page_start('Nilai & Rapor', 'nilai', $s);
if (!$per) echo '<section class="panel"><p class="empty">Belum ada nilai yang diinput guru.</p></section>';
foreach ($per as $smt => $rows):
    $vals = array_map(fn($r) => (float)$r['nilai_angka'], $rows);
    $rata = array_sum($vals) / count($vals); [$pr, $cl] = predikat($rata);
?>
<section class="panel">
  <div class="panel-head"><h3>📘 Semester <?= $smt ?></h3>
    <div><span class="pill <?= $cl ?>">Rata-rata <?= number_format($rata, 1) ?> · <?= $pr ?></span>
    <span class="pill">Tertinggi <?= number_format(max($vals), 1) ?></span> <span class="pill">Terendah <?= number_format(min($vals), 1) ?></span></div></div>
  <table><tr><th>Mata Pelajaran</th><th>Guru</th><th>Nilai</th><th>Predikat</th><th>Keterangan</th></tr>
  <?php foreach ($rows as $r): $v = (float)$r['nilai_angka']; [$p2, $c2] = predikat($v); ?>
    <tr><td><b><?= e($r['nama_mapel']) ?></b></td><td><?= e($r['guru']) ?></td><td><?= number_format($v, 1) ?></td>
        <td><span class="pill <?= $c2 ?>"><?= $p2 ?></span></td><td><?= $v >= 75 ? '✅ Tuntas' : '⚠️ Remedial' ?></td></tr>
  <?php endforeach; ?></table>
</section>
<?php endforeach; page_end();
