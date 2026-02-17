@echo off
REM Start Laravel Octane Server with Windows Optimizations
REM This script starts the server with all performance optimizations

echo Starting Laravel Octane Server...
echo.

REM Set environment variables for Windows optimization
set OCTANE_SERVER=swoole
set OCTANE_HOST=127.0.0.1
set OCTANE_PORT=8000
set OCTANE_WORKERS=4
set OCTANE_MAX_REQUESTS=1000

REM Start Octane server
php artisan octane:start --server=%OCTANE_SERVER% --host=%OCTANE_HOST% --port=%OCTANE_PORT% --workers=%OCTANE_WORKERS% --max-requests=%OCTANE_MAX_REQUESTS%

pause
