# Website Sekolah — Penjelasan Proyek (PHP + MySQL)

Dokumen ini menjelaskan semua yang dikerjakan, mengikuti materi Pertemuan 7
(*Keamanan Transaksi dan Integritas Data*).

## 1. Struktur Proyek

```
website_sekolah/
├── database/sekolah.sql     → database + 8 tabel
├── config.php               → koneksi PDO, sesi, CSRF, audit
├── seed.php                 → membuat akun contoh (password di-hash)
├── index.php                → halaman beranda (pilih login Guru / Siswa)
├── register.php / login.php?as=guru|siswa|admin / logout.php
├── guru/                    → portal guru: index, siswa, nilai, jadwal
├── siswa/                   → portal siswa: index, nilai, jadwal
├── includes/                → auth.php (cek peran) + layout.php (tampilan bersama)
├── assets/portal.css        → desain portal (tema hijau=guru, biru=siswa)
├── dashboard.php            → dashboard sekolah (sidebar, statistik, pengumuman, nilai/siswa, audit log)
├── style.css
└── backup/ backup.sh · restore.sh · backup.bat
```

## 2. Cara Menjalankan (XAMPP / Laragon)

1. Salin folder ke `htdocs` (XAMPP) atau `www` (Laragon), nyalakan Apache + MySQL.
2. Impor database: buka phpMyAdmin → tab *Import* → pilih `database/sekolah.sql`
   (atau terminal: `mysql -u root < database/sekolah.sql`).
3. Jalankan sekali: `php seed.php`
   Akun contoh: `admin / Admin#2026`, `guru01 / Guru#2026`, `siswa01 / Siswa#2026`.
4. Buka `http://localhost/website_sekolah/login.php`.

## 3. Database & Tabel

| Tabel | Fungsi | Kunci penting |
|---|---|---|
| users | akun login + role | `username` UNIQUE, `password_hash` |
| kelas | data kelas | UNIQUE (nama_kelas, tahun_ajaran) |
| guru | profil guru | FK `id_user`, `nip` UNIQUE |
| siswa | profil siswa | FK `id_user`, FK `id_kelas`, `nis` UNIQUE |
| mapel | mata pelajaran | `kode_mapel` UNIQUE |
| nilai | nilai siswa per mapel/semester | FK siswa, mapel, guru |
| pengumuman | berita sekolah | FK `id_user` |
| audit_log | jejak aktivitas (Audit Trail) | index user & waktu |

Relasi utama: `users 1—1 siswa/guru`, `kelas 1—N siswa`, `siswa N—N mapel` (lewat `nilai`).

### Normalisasi (untuk sesi berikutnya)
Struktur akhir sudah **3NF**. Saat Anda minta contoh, kita buat tabel awal "berantakan"
(satu tabel nilai berisi nama siswa, kelas, wali kelas, mapel berulang) lalu diturunkan:
- **1NF**: tiap sel satu nilai (tidak ada daftar mapel dalam satu kolom), ada primary key.
- **2NF**: tidak ada ketergantungan parsial pada sebagian primary key komposit
  (mis. `nama_mapel` hanya bergantung ke `kode_mapel` → pindah ke tabel `mapel`).
- **3NF**: tidak ada ketergantungan transitif (mis. `nama_kelas` bergantung ke `id_kelas`,
  bukan ke `id_siswa` → pindah ke tabel `kelas`).

## 4. Hashing Password

- Password **tidak pernah disimpan asli**. `password_hash($password, PASSWORD_DEFAULT)`
  menghasilkan hash bcrypt + salt acak otomatis (±60 karakter, makanya kolom `VARCHAR(255)`).
- Saat login, `password_verify()` mencocokkan input dengan hash.
- `password_needs_rehash()` meng-upgrade hash otomatis jika algoritma PHP diperbarui.
- Hashing ≠ enkripsi: hash **satu arah**, tidak bisa dikembalikan. Di slide, MD5/SHA-256
  dipakai untuk *checksum file*; untuk **password** gunakan bcrypt/Argon2 (lambat sengaja,
  tahan brute force). MD5 dan SHA-256 polos tidak cocok untuk password.

## 5. SQL Injection

**Apa itu?** Penyerang menyisipkan perintah SQL lewat input (form/URL) sehingga
query berubah dari yang dimaksud programmer.

**Kode RENTAN (jangan dipakai):**
```php
$q = "SELECT * FROM users WHERE username='$u' AND password='$p'";
```
Jika `$u` diisi `admin' -- ` , sisa query jadi komentar dan login tanpa password.

**Kode AMAN (dipakai di `login.php`):**
```php
$st = $pdo->prepare('SELECT ... FROM users WHERE username = ?');
$st->execute([$username]);
```
Query dan data dikirim **terpisah**; input dianggap data, bukan perintah SQL.

Lapisan pertahanan lain di proyek ini: `ATTR_EMULATE_PREPARES=false`, validasi input
(regex di register), `htmlspecialchars` via `e()` (anti XSS), token CSRF, `session_regenerate_id`,
pesan error generik, dan user database dengan hak terbatas.

## 6. Mapping ke Materi Pertemuan 7

| Materi | Implementasi di proyek |
|---|---|
| Enkripsi / protokol | Pasang HTTPS (SSL/TLS) di server; cookie `httponly` + `samesite` |
| Autentikasi | login + role; tambahkan MFA sebagai pengembangan |
| Integritas data | FK, UNIQUE, CHECK nilai 0–100, **transaksi** di register, checksum SHA-256 backup |
| Audit trail | tabel `audit_log` (login sukses/gagal, register, logout) |
| Kebijakan backup | `backup.sh` harian + retensi 14 hari + offsite |
| Pencegahan penipuan | pencatatan login gagal (bisa dikembangkan jadi pembatasan percobaan) |

**Transaksi (atomicity)** di `register.php`: `beginTransaction` → insert `users` → insert `siswa`
→ `commit`. Jika salah satu gagal → `rollBack`, tidak ada data setengah jadi.

## 7. Cara Backup & Pemulihan Data (Tutorial)

### A. Membuat backup
```bash
mysqldump -u root -p --single-transaction --routines --triggers db_sekolah > backup.sql
```
Penjelasan: `--single-transaction` = salinan konsisten tanpa mengunci tabel InnoDB.
Atau jalankan `backup/backup.sh` (Linux) / `backup/backup.bat` (Windows).
Lewat phpMyAdmin: pilih database → *Export* → *Quick* → format SQL → *Go*.

### B. Memulihkan (Restore) penuh
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS db_sekolah CHARACTER SET utf8mb4"
mysql -u root -p db_sekolah < backup.sql
```
Atau pakai `backup/restore.sh file.sql.gz` (otomatis cek checksum dulu).
Lewat phpMyAdmin: pilih database → *Import* → pilih file `.sql`.

### C. Latihan simulasi bencana (bagus untuk demo presentasi)
1. `SELECT COUNT(*) FROM siswa;` → catat jumlahnya.
2. Backup: jalankan `mysqldump` seperti di atas.
3. "Bencana": `DELETE FROM siswa;` (atau `DROP TABLE nilai;`).
4. Restore dari file backup (langkah B).
5. `SELECT COUNT(*) FROM siswa;` → jumlah kembali sama. Pemulihan berhasil.

### D. Point-in-Time Recovery (PITR)
Backup harian saja bisa kehilangan data hingga 24 jam. Dengan **binary log**:
1. Aktifkan di `my.cnf`: `log_bin=mysql-bin`, `binlog_expire_logs_seconds=1209600`.
2. Restore backup terakhir (jam 02:00).
3. Putar ulang transaksi sampai sesaat sebelum kesalahan:
```bash
mysqlbinlog --start-datetime="2026-11-09 02:00:00" \
            --stop-datetime="2026-11-09 10:14:59" mysql-bin.000012 | mysql -u root -p
```

### E. Hubungan dengan teknik di slide
- **Full** (seluruh data) · **Incremental** (perubahan sejak backup terakhir) ·
  **Differential** (perubahan sejak full terakhir) · **Mirror/Replikasi** · **Cloud**.
- Pemulihan: **Cold site** (murah, lambat) · **Warm site** · **Hot site** (cepat, mahal) ·
  **Point-in-Time** · **DRP** (dokumen rencana pemulihan).

## 8. Saran Teknis Pemulihan Data untuk Website Sekolah

Konteks: data kecil–menengah (ratusan–ribuan siswa), anggaran terbatas, ada jam sepi (malam).

1. **Strategi backup (rekomendasi utama)**
   - **Full backup tiap malam** (02:00). Database sekolah kecil, jadi full backup cepat dan sederhana;
     tidak perlu incremental yang rumit.
   - **Binary log aktif** → bisa PITR, kehilangan data mendekati nol (RPO ≈ menit).
   - **Full backup mingguan + bulanan** diarsipkan lebih lama (mis. harian 14 hari, mingguan 8 minggu,
     bulanan 12 bulan). Wajib sebelum **kenaikan kelas / input rapor / akhir semester**.
2. **Aturan 3-2-1**: 3 salinan data, 2 jenis media berbeda, 1 salinan **offsite**
   (cloud seperti Google Drive/Backblaze via `rclone`, atau server lain).
3. **Enkripsi backup** sebelum dikirim offsite, karena berisi data pribadi siswa:
   `gpg --symmetric --cipher-algo AES256 file.sql.gz`.
4. **Verifikasi integritas**: simpan checksum SHA-256 (sudah ada di `backup.sh`) dan cek sebelum restore.
5. **Uji restore tiap bulan** di server percobaan. Backup yang tidak pernah diuji = belum tentu bisa dipakai.
6. **Target pemulihan (isi di DRP)**: RTO ≤ 2–4 jam (waktu sampai website hidup lagi),
   RPO ≤ 15 menit–24 jam sesuai kebijakan. Untuk sekolah, **warm/cold site** (VPS cadangan
   atau rencana deploy ulang cepat) sudah memadai; hot site biasanya berlebihan.
7. **Pisahkan hak akses**: buat user MySQL khusus backup (`SELECT, LOCK TABLES, SHOW VIEW, TRIGGER, EVENT`)
   dan user aplikasi (`SELECT, INSERT, UPDATE, DELETE` saja, bukan `root`).
8. **Backup juga file aplikasi** (kode PHP, folder upload, `config.php`) — gunakan Git untuk kode
   (ini penerapan *Kontrol Versi* di slide).
9. **Monitoring**: kirim email/notifikasi bila cron backup gagal; cek ukuran file backup tidak 0 KB.
10. **Dokumentasikan** prosedur (siapa, langkah, lokasi backup, kontak) sebagai Disaster Recovery Plan.

## 9. Usulan Alur Presentasi
1. Latar belakang & tujuan website sekolah
2. Desain database + relasi (ERD)
3. Normalisasi 1NF → 2NF → 3NF (contoh akan dibuat berikutnya)
4. Keamanan: hashing password, SQL injection (demo rentan vs aman), CSRF/XSS, audit log
5. Integritas data: constraint, transaksi, checksum
6. Backup & pemulihan: demo simulasi bencana (bagian 7C)
7. Rekomendasi teknis & penutup

## 10. Catatan Penting
- Ganti password default dan `DB_USER`/`DB_PASS` di `config.php` sebelum dipakai nyata.
- Aktifkan HTTPS di server produksi.
- Proyek ini fondasi untuk demo/pembelajaran; belum mencakup halaman input nilai, manajemen kelas,
  pembatasan percobaan login, atau MFA — bisa ditambahkan bertahap.

## 11. Tampilan Dashboard
Dashboard menyesuaikan **peran (role)** pengguna:
- **Siswa**: kartu rata-rata nilai, kelas & NIS, tabel nilai per mata pelajaran, pengumuman.
- **Guru**: statistik sekolah, tabel siswa terbaru, pengumuman.
- **Admin**: semua di atas + panel *Audit Trail* (aktivitas login/logout/registrasi terbaru).
Elemen: sidebar navigasi, topbar dengan tanggal dan avatar, banner sapaan (pagi/siang/sore/malam),
4 kartu statistik (siswa, guru, kelas, mapel), dan tampilan responsif untuk HP.
Menu selain Dashboard masih placeholder (`href="#"`) sebagai pengembangan berikutnya.

## 12. Portal Guru & Siswa (Pembaruan)
- **Beranda (`index.php`)**: dua kartu besar untuk memilih *Login Guru* atau *Login Siswa*, statistik sekolah, fitur, dan pengumuman.
- **Login berbasis peran**: `login.php?as=guru|siswa|admin`. Akun siswa tidak bisa masuk lewat halaman guru (dan sebaliknya); setelah login pengguna diarahkan ke dashboard sesuai peran.
- **Folder `guru/`**: Dashboard (statistik kelas/mapel/siswa, jadwal hari ini, grafik rata-rata nilai, nilai terbaru, pengumuman), Data Siswa (cari + filter kelas), **Input Nilai** (memakai *transaksi* + validasi 0-100 + audit log), Jadwal Mengajar.
- **Folder `siswa/`**: Dashboard (rata-rata & predikat, jadwal hari ini, grafik nilai, kartu pelajar, pengumuman), Nilai & Rapor per semester (predikat A-D, tuntas/remedial), Jadwal Pelajaran mingguan.
- **Database**: tabel baru `jadwal` (sudah ada di `sekolah.sql`; jika database lama sudah ada, jalankan `database/tambahan_jadwal.sql`).
- **Akun contoh** (`php seed.php`, boleh dijalankan ulang): `admin/Admin#2026`, `guru01` & `guru02` / `Guru#2026`, `siswa01`-`siswa06` / `Siswa#2026`.
- **Keamanan**: setiap halaman portal memanggil `require_role()` sehingga akses tanpa login atau dengan peran salah otomatis dialihkan.
"# website-_sekolah_kbd" 
