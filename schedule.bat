@echo off
cd /d "%~dp0"

echo ================================
echo   🔄 Laravel Scheduler
echo   📡 Monitoring Service
echo ================================
echo.
echo 📌 Folder: %CD%
echo ⏱️  Schedule akan berjalan setiap 5 menit
echo.
echo ✅ Tekan Ctrl+C untuk berhenti
echo.

php artisan schedule:work

pause