#!/bin/bash
# scheduler.sh - ESP Monitor untuk Laragon & Server

cd /c/laragon/www/laravel_monitoring || exit

echo "=========================================="
echo "  🔥 ESP MONITOR STARTED"
echo "  📡 Running every 60 seconds"
echo "  🕐 Started: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="
echo ""
echo "✅ Press CTRL+C to stop"
echo ""

while true; do
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] 🔄 Running ESP check..."
    php artisan app:check-smoke-devices
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Done. Waiting 60 seconds..."
    echo ""
    sleep 60
done