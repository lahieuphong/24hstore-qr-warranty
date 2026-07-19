@echo off
setlocal

title 24hStore QR Warranty - Backend
cd /d "%~dp0backend"

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP was not found in PATH.
    echo Install PHP and reopen this window before trying again.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [ERROR] backend\.env does not exist.
    echo Create it from backend\.env.example and configure APP_KEY and DB_* first.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERROR] Backend PHP dependencies are missing.
    echo Run: cd backend ^&^& composer install
    pause
    exit /b 1
)

if not exist "public\build\manifest.json" (
    echo [ERROR] Backend assets have not been built.
    echo Run: cd backend ^&^& yarn install --frozen-lockfile ^&^& yarn build
    pause
    exit /b 1
)

echo Starting the backend at http://127.0.0.1:8000
echo Admin: http://127.0.0.1:8000/admin
echo API:   http://127.0.0.1:8000/api/v1
echo Press Ctrl+C to stop the server.
echo.

php artisan serve --host=127.0.0.1 --port=8000
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
    echo.
    echo [ERROR] The backend stopped with exit code %EXIT_CODE%.
    pause
)

exit /b %EXIT_CODE%
