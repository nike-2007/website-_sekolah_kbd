<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$s = require_role('siswa');
$st = $pdo->prepare('SELECT j.hari, j.jam_mulai, j.jam_selesai, j.ruang, m.nama_mapel, g.nama AS ket
    FROM jadwal j JOIN mapel m ON m.id_mapel = j.id_mapel JOIN guru g ON g.id_guru = j.id_guru
    WHERE j.id_kelas = ? ORDER BY j.jam_mulai');
$st->execute([$s['id_kelas']]);
page_start('Jadwal Pelajaran', 'jadwal', $s);
render_jadwal($st->fetchAll());
page_end();
