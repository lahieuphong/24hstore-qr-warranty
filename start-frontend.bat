@echo off
setlocal

title 24hStore QR Warranty - Frontend
cd /d "%~dp0frontend"

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP was not found in PATH.
    echo Install PHP and reopen this window before trying again.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [ERROR] frontend\.env does not exist.
    echo Create it from frontend\.env.example and configure APP_KEY and BACKEND_API_URL first.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERROR] Frontend PHP dependencies are missing.
    echo Run: cd frontend ^&^& composer install
    pause
    exit /b 1
)

if not exist "public\build\manifest.json" (
    echo [ERROR] Frontend assets have not been built.
    echo Run: cd frontend ^&^& yarn install --frozen-lockfile ^&^& yarn build
    pause
    exit /b 1
)

echo Starting the public frontend at http://localhost:8001
echo The backend must also be running at http://127.0.0.1:8000
echo Press Ctrl+C to stop the server.
echo.

php artisan serve --host=127.0.0.1 --port=8001
set "EXIT_CODE=%ERRORLEVEL%"

if not "%EXIT_CODE%"=="0" (
    echo.
    echo [ERROR] The frontend stopped with exit code %EXIT_CODE%.
    pause
)

exit /b %EXIT_CODE%
