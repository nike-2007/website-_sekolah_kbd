<?php
// Jalankan lewat terminal:  php seed.php   (aman dijalankan berulang kali)
if (PHP_SAPI !== 'cli') exit('Hanya boleh dijalankan lewat terminal.');
require __DIR__ . '/config.php';

function buatUser(PDO $pdo, string $u, string $p, string $r): int {
    $st = $pdo->prepare('SELECT id_user FROM users WHERE username = ?');
    $st->execute([$u]);
    $id = $st->fetchColumn();
    if ($id) return (int)$id;
    $pdo->prepare('INSERT INTO users (username, password_hash, role) VALUES (?,?,?)')
        ->execute([$u, password_hash($p, PASSWORD_DEFAULT), $r]);
    return (int)$pdo->lastInsertId();
}
function satuNilai(PDO $pdo, string $sql, array $par = []) { $st = $pdo->prepare($sql); $st->execute($par); return $st->fetchColumn(); }

$pdo->beginTransaction();
try {
    $admin = buatUser($pdo, 'admin', 'Admin#2026', 'admin');

    // --- Guru ---
    $guru = [
        ['guru01', '198001012005011001', 'Budi Santoso', 'budi@sman-nusantara.sch.id'],
        ['guru02', '198507152010012002', 'Rina Wijaya',  'rina@sman-nusantara.sch.id'],
    ];
    $idGuru = [];
    foreach ($guru as [$u, $nip, $nama, $email]) {
        $uid = buatUser($pdo, $u, 'Guru#2026', 'guru');
        $pdo->prepare('INSERT IGNORE INTO guru (id_user, nip, nama, email) VALUES (?,?,?,?)')->execute([$uid, $nip, $nama, $email]);
        $idGuru[$u] = (int)satuNilai($pdo, 'SELECT id_guru FROM guru WHERE id_user = ?', [$uid]);
    }

    // --- Referensi kelas & mapel ---
    $kelas = [];  foreach ($pdo->query('SELECT id_kelas, nama_kelas FROM kelas') as $r) $kelas[$r['nama_kelas']] = (int)$r['id_kelas'];
    $mapel = [];  foreach ($pdo->query('SELECT id_mapel, kode_mapel FROM mapel') as $r) $mapel[$r['kode_mapel']] = (int)$r['id_mapel'];
    $guruMapel = ['MTK' => 'guru01', 'BIG' => 'guru01', 'BIN' => 'guru02', 'INF' => 'guru02'];

    // --- Siswa ---
    $siswa = [
        ['siswa01', '2026001', 'Siti Aminah',   'P', 'X IPA 1'],
        ['siswa02', '2026002', 'Ahmad Fauzi',   'L', 'X IPA 1'],
        ['siswa03', '2026003', 'Dewi Lestari',  'P', 'X IPA 1'],
        ['siswa04', '2026004', 'Rizky Pratama', 'L', 'X IPA 1'],
        ['siswa05', '2026005', 'Nadia Putri',   'P', 'X IPA 1'],
        ['siswa06', '2026006', 'Bagas Saputra', 'L', 'X IPS 1'],
    ];
    $idSiswa = [];
    foreach ($siswa as [$u, $nis, $nama, $jk, $kls]) {
        $uid = buatUser($pdo, $u, 'Siswa#2026', 'siswa');
        $pdo->prepare('INSERT IGNORE INTO siswa (id_user, id_kelas, nis, nama, jenis_kelamin) VALUES (?,?,?,?,?)')
            ->execute([$uid, $kelas[$kls], $nis, $nama, $jk]);
        $pdo->prepare('UPDATE siswa SET id_kelas = ? WHERE id_user = ? AND id_kelas IS NULL')->execute([$kelas[$kls], $uid]);
        $idSiswa[] = (int)satuNilai($pdo, 'SELECT id_siswa FROM siswa WHERE id_user = ?', [$uid]);
    }

    // --- Nilai (semester 1 & 2) ---
    mt_srand(2026);
    $ins = $pdo->prepare('INSERT IGNORE INTO nilai (id_siswa, id_mapel, id_guru, semester, nilai_angka) VALUES (?,?,?,?,?)');
    foreach ($idSiswa as $s) foreach ($guruMapel as $kode => $g) foreach ([1, 2] as $smt)
        $ins->execute([$s, $mapel[$kode], $idGuru[$g], $smt, mt_rand(660, 980) / 10]);

    // --- Jadwal ---
    if ((int)$pdo->query('SELECT COUNT(*) FROM jadwal')->fetchColumn() === 0) {
        $jd = [
            ['X IPA 1','Senin','07:00','08:30','MTK','R.101'], ['X IPA 1','Senin','08:30','10:00','BIN','R.101'], ['X IPA 1','Senin','10:15','11:45','INF','Lab Komputer'],
            ['X IPA 1','Selasa','07:00','08:30','BIG','R.101'], ['X IPA 1','Selasa','08:30','10:00','MTK','R.101'], ['X IPA 1','Selasa','10:15','11:45','BIN','R.101'],
            ['X IPA 1','Rabu','07:00','09:15','INF','Lab Komputer'], ['X IPA 1','Rabu','09:30','11:00','MTK','R.101'],
            ['X IPA 1','Kamis','07:00','08:30','BIN','R.101'], ['X IPA 1','Kamis','08:30','10:00','BIG','R.101'], ['X IPA 1','Kamis','10:15','11:45','MTK','R.101'],
            ['X IPA 1','Jumat','07:00','08:30','INF','Lab Komputer'], ['X IPA 1','Jumat','08:30','10:00','BIG','R.101'],
            ['X IPS 1','Senin','07:00','08:30','MTK','R.201'], ['X IPS 1','Rabu','07:00','08:30','BIN','R.201'], ['X IPS 1','Kamis','10:15','11:45','INF','Lab Komputer'],
        ];
        $q = $pdo->prepare('INSERT INTO jadwal (id_kelas,id_mapel,id_guru,hari,jam_mulai,jam_selesai,ruang) VALUES (?,?,?,?,?,?,?)');
        foreach ($jd as [$k, $h, $a, $b, $m, $r]) $q->execute([$kelas[$k], $mapel[$m], $idGuru[$guruMapel[$m]], $h, $a, $b, $r]);
    }

    // --- Pengumuman ---
    if ((int)$pdo->query('SELECT COUNT(*) FROM pengumuman')->fetchColumn() === 0) {
        $p = $pdo->prepare('INSERT INTO pengumuman (id_user, judul, isi) VALUES (?,?,?)');
        $p->execute([$admin, 'Selamat Datang di Website Sekolah', 'Website resmi SMA Nusantara kini aktif. Guru dan siswa dapat masuk melalui menu login masing-masing.']);
        $p->execute([$admin, 'Jadwal Ujian Tengah Semester', "UTS semester ganjil dimulai minggu depan.\nSiswa wajib membawa kartu ujian dan alat tulis."]);
        $p->execute([$admin, 'Pengumpulan Nilai oleh Guru', 'Bapak/Ibu guru diharapkan menginput nilai ke sistem paling lambat akhir bulan ini.']);
        $p->execute([$admin, 'Pemeliharaan Sistem', 'Backup database otomatis berjalan setiap pukul 02.00 WIB.']);
    }

    $pdo->commit();
    echo "Seed berhasil.\nAkun: admin/Admin#2026 | guru01,guru02/Guru#2026 | siswa01..siswa06/Siswa#2026\nSegera ganti password default!\n";
} catch (Throwable $t) {
    $pdo->rollBack();
    echo 'Gagal: ' . $t->getMessage() . "\n";
}
