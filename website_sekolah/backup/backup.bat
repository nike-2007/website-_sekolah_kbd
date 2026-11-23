@echo off
setlocal

REM === Cari mysqldump Laragon otomatis (versi apa pun) ===
set MYSQLBIN=
for /d %%D in ("C:\laragon\bin\mysql\*") do (
  if exist "%%D\bin\mysqldump.exe" set MYSQLBIN=%%D\bin
)
if "%MYSQLBIN%"=="" (
  echo [%date% %time%] mysqldump tidak ditemukan >> "%~dp0backup.log"
  exit /b 1
)

REM === Lokasi hasil backup: folder "hasil" di samping script ===
set BACKUPDIR=%~dp0hasil
if not exist "%BACKUPDIR%" mkdir "%BACKUPDIR%"

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmmss"') do set TS=%%i
set FILE=%BACKUPDIR%\db_sekolah_%TS%.sql

REM === Backup ===
"%MYSQLBIN%\mysqldump.exe" -u root --single-transaction --routines --triggers db_sekolah > "%FILE%"
if errorlevel 1 (
  echo [%date% %time%] GAGAL backup, pastikan MySQL Laragon menyala >> "%~dp0backup.log"
  del "%FILE%" 2>nul
  exit /b 1
)

REM === Checksum SHA-256 (bukti file tidak rusak) ===
certutil -hashfile "%FILE%" SHA256 > "%FILE%.sha256.txt"

REM === Hapus backup lebih dari 14 hari ===
forfiles /p "%BACKUPDIR%" /m "db_sekolah_*" /d -14 /c "cmd /c del @path" 2>nul

echo [%date% %time%] Backup berhasil: %FILE% >> "%~dp0backup.log"
exit /b 0