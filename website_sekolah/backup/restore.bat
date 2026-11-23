@echo off
setlocal EnableDelayedExpansion

REM === Cari mysql.exe Laragon otomatis ===
set MYSQLBIN=
for /d %%D in ("C:\laragon\bin\mysql\*") do (
  if exist "%%D\bin\mysql.exe" set MYSQLBIN=%%D\bin
)
if "%MYSQLBIN%"=="" (
  echo mysql.exe tidak ditemukan. Pastikan Laragon terpasang di C:\laragon
  pause & exit /b 1
)

set FILE=%~1
if "%FILE%"=="" (
  echo Pemakaian: restore.bat "hasil\db_sekolah_xxx.sql"
  pause & exit /b 1
)
if not exist "%FILE%" (
  echo File tidak ditemukan: %FILE%
  pause & exit /b 1
)

echo [1/3] Verifikasi checksum...
if not exist "%FILE%.sha256.txt" (
  echo File checksum tidak ditemukan, lanjut tanpa verifikasi.
  goto :tanya
)

REM === Bandingkan hash via PowerShell (paling andal) ===
powershell -NoProfile -Command ^
  "$f='%FILE%'; $s=$f+'.sha256.txt';" ^
  "$h1=(Get-FileHash -Algorithm SHA256 -LiteralPath $f).Hash.ToLower();" ^
  "$h2=(Get-Content -LiteralPath $s | Where-Object { $_ -match '^[0-9a-fA-F ]{64,}$' } | Select-Object -First 1) -replace ' ','';" ^
  "$h2=$h2.ToLower();" ^
  "if ($h1 -eq $h2) { exit 0 } else { Write-Host ('  File : '+$h1); Write-Host ('  SHA  : '+$h2); exit 1 }"

if errorlevel 1 (
  echo CHECKSUM TIDAK COCOK! File backup rusak atau berubah. Restore dibatalkan.
  pause & exit /b 1
)
echo Checksum cocok.

:tanya
echo.
echo PERINGATAN: database db_sekolah akan DIHAPUS lalu diganti isi file ini.
set /p OK=Lanjut? (y/n): 
if /i not "%OK%"=="y" exit /b 0

echo [2/3] Membuat ulang database...
"%MYSQLBIN%\mysql.exe" -u root -e "DROP DATABASE IF EXISTS db_sekolah; CREATE DATABASE db_sekolah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo [3/3] Memulihkan data...
"%MYSQLBIN%\mysql.exe" -u root db_sekolah < "%FILE%"

echo.
echo Selesai. Jumlah user di database:
"%MYSQLBIN%\mysql.exe" -u root -e "SELECT COUNT(*) FROM db_sekolah.users;"
pause