<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$g = require_role('guru');

$q = trim($_GET['q'] ?? ''); $k = (int)($_GET['kelas'] ?? 0);
$kelasList = $pdo->query('SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll();

$sql = 'SELECT s.nis, s.nama, s.jenis_kelamin, COALESCE(k.nama_kelas, "-") AS kelas, AVG(n.nilai_angka) AS rata
          FROM siswa s LEFT JOIN kelas k ON k.id_kelas = s.id_kelas
          LEFT JOIN nilai n ON n.id_siswa = s.id_siswa WHERE 1 = 1';
$par = [];
if ($q !== '') { $sql .= ' AND (s.nama LIKE ? OR s.nis LIKE ?)'; $par[] = "%$q%"; $par[] = "%$q%"; }   // prepared: aman dari SQL injection
if ($k)        { $sql .= ' AND s.id_kelas = ?'; $par[] = $k; }
$sql .= ' GROUP BY s.id_siswa, s.nis, s.nama, s.jenis_kelamin, k.nama_kelas ORDER BY k.nama_kelas, s.nama';
$st = $pdo->prepare($sql); $st->execute($par); $rows = $st->fetchAll();

page_start('Data Siswa', 'siswa', $g);
?>
<section class="panel">
  <form class="filter" method="get">
    <input name="q" placeholder="🔍 Cari nama atau NIS..." value="<?= e($q) ?>">
    <select name="kelas"><option value="0">Semua Kelas</option>
      <?php foreach ($kelasList as $kl): ?><option value="<?= (int)$kl['id_kelas'] ?>" <?= $k === (int)$kl['id_kelas'] ? 'selected' : '' ?>><?= e($kl['nama_kelas']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn">Tampilkan</button>
  </form>
  <p class="muted"><?= count($rows) ?> siswa ditemukan</p>
  <?php if (!$rows): ?><p class="empty">Tidak ada siswa yang cocok.</p><?php else: ?>
  <table><tr><th>NIS</th><th>Nama</th><th>L/P</th><th>Kelas</th><th>Rata-rata Nilai</th></tr>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['nis']) ?></td><td><b><?= e($r['nama']) ?></b></td><td><?= e($r['jenis_kelamin'] ?? '-') ?></td><td><?= e($r['kelas']) ?></td>
        <td><?php if ($r['rata'] !== null): [$pr, $cl] = predikat((float)$r['rata']); ?><span class="pill <?= $cl ?>"><?= number_format((float)$r['rata'], 1) ?> · <?= $pr ?></span><?php else: ?>-<?php endif; ?></td></tr>
  <?php endforeach; ?></table><?php endif; ?>
</section>
<?php page_end();
