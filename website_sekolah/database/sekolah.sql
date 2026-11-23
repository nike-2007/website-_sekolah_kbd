-- =====================================================
-- DATABASE WEBSITE SEKOLAH (db_sekolah)
-- Struktur sudah dinormalisasi sampai 3NF
-- =====================================================
CREATE DATABASE IF NOT EXISTS db_sekolah
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_sekolah;

-- 1. USERS: akun login (password disimpan dalam bentuk HASH, bukan teks asli)
CREATE TABLE users (
  id_user       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','guru','siswa') NOT NULL DEFAULT 'siswa',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login    DATETIME NULL
) ENGINE=InnoDB;

-- 2. KELAS
CREATE TABLE kelas (
  id_kelas     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_kelas   VARCHAR(20) NOT NULL,
  tingkat      TINYINT UNSIGNED NOT NULL,
  tahun_ajaran VARCHAR(9) NOT NULL,           -- contoh: 2026/2027
  UNIQUE KEY uq_kelas (nama_kelas, tahun_ajaran)
) ENGINE=InnoDB;

-- 3. GURU
CREATE TABLE guru (
  id_guru INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  nip     VARCHAR(20) NOT NULL UNIQUE,
  nama    VARCHAR(100) NOT NULL,
  email   VARCHAR(100) NULL,
  telepon VARCHAR(20) NULL,
  CONSTRAINT fk_guru_user FOREIGN KEY (id_user) REFERENCES users(id_user)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. SISWA
CREATE TABLE siswa (
  id_siswa      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user       INT UNSIGNED NOT NULL UNIQUE,
  id_kelas      INT UNSIGNED NULL,
  nis           VARCHAR(20) NOT NULL UNIQUE,
  nama          VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NULL,
  tanggal_lahir DATE NULL,
  alamat        VARCHAR(255) NULL,
  CONSTRAINT fk_siswa_user  FOREIGN KEY (id_user)  REFERENCES users(id_user) ON DELETE CASCADE,
  CONSTRAINT fk_siswa_kelas FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. MATA PELAJARAN
CREATE TABLE mapel (
  id_mapel   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_mapel VARCHAR(10) NOT NULL UNIQUE,
  nama_mapel VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 6. NILAI (relasi siswa - mapel - guru)
CREATE TABLE nilai (
  id_nilai    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_siswa    INT UNSIGNED NOT NULL,
  id_mapel    INT UNSIGNED NOT NULL,
  id_guru     INT UNSIGNED NOT NULL,
  semester    TINYINT UNSIGNED NOT NULL,
  nilai_angka DECIMAL(5,2) NOT NULL CHECK (nilai_angka BETWEEN 0 AND 100),
  UNIQUE KEY uq_nilai (id_siswa, id_mapel, semester),
  FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa) ON DELETE CASCADE,
  FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel),
  FOREIGN KEY (id_guru)  REFERENCES guru(id_guru)
) ENGINE=InnoDB;

-- 7. PENGUMUMAN
CREATE TABLE pengumuman (
  id_pengumuman INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user       INT UNSIGNED NOT NULL,
  judul         VARCHAR(150) NOT NULL,
  isi           TEXT NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user)
) ENGINE=InnoDB;

-- 8. AUDIT LOG (jejak aktivitas - komponen "Audit Trail")
CREATE TABLE audit_log (
  id_log   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user  INT UNSIGNED NULL,
  aksi     VARCHAR(100) NOT NULL,
  ip_addr  VARCHAR(45) NULL,
  waktu    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_log_user (id_user),
  INDEX idx_log_waktu (waktu)
) ENGINE=InnoDB;

-- DATA CONTOH (non-user; akun dibuat lewat seed.php agar password ter-hash)
INSERT INTO kelas (nama_kelas, tingkat, tahun_ajaran) VALUES
  ('X IPA 1', 10, '2026/2027'), ('X IPS 1', 10, '2026/2027'), ('XI IPA 1', 11, '2026/2027');
INSERT INTO mapel (kode_mapel, nama_mapel) VALUES
  ('MTK','Matematika'), ('BIN','Bahasa Indonesia'), ('BIG','Bahasa Inggris'), ('INF','Informatika');

-- 9. JADWAL PELAJARAN
CREATE TABLE jadwal (
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
