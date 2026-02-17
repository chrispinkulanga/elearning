@echo off
REM Laravel Octane Windows Service Script
REM This script provides Windows-compatible commands for Laravel Octane

echo Starting Laravel Octane for Windows...

REM Check if Octane is installed
php artisan octane:install --server=swoole

REM Start Octane server with Windows-optimized settings
php artisan octane:start --server=swoole --host=127.0.0.1 --port=8000 --workers=4 --max-requests=1000

pause
