@echo off
cd /d "%~dp0"

echo ================================
echo   🔄 Laravel Scheduler
echo   📡 Monitoring Service
echo ================================
echo.
echo 📌 Folder: %CD%
echo ⏱️  Schedule akan berjalan OTOMATIS setiap menit
echo 📡 Monitoring ESP akan berjalan setiap menit
echo.
echo ✅ Tekan Ctrl+C untuk berhenti
echo.

php artisan schedule:work

pause