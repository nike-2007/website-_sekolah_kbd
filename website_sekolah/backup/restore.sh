#!/bin/bash
# restore.sh - pulihkan db_sekolah dari file backup
# Pemakaian: ./restore.sh /var/backups/sekolah/db_sekolah_20261109_020000.sql.gz
set -euo pipefail
FILE="${1:?Sebutkan file backup .sql.gz}"

echo "[1/3] Verifikasi checksum..."
sha256sum -c "$FILE.sha256"

echo "[2/3] Membuat ulang database..."
mysql -e "DROP DATABASE IF EXISTS db_sekolah; CREATE DATABASE db_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "[3/3] Memulihkan data..."
gunzip -c "$FILE" | mysql db_sekolah

echo "Selesai. Cek: mysql -e 'SELECT COUNT(*) FROM db_sekolah.users;'"
