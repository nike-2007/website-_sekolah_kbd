#!/bin/bash
# backup.sh - backup harian db_sekolah (Linux). Jadwalkan dengan cron.
# Kredensial disimpan di ~/.my.cnf (chmod 600), BUKAN di script:
#   [client]
#   user=backup_user
#   password=PASSWORD_KUAT
set -euo pipefail
DB="db_sekolah"
DIR="/var/backups/sekolah"
TS=$(date +%Y%m%d_%H%M%S)
FILE="$DIR/${DB}_${TS}.sql.gz"
mkdir -p "$DIR"

# --single-transaction : backup konsisten tanpa mengunci tabel (InnoDB)
# --routines --triggers: ikut menyimpan stored procedure & trigger
# --flush-logs         : memulai binary log baru (dipakai untuk Point-in-Time Recovery)
mysqldump --single-transaction --routines --triggers --flush-logs "$DB" | gzip > "$FILE"

# Checksum SHA-256 -> membuktikan file backup tidak rusak/berubah (Integritas Data)
sha256sum "$FILE" > "$FILE.sha256"

# Hapus backup lebih dari 14 hari
find "$DIR" -name "${DB}_*.sql.gz*" -mtime +14 -delete

# OFFSITE (aktifkan salah satu, mis. server lain / cloud):
# rsync -az "$DIR"/ user@server-backup:/backup/sekolah/
# rclone copy "$DIR" remote:backup-sekolah

echo "Backup selesai: $FILE"
# Cron (setiap hari 02:00):  0 2 * * * /opt/sekolah/backup/backup.sh >> /var/log/backup_sekolah.log 2>&1
