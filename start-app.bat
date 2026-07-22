@echo off
setlocal

title 24hStore QR Warranty
cd /d "%~dp0"

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP was not found in PATH.
    echo Install PHP and reopen this window before trying again.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [ERROR] .env does not exist.
    echo Create .env and configure APP_KEY and DB_* first.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERROR] PHP dependencies are missing.
    echo Run: composer install
    pause
    exit /b 1
)

if not exist "public\build\manifest.json" (
    echo [ERROR] Application assets have not been built.
    echo Run: yarn install --frozen-lockfile ^&^& yarn build
    pause
    exit /b 1
)

echo Starting the application at http://127.0.0.1:8000
echo Admin: http://127.0.0.1:8000/admin
echo Tra cuu: http://127.0.0.1:8000/check
echo API:   http://127.0.0.1:8000/api/v1
echo Press Ctrl+C to stop the server.
echo.

set "PHP_CLI_SERVER_WORKERS="

php artisan serve --host=127.0.0.1 --port=8000
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
    echo.
    echo [ERROR] The application stopped with exit code %EXIT_CODE%.
    pause
)

exit /b %EXIT_CODE%
