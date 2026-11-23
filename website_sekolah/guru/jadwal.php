<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/layout.php';
$g = require_role('guru');
$st = $pdo->prepare('SELECT j.hari, j.jam_mulai, j.jam_selesai, j.ruang, m.nama_mapel, CONCAT("Kelas ", k.nama_kelas) AS ket
    FROM jadwal j JOIN mapel m ON m.id_mapel = j.id_mapel JOIN kelas k ON k.id_kelas = j.id_kelas
    WHERE j.id_guru = ? ORDER BY j.jam_mulai');
$st->execute([(int)$g['id_guru']]);
page_start('Jadwal Mengajar', 'jadwal', $g);
render_jadwal($st->fetchAll());
page_end();
