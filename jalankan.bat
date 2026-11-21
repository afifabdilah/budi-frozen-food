@echo off
rem ====================================================================
rem  BUDI FROZEN - Jalankan aplikasi dengan PHP built-in server
rem  MySQL XAMPP akan dinyalakan otomatis jika belum berjalan.
rem ====================================================================
setlocal
set "PHP=C:\xampp\php\php.exe"
set "MYSQLD=C:\xampp\mysql\bin\mysqld.exe"
set "MYINI=C:\xampp\mysql\bin\my.ini"

if not exist "%PHP%" (
    echo [X] PHP tidak ditemukan di %PHP%
    echo     Pastikan XAMPP sudah terpasang di C:\xampp
    pause
    exit /b 1
)

netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul
if errorlevel 1 (
    echo [*] MySQL belum berjalan, mencoba menyalakan...
    start "" "%MYSQLD%" --defaults-file="%MYINI%" --standalone
    timeout /t 6 /nobreak >nul
)

echo.
echo  BUDI FROZEN siap diakses di: http://localhost:8000
echo  Tekan Ctrl+C untuk menghentikan server.
echo.

start "" "http://localhost:8000"
"%PHP%" -S localhost:8000 -t "%~dp0"
