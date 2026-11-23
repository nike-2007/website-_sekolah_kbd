<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$g = require_role('guru'); $idGuru = (int)$g['id_guru'];

$kelasList = $pdo->query('SELECT id_kelas, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll();
$mapelList = $pdo->query('SELECT id_mapel, nama_mapel FROM mapel ORDER BY nama_mapel')->fetchAll();
$idKelas = (int)($_REQUEST['kelas'] ?? 0);
$idMapel = (int)($_REQUEST['mapel'] ?? 0);
$smt     = (int)($_REQUEST['semester'] ?? 1); if (!in_array($smt, [1, 2], true)) $smt = 1;
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->beginTransaction();            // semua nilai tersimpan, atau tidak sama sekali (atomicity)
    try {
        $cek = $pdo->prepare('SELECT 1 FROM siswa WHERE id_siswa = ? AND id_kelas = ?');
        $up  = $pdo->prepare('INSERT INTO nilai (id_siswa, id_mapel, id_guru, semester, nilai_angka) VALUES (?,?,?,?,?)
                              ON DUPLICATE KEY UPDATE nilai_angka = VALUES(nilai_angka), id_guru = VALUES(id_guru)');
        $n = 0;
        foreach (($_POST['nilai'] ?? []) as $idS => $v) {
            $v = trim((string)$v);
            if ($v === '') continue;
            if (!is_numeric($v) || (float)$v < 0 || (float)$v > 100) throw new InvalidArgumentException('Nilai harus berupa angka 0 - 100.');
            $cek->execute([(int)$idS, $idKelas]);
            if (!$cek->fetch()) continue;      // abaikan siswa yang bukan anggota kelas ini
            $up->execute([(int)$idS, $idMapel, $idGuru, $smt, round((float)$v, 2)]);
            $n++;
        }
        $pdo->commit();
        audit($pdo, (int)$_SESSION['id_user'], 'INPUT_NILAI:' . $n . ' data');
        $msg = $n . ' nilai berhasil disimpan.';
    } catch (Throwable $x) {
        $pdo->rollBack();
        $err = $x instanceof InvalidArgumentException ? $x->getMessage() : 'Gagal menyimpan nilai.';
    }
}

$siswa = [];
if ($idKelas && $idMapel) {
    $st = $pdo->prepare('SELECT s.id_siswa, s.nis, s.nama, n.nilai_angka FROM siswa s
        LEFT JOIN nilai n ON n.id_siswa = s.id_siswa AND n.id_mapel = ? AND n.semester = ?
        WHERE s.id_kelas = ? ORDER BY s.nama');
    $st->execute([$idMapel, $smt, $idKelas]);
    $siswa = $st->fetchAll();
}
page_start('Input Nilai', 'nilai', $g);
?>
<section class="panel">
  <h3>1. Pilih Kelas, Mata Pelajaran, dan Semester</h3>
  <form class="filter" method="get">
    <select name="kelas" required><option value="">- Kelas -</option>
      <?php foreach ($kelasList as $x): ?><option value="<?= (int)$x['id_kelas'] ?>" <?= $idKelas === (int)$x['id_kelas'] ? 'selected' : '' ?>><?= e($x['nama_kelas']) ?></option><?php endforeach; ?></select>
    <select name="mapel" required><option value="">- Mata Pelajaran -</option>
      <?php foreach ($mapelList as $x): ?><option value="<?= (int)$x['id_mapel'] ?>" <?= $idMapel === (int)$x['id_mapel'] ? 'selected' : '' ?>><?= e($x['nama_mapel']) ?></option><?php endforeach; ?></select>
    <select name="semester"><option value="1" <?= $smt === 1 ? 'selected' : '' ?>>Semester 1</option><option value="2" <?= $smt === 2 ? 'selected' : '' ?>>Semester 2</option></select>
    <button class="btn">Tampilkan Siswa</button>
  </form>
</section>

<?php if ($msg): ?><p class="alert ok">✅ <?= e($msg) ?></p><?php endif; ?>
<?php if ($err): ?><p class="alert bad">⚠️ <?= e($err) ?></p><?php endif; ?>

<?php if ($idKelas && $idMapel): ?>
<section class="panel">
  <h3>2. Isi Nilai (0 - 100)</h3>
  <?php if (!$siswa): ?><p class="empty">Belum ada siswa di kelas ini.</p><?php else: ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="kelas" value="<?= $idKelas ?>"><input type="hidden" name="mapel" value="<?= $idMapel ?>"><input type="hidden" name="semester" value="<?= $smt ?>">
    <table><tr><th>NIS</th><th>Nama Siswa</th><th style="width:140px">Nilai</th></tr>
    <?php foreach ($siswa as $s): ?>
      <tr><td><?= e($s['nis']) ?></td><td><?= e($s['nama']) ?></td>
          <td><input class="num" type="number" step="0.01" min="0" max="100" name="nilai[<?= (int)$s['id_siswa'] ?>]" value="<?= e((string)($s['nilai_angka'] ?? '')) ?>"></td></tr>
    <?php endforeach; ?></table>
    <p><button class="btn">💾 Simpan Semua Nilai</button></p>
  </form><?php endif; ?>
</section>
<?php endif; ?>
<?php page_end();
