-- Jalankan file ini jika database db_sekolah SUDAH terlanjur dibuat sebelumnya
USE db_sekolah;
CREATE TABLE IF NOT EXISTS jadwal (
  id_jadwal   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kelas    INT UNSIGNED NOT NULL,
  id_mapel    INT UNSIGNED NOT NULL,
  id_guru     INT UNSIGNED NOT NULL,
  hari        ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  jam_mulai   TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  ruang       VARCHAR(30) NOT NULL,
  FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas) ON DELETE CASCADE,
  FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel),
  FOREIGN KEY (id_guru)  REFERENCES guru(id_guru),
  INDEX idx_jadwal_hari (hari)
) ENGINE=InnoDB;
