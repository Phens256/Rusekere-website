@echo off
REM Starts the site with PHP's built-in server (needs PHP in PATH, or use XAMPP's php.exe).
REM MySQL must be running and database\rusekere_school.sql must be imported first.
cd /d "%~dp0"
echo Open http://localhost:8000/setup-check.php to verify, then http://localhost:8000/
php -S localhost:8000
pause
