@echo off
echo Starting Smart Campus CRM on http://localhost:8000 ...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo Starting local MySQL engine...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe" --port=3307 --standalone
    timeout /t 2 /nobreak >nul
)
"C:\xampp\php\php.exe" -S localhost:8000 -t "%~dp0."
