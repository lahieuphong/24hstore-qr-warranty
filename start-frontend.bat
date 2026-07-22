@echo off
setlocal

title 24hStore QR Warranty
echo Trang tra cuu da duoc tich hop vao backend: http://127.0.0.1:8000/check
echo Dang khoi dong backend tren cong 8000...
echo.

call "%~dp0start-backend.bat"
set "EXIT_CODE=%ERRORLEVEL%"

exit /b %EXIT_CODE%
