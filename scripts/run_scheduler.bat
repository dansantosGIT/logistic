@echo off
REM ensure we run from the app folder
cd /d "C:\laragon\www\logistic"
REM ensure logs folder exists
if not exist "storage\logs" mkdir "storage\logs"

"C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" "artisan" schedule:run >> "storage\logs\schedule.log" 2>&1
